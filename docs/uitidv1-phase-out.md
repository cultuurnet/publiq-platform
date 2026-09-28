# Phasing out UiTiD v1

## Context

UiTiD v1 is being decommissioned. `UITID_V1_CONSUMER_CREATION_ENABLED` is already `false` in production, so new integrations get Keycloak client credentials instead of a UiTiD v1 consumer. Existing integrations must keep their stored API key: the UI promises integrators that after upgrading to client credentials "your API key remains valid for another 6 months" (`resources/translations/{nl,en}.json`, backed by `config/key_visibility.php` → 180 days).

The remaining problem is that the app still *talks to* UiTiD v1 in several places. Everything that makes a live HTTP call — the consumer status column in Nova, the Block/Unblock actions, the "Open in UiTiD v1" link, the create/update listeners — breaks when UiTiD v1 is switched off. The goal is a state where the platform never calls UiTiD v1, while `uitidv1_consumers` remains a read-only archive of API keys.

**Scope decisions (agreed):**
- Staged phase-out of the *integration*; each stage ships independently.
- API keys stay in `uitidv1_consumers`. **The table is never dropped**; the model and repository read path survive. Only the SDK, listeners, jobs and write paths go.
- Nova keeps a read-only api_key + environment view; live status, "Open in UiTiD v1", Block/Unblock and Create-missing all go.
- Fix the `KeyVisibility::v1` trap so new integrations always get v2.
- Admins can only *create* `v2` integrations; the Nova update form keeps `v1`/`v2`/`all` (see Stage 1a).

**Non-goals:** dropping `uitidv1_consumers`; deleting the `UiTiDv1Environment` PHP enum (it is the persisted `environment` column value, used by `UiTiDv1ConsumerModel::toDomain()`); removing the integrator-facing v1→v2 upgrade flow, which must survive until every v1 key has expired.

Work continues on `PPF-758-cleanup-v1-code` (currently identical to `PPF-717-remove-uitidv1-creation`, which already decoupled `ProjectAanvraag` and replaced `ConsumerCreated` with `App\Keycloak\Events\ClientsCreated`).

**Delivery decisions:** one PR per stage, small commits inside. Stage 2a's upstream drift is reconciled with a throwaway artisan command, not accepted as residual risk. The pre-Stage-1 production queries are skipped; already-broken `v1`/`all` integrations are a separate follow-up.

---

## Findings that drive the ordering

Verified in the code, not hypotheticals.

1. **"The flag is false in prod" does not mean "no v1 writes happen in prod".** `app/Nova/Actions/UiTiDv1/BlockUiTiDv1Consumer.php:36` (and its Unblock twin) inject `BlockConsumerHandler` and call `$dispatcher->dispatchSync($command, $this->listener)`, which invokes the handler **directly** — the `Event::listen(BlockConsumer::class, ...)` lines inside the flag block at `UiTiDv1ServiceProvider.php:82-83` are dead wiring for this path. The guards (`BlockUiTiDv1ConsumerGuard`) also call `fetchStatusOfConsumer()` on every Nova detail render, and the SDK bindings are ungated. **The Nova v1 resource is a live integration with UiTiD v1 right now.** Removing the Block action is therefore a real capability removal — it is the only in-app way to revoke a leaked legacy key — not a no-op.

2. **What the flag *does* gate is integration-lifecycle writes**, including `BlockConsumers` on `IntegrationBlocked`/`IntegrationDeleted` (`UiTiDv1ServiceProvider.php:73-88`). So since the flag flipped, blocking or deleting an integration has **not** revoked its v1 API key upstream. There is existing drift to reconcile while the SDK still exists.

3. **New integrations can still be assigned `KeyVisibility::v1`**, two ways: `IntegrationController::store()` line 134 calls `withKeyVisibility($this->getKeyVisibility($integration))`, which looks the contributor up in `contacts_key_visibility`; and `app/Nova/Resources/Integration.php:138` still offers `v1` in the admin select. With creation disabled such an integration never gets credentials, and `Credentials.tsx:43-45` computes `hasCredentials` from `legacyAuthConsumers.length` and polls "pending credentials" forever.

4. **An e2e test pins that exact bug.** `e2e/tests/integrations/integrator/create-integration-after-migration.test.ts:23` asserts `Key Visibilityv1` for a newly created integration. It cannot survive the fix and must be deleted, along with the `user-v1.json` auth fixture that only it consumes (`e2e/setup/auth.setup.ts:64-98`) and the `E2E_TEST_V1_EMAIL`/`E2E_TEST_V1_PASSWORD` forwarding at `Makefile:100`.

5. **Three more e2e tests assert live v1 status.** `e2e/tests/integrations/admin/assert-key-visibility.ts` asserts `[data-relationship="uiTiDv1Consumers"]` rows *named by their live status column*, and is imported by `approve-integration`, `block-integration` and `activate-integration`. These pass today only because the *deployed acceptance environment* the e2e suite runs against still has v1 creation enabled — not because of `.env.ci`, whose `UITID_V1_*_CONSUMER_KEY` are empty, leaving the cluster SDK with zero environment SDKs. They break in two independent ways later (no rows, then no Status column).

6. **Nova hard-breaks if the env vars are blanked before the Status field is removed.** `UiTiDv1ClusterSDK::fetchStatusOfConsumer()` indexes `$this->uitidv1EnvironmentSDKs[...]` directly, and the provider filters out environments with empty credentials — emptying config first yields an undefined-key error, not a graceful degrade. Config must go *after* the code that reads it.

   Worse than that in the *unset* case: `config/uitidv1.php` declares no defaults, so an unset var yields `null`, and the provider's filter (`UiTiDv1ServiceProvider.php:39-41`) compares strictly against `''`. `null !== ''` is true, so an unconfigured environment is **not** filtered out and reaches `createOAuth1HttpClient(null, null, null)` → TypeError. Setting the vars to empty strings degrades gracefully; deleting them before the code does not.

7. **The consumer secret is shipped to the browser.** `Integration::toArray():277` and `IntegrationController::index()` serialize whole `UiTiDv1Consumer` objects, so `consumerKey`/`consumerSecret` land in the Inertia payload although the UI only renders `apiKey` (`resources/ts/types/Credentials.ts:8`).

8. **`config/uitidv1.php:6` defaults to `true`.** Deleting the config key while any `Event::listen` call still reads it would silently re-enable v1 writes in every environment whose `.env` lacks the var. Flag and listeners must die together.

9. **`isKeyVisibleForEnvironment()` has zero test coverage.** No test anywhere exercises it, and Stage 3 changes its signature and drops a branch. Characterization tests come first.

---

## Stage 0 — Decouple the e2e suite (ship first, small, standalone)

Must be first: e2e runs against a deployed environment, and every later stage breaks it.

- `e2e/tests/integrations/admin/assert-key-visibility.ts` — drop the `uiTiDv1Consumers` locator and remove it from the `tables` array, keeping `keycloakClients`. (Asserting the v1 table by status is what breaks; asserting mere presence is optional and not worth keeping.)
- Delete `e2e/tests/integrations/integrator/create-integration-after-migration.test.ts` (finding 4).
- `e2e/setup/auth.setup.ts:64-98` — remove the "authenticate as contributor with v1 key visibility" setup writing `playwright/.auth/user-v1.json`. `playwright.config.ts` needs no change (the setup project is a glob).
- `Makefile:100` — drop `-e E2E_TEST_V1_EMAIL -e E2E_TEST_V1_PASSWORD` from `E2E_DOCKER_OPTIONS`.
- `Jenkinsfile:105-107` — drop the `publiq-platform_e2etest_v1` credentials binding. The e2e suite runs on Jenkins (triggered by `.github/workflows/acceptance_tests.yml`), not in GitHub Actions; this is where the secrets actually come from. Retire the Jenkins credential itself afterwards, not before.

**Ordering:** delete the auth setup *before* dropping the env vars. The other way round, `auth.setup.ts` logs in with empty credentials and the failing *setup project* takes the whole suite down, not just one test.

**Verify:** `make test-e2e`; at minimum `make npm-ci`.

---

## Stage 1 — Close the v1 trap and the secret leak (safe now, independent of shutdown)

Ship separately from any code deletion — this is the only stage with user-visible product semantics, so keep it revertable on its own.

**1a. Always create v2 integrations (finding 3).**
- `IntegrationController.php:134` — drop `->withKeyVisibility($this->getKeyVisibility($integration))`. `Integration::__construct` already defaults to `KeyVisibility::v2` (`Integration.php:65`), so no replacement call is needed. Delete `getKeyVisibility()` (~line 364), the `ContactKeyVisibilityRepository` constructor param (line 71) and its import (line 9) — it is the only consumer of that repository in `app/`.
- `app/Nova/Resources/Integration.php:129-149` — split the single `dependsOn(['type'], ...)` into `dependsOnCreating` (options: `v2` only) and `dependsOnUpdating` (unchanged: `v1`/`v2`/`all` for non-UiTPAS, `v2` for UiTPAS). Nova supports both natively (`vendor/laravel/nova/src/Fields/SupportsDependentFields.php:35,49`).
  - Dropping only `v1` is not enough: with creation disabled, a newly created `all` integration also never gets consumers, so `Credentials.tsx` polls forever. Same trap, different enum case.
  - **`v1` and `all` must stay on the update form.** Nova renders the current value against the option list, so editing an existing `v1` integration with no `v1` option blanks the select and silently rewrites `key_visibility` on save.
  - `e2e/tests/integrations/admin/create-integration.ts:20` selects `"all"` and must move to `"v2"` in the same commit, or four admin e2e tests go red. (It is also latently wrong for UiTPAS today, which only ever offers `v2`.)
- **Do not touch `KeyVisibility::v1` the enum case** — it remains a valid persisted value in `integrations.key_visibility`.
- Before shipping, query production for `contacts_key_visibility` rows with `key_visibility = 'v1'`, and for integrations that are `v1`/`all` with zero `uitidv1_consumers` rows — the latter are already-broken integrations needing a manual fix.

On `contacts_key_visibility`: keep the table (it is the audit trail explaining why existing integrations are v1) and keep `ContactKeyVisibilityModel`. Delete `app/Nova/Resources/ContactKeyVisibility.php` — leaving it creatable is a footgun, since seeding a row now does nothing. `ContactKeyVisibilityRepository`, `EloquentContactKeyVisibilityRepository` and the binding at `AppServiceProvider.php:26` then become dead; delete them plus `tests/Domain/Contacts/Repositories/EloquentContactKeyVisibilityRepositoryTest.php` and `database/seeders/ContactsKeyVisibilitySeeder.php` (wired at `database/seeders/DatabaseSeeder.php:19`; it seeds the very `dev+e2etest-v1@publiq.be` account Stage 0 retires). Those must go in **one** commit — the seeder type-hints the interface and the provider binds it, so any partial split leaves `make stan` red.

**1a-bis. Stop the credentials page polling forever (finding 3).**
`resources/ts/Components/Integrations/Detail/Credentials.tsx:43-46` derives `hasCredentials` from `legacyAuthConsumers.length` whenever `keyVisibility !== v2`, which drives `usePolling`. Change it to the union `authClients.length > 0 || legacyAuthConsumers.length > 0`. That is a strict superset of today's condition on both branches, so it cannot hide a page that currently renders — unlike Stage 4's unconditional `authClients.length > 0`, which assumes every `v1`/`all` integration has Keycloak clients. Stage 4 collapses the union once `legacyAuthConsumers` is deleted.

**1b. Stop shipping the consumer secret to the browser (finding 7).**
Serialize only `id`, `integrationId`, `environment`, `apiKey` for `legacyAuthConsumers` (`Integration.php:277`) and `legacyConsumers` (`IntegrationController::index()`), and drop `consumerKey`/`consumerSecret` from `resources/ts/types/Credentials.ts`. Nothing in the frontend reads them. Flag to security that these secrets have been in page payloads historically; rotation is moot if Stage 2's reconciliation or the shutdown revokes them, but it is their call.

**1c. Remove the dead "Create missing consumers" path.**
`app/Nova/Actions/UiTiDv1/CreateMissingUiTiDv1Consumers.php` uses `Event::dispatch()`, so with the flag off it silently no-ops while still reporting success. Delete it, its wiring at `Integration.php:329-336` + import at line 22, `IntegrationModel::hasMissingUiTiDv1Consumers()` (line 339, no other callers), and the `CreateMissingConsumers` job + handler.

This stage must also delete `UiTiDv1ServiceProvider.php:16,17,87` — the `Event::listen(CreateMissingConsumers::class, ...)` registration lives *inside* the flag block, so removing the job without it is stan-red. The flag itself survives until Stage 2a.

**Verify:** `make ci` plus `make test-filter filter=IntegrationControllerTest` — `tests/Domain/Integrations/Controllers/IntegrationControllerTest.php:59,94` assert `key_visibility => v1` on store and must be updated. Create an integration on acceptance as a contributor with a `v1` row and confirm it gets v2 and renders without endless polling. Inspect the Inertia payload for absence of `consumerSecret`.

---

## Stage 2a — Remove all write paths and the flag (safe now; MUST land before shutdown)

**Prerequisite — reconcile the drift (finding 2), while the SDK still exists.** For integrations with `status IN (Blocked, Deleted)` that still have `uitidv1_consumers` rows, block them upstream via `UiTiDv1ClusterSDK::blockConsumers()`. A throwaway artisan command is appropriate; do not add a permanent one. If publiq instead accepts the residual risk until shutdown, record that decision explicitly — **this needs a security/product owner, not a silent choice.** This is the last moment the capability exists.

Also capture the three `consumerDetailUrlTemplate` values from `config/uitidv1.php` into the ops runbook before Stage 2b deletes them: after this stage, revoking a legacy key is a UiTiD v1 admin-UI operation and those URLs are the only record of how to get there.

Delete together, in one PR (finding 8):

- `app/UiTiDv1/UiTiDv1ServiceProvider.php:73-88` — the entire `if (config('uitidv1.enabled'))` block.
- `app/UiTiDv1/Listeners/{CreateConsumers,UpdateConsumers,BlockConsumers,UnblockConsumers}.php`.
- `app/UiTiDv1/Jobs/*` and `app/UiTiDv1/Events/{ConsumerBlocked,ConsumerUnblocked}.php` (terminal — dispatched only by the handlers, no listeners).
- `app/Nova/Actions/UiTiDv1/*` and `app/Nova/ActionGuards/UiTiDv1/*`.
- `app/Nova/Resources/UiTiDv1.php` — the `Status` field (lines 72-79), the `Open in UiTiD v1` field and `getActionUrlTemplates()`, and `actions()` + `canActivate`/`canBlock`/`can`.
- `IntegrationModel.php` — the `UiTiDv1Environment` import at line 34 if now unused.

Delete the matching tests: `tests/UiTiDv1/Listeners/**` (including the `consumer*.xml` fixtures), `tests/UiTiDv1/Jobs/**`, `tests/Nova/ActionGuards/UiTiDv1/**`.

**Verify:** `make ci` — PHPStan is the real completeness gate for "nothing still references a removed class". On acceptance, open an existing integration in Nova: the detail page must render (also a latency win — the Status column previously made one v1 API call per consumer per render), with no Block/Unblock/Create-missing actions and `api_key` still displayed. Block an integration and confirm no `'domain' => 'uitid'` log entries.

---

## Stage 2b — Delete the dead SDK and config (pure dead-code PR, right after 2a)

Kept separate so 2a's diff stays reviewable as a behavioural change.

- `app/UiTiDv1/{UiTiDv1ClusterSDK,UiTiDv1EnvironmentSDK,OAuth1,CachedUiTiDv1Status,UiTiDv1ConsumerStatus,UiTiDv1SDKException,UiTiDv1EnvironmentNotConfigured}.php`.
- `UiTiDv1ServiceProvider.php` — the two `singleton()` bindings; the provider shrinks to the single repository bind. Consider folding that into `AppServiceProvider` and dropping `config/app.php:202`.
- Delete `config/uitidv1.php` entirely, the `UITID_V1_*` blocks in `.env.example:107-136` and `.env.ci:89-118`, and the flag line at `README.md:91`.
- Drop `guzzlehttp/oauth-subscriber` from `composer.json` — `app/UiTiDv1/OAuth1.php` is its only consumer.
- Delete `tests/UiTiDv1/CachedUiTiDv1StatusTest.php`, `tests/UiTiDv1/CreatesMockUiTiDv1ClusterSDK.php` and `tests/UiTiDv1/CreatesMockUiTiDv1Consumer.php` — after Stage 2a every user of the latter is gone too.
- Have infra remove the real secrets from deployed environments.

**Verify:** `make ci`, then clear and rebuild config (`php artisan config:clear`) and boot — a stale cached config referencing the deleted file is the classic failure here. Confirm deployment env files no longer set `UITID_V1_*` before merging.

---

## Stage 3 — Shrink the archive surface (safe now)

- `app/Nova/Resources/UiTiDv1.php` — remove the `Visible for integrator` field (lines 81-89); it is the only caller of the `UiTiDv1Environment` branch of `isKeyVisibleForEnvironment`. Final resource: `ID` + readonly `environment` select + readonly `api_key`, keeping `defaultOrderings` and `$displayInNavigation = false`. Relabel the `HasMany` at `Integration.php:234` to something like "Legacy API keys"; keep `$with` (line 63) and the `SearchableRelation` on `consumer_key` (line 74) — searching legacy integrations by consumer key is exactly what an archive is for.
- `app/Domain/Integrations/Integration.php:222-228` — the ternary is deliberately inverted and easy to misread: v1 keys are visible when `keyVisibility !== v2`, Keycloak keys when `keyVisibility !== v1`; the shared `'acc'` check only works because both enums happen to use that literal. With the v1 caller gone, the two call sites are type-disjoint (`KeycloakClient.php:91` passes `Environment`), so it collapses to:

  ```php
  public function isKeyVisibleForEnvironment(Environment $environment): bool
  {
      return $environment !== Environment::Acceptance
          && $this->status !== IntegrationStatus::Deleted
          && $this->getKeyVisibility() !== KeyVisibility::v1;
  }
  ```

  Use the enum case, not `->value !== 'acc'`. No temporary legacy helper is needed. `Environment` has exactly `acc`/`test`/`prod`, so `!== Environment::Acceptance` is equivalent to the old string compare.

  **Add characterization tests first** (finding 9), in `tests/Domain/Integrations/IntegrationTest.php`: two `#[DataProvider]` tables, one per parameter type, pinning the inversion — for an `Environment`, `v1` ⇒ not visible; for a `UiTiDv1Environment`, `v2` ⇒ not visible. Both cover `Acceptance` ⇒ false and `IntegrationStatus::Deleted` ⇒ false. The `UiTiDv1Environment` table is deleted together with the branch it pins, deliberately, in the refactor commit. Note also the ordering constraint: the `Visible for integrator` field must go **before** the signature is narrowed, or `UiTiDv1.php:85` is stan-red.
- `UiTiDv1ConsumerRepository` + `EloquentUiTiDv1ConsumerRepository` — after Stage 2a the surviving caller is `IntegrationController::index()` (`getByIntegrationIds`). Drop `save()`, `getById()`, `getByIntegrationId()` and `getMissingEnvironmentsByIntegrationId()`, and trim `tests/UiTiDv1/Repositories/EloquentUiTiDv1ConsumerRepositoryTest.php` to match. Note `IntegrationModel::toDomain()` reads the Eloquent relation directly, not the repository, so it is unaffected.
- `UiTiDv1ConsumerPolicy` already returns `false` for every mutating ability and `true` only for `viewAny` — it is already the correct archive policy. Leave it and `AuthServiceProvider.php:52` alone.

**Verify:** `make ci`, `make test-filter filter=EloquentUiTiDv1ConsumerRepositoryTest`, Nova smoke test of the archive list.

---

## Stage 4 — Integrator-facing removal (ONLY after shutdown and after the 6-month promise expires)

This is the only stage genuinely gated on the external shutdown. Track integrations with `key_visibility IN ('v1','all')`; when that reaches zero and keys no longer work, remove:

- `IntegrationController::index()` — the `legacyConsumers` payload, the `UiTiDv1ConsumerRepository` param and import.
- `Integration.php` — the `uiTiDv1Consumers` property (38-39, 61), `withUiTiDv1Consumers()` (110-114), `uiTiDv1Consumers()` (183-186), `'legacyAuthConsumers'` in `toArray()` (277).
- `IntegrationModel::toDomain()` — the `withUiTiDv1Consumers(...)` chain (373-377). **Keep the `uiTiDv1Consumers()` HasMany**; Nova still needs it.
- Frontend: delete `CredentialsLegacyAuthConsumers.tsx`; update `Credentials.tsx`, `IntegrationCard.tsx`, `Pages/Integrations/Index.tsx:64-76`, `types/Credentials.ts`, `types/Integration.ts:39`, `types/UiTiDv1Environment.ts` (the TS union is independent of the PHP enum and does go here), and the `uitid_alert` CTA in `CredentialsAuthClients.tsx:59-63` plus its translations.
- **The regression-prone spot:** `Credentials.tsx:43-45` falls back to `legacyAuthConsumers.length > 0` whenever `keyVisibility !== v2`. Replace with `authClients.length > 0` unconditionally, or v1/`all` integrations render "pending credentials" forever and `usePolling` spins indefinitely.
- `IntegrationSettings.tsx:235` (`keyVisibility !== KeyVisibility.v1`) **stays** — it gates EntryApi URL settings, not v1 credentials.

**Verify:** `make ci`, `make npm-ci`, `make test-filter filter=IntegrationControllerTest` (it asserts the Inertia `credentials` shape), and a manual pass over a `v1`, an `all` and a `v2` integration.

---

## Verification summary

All commands run through Docker via the Makefile.

| Command | Purpose |
|---|---|
| `make lint` / `make stan` / `make test` | php-cs-fixer / PHPStan / PHPUnit |
| `make ci` | all three; run on every stage |
| `make npm-ci`, `make npm-build` | stages touching TS (0 and 4) |
| `make test-e2e` | required for Stage 0 |
| `make test-filter filter=X` | targeted reruns noted per stage |

Treat a green `make stan` as the completeness check after each deletion. The decisive end-to-end proof is after Stage 2b: with the `UITID_V1_*` secrets removed from the environment, the Nova integration detail page, the integrator credentials page, and integration create/update/block/delete must all work — that is the demonstration that the platform survives UiTiD v1 being switched off.
