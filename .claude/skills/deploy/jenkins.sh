#!/usr/bin/env bash
# Talks to the platform-api job on Jenkins. Credentials come from ~/.config/jenkins/publiq.env
# (JENKINS_URL, JENKINS_USER, JENKINS_TOKEN) so the token never appears in a command line or transcript.
set -euo pipefail

ENV_FILE="${JENKINS_ENV_FILE:-$HOME/.config/jenkins/publiq.env}"
if [ ! -f "$ENV_FILE" ]; then
  echo "Missing $ENV_FILE with JENKINS_URL, JENKINS_USER and JENKINS_TOKEN." >&2
  exit 2
fi
# shellcheck source=/dev/null
. "$ENV_FILE"

JOB="$JENKINS_URL/job/platform-api"

api() {
  # Credentials go through stdin, because curl arguments are visible to every process on the machine.
  printf 'user = "%s:%s"\n' "$JENKINS_USER" "$JENKINS_TOKEN" | curl -sSf -g -K - "$@"
}

run_number() {
  if ! [[ "${1:-}" =~ ^[0-9]+$ ]]; then
    echo "Expected a run number, got '${1:-}'." >&2
    exit 2
  fi
  echo "$1"
}

case "${1:-}" in
  runs)
    # Latest runs with the publiq-platform commit each one built.
    for n in $(api "$JOB/api/json?tree=builds[number]{0,$(run_number "${2:-5}")}" | jq -r '.builds[].number'); do
      sha=$(api "$JOB/$n/api/json?tree=actions[remoteUrls,lastBuiltRevision[SHA1]]" \
        | jq -r '[.actions[] | select(.remoteUrls) | select(any(.remoteUrls[]; test("publiq-platform"))) | .lastBuiltRevision.SHA1][0] // "?"')
      api "$JOB/$n/wfapi/describe" | jq -r --arg sha "$sha" '"#\(.id) \(.name) \(.status) commit=\($sha[0:8])"'
    done
    ;;
  status)
    n=$(run_number "${2:-}")
    api "$JOB/$n/wfapi/describe" \
      | jq -r '"#\(.id) \(.name) \(.status)", (.stages[] | "  \(.name): \(.status)")'
    api "$JOB/$n/wfapi/pendingInputActions" | jq -r '.[] | "  waiting: \(.message) (input \(.id))"'
    ;;
  start)
    # Starts a new run, which always builds the newest main, and prints its number once it leaves the queue.
    queue=$(api -X POST -D - -o /dev/null "$JOB/build" | tr -d '\r' | sed -n 's/^[Ll]ocation: //p')
    if [ -z "$queue" ]; then
      echo "Jenkins did not return a queue item." >&2
      exit 1
    fi
    while :; do
      n=$(api "${queue%/}/api/json" | jq -r '.executable.number // empty')
      if [ -n "$n" ]; then
        echo "Started #$n."
        break
      fi
      sleep 5
    done
    ;;
  proceed)
    # Approves the one pending input of a run, e.g. "Deploy to Testing?".
    n=$(run_number "${2:-}")
    id=$(api "$JOB/$n/wfapi/pendingInputActions" | jq -r '.[0].id // empty')
    if [ -z "$id" ]; then
      echo "Run #$n is not waiting for input." >&2
      exit 1
    fi
    api -X POST "$JOB/$n/input/$id/proceedEmpty" > /dev/null
    echo "Approved input $id on #$n."
    ;;
  wait)
    # Blocks until the run finishes or stops at the next input, then prints its status.
    # Gives up after an hour, the pipeline's own timeout.
    n=$(run_number "${2:-}")
    deadline=$((SECONDS + 3600))
    while [ "$SECONDS" -lt "$deadline" ]; do
      case "$(api "$JOB/$n/wfapi/describe" | jq -r .status)" in
        IN_PROGRESS|NOT_EXECUTED) sleep 20 ;;
        *) break ;;
      esac
    done
    "$0" status "$n"
    ;;
  log)
    n=$(run_number "${2:-}")
    api "$JOB/$n/consoleText" | tail -n "$(run_number "${3:-80}")"
    ;;
  *)
    echo "Usage: $0 runs [count] | start | status <run> | proceed <run> | wait <run> | log <run> [lines]" >&2
    exit 2
    ;;
esac
