# Manager response reporting

## Scope

`TLE_Manager_Report_Service` returns raw answer distributions for dashboard and PDF consumers. It does not change leadership capability scoring. No form IDs or context-field IDs are hardcoded in this service.

Supported choice-label sets are five-choice agreement, four-choice agreement without Neutral, four-choice agreement with Neutral and no Strongly agree, five-choice satisfaction and Yes/No. Source labels and stored choice values are read from form metadata. The known source spelling `Somewhat disatisfied` is recognised. Other scales are listed in `skipped_field_ids` and need an explicit implementation decision; they are not silently converted into agreement scores.

## PHP and REST

```php
$result = TLE_Manager_Report_Service::get_report([
    'program' => 'I-LEAD Pride',
    'cohort' => '2026',
    // Optional: program_group and participant_key.
]);
if (is_wp_error($result)) {
    // Preserve and display an appropriate retrieval/configuration error.
}
```

With no IDs, enabled profiles at stage `manager` are discovered. An optional second argument selects explicit positive integer form IDs, such as `[50]` for a diagnostic test. Profiles and context mappings must be configured in Settings > Evaluation Forms. Saved profiles are WordPress options, not installed by copying PHP files.

Browser endpoint: `GET /wp-json/tle/v1/manager-evaluations` with the same filters and optional `form_ids[]`. Login and WordPress REST cookie nonce authentication are required. The endpoint follows the existing evaluations endpoint's permission policy. It returns aggregated data only, not raw entries or participant identifiers.

## Return structure

```text
status: available or no_matching_responses
filters
submission_count
groups[]
  context: program_group, program, cohort
  series[]
    form_id, title, evaluation_stage, status, submission_count
    questions[]
      field_id, statement, scale
      categories[]: label, count, percentage, positive
      answered_count, missing_count, invalid_count, excluded_count
      positive_count, positive_percentage, positive_definition
    skipped_field_ids
```

Groups and forms remain separate. No matching submissions is a successful empty report with null ratings and statement metadata. Connection, authentication, missing mappings or unsupported form setup return `WP_Error`, not empty data.

## Calculations

Each category percentage is `category count / valid answered count * 100`. Positive percentage is `positive count / valid answered count * 100`. Keep returned precision for rendering; format only the displayed labels. Small rounded segments may not sum to exactly 100 even though the underlying percentages do.

Missing answers, unrecognised choices, ambiguous tokens and N/A are not in the denominator. Counts identify why answers were excluded. A real 0% is different from null. Stored numeric choice scores are never used to infer response labels, including already-reversed scales. Known Likert row:choice tokens are decoded using form row metadata.

Default positive categories: Agree/Strongly agree, Somewhat satisfied/Extremely satisfied, or Yes. For agreement questions this means agreement with the statement, not necessarily favourable leadership behaviour. In particular, the short-term-goals question requires a confirmed policy before its badge is described as favourable growth. Its raw distribution must never be reversed.

## Per-question configuration

An optional `manager_questions` object in the existing profile JSON can exclude fields or specify positive labels:

```json
{
  "manager_questions": {
    "44": {"enabled": false},
    "19": {
      "enabled": true,
      "positive_choices": ["Strongly disagree", "Disagree"]
    }
  }
}
```

This is a merge fragment, not a complete profile. Preserve the existing fields, custom items, overrides, enabled state and stage. The field 19 direction above is only an example requiring confirmation; do not apply it automatically. Configured positive labels must exist in the source scale or reporting returns an error.

All supported scales are discovered independently of capability mappings, so satisfaction, employment and Yes/No questions can appear without being added to the leadership catalog. If the form has other Yes/No or supported-scale questions that should not appear, explicitly disable them through `manager_questions`.

## Program aliases

When Julian's newer `TLE_Report_Service` exposes `program_filter_values()` and `canonical_program()`, the Manager service uses them and deduplicates overlapping retrieved IDs. On the clean backend base without those methods, use the exact source program value. This patch deliberately does not overwrite Julian's program-alias implementation.

## Tests

```bash
php tests/backend-test.php
php tests/manager-report-test.php
```

The current offline suites contain 70 existing checks plus 40 Manager checks. They cover distributions, reversed scores, valid denominator, invalid/missing/N/A answers, Likert tokens, arbitrary form IDs, filtering, grouping, no-data cases, errors and configuration. These tests use fixtures, not the client's API.

## Integration ownership

The backend changes package does not modify admin/pdf-export.php, assets, dashboard templates or the shortcode. The separately labelled integration test copy includes Julian's frontend plus experimental connections; do not copy or commit that whole test copy into the backend branch. Verify browser requests, current live field metadata, source values and rendered PDF against the approved mock-up in Local. The mock-up duplicates an awareness statement; the service uses actual source field labels, including the short-term-goals item.
