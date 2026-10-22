# Backend Connection Reference

Hi Julian,

I have centralised the reporting backend so your dashboard and PDF exporter can use the same service for existing and future configured forms. This document covers the connection points and returned data. Please inspect the implementation and use these interfaces in your frontend.

## 1. Files to inspect

| File | Responsibility |
| --- | --- |
| `includes/class-report-service.php` | Central report retrieval and PDF adapter |
| `includes/class-rest-api.php` | Browser endpoints, supported parameters and permissions |
| `includes/class-form-registry.php` | Form discovery, configuration and question mappings |
| `includes/class-gravity-forms-provider.php` | Client Gravity Forms REST retrieval and pagination |
| `includes/class-evaluation-data-service.php` | Normalisation and validation of retrieved records |
| `includes/class-reporting-service.php` | Score preparation, averaging, groups and series |
| `includes/class-form-setup.php` | Administrator form configuration |
| `config/capability-catalog.json` | Document question and capability catalog |
| `config/gravity-forms-field-map.php` | Legacy defaults used by the registry |
| `config/tested-form-profiles.json` | Reference for tested form configurations |
| `tests/backend-test.php` | Runnable examples of retrieval, filtering and adapter behaviour |
| `docs/dynamic-form-reporting.md` | Detailed backend configuration and reporting reference |
| `tasmanian-leaders-evaluation.php` | Loads backend classes and registers plugin functionality |

The old `form37-report-adapter.php` has been removed. Use the central functions below.

## 2. Server-side entry points

### Full report

```php
$report = TLE_Report_Service::get_report($filters, $form_ids);

if (is_wp_error($report)) {
    // Handle the returned error before reading report fields.
}
```

Use this from PHP when you need the full report, including separate program contexts and form series.

### PDF adapter

```php
$data = tle_get_report_data(
    $program,
    $cohort,
    $form_ids,
    $extra_filters
);

if (is_wp_error($data)) {
    // Handle the returned error before reading report fields.
}
```

This adapts the central report to the existing capability-row structure. It accepts one program context and at most one form per stage. For multiple contexts or forms sharing a stage, use the full report and its separate groups and series.

Both functions are in `includes/class-report-service.php`. A PHP PDF exporter can call them directly; it does not need to request its own WordPress endpoint over HTTP.

### Filters and form selection

| Parameter | Purpose |
| --- | --- |
| `participant_key` | Filter by configured participant identifier |
| `program` | Filter by program |
| `program_group` | Keep program groups distinct |
| `cohort` | Filter by cohort/year |
| `evaluation_stage` | Select one backend stage |
| `form_id` or `form_key` | Select a form through the full service |
| `$form_ids` | Explicit array of selected form IDs or keys |

For a full service call without explicit form IDs, the service discovers enabled forms and applies the selected stage. Use `TLE_Form_Registry::discover()` when you need to build an explicit selection. Handle its `WP_Error` result.

For multiple stages, pass the relevant form IDs and omit a single `evaluation_stage` filter. New form IDs do not require separate adapters.

| Evaluation point | Backend stage |
| --- | --- |
| Pre-program | `pre_program` |
| Completion | `completion` |
| Delayed | `delay` |
| Manager | `manager` |

Translate your submitted frontend values into these names before calling the backend. For example, `three-month-delay` maps to `delay`.

## 3. Returned data

### Full report structure

| Field | Meaning |
| --- | --- |
| `groups` | Separate program-group/program/cohort contexts |
| `groups[].series` | Separate form and stage series within each context |
| `groups[].series[].form_id` | Source form |
| `groups[].series[].evaluation_stage` | Source stage |
| `groups[].series[].capability_averages` | Adjusted scores by section, capability and stage |
| `capability_averages` | Combined convenient view when the report is unambiguous; may contain empty stage objects otherwise |
| `records` | Normalised source records |
| `participant_count` | Count of identified participants |
| `submission_count` | Count of submissions |
| `record_count` | Count of normalised answer records |
| `selected_forms` | Selected form metadata and mapping diagnostics |
| `retrieved_stages` | Stages represented by selected profiles |
| `scoring_status` | Scoring status, currently provisional for tested configurations |

Example score path for a single unambiguous report:

```php
$score = $report['capability_averages']['insight']
    ['Self-awareness']['pre_program'] ?? null;
```

Inspect `groups[].series` when the request spans separate contexts or same-stage forms. Do not assume the top-level convenient view represents every series.

### PDF adapter structure

The adapter returns `insight`, `influence` and `impact` arrays. Each row contains:

| Key | Meaning |
| --- | --- |
| `measure` | Capability name |
| `pre` | Pre-program score or null |
| `completion` | Completion score or null |
| `delay` | Delayed score or null |
| `manager` | Manager score or null |
| `benchmark` | Currently null |
| `stages` | Values keyed by backend stage name |

It also returns context, counts, `scoring_status`, `available_stages`, `series` and the underlying `report`.

Use adjusted capability scores directly. `question_averages` are raw question summaries and are not interchangeable with adjusted capability scores. Null or absent stage values mean unavailable, not zero.

## 4. Browser connection

| Endpoint | Purpose | Current permission |
| --- | --- | --- |
| `GET /wp-json/tle/v1/evaluations` | Retrieve reports | Logged-in WordPress user |
| `GET /wp-json/tle/v1/forms` | Discover forms | `manage_options` |
| `GET /wp-json/tle/v1/forms/{id}/profile` | Inspect a profile | `manage_options` |
| `POST /wp-json/tle/v1/forms/{id}/profile` | Save a profile | `manage_options` |

Example evaluation query:

```text
?program=I-LEAD%20Young%20Professionals&cohort=2026&evaluation_stage=pre_program
```

The evaluations endpoint accepts the filters listed above. For explicit multiple forms, send `form_ids` as an array.

WordPress cookie authentication needs a REST nonce. Configure `wp-api-fetch`, or supply `rest_url()` and `wp_create_nonce('wp_rest')` to your script and send same-origin credentials with `X-WP-Nonce`. Inspect `class-rest-api.php` for the actual callbacks and request validation.

## 5. Local configuration

The provider uses the client's Gravity Forms REST API configured server-side:

```text
TLE_GF_API_BASE_URL
TLE_GF_CONSUMER_KEY
TLE_GF_CONSUMER_SECRET
```

These belong in the authorised server configuration, not browser JavaScript. A local WordPress URL is only a valid Gravity Forms source if it actually hosts that API.

Form profiles are saved in WordPress options. Copying PHP files does not transfer saved profiles. Inspect Settings > Evaluation Forms and use the documented configuration process. `tested-form-profiles.json` is a reference; verify the profiles have actually been configured in your environment.

Future forms need their stage, context fields and question mappings configured and enabled. They then use the same service.

## 6. How to verify your connection

1. Run `php tests/backend-test.php` and inspect its central-service and adapter examples.
2. Call the service with your selected filters and confirm the returned context, counts and series.
3. Connect your PHP exporter or browser request using the relevant interface above.
4. Compare the values your frontend uses with the backend values from the same request.

The backend performs retrieval and configured scoring. Your frontend should consume those results through these interfaces. You can inspect the listed files and use WordPress REST authentication documentation to decide how best to integrate them into your implementation.
