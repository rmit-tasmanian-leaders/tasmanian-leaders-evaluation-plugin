<?php

/**
 * Front-End Evaluation Dashboard Template
 *
 * Provides the Sprint 2 dashboard structure for the Tasmanian Leaders
 * evaluation reporting plugin.
 *
 * The controls currently demonstrate the reporting workflow and will be
 * connected to the shared evaluation data service during later Sprint 2 work.
 *
 * @package TasmanianLeadersEvaluation
 */

// Prevent direct access to this template outside WordPress.
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="tle-dashboard">

    <header class="tle-dashboard__final-header">
        <div class="tle-dashboard__wordmark">
            <img
                src="<?php
                    echo esc_url(
                        plugins_url(
                            'assets/images/tasmanian-leaders-logo-black.png',
                            dirname(__DIR__) . '/tasmanian-leaders-evaluation.php'
                        )
                    );
                ?>"
                alt="Tasmanian Leaders"
            >
        </div>

        <div class="tle-dashboard__header-divider" aria-hidden="true"></div>

        <div class="tle-dashboard__report-tabs">
            <button
                type="button"
                class="tle-dashboard__report-tab tle-dashboard__report-tab--active"
            >
                Create new report
            </button>
        </div>
    </header>
    <form
    id="tle-pdf-export-form"
    method="post"
    action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
    enctype="multipart/form-data"
>
    <input type="hidden" name="action" value="tle_export_pdf">

    <?php wp_nonce_field('tle_export_pdf_action', 'tle_export_pdf_nonce'); ?>

    <input type="hidden" name="report_type" value="Final ELF Evaluation Report">
    <input type="hidden" name="comparison" value="Pre-program / Completion / 3-Month Delay">
    <div class="tle-dashboard__layout">

        <aside class="tle-dashboard__sidebar">

            <section
                id="tle-report-setup"
                class="tle-dashboard__card tle-dashboard__configuration"
                aria-labelledby="tle-report-setup-heading"
            >
                <p class="tle-dashboard__step">1</p>

                <h2 id="tle-report-setup-heading" class="tle-dashboard__section-title">
                    Program Selection
                </h2>

                <div class="tle-dashboard__programs" data-tle-programs>

                    <div
                        class="tle-dashboard__program-row"
                        data-tle-program-row
                        data-tle-primary-program-row
                    >
                        <div class="tle-dashboard__field tle-dashboard__program-search">
                            <label for="tle-program-0">Program</label>

                            <div class="tle-dashboard__program-search-wrap">
                                <input
                                    id="tle-program-0"
                                    type="text"
                                    placeholder="Search program or year"
                                    autocomplete="off"
                                    data-tle-program-search
                                >

                                <input
                                    type="hidden"
                                    name="program"
                                    value=""
                                    data-tle-primary-program-value
                                >

                                <div
                                    class="tle-dashboard__program-suggestions"
                                    data-tle-program-suggestions
                                    hidden
                                ></div>
                            </div>
                        </div>

                        <input
                            type="hidden"
                            name="evaluation_points[]"
                            value="pre-program"
                        >
                        <input
                            type="hidden"
                            name="evaluation_points[]"
                            value="completion"
                        >
                        <input
                            type="hidden"
                            name="evaluation_points[]"
                            value="three-month-delay"
                        >

                        <label
                            class="tle-dashboard__checkbox tle-dashboard__manager-option"
                            data-tle-manager-option
                            hidden
                        >
                            <input
                                type="checkbox"
                                name="evaluation_points[]"
                                value="manager"
                            >
                            <span>Manager feedback (optional)</span>
                        </label>
                    </div>

                </div>

                <input
                    id="tle-cohort"
                    type="hidden"
                    name="cohort"
                    value=""
                    data-tle-cohort
                >

                <div
                    class="tle-dashboard__comparison"
                    data-tle-comparison
                >
                    <h3 class="tle-dashboard__comparison-title">
                        Comparison (optional)
                    </h3>

                    <p
                        class="tle-dashboard__comparison-label"
                        data-tle-comparison-label
                        hidden
                    >
                        Compare to:
                    </p>

                    <div
                        class="tle-dashboard__comparison-rows"
                        data-tle-comparison-rows
                    ></div>

                    <button
                        type="button"
                        class="tle-dashboard__add-program tle-dashboard__add-comparison"
                        data-tle-add-comparison
                    >
                        + Add comparison
                    </button>

                    <template id="tle-comparison-row-template">
                        <div
                            class="tle-dashboard__comparison-row"
                            data-tle-comparison-row
                        >
                            <button
                                type="button"
                                class="tle-dashboard__remove-comparison"
                                data-tle-remove-comparison
                                aria-label="Remove comparison"
                            >
                                ×
                            </button>

                            <div class="tle-dashboard__program-search-wrap">
                                <input
                                    type="text"
                                    placeholder="Enter a program name here"
                                    autocomplete="off"
                                    data-tle-comparison-search
                                >
                                <input
                                    type="hidden"
                                    name="comparison_programs[]"
                                    value=""
                                    data-tle-comparison-program-value
                                >

                                <input
                                    type="hidden"
                                    name="comparison_cohorts[]"
                                    value=""
                                    data-tle-comparison-cohort-value
                                >

                                <div
                                    class="tle-dashboard__program-suggestions"
                                    data-tle-comparison-suggestions
                                    hidden
                                >
                                    <button
                                        type="button"
                                        data-tle-comparison-option="I-LEAD Young Professionals"
                                    >
                                        I-LEAD Young Professionals
                                    </button>

                                    <button
                                        type="button"
                                        data-tle-comparison-option="I-LEAD Women in Community"
                                    >
                                        I-LEAD Women in Community
                                    </button>

                                    <button
                                        type="button"
                                        data-tle-comparison-option="I-LEAD Wine Industry"
                                    >
                                        I-LEAD Wine Industry
                                    </button>

                                    <button
                                        type="button"
                                        data-tle-comparison-option="I-LEAD Pride"
                                    >
                                        I-LEAD Pride
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            <section
                id="tle-appearance"
                class="tle-dashboard__card"
                aria-labelledby="tle-appearance-heading"
            >
                <p class="tle-dashboard__step">2</p>

                <h2 id="tle-appearance-heading" class="tle-dashboard__section-title">
                    Appearance
                </h2>

                <div class="tle-dashboard__appearance-grid">
                    <div class="tle-dashboard__cover-control">
                        <span class="tle-dashboard__field-label">Cover Image</span>

                        <label
                            class="tle-dashboard__cover-upload"
                            for="tle-cover-image"
                            data-tle-cover-upload
                        >
                            <input
                                id="tle-cover-image"
                                class="tle-dashboard__cover-input"
                                type="file"
                                name="cover_image"
                                accept="image/jpeg,image/png"
                                data-tle-cover-input
                            >

                            <span
                                class="tle-dashboard__cover-placeholder"
                                data-tle-cover-placeholder
                            >
                                Upload an image
                            </span>

                            <img
                                class="tle-dashboard__cover-preview"
                                data-tle-cover-preview
                                alt="Selected report cover preview"
                                hidden
                            >
                        </label>
                    </div>

                    <fieldset class="tle-dashboard__fieldset tle-dashboard__colour-palette">
                        <legend>Colour Palette</legend>

                        <p class="tle-dashboard__field-help">
                            Select a preset colour palette or upload an image to display more options.
                        </p>

                        <p class="tle-dashboard__palette-label">Brand Colours</p>

                        <div class="tle-dashboard__palette-options">
                            <label class="tle-dashboard__palette-choice" title="Aqua">
                                <input type="radio" name="report_colour" value="aqua" checked>
                                <span
                                    class="tle-dashboard__palette-swatch"
                                    style="--tle-swatch: #a2f8ff;"
                                ></span>
                            </label>

                            <label class="tle-dashboard__palette-choice" title="Green">
                                <input type="radio" name="report_colour" value="green">
                                <span
                                    class="tle-dashboard__palette-swatch"
                                    style="--tle-swatch: #5af474;"
                                ></span>
                            </label>

                            <label class="tle-dashboard__palette-choice" title="Yellow">
                                <input type="radio" name="report_colour" value="yellow">
                                <span
                                    class="tle-dashboard__palette-swatch"
                                    style="--tle-swatch: #fff25c;"
                                ></span>
                            </label>

                            <label class="tle-dashboard__palette-choice" title="Orange">
                                <input type="radio" name="report_colour" value="orange">
                                <span
                                    class="tle-dashboard__palette-swatch"
                                    style="--tle-swatch: #ff643c;"
                                ></span>
                            </label>

                            <label class="tle-dashboard__palette-choice" title="Pink">
                                <input type="radio" name="report_colour" value="pink">
                                <span
                                    class="tle-dashboard__palette-swatch"
                                    style="--tle-swatch: #ffb6ff;"
                                ></span>
                            </label>
                        </div>

                        <p class="tle-dashboard__palette-label">
                            Based on your Cover Image
                        </p>

                        <div
                            class="tle-dashboard__palette-options tle-dashboard__palette-options--image"
                            data-tle-image-colours
                        >
                            <?php for ($colour_index = 0; $colour_index < 5; $colour_index++) : ?>
                                <label
                                    class="tle-dashboard__palette-choice"
                                    title="Upload a cover image to generate this colour"
                                >
                                    <input
                                        type="radio"
                                        name="report_colour"
                                        value="custom"
                                        data-tle-image-colour-radio
                                        disabled
                                    >

                                    <span
                                        class="tle-dashboard__palette-swatch tle-dashboard__palette-swatch--unavailable"
                                        data-tle-image-colour-swatch
                                    ></span>
                                </label>
                            <?php endfor; ?>
                        </div>

                        <input
                            type="hidden"
                            name="report_colour_custom"
                            value=""
                            data-tle-report-colour-custom
                        >
                    </fieldset>
                </div>
            </section>

            <section
                id="tle-report-text"
                class="tle-dashboard__card tle-dashboard__text-customisation"
                aria-labelledby="tle-report-text-heading"
            >
                <p class="tle-dashboard__step">3</p>

                <h2 id="tle-report-text-heading" class="tle-dashboard__section-title">
                    Text Customisation
                </h2>

                <p class="tle-dashboard__field-help">
                    Edit text within the report here.
                </p>

                <div class="tle-dashboard__field">
                    <label for="tle-report-title">Report Title</label>

                    <input
                        id="tle-report-title"
                        type="text"
                        name="report_title"
                        placeholder="Title your report here"
                        data-tle-auto-title
                        data-tle-auto-text
                    >
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-purpose-text">Purpose Paragraph</label>

                    <textarea
                        id="tle-purpose-text"
                        name="purpose_text"
                        rows="3"
                        placeholder="Describe your report briefly"
                        data-tle-auto-purpose
                        data-tle-auto-text
                    ></textarea>
                </div>
                <div class="tle-dashboard__field">
                    <label for="tle-overview-intro">Overview Graph Explanation</label>
                    <p class="tle-dashboard__field-help">
                        Appears beneath the Overview chart on page 2 of the PDF.
                    </p>
                    <textarea
                        id="tle-overview-intro"
                        name="overview_intro_text"
                        rows="4"
                        data-tle-overview-intro
                        data-tle-auto-text
                        placeholder="Explain what the Overview graph measures"
                    ></textarea>
                </div>

                <div class="tle-dashboard__text-grid tle-dashboard__text-grid--two">
                    <div class="tle-dashboard__field">
                        <label for="tle-highlight-1">
                            Highlight #1 (Self-awareness)
                        </label>

                        <input
                            id="tle-highlight-1"
                            type="text"
                            name="highlight_text_1"
                            data-tle-auto-text
                            data-tle-default="Participants saw an incredible improvement in self-awareness."
                        >
                    </div>

                    <div class="tle-dashboard__field">
                        <label for="tle-highlight-2">
                            Highlight #2 (Tolerance for ambiguity)
                        </label>

                        <input
                            id="tle-highlight-2"
                            type="text"
                            name="highlight_text_2"
                            data-tle-auto-text
                            data-tle-default="A major increase was seen here."
                        >
                    </div>

                    <div class="tle-dashboard__field">
                        <label for="tle-highlight-3">
                            Highlight #3 (Creative decision-making)
                        </label>

                        <input
                            id="tle-highlight-3"
                            type="text"
                            name="highlight_text_3"
                            data-tle-auto-text
                            data-tle-default="A significant improvement to a necessary skill."
                        >
                    </div>

                    <div class="tle-dashboard__field">
                        <label for="tle-highlight-4">
                            Highlight #4 (Capacity to foster belonging)
                        </label>

                        <input
                            id="tle-highlight-4"
                            type="text"
                            name="highlight_text_4"
                            data-tle-auto-text
                            data-tle-default="Participants saw an incredible improvement in fostering belonging."
                        >
                    </div>
                </div>

                <div class="tle-dashboard__text-section">
                    <h3 class="tle-dashboard__text-section-title">Insight</h3>

                    <div class="tle-dashboard__text-grid tle-dashboard__text-grid--three">
                        <div class="tle-dashboard__field">
                            <label for="tle-insight-self-awareness">Self-awareness</label>
                            <textarea
                                id="tle-insight-self-awareness"
                                name="insight_self_awareness_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-social-awareness">Social awareness</label>
                            <textarea
                                id="tle-insight-social-awareness"
                                name="insight_social_awareness_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-situational-awareness">Situational awareness</label>
                            <textarea
                                id="tle-insight-situational-awareness"
                                name="insight_situational_awareness_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-clarity-purpose">Clarity of purpose</label>
                            <textarea
                                id="tle-insight-clarity-purpose"
                                name="insight_clarity_purpose_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-strategic-foresight">Strategic foresight</label>
                            <textarea
                                id="tle-insight-strategic-foresight"
                                name="insight_strategic_foresight_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-balanced-processing">Balanced processing</label>
                            <textarea
                                id="tle-insight-balanced-processing"
                                name="insight_balanced_processing_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-self-compassion">Self-compassion</label>
                            <textarea
                                id="tle-insight-self-compassion"
                                name="insight_self_compassion_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>
                    </div>

                    <div class="tle-dashboard__growth-editor" data-tle-growth-editor-section="insight" hidden>
    <h4 class="tle-dashboard__growth-editor-heading">Key Growth Program Summaries</h4>
    <p class="tle-dashboard__field-help">Automatically calculated from Pre-program to Delayed. Edit any summary before exporting, or use Reset to Automatic.</p>
    <div class="tle-dashboard__growth-editor-programs" data-tle-growth-editor-programs></div>
    <p class="tle-dashboard__field-help" data-tle-growth-editor-status role="status" aria-live="polite" hidden></p>
</div>

                    <div class="tle-dashboard__field tle-dashboard__quote-field">
                        <label for="tle-insight-quote">
                            Participant Quote (optional)
                        </label>

                        <textarea
                            id="tle-insight-quote"
                            name="insight_quote"
                            rows="2"
                            placeholder="Enter a participant's quote here"
                        ></textarea>
                    </div>

                    <div
                        class="tle-dashboard__field tle-dashboard__comparison-text-field"
                        data-tle-comparison-text-section
                        hidden
                    >
                        <label for="tle-insight-comparison">
                            Comparison
                        </label>

                        <textarea
                            id="tle-insight-comparison"
                            name="insight_comparison_text"
                            rows="3"
                            data-tle-comparison-text
                            data-tle-auto-text
                            placeholder="Enter comparison context here"
                        ></textarea>
                    </div>
                </div>

                <div class="tle-dashboard__text-section">
                    <h3 class="tle-dashboard__text-section-title">Influence</h3>

                    <div class="tle-dashboard__text-grid tle-dashboard__text-grid--three">
                        <div class="tle-dashboard__field">
                            <label for="tle-influence-ambiguity">Tolerance for ambiguity</label>
                            <textarea
                                id="tle-influence-ambiguity"
                                name="influence_tolerance_ambiguity_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-informal">Capacity for informal influence</label>
                            <textarea
                                id="tle-influence-informal"
                                name="influence_informal_influence_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-complexity">Tolerance for complexity</label>
                            <textarea
                                id="tle-influence-complexity"
                                name="influence_tolerance_complexity_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-collaboration">Capacity for collaboration</label>
                            <textarea
                                id="tle-influence-collaboration"
                                name="influence_collaboration_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-networks">Capacity to establish networks</label>
                            <textarea
                                id="tle-influence-networks"
                                name="influence_networks_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-creative">Creative decision-making</label>
                            <textarea
                                id="tle-influence-creative"
                                name="influence_creative_decision_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>
                    </div>

                    <div class="tle-dashboard__growth-editor" data-tle-growth-editor-section="influence" hidden>
    <h4 class="tle-dashboard__growth-editor-heading">Key Growth Program Summaries</h4>
    <p class="tle-dashboard__field-help">Automatically calculated from Pre-program to Delayed. Edit any summary before exporting, or use Reset to Automatic.</p>
    <div class="tle-dashboard__growth-editor-programs" data-tle-growth-editor-programs></div>
    <p class="tle-dashboard__field-help" data-tle-growth-editor-status role="status" aria-live="polite" hidden></p>
</div>

                    <div class="tle-dashboard__field tle-dashboard__quote-field">
                        <label for="tle-influence-quote">
                            Participant Quote (optional)
                        </label>

                        <textarea
                            id="tle-influence-quote"
                            name="influence_quote"
                            rows="2"
                            placeholder="Enter a participant's quote here"
                        ></textarea>
                    </div>

                    <div
                        class="tle-dashboard__field tle-dashboard__comparison-text-field"
                        data-tle-comparison-text-section
                        hidden
                    >
                        <label for="tle-influence-comparison">
                            Comparison
                        </label>

                        <textarea
                            id="tle-influence-comparison"
                            name="influence_comparison_text"
                            rows="3"
                            data-tle-comparison-text
                            data-tle-auto-text
                            placeholder="Enter comparison context here"
                        ></textarea>
                    </div>
                </div>

                <div class="tle-dashboard__text-section">
                    <h3 class="tle-dashboard__text-section-title">Impact</h3>

                    <div class="tle-dashboard__text-grid tle-dashboard__text-grid--three">
                        <div class="tle-dashboard__field">
                            <label for="tle-impact-belonging">Capacity to foster belonging</label>
                            <textarea
                                id="tle-impact-belonging"
                                name="impact_belonging_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-motivation">Capacity to foster intrinsic motivation</label>
                            <textarea
                                id="tle-impact-motivation"
                                name="impact_intrinsic_motivation_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-place">Place-attachment</label>
                            <textarea
                                id="tle-impact-place"
                                name="impact_place_attachment_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-extra-role">Extra-role behaviours</label>
                            <textarea
                                id="tle-impact-extra-role"
                                name="impact_extra_role_text"
                                data-tle-auto-text
                                placeholder="Automatically generated from report data unless edited."
                             rows="3"></textarea>
                        </div>
                    </div>

                    <div class="tle-dashboard__growth-editor" data-tle-growth-editor-section="impact" hidden>
    <h4 class="tle-dashboard__growth-editor-heading">Key Growth Program Summaries</h4>
    <p class="tle-dashboard__field-help">Automatically calculated from Pre-program to Delayed. Edit any summary before exporting, or use Reset to Automatic.</p>
    <div class="tle-dashboard__growth-editor-programs" data-tle-growth-editor-programs></div>
    <p class="tle-dashboard__field-help" data-tle-growth-editor-status role="status" aria-live="polite" hidden></p>
</div>

                    <div class="tle-dashboard__field tle-dashboard__quote-field">
                        <label for="tle-impact-quote">
                            Participant Quote (optional)
                        </label>

                        <textarea
                            id="tle-impact-quote"
                            name="impact_quote"
                            rows="2"
                            placeholder="Enter a participant's quote here"
                        ></textarea>
                    </div>

                    <div
                        class="tle-dashboard__field tle-dashboard__comparison-text-field"
                        data-tle-comparison-text-section
                        hidden
                    >
                        <label for="tle-impact-comparison">
                            Comparison
                        </label>

                        <textarea
                            id="tle-impact-comparison"
                            name="impact_comparison_text"
                            rows="3"
                            data-tle-comparison-text
                            data-tle-auto-text
                            placeholder="Enter comparison context here"
                        ></textarea>
                    </div>
                </div>


                <div
                    class="tle-dashboard__text-section"
                    data-tle-manager-text-section
                    hidden
                >
                    <h3 class="tle-dashboard__text-section-title">Manager feedback</h3>

                    <div class="tle-dashboard__field">
                        <label for="tle-manager-summary">Summary</label>

                        <textarea
                            id="tle-manager-summary"
                            name="manager_summary_text"
                            rows="4"
                            placeholder="Summarise the manager evaluation findings"
                        ><?php echo esc_textarea('Managers were asked the extent to which they agreed with the following statements, after their employee had completed Tasmanian Leaders’ leadership development.'); ?></textarea>
                    </div>
                </div>
            </section>

        </aside>

        <div class="tle-dashboard__final-actions">
            <button
                type="button"
                class="tle-dashboard__action tle-dashboard__action--secondary"
                onclick="window.history.back();"
            >
                Back
            </button>

            <div class="tle-dashboard__final-actions-right">

                <button
                    type="submit"
                    class="tle-dashboard__action tle-dashboard__action--primary"
                >
                    Export as PDF
                </button>
            </div>
        </div>

    </div>

</form>

</div>
