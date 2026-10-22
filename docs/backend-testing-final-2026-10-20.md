# Final Backend Testing Report

Project: Tasmanian Leaders Evaluation Dashboard
Task: [Infrastructure] - Test Backend : 120
Tester: Julian Klestorny (Dev 2)
Test date: 20 October 2026
Branch: integration/manager-reporting
Tested commit: 695646f

## 1. Environment and installation

- LocalWP site: tasmanian-leaders-test
- Plugin installation verified against local Git repository.
- Local repository synchronised with GitHub.
- Gravity Forms API configuration entries present.
- PHP syntax validation: 15/15 files passed.

## 2. Automated backend tests

- Backend tests: 70/70 passed.
- Manager reporting tests: 40/40 passed.
- Total: 110/110 passed.

Tests cover form discovery, retrieval, scoring,
normalisation, filtering, mapping validation,
pagination, Manager calculations and error handling.

## 3. REST API access control

Unauthenticated requests were rejected:

- /evaluations: HTTP 401
- /manager-evaluations: HTTP 401
- /comparison-options: HTTP 401
- /forms: HTTP 401

Result: 4/4 passed.

## 4. Authenticated live data retrieval

- Form discovery: HTTP 200, 22 forms found.
- Comparison options: HTTP 200, 9 options.
- Filtered evaluation: HTTP 200.

I-LEAD Young Professionals 2026, Pre-program:
- 20 submissions.
- 20 reported participants.
- 860 evaluation answer records.
- 1 reporting group.

## 5. Manager reporting

- Manager Form 50 successfully retrieved.
- HTTP 200.
- 27 Manager submissions.
- 18 statement rows.
- Zero calculation validation failures.
- Program/year filter did not attribute unrelated
  Manager responses to the selected program.

## 6. Error handling and missing data

- Invalid program filter: HTTP 400.
- Malformed form IDs: HTTP 400.
- Invalid Manager filter: HTTP 400.
- Unavailable cohort: HTTP 200, zero records.

Result: 4/4 passed.

## 7. Backend-to-PDF consistency

I-LEAD Young Professionals 2026:
- Insight: 4.47/7 (64%) - matched.
- Influence: 4.42/7 (63%) - matched.
- Impact: 5.17/7 (74%) - matched.

I-LEAD Women in Community 2025:
- Insight: 4.88/7 (70%) - matched.
- Influence: 5.67/7 (81%) - matched.
- Impact: 6.27/7 (90%) - matched.

Result: 6/6 comparisons matched.

## 8. Known limitations

- Historical Manager responses lack reliable
  program/cohort attribution. The PDF identifies
  unfiltered Manager responses accordingly.
- Incomplete historical participant identifiers and
  context limit certain cross-stage comparisons.
- Capability mappings and scoring rules remain
  subject to final client confirmation.
- WordPress access roles require final client approval.
- Some historical data-quality and coverage issues
  remain documented in the backend blocker register.
- Direct inspection of the local evaluation database
  table was not performed in this final test. The
  configured remote Gravity Forms data source was
  successfully tested instead.

## 9. Conclusion

All executed backend tests passed.

Live retrieval, filtering, Manager calculations,
access control, error handling and PDF score
consistency were verified successfully.

No new backend defects were identified in the
executed cases.

The implementation is ready for team review,
subject to the documented client-data and
scoring-validation limitations.

Frontend testing and final PM sign-off remain
separate tasks.