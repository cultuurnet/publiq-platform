# Lessons learned

Notes from fixing IDOR/broken-access-control issues in the contact/organizer/URL
deletion endpoints (PRs around the "protected contact deletion" pentest finding).

## Authorization layering

- Web routes are authorized via `Gate::define()` abilities consumed through
  `can:ability,routeParam1,routeParam2` route middleware (see
  `App\Domain\Auth\AuthServiceProvider`, e.g. `access-integration`,
  `delete-contact`). Route params passed to `can:` middleware are resolved as
  raw strings via `$request->route($name)` — they are **not** Eloquent models,
  since these routes don't use route-model binding.
- Policy classes (registered in `App\Providers\AuthServiceProvider`'s
  `$policies` map) are consumed **only by Nova**, which auto-applies them to
  its own resource CRUD actions. Before this fix, there was not a single
  `$this->authorize()` / `Gate::authorize()` call anywhere in a web
  controller — don't assume a registered policy is actually enforced on the
  public API just because the class exists and looks correct.
- Don't call a Policy class directly from a Gate/web controller. A Policy is
  written for a single-resource context (e.g. `ContactPolicy` only ever knows
  about one `ContactModel`, never which integration it belongs to). A web
  Gate often needs more context (e.g. scoping a contact lookup to the
  integration id in the URL) than the Policy's signature allows. Reusing the
  Policy anyway couples "Nova admin authorization" to "public API
  authorization" — someone editing the Policy for Nova reasons can silently
  change public API behavior.
  - Instead, extract the actual business rule into the domain layer (e.g. an
    enum method like `ContactType::isDeletable()`), matching the existing
    style of `Integration::contactHasAccess()`. Both the Nova Policy and the
    web Gate then depend on that same neutral rule, not on each other.

## Auditing for "policy exists but is never enforced"

- A policy method can look completely correct and still never run. Grep for
  actual invocation (`$this->authorize(`, `Gate::authorize`, `Gate::allows`,
  `can:` middleware) before trusting that a `Policy` class protects anything
  outside of Nova.
- Check git blame/log on policy files — a policy deliberately tightened
  (e.g. `delete` flipped from `true` to `false`) is a strong signal that the
  intent was never wired up to the actual controller action.
- Existing tests can encode the bug as expected behavior. E.g.
  `test_it_can_destroy_a_contact` asserted that deleting a *functional*
  (supposedly protected) contact succeeds — a passing test that is itself
  proof of the vulnerability. When fixing this class of bug, re-read every
  test touching the affected endpoint, not just the ones with "unauthorized"
  in the name.

## Testing this app's error responses

- `App\Exceptions\Handler::handle404Error()` returns
  `Inertia::render('Error', ['statusCode' => 404])`, which does **not** set
  the actual HTTP status code to 404 — Inertia responses default to 200.
  `$response->assertNotFound()` will fail here. Use:
  `$response->assertInertia(fn ($page) => $page->component('Error', false)->where('statusCode', 404))`.
  The `false` is required — `component()` otherwise tries to resolve the
  frontend page file and fails since `'Error'` isn't a real page path in this
  check's search location.

## Local Docker environment

- This repo's `platform` service is hardcoded to publish host port 8000
  (`docker-compose.yml`), which can conflict with an unrelated sibling
  project already running (e.g. `udb3` / `uitdatabank` also using 8000).
- A `docker-compose.override.yml` with `ports: []` does **not** clear the
  inherited port mapping — Compose merges/concatenates list-valued keys
  (like `ports`) across files by default, it doesn't replace them. Use the
  Compose Spec `!reset` tag to actually clear it:
  ```yaml
  services:
    platform:
      ports: !reset []
  ```
- After changing compose files, a plain `docker compose up -d` may reuse an
  already-existing container without applying the new config (e.g. network
  attachment). Use `docker compose up -d --force-recreate <service>` to force
  it to pick up the change.
- Don't commit the override file — it's a local workaround, not part of the
  project's Docker config.
