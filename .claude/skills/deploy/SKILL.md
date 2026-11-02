---
name: deploy
description: Deploy publiq-platform by starting the platform-api pipeline on Jenkins, approving its testing and production steps, and following the run until it is done. Use whenever asked to deploy, roll out, push to testing or production, or check how the deploy pipeline is doing.
---

# Deploying publiq-platform

The `platform-api` job on Jenkins only runs when someone starts it, merging to `main` does not. A run builds the newest `main`, deploys to development and acceptance, runs the acceptance tests, then waits at `Deploy to Testing?` and after that at `Deploy to Production?`.

All Jenkins calls go through `jenkins.sh` next to this file. It reads the credentials from `~/.config/jenkins/publiq.env`. Never print, echo or read that file yourself. When the script reports it missing, tell the user to create it with `JENKINS_URL=https://jenkins.publiq.be`, `JENKINS_USER` and `JENKINS_TOKEN` (an API token from their Jenkins profile, under Security), and to `chmod 600` it.

```
.claude/skills/deploy/jenkins.sh runs 5        # latest runs with the commit they built
.claude/skills/deploy/jenkins.sh start         # start a new run of the newest main, prints its number
.claude/skills/deploy/jenkins.sh status <run>  # stages, and the input it waits for
.claude/skills/deploy/jenkins.sh proceed <run> # approve the waiting input
.claude/skills/deploy/jenkins.sh wait <run>    # block until done or waiting again
.claude/skills/deploy/jenkins.sh log <run>     # tail of the console log
```

## Getting a run

The commit to deploy is the target of the newest release (`gh release list -L 1`, then `git rev-parse <tag>`), unless the user names another.

Look with `runs` for a run that built that commit and has not finished. When there is one, use it, and `wait` on it when it is still building.

Otherwise a new run is needed. A run always builds the newest `main`, so check that `origin/main` is the commit to deploy. When it is not, say which PRs were merged after it and stop, because they would be deployed too. When it is, ask before running `start`, then `wait` on the new run in the background until it stops at `Deploy to Testing?`.

## Deploying

Show the run, its commit and its stages. When the acceptance tests stage did not succeed, stop and say so.

Deploy one environment at a time:

1. Ask before approving `Deploy to Testing?`. Then `proceed`, and run `wait` in the background so you are told when it stops.
2. Report the result. Ask again before approving `Deploy to Production?`, a yes for testing never counts for production.
3. `proceed`, `wait`, and report the final status.

When a stage fails, show the last lines of `log` and stop. Never retry or rebuild on your own.

End with the run's URL, `https://jenkins.publiq.be/job/platform-api/<run>/`.
