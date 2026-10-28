# Tasmanian Leaders Evaluation Dashboard

A WordPress evaluation reporting plugin developed by **RMIT University, Team 55** for **Tasmanian Leaders** (COSC2408 Programming Project 1, Semester 2, 2026).

The plugin turns existing Gravity Forms survey submissions into leadership-development reporting for selected programs and cohorts. Staff can configure the report, compare cohorts, edit explanatory text and download a branded PDF. It is **locally implemented and tested**; production installation and independent reconciliation with the client's historical ELF reports are separate handover activities.

## Features

- **Program and cohort selection:** Find eligible reporting cohorts dynamically from available evaluation submissions rather than a hardcoded list.
- **Evaluation stages:** Pre-program, Completion (post-program) and Delayed follow-up, with Manager feedback when available for the selected program and cohort.
- **Leadership reporting:** Charts and summaries for 17 capabilities grouped into Insight (7), Influence (6) and Impact (4).
- **Program comparisons:** Compare a primary program/cohort with up to two others. Comparison charts show signed relative growth from Pre-program to Delayed, with appropriate positive and negative chart axes.
- **Editable report content:** Report title, purpose, Overview explanation, capability/domain explanations, highlights, per-program growth summaries, Manager summary text and participant quotes. Automatically generated text can be overridden and reset.
- **Report appearance:** Cover-image upload, branded colour palettes and custom report colour.
- **PDF exports:** Five pages without Manager feedback; seven pages when Manager feedback is included. PDFs include page numbering, capability graphs, growth summaries, optional quotes and Manager response distributions.

The interface does **not** save and reopen previously configured reports. Unimplemented Save/Open controls were removed rather than presented as working features.

## Technology and architecture

| Component | Implementation |
| --- | --- |
| Application | WordPress plugin (PHP, JavaScript and CSS) |
| Local development environment tested | LocalWP, PHP 8.2.29, MySQL 8.4.0 |
| Source evaluations | **Remote** Gravity Forms REST API v2, authenticated with a consumer key and secret |
| PDF renderer | Dompdf `^3.1`, installed through Composer 2 |
| Configuration storage | WordPress options for form profiles and historical context rules |
| Version control | Git/GitHub; PR #11 integrates `integration/manager-reporting` into `main` |

Reporting data flows as follows:

```text
Authenticated WordPress user
    -> Dashboard shortcode / report controls
    -> WordPress REST endpoints
    -> Gravity Forms provider (remote REST API)
    -> Form profiles + historical cohort-context resolution
    -> Capability scoring + reporting / comparison services
    -> Dashboard charts and customised Dompdf PDF
```

The original Gravity Forms entries remain at the source. The plugin is **not** backed by a separate reporting database requiring a seed import. It does, however, depend on site-specific WordPress configuration and access to the intended survey records.

### Key directories

```text
tasmanian-leaders-evaluation-plugin/
├── admin/
│   └── pdf-export.php                  # Admin report view and PDF export
├── assets/
│   ├── css/dashboard.css
│   ├── js/dashboard.js
│   ├── fonts/                         # Bundled report font assets
│   └── images/
├── config/
│   ├── capability-catalog.json        # ELF question/capability catalogue
│   ├── gravity-forms-field-map.php    # Legacy form/context defaults
│   └── tested-form-profiles.json      # Reference/test profile configuration
├── includes/
│   ├── class-dashboard-shortcode.php
│   ├── class-gravity-forms-provider.php
│   ├── class-form-registry.php
│   ├── class-form-setup.php
│   ├── class-evaluation-data-service.php
│   ├── class-reporting-service.php
│   ├── class-report-service.php
│   ├── class-manager-report-service.php
│   ├── class-comparison-growth.php
│   ├── class-comparison-chart.php
│   ├── class-comparison-summary.php
│   └── class-rest-api.php
├── templates/evaluation-dashboard.php
├── tests/                             # Eight standalone PHP test suites
├── docs/                              # Development and implementation notes
├── composer.json
├── composer.lock
└── tasmanian-leaders-evaluation.php   # WordPress plugin entry point
```

## Local installation

### Prerequisites

1. A local WordPress installation (tested using **LocalWP** with PHP 8.2.29 and MySQL 8.4.0).
2. Git, Composer 2 and a compatible PHP CLI for installation/testing.
3. Access to the client's **remote** WordPress site hosting Gravity Forms, its REST API v2, and **authorised read credentials** for the required forms and entries.
4. An approved copy of the site-specific form profiles, historical context rules and any other relevant WordPress settings. Do not copy private survey entries into this repository.

**Important:** The local WordPress installation does not need to hold a duplicate Gravity Forms database. The plugin queries the configured remote Gravity Forms API. The **remote source** WordPress site must have Gravity Forms and its REST API available.

### 1. Install plugin code and Composer dependencies

Clone an approved repository revision into the local site's WordPress `wp-content/plugins/` directory, or link your existing checkout into that directory using a directory junction on Windows. For example, from the plugins directory:

```powershell
git clone https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin.git
cd tasmanian-leaders-evaluation-plugin
composer install
```

If deploying from GitHub, select the **reviewed and approved release revision**; the default branch or most recent branch is not automatically an approved production release. Composer must create `vendor/autoload.php` before a PDF can be exported.

### 2. Configure the remote Gravity Forms connection

In the **local WordPress site's** `wp-config.php`, define the following constants using credentials supplied through a secure channel:

```php
define('TLE_GF_API_BASE_URL', 'https://YOUR-REMOTE-WP-SITE.example/wp-json/gf/v2/');
define('TLE_GF_CONSUMER_KEY', 'YOUR_AUTHORISED_CONSUMER_KEY');
define('TLE_GF_CONSUMER_SECRET', 'YOUR_AUTHORISED_CONSUMER_SECRET');
```

The base URL points to the **Gravity Forms REST API root on the source site**, not the local dashboard site. The provider appends endpoints such as `forms` and uses HTTP Basic authentication for its requests. Use HTTPS and credentials with only the access required. **Never commit real credentials, secrets, source survey exports or a production `wp-config.php`.** No `.env` file is required by the current provider implementation.

### 3. Activate and configure form mappings

1. In WordPress Admin, open **Plugins > Installed Plugins** and activate **Tasmanian Leaders Evaluation Plugin**.
2. Open **Settings > Evaluation Forms** as an administrator. The page discovers forms from the remote API.
3. For each relevant form, review the evaluation stage, enablement, program/cohort/participant field IDs, matched questions, scoring rules and diagnostics. Confirm ambiguous or missing mappings against the source survey.
4. Transfer the approved historical cohort context rules from the existing WordPress configuration, where needed. They are **not** recreated by `composer install` or by cloning the repository.
5. Check that the intended programs/cohorts appear and that Manager submissions do not leak into unrelated programs.

Configuration keys in the WordPress options table:

| Option key | Purpose |
| --- | --- |
| `tle_form_profiles_v1` | Administrator-configured form profiles, field mappings and scoring settings |
| `tle_historical_context_rules_v1` | Historical program/year/stage context resolution for incomplete entries |

The local development environment used 13 historical context rules. **Do not assume these values are present on a fresh WordPress installation**; transfer the client-approved configuration, then verify it. The three legacy participant stages map to survey forms 37 (Pre-program), 40 (Completion) and 43 (Delayed), with Manager form 50 used in the tested client dataset. The system can discover additional forms when configured appropriately.

### 4. Create the dashboard page

Create a WordPress page with this shortcode:

```text
[tasmanian_leaders_evaluation_dashboard]
```

The development site used `http://tasmanian-leaders-test.local/evaluation-dashboard-test/`; the address is an **example**, not a publicly hosted deployment.

Sign in to WordPress, open the page, select an eligible program/cohort and check that evaluation data loads. A program is eligible for the complete three-stage reporting workflow only when usable Pre-program, Completion and Delayed scores exist.

### 5. Generate a report

1. Select the primary program and cohort.
2. Choose evaluation stages and optionally include Manager feedback if available.
3. Optionally add up to two comparison programs/cohorts.
4. Select a cover image and colour scheme; review or edit the generated report text and quotes.
5. Export the PDF and inspect the Overview, Insight, Influence, Impact and optional Manager pages.

The standard growth summaries describe **percentage-point changes** in reported average percentage scores. Comparison charts instead display **relative percentage change**: `(delayed − pre-program) / pre-program × 100`. These are different measures and should not be treated as interchangeable. Missing or invalid baseline values must be evaluated rather than presented as zero growth.

## Permissions and WordPress API

| API route | Purpose | Current access model |
| --- | --- | --- |
| `GET /wp-json/tle/v1/evaluations` | Evaluation data | Signed-in WordPress user |
| `GET /wp-json/tle/v1/comparison-options` | Eligible comparison cohorts | Signed-in WordPress user |
| `GET /wp-json/tle/v1/manager-evaluations` | Manager reporting data | Signed-in WordPress user |
| `GET /wp-json/tle/v1/forms` | Discover source forms | Administrator (`manage_options`) |
| `GET` / `POST /wp-json/tle/v1/forms/{id}/profile` | Inspect/update form mapping | Administrator (`manage_options`) |

The shortcode currently permits **any signed-in WordPress user**, whereas the administrator PDF export and configuration functions require `manage_options`. Browser REST requests use WordPress REST nonces. **The client must review and tighten the production roles/capabilities** before opening the dashboard to broader accounts; signed-in access alone is not a final least-privilege policy.

## Verification and tests

### Automated tests

Run these standalone PHP test scripts from the repository root. They do not require the production site or live credentials:

```powershell
$tests = @(
    'backend-test.php',
    'manager-report-test.php',
    'comparison-growth-test.php',
    'comparison-series-test.php',
    'comparison-chart-test.php',
    'comparison-summary-test.php',
    'comparison-chart-design-test.php',
    'comparison-axis-test.php'
)

foreach ($test in $tests) {
    php "tests/$test"
    if ($LASTEXITCODE -ne 0) { throw "Test failed: $test" }
}
```

The final local validation of integration commit **`178604e`** recorded:

| Test suite | Passed checks |
| --- | ---: |
| Backend | 70 |
| Manager reporting | 40 |
| Comparison growth | 20 |
| Comparison series | 20 |
| Comparison chart | 17 |
| Comparison summary | 17 |
| Comparison chart design | 12 |
| Comparison axis | 12 |
| **Total** | **208 / 208** |

All 25 plugin/test PHP files passed `php -l` syntax checks and `assets/js/dashboard.js` passed `node --check` in the final local run. These are **developer-run tests**, not an established GitHub Actions CI or production smoke-test result.

### Manual smoke tests

- Export a five-page report without Manager feedback or comparisons.
- Export a seven-page report with Manager feedback; confirm correct program attribution and page numbers.
- Export a seven-page report with Manager feedback and two comparison cohorts.
- Verify charts, legends, descriptive text, signed growth axes and report colours.
- Verify long/short participant quotes, quote omission and full-width summary layout.
- Check the comparison description boxes do not overlap summary headings on pages 3–5.
- Use approved realistic quote text for client-facing demonstrations; do not distribute reports containing test placeholders.

The team visually reviewed reports 97 (comparison + Manager), 98 (Manager, no comparison) and 99 (standard) on 28 October 2026. Their layouts passed visual inspection, **not** independent validation against the original ELF calculations.

## Deployment and handover

Before production use:

1. Obtain review/approval and merge the tested PR into the intended release branch; deploy an identified commit or tagged release.
2. Back up the client site's WordPress database and existing plugin files.
3. Verify PHP, Composer/Dompdf, WordPress, Gravity Forms API access and hosting compatibility in a staging environment.
4. Transfer approved secrets and required WordPress configuration without placing private data in Git.
5. Run automated checks and manual five/seven-page report smoke tests on the release.
6. Reconcile report figures, Manager eligibility and historical submissions against the client's approved data/methodology before signing off on production reporting.
7. Document a rollback path to the previous plugin files and configuration.

### Known limitations and dependencies

- **Client source-data reconciliation:** Some Women in Industry 2025 figures differ from the historical ELF report; the client has agreed to manage the required source-data cleanup internally. Do not change scoring or delete entries solely to force the numbers to match. Confirm the correct inclusion/exclusion rules and calculation methodology.
- **Incomplete historical submissions:** Some Women in Community 2025 records are incomplete. Historical context matching and question profiles are dependent on correct WordPress settings and client-confirmed mappings.
- **Production not independently verified:** Testing to date has occurred in the team's local WordPress environment; clean-machine reproduction, production performance and production deployment remain to be verified.
- **Access permissions:** The signed-in-user dashboard policy requires a client-approved least-privilege review for production.
- **No saved-report persistence:** Reports can be configured and exported, but configurations are not stored for reopening later.
- **Source response labels:** A known misspelling of “somewhat dissatisfied” may appear in Manager report legends because the original response labels are preserved. Clean or normalise the source label with client approval.

## Project documents

- The **final approved handover document** is the authoritative reference for environment access, release ownership, transfer of credentials and support contacts.
- `docs/dynamic-form-reporting.md`, `docs/manager-reporting.md` and other files under `docs/` contain implementation background. Some are historical development notes, so statements about planned work or old frontends should not override this final README or the approved handover.
- [GitHub pull request #11](https://github.com/rmit-tasmanian-leaders/tasmanian-leaders-evaluation-plugin/pull/11) records the final integration and review discussion.

**Project:** Tasmanian Leaders Evaluation Dashboard · **Client:** Tasmanian Leaders · **RMIT:** Team 55 · **Semester:** 2, 2026.
