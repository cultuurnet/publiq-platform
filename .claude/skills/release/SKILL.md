---
name: release
description: Draft a publiq-platform GitHub release tagged with the current date and time, with release notes grouped into Fixed, Added, Changed, Internal and Dependabot. Use whenever asked to create, draft or prepare a release.
---

# Drafting a release

## Tag

The tag and the release title are the current local date and time, with the seconds always `00`:

```
date +%Y.%m.%d.%H%M00
```

So `2026.10.26.095100`. The target is `main`.

## Collecting the PRs

Take the previous tag from `gh release list -L 1 --json tagName --jq '.[0].tagName'`, then let GitHub list every PR merged since then:

```
gh api repos/cultuurnet/publiq-platform/releases/generate-notes -f tag_name=<new tag> -f target_commitish=main -f previous_tag_name=<previous tag> --jq .body
```

Keep only the `* <title> by @<author> in <url>` lines, exactly as GitHub wrote them. Drop the `## What's Changed`, `## New Contributors` and `**Full Changelog**` parts.

When a title alone does not make the group clear, read the PR with `gh pr view <number>`.

## Groups

Use these `###` headings in this order, and leave out any group that is empty:

- **Fixed** for bugs that are solved, behaviour that was wrong and now is right.
- **Added** for something new a user or integrator can see or use.
- **Changed** for existing behaviour that now works differently on purpose.
- **Internal** for everything users do not notice: CI, deployment, tooling, tests, refactoring, docs, and dependency upgrades done by a person.
- **Dependabot** for every PR by `@dependabot[bot]`, without exception.

```markdown
### Fixed
* Use Europe/Brussels as app timezone by @lucwollants in https://github.com/cultuurnet/publiq-platform/pull/2666

### Dependabot
* Bump i18next from 26.4.2 to 26.5.6 by @dependabot[bot] in https://github.com/cultuurnet/publiq-platform/pull/2664
```

## Before creating

Show the tag and the grouped notes, and call out every PR whose group was a judgement call. Wait for approval and apply any moves the user asks for.

When there are no PRs since the previous tag, say so and stop.

## Creating

Always as a draft, so the user publishes it:

```
gh release create <tag> --draft --target main --title <tag> --notes-file <file>
```

Write the notes to a file in the scratchpad first. End with the release URL, and offer to deploy it with the `deploy` skill.
