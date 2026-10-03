# Form 37 Backend Handover

## Status

Working provisional development implementation.
Question mappings and scoring rules are inferred, not client-confirmed.
Do not describe these results as final validated leadership scores.

## Scope

Form 37: ELF1 Pre-program.
Data access uses the Gravity Forms REST API through the provider.
Forms 40 and 43 retain retrieval support, but adjusted capability scoring
currently supports only pre_program.
Form 50 requires field mapping and integration.

## Retrieval

get_all_entries() retrieves all matching submission pages.
get_all_normalised_entries() converts them into reporting records.
get_all_evaluations() validates the complete normalised dataset.

Complete retrieval checks API total_count, overlapping submission IDs,
empty intermediate pages and changes to the total during retrieval.
Failures return WP_Error rather than a partial report.

Submission IDs identify submissions, not participants.
Participant identity for Form 37 uses field 61.

## Provisional capability mapping

Insight:
- Self-awareness: 12.1, 12.5, 18.2, 18.3
- Social awareness: 12.2, 14
- Situational awareness: 12.4, 17.3
- Clarity of purpose: 12.3, 17.1
- Strategic foresight: 17.4, 13
- Balanced processing: 18.5
- Self-compassion: 17.2, 18.4

Influence:
- Tolerance for ambiguity: 19, 24, 25, 37.3
- Capacity for informal influence: 29, 30
- Tolerance for complexity: 26, 28, 37.6
- Capacity for collaboration: 32, 33, 37.5
- Capacity to establish networks: 27, 31, 34
- Creative decision-making: 37.1, 37.2, 37.4, 37.7

Impact:
- Capacity to foster belonging: 40, 44
- Capacity to foster intrinsic motivation: 41, 42, 45
- Place-attachment: 43, 46
- Extra-role behaviours: 39, 47

Q26, Q27 and Q37.1 placements are particularly uncertain.

## Provisional scoring

Most raw items use scores 1-7.

Reverse-scored questions:
12.2, 12.3, 12.5, 17.4, 18.2, 18.3,
19, 24, 25, 27, 28, 30, 31, 33,
37.5, 37.7, 44, 45, 46, 47.

Reverse formula: 8 - raw_score.
This list is inferred from wording and requires confirmation.
Q26 and Q37.1 retain their original direction pending review.

Q13:
Raw category scores 1-5 become 1, 2.5, 4, 5.5, 7.
Formula: 1 + (raw_score - 1) * 6 / 4.
This assumes equally spaced scored categories.
It does not use the uneven percentage intervals in the choice labels.

Q14:
Raw score 1 represents Not Applicable and is excluded.
Raw agreement scores 2-8 become 1-7 by subtracting 1.
The form's reversed metadata is not treated as an official scoring key.

Missing, invalid and N/A scores are excluded from capability calculations.
Unavailable question means are skipped.
A capability with no valid answers returns an empty stage object.

## Averaging

1. Adjust each Form 37 answer using the provisional scoring rules.
2. Average valid adjusted answers for each question.
3. Average the available question means equally within each capability.
4. Round the final capability average to two decimal places.

Intermediate adjusted question means are not rounded.
Questions receive equal weight even when their response counts differ.
This is not participant-first capability averaging.
Repeated submissions are not deduplicated by participant.
These choices require agreement before final reporting.

Original records, responses and raw question averages are preserved.
The change field remains based on raw question averages.

## Frontend integration

Endpoint:
GET /wp-json/tle/v1/evaluations

Example query:
?program=I-LEAD%20Pride&cohort=2026&evaluation_stage=pre_program

The endpoint currently requires a logged-in WordPress user.
Browser cookie authentication also needs a valid WordPress REST nonce.
The terminal route tests did not verify browser HTTP authentication.

Use capability_averages for the graph:
capability_averages.insight["Self-awareness"].pre_program
capability_averages.influence["Capacity for informal influence"].pre_program
capability_averages.impact["Place-attachment"].pre_program

Each capability is an axis label.
The pre_program values form the Pre-program line.
Frontend owns graph styling and rendering.

Use capability_scoring and scoring_status to identify provisional results.
Do not use raw question_averages as adjusted capability scores.
Treat an absent stage value as unavailable, not zero.
Do not fabricate Completion, Delay or Manager lines.

## Verified results

Form 37 complete retrieval:
302 active submissions, 302 unique submission IDs at test time.

I-LEAD Pride, cohort 2026:
13 participant keys, 13 submissions, 559 answer records.

All 17 adjusted capability averages returned.
Example: Place-attachment pre_program = 6.27.

Checks passed:
- PHP syntax checks
- REST pagination and filtering
- Multi-page normalisation and validation
- 12 scoring transformation checks
- 31 combined calculation checks, zero failures
- Registered route returns 200 for a local administrator
- Registered route returns 401 for an unauthenticated user

These tests establish implementation behaviour, not scoring validity.

## Remaining limitations

Confirm capability mappings, reverse directions, Q13/Q14 treatment,
missing-answer policy and duplicate-submission policy.

Forms 40 and 43 lack reliable shared participant/program/cohort fields.
Do not join participants by name alone.
Unfiltered stage averages do not establish participant-linked change.
Form 50 needs its own verified field and question mapping.

No frontend design changes are required for this backend handover.
