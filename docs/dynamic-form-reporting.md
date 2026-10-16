# Dynamic Gravity Forms backend

This update discovers forms from the configured remote Gravity Forms REST API.
There is no fixed list of reportable form IDs. Existing ELF-I/ELF-E/ELF-D context
mappings are imported as migration defaults from gravity-forms-field-map.php;
new forms are discovered automatically and configured through WordPress.
The legacy Form 37 function remains a compatibility wrapper only.

## Installation and first test

1. Back up the existing plugin folder and database. This package includes the
   older frontend/PDF files supplied in the uploaded ZIP. Do not overwrite
   Julian's newer frontend files with those older files. Copy the changed
   backend files listed below into the combined integration checkout instead.
2. Keep the existing remote API URL and authorised credentials in wp-config.php:
   TLE_GF_API_BASE_URL, TLE_GF_CONSUMER_KEY, TLE_GF_CONSUMER_SECRET.
   The URL must point to the WordPress installation hosting Gravity Forms,
   not a local WordPress installation without Gravity Forms. No credentials
   are included in this package.
3. Activate this plugin. Open Settings > Evaluation Forms as an administrator.
4. Select a discovered form. Set enabled and evaluation_stage, and enter the
   participant_key, program, program_group and cohort field IDs from its field
   list. These are form fields, not an assumed WordPress user account ID.
   The same participant identifier must be used across forms to identify a
   person. Missing context mappings cannot be used for filtering.
5. Review the matched questions and scoring ranges. Missing document questions
   are shown as coverage gaps; they are not silently invented or scored zero.
   Duplicate matches or invalid context/scoring mappings block reporting.
6. Save the profile, then retrieve a report for that form and the desired filters.
   Confirm live results against the actual API data before deployment.

The setup page provides a JSON profile editor and field/matching diagnostics.
It is an administrator configuration interface, not a redesigned dashboard.

Example profile (replace field IDs with the IDs shown for your selected form):

```json
{
  "enabled": true,
  "evaluation_stage": "pre_program",
  "scoring_confirmed": false,
  "fields": {
    "participant_key": "100",
    "program": "101",
    "cohort": "102",
    "program_group": "103",
    "location": "",
    "sector": ""
  },
  "question_overrides": {}
}
```

If wording differs, use an explicit question override. The item_id comes from
that page's document catalog. An empty item_id explicitly excludes the field.
A scoring override can define raw_min, raw_max, allowed_scores, excluded_scores
and reverse. The shared target scale is always 1-7. The same-stage PDF wrapper
rejects multiple forms; the full report exposes each form as a separate series.

## Document-based question matching

config/capability-catalog.json is transcribed from the supplied
ELF - Growth areas - question mapping.docx: 48 items, 17 capabilities,
7 Insight / 6 Influence / 4 Impact. Reverse directions use its (R) markers.
This supersedes the old inferred Form 37 capability membership and reverse list.
Asterisk markers in the source are removed from text matching; no weighting
meaning is inferred from them.

Matching ignores punctuation, whitespace, HTML, case and typographic quotes.
It does not use fuzzy matching or guess the meaning of unknown questions.
Multi-row survey inputs use their own labels, with row text as a fallback when
labels are absent. Review the diagnostics against the actual API form.
Administrator overrides are required for changed or ambiguous wording.
Additional forms using these ELF capabilities do not require PHP changes.
A genuinely new capability model requires updating the catalog and its display
requirements; the system does not claim to interpret arbitrary survey semantics.

## Scoring and grouping policy

- Raw records and raw scores remain available.
- Choice metadata provides the valid scores and raw endpoints.
- Recognised N/A choices are excluded.
- Valid scored categories are linearly rescaled onto 1-7; document reverse
  directions are then applied. This generalises the earlier Q13/Q14 transforms.
- Equal intervals between scored categories remain an assumption, particularly
  for percentage items. Metadata supplies the choice scores, not psychometric
  validation. scoring_confirmed should stay false until the method is agreed.
- Average valid adjusted answers for each document item, then average available
  item means equally per capability; round only the final result to two decimals.
- Missing items are skipped and reported. There is no minimum coverage threshold.
- Repeated submissions are included; no automatic latest-submission selection
  or participant-first averaging is claimed.
- Group records by program_group, program and cohort. Form-local field IDs and
  submission IDs retain their form identity. Forms sharing a stage stay separate.
- Filter by participant_key for individual reporting. Participant counts use
  non-empty participant IDs within each context. No name-based joining occurs.
- No benchmark, social desirability, growth calculation or matched participant
  comparison is fabricated. change is empty in this schema; raw stage differences
  from the old implementation are not presented as validated growth.

## Frontend/PDF contract

Read-only report route:
GET /wp-json/tle/v1/evaluations

Filters: participant_key, program, program_group, cohort, evaluation_stage,
form_id or form_key. Multiple forms: form_ids[]=ID&form_ids[]=ID.
Use the site's actual discovered IDs. year is represented by cohort.
Omitting forms selects enabled configured forms matching the stage filter;
unknown or unmapped selected context returns an explicit error.

Administrator routes:
GET /wp-json/tle/v1/forms
GET /wp-json/tle/v1/forms/ID/profile
POST /wp-json/tle/v1/forms/ID/profile (JSON configuration)

Existing report access remains logged-in-user only. Configuration requires
manage_options. Browser cookie requests require a WordPress REST nonce in
X-WP-Nonce. Server-side PDF callers must keep their permission/nonce checks.

PHP interfaces:

```php
$report = TLE_Report_Service::get_report(
    ['program' => $program, 'cohort' => $cohort, 'program_group' => $group],
    $selected_form_ids
);
if (is_wp_error($report)) { /* Show its error, do not use sample data. */ }

$data = tle_get_report_data($program, $cohort, $selected_form_ids,
    ['program_group' => $group]);
```

The full report schema_version is 2.0. groups contains context and series.
Each series has form_id, form_key, evaluation_stage, capability_averages,
adjusted_item_averages, item_response_counts and mapping_diagnostics.
Raw question average keys are namespaced as form-ID:FIELD-ID.
Top-level capability_averages is populated only for a single context and
unambiguous single-form stages. Otherwise render groups/series separately.

The PDF adapter provides insight/influence/impact rows with measure, pre,
completion, delay, manager, benchmark and stages. Unavailable values are null.
New stage keys appear in stages and available_stages, rather than requiring
another PHP property. Render them dynamically if needed.
The adapter refuses multiple contexts and multiple forms at the same stage.
Use the full report for layouts displaying those separate series.

Julian must replace sample-data retrieval in his current exporter with this
adapter, retain all existing tables/editorial customisation, and handle null
values, diagnostics and new stage labels. This package does not edit or replace
his newer 4,000-line implementation. Its supplied older PDF/dashboard continue
to display prototype data until their caller is connected to the shared service.

## Changed/new files

- config/capability-catalog.json (new)
- includes/class-form-registry.php (new)
- includes/class-form-setup.php (new)
- includes/class-report-service.php (new)
- includes/class-gravity-forms-provider.php
- includes/class-evaluation-data-service.php
- includes/class-reporting-service.php
- includes/class-rest-api.php
- includes/form37-report-adapter.php
- tasmanian-leaders-evaluation.php
- tests/backend-test.php (new)
- docs/dynamic-form-reporting.md (new)
- README.md and the historical Form 37 handover receive pointers to this document.

Frontend/PDF layout files, assets, database table code and dependencies are
unchanged. The existing evaluation database is not the REST data source.

## Verification

Run php tests/backend-test.php from the plugin folder. Standalone tests use
simulated API responses and WordPress stubs, never live credentials.
Checks cover discovery of arbitrary form IDs, all 48 matches and 17 capability
midpoints, reverse/scaled/N/A scoring, pagination, participant/context filters,
form identity, missing mappings, ambiguous matches, separate stage series,
PDF adapter null values, access callbacks and profile validation.

These tests do not establish browser authentication, actual live form matching,
WordPress admin rendering, or Julian's final PDF integration. Those need local
WordPress testing with the remote API configured.
