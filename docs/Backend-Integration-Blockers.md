# Backend and Integration Blockers

| Detail | Value |
| --- | --- |
| Author | Dani Adonai |
| Updated | 11 October 2026 |

The central backend retrieves records and produces configured scores for all four current forms. All 70 standalone tests passed. The items below are the remaining data, validation and integration issues; they do not mean retrieval has failed.

## 1. Outstanding items

| Item | Current evidence | Effect | Owner / next action |
| --- | --- | --- | --- |
| Missing participant and program context | Historical submissions have missing participant ID, program group, program or cohort values. Latest identified participant counts: Form 37: 59; Form 40: 0; Form 43: 1; Form 50: 0. | Reliable participant-linked stage comparisons and filtering of incomplete historical data are limited. | Client to populate or correct the dataset; I will retest after the update. |
| No verified participant link across stages | The earlier test found no matching ID between the one complete delayed submission and the complete pre-program submissions tested. | We cannot claim individual growth from cohort averages. | Client to confirm consistent participant IDs and context; I will rerun matching and check duplicates. |
| Ten document questions remain unmatched | Forms 37, 40 and 43 each match 38 of the client's 48 document questions. | Some capability averages use only part of the document's question set. | PM to clarify with the client whether fields are absent, wording differs, or mappings/document need updating. |
| Scoring assumptions are not finally approved | Tested reports remain `provisional_adjusted`. | Passing calculation tests does not establish approved or validated leadership scores. | PM/client to confirm mappings, score direction, scale conversion and averaging policies. |
| Manager coverage is incomplete | Form 50 has 15 custom mapped questions covering 14 capabilities. | Collaboration, belonging and intrinsic motivation are unavailable under the current mapping. | PM/client to confirm the intended manager measures and whether additional questions or mappings are required. |
| Two browser PDF values differ | Networks: PDF 4.08 versus terminal 4.07. Place-attachment: PDF 5.58 versus terminal 5.57. | Exact PDF score agreement is not yet verified. | Julian to trace values within one browser export; I will help if the evidence points to backend processing. |
| Dashboard browser integration is unverified | Backend route tests passed, but attempts on the dashboard found neither `wpApiSettings` nor `wp.apiFetch` available. | Backend availability does not yet prove the dashboard is fetching live reports. | Julian to configure authenticated browser requests and verify returned selections and values. |

### Current working instruction

Our PM told me to use the available questions for now and said he would contact the client about the additional questions. The ten unmatched items are therefore recorded for clarification rather than replaced with guessed equivalents.

## 2. The ten unmatched document questions

These questions exist in the client's ELF growth-area document sent by email. Their corresponding fields have not been matched in the current Forms 37, 40 and 43.

| Capability | Document question |
| --- | --- |
| Clarity of purpose | I have a clear idea of how projects will be completed. |
| Balanced processing | I often find that I am overly critical about myself. |
| Tolerance for complexity | It is frightening just how messy the world is. |
| Capacity for informal influence | I inspire others to want to achieve their goals. |
| Capacity for collaboration | I am open and honest with those around me. |
| Capacity to establish networks | I introduce myself to the person next to me while waiting in line. |
| Creative decision-making | I prioritise thinking outside the box over tried-and-tested solutions. |
| Capacity to foster belonging | When I see two people conflicting at work, I take steps to help them resolve it. |
| Place-attachment | I consider the impact my decisions will have on my community. |
| Extra-role behaviours | I take care not to interrupt colleagues even if I need information from them. |

The matching test reported no ambiguous fields or other profile issues. That does not establish full document coverage or prove that an unmatched question has no equivalent under different wording. Confirm any proposed equivalent before adding an override.

## 3. Client dataset corrections needed

### Identity and context

Please confirm and populate the same participant identifier across stages, together with the correct program group, program and cohort. Form 50 still needs a shared participant identifier mapped. Some context fields exist but their historical values are blank.

Adding a field to a form alone does not fill historical submissions. Existing entries need correction where appropriate before they can be reliably linked or filtered. Names should not be used as the only joining key.

### Scoring agreement

Please confirm:

1. The document and manager capability mappings, including changed wording.
2. Reverse-scoring directions, including items whose choice scores are already reversed.
3. Scale conversions, including percentage-category and N/A handling.
4. Equal weighting of available question means and the missing-answer policy.
5. How repeated submissions and multiple manager responses should be handled.

The current implementation excludes unavailable answers rather than scoring them as zero. Its equal-question averaging is not participant-first averaging, and repeated submissions are not deduplicated by participant.

## 4. PDF discrepancy: evidence and next check

| Selection | Value |
| --- | --- |
| Program | I-LEAD Young Professionals |
| Cohort | 2026 |
| Stage | Pre-program |

| Capability | Exported PDF | Terminal backend, adapter and PDF data function |
| --- | ---: | ---: |
| Capacity to establish networks | 4.08 | 4.07 |
| Place-attachment | 5.58 | 5.57 |

Fifteen of the 17 PDF scores matched the terminal results at the comparison test. The functions loaded from the expected installed plugin folder. Restarting Local did not remove the two differences. The cause has not been established, so we should not assume it is a calculation or formatting bug.

Julian should capture the selected filters, selected form IDs and the two scores immediately after retrieval and immediately before rendering within the same browser export request. That will distinguish request/runtime differences from later transformations. Do not change backend values simply to force agreement with the PDF.

## 5. Integration work still to finish

These are frontend implementation tasks or known interface limits, rather than failed backend retrieval:

| Work | Current position | Next step |
| --- | --- | --- |
| Final PDF exporter connection | The modified exporter was a temporary live-data test. | Julian to connect his final implementation to the central service. |
| Multiple programs / same-stage forms | The full report returns separate groups and series. The simple PDF adapter rejects ambiguous combinations. | Julian to use separate requests or render the full report's groups and series. |
| Manager stage integration | Backend returns manager scores; the old exporter was based on three stages. | Julian to handle the manager data in his implementation. |
| Configuration in another environment | Saved profiles are WordPress options, not automatically transferred with PHP files. | Configure/import and verify profiles before testing on another machine. |

## 6. Additional verification and unavailable measures

| Item | Position |
| --- | --- |
| Response completeness | We have not audited whether every manager submission answered all 15 mapped questions. |
| Benchmark / overall score / social desirability | No defined live source or approved calculation has been integrated for these outputs; they remain unavailable. Confirm whether they are required and what source/method to use. |
| Final cross-stage validation | Retest filtered and participant-linked reports after the client updates identity/context data and confirms duplicate handling. |

## 7. What is already working

1. All four forms returned live records through the Gravity Forms REST provider.
2. Forms 37, 40 and 43 returned scores across all 17 capabilities using matched questions.
3. Form 50 returned scores across its 14 represented capabilities.
4. The central report service and adapter work without the old Form 37 wrapper.
5. A PDF containing live scores was generated and downloaded.
6. All 70 standalone backend tests passed after cleanup, and the latest live score check found no invalid returned scores.

The tests establish implementation behaviour. Client data corrections, scoring approval and frontend integration checks remain separate follow-up work.
