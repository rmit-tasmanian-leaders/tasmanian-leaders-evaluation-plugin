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
        <div class="tle-dashboard__wordmark" aria-label="Tasmanian Leaders">
            <span>Tasmanian</span>
            <span>Leaders</span>
        </div>

        <div class="tle-dashboard__header-divider" aria-hidden="true"></div>

        <div class="tle-dashboard__report-tabs">
            <button
                type="button"
                class="tle-dashboard__report-tab tle-dashboard__report-tab--active"
            >
                Create new report
            </button>

            <button
                type="button"
                class="tle-dashboard__report-tab"
                disabled
                aria-disabled="true"
                title="Saved report functionality is not connected yet."
            >
                Open saved report
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

                    <div class="tle-dashboard__program-row" data-tle-program-row>
                        <div class="tle-dashboard__field tle-dashboard__program-search">
                            <label for="tle-program-0">Program</label>

                            <div class="tle-dashboard__program-search-wrap">
                                <input
                                    id="tle-program-0"
                                    type="text"
                                    name="program"
                                    placeholder="Search for a program"
                                    autocomplete="off"
                                    data-tle-program-search
                                >

                                <div
                                    class="tle-dashboard__program-suggestions"
                                    data-tle-program-suggestions
                                    hidden
                                >
                                    <button
                                        type="button"
                                        data-tle-program-option="I-LEAD Young Professionals"
                                    >
                                        I-LEAD Young Professionals
                                    </button>

                                    <button
                                        type="button"
                                        data-tle-program-option="Emerging Leaders Program"
                                    >
                                        Emerging Leaders Program
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="tle-dashboard__evaluation-toggle"
                            data-tle-evaluation-toggle
                            aria-expanded="false"
                            hidden
                        >
                            Evaluation Points
                        </button>

                        <div
                            class="tle-dashboard__evaluation-menu"
                            data-tle-evaluation-menu
                            hidden
                        >
                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="evaluation_points[]"
                                    value="pre-program"
                                    checked
                                >
                                <span>Pre-program</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="evaluation_points[]"
                                    value="completion"
                                    checked
                                >
                                <span>Completion</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="evaluation_points[]"
                                    value="three-month-delay"
                                >
                                <span>3-Month Delayed</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="evaluation_points[]"
                                    value="manager"
                                >
                                <span>Manager Evaluation</span>
                            </label>
                        </div>

                        <button
                            type="button"
                            class="tle-dashboard__remove-program"
                            data-tle-remove-program
                            hidden
                        >
                            Remove program
                        </button>
                    </div>

                </div>

                <button
                    type="button"
                    class="tle-dashboard__add-program"
                    data-tle-add-program
                >
                    + Add program
                </button>

                <template id="tle-program-row-template">
                    <div class="tle-dashboard__program-row" data-tle-program-row>
                        <div class="tle-dashboard__field tle-dashboard__program-search">
                            <label for="tle-program-__INDEX__">Program</label>

                            <div class="tle-dashboard__program-search-wrap">
                                <input
                                    id="tle-program-__INDEX__"
                                    type="text"
                                    name="additional_programs[__INDEX__][program]"
                                    placeholder="Search for a program"
                                    autocomplete="off"
                                    data-tle-program-search
                                >

                                <div
                                    class="tle-dashboard__program-suggestions"
                                    data-tle-program-suggestions
                                    hidden
                                >
                                    <button
                                        type="button"
                                        data-tle-program-option="I-LEAD Young Professionals"
                                    >
                                        I-LEAD Young Professionals
                                    </button>

                                    <button
                                        type="button"
                                        data-tle-program-option="Emerging Leaders Program"
                                    >
                                        Emerging Leaders Program
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="tle-dashboard__evaluation-toggle"
                            data-tle-evaluation-toggle
                            aria-expanded="false"
                            hidden
                        >
                            Evaluation Points
                        </button>

                        <div
                            class="tle-dashboard__evaluation-menu"
                            data-tle-evaluation-menu
                            hidden
                        >
                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="additional_programs[__INDEX__][evaluation_points][]"
                                    value="pre-program"
                                    checked
                                >
                                <span>Pre-program</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="additional_programs[__INDEX__][evaluation_points][]"
                                    value="completion"
                                    checked
                                >
                                <span>Completion</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="additional_programs[__INDEX__][evaluation_points][]"
                                    value="three-month-delay"
                                >
                                <span>3-Month Delayed</span>
                            </label>

                            <label class="tle-dashboard__checkbox">
                                <input
                                    type="checkbox"
                                    name="additional_programs[__INDEX__][evaluation_points][]"
                                    value="manager"
                                >
                                <span>Manager Evaluation</span>
                            </label>
                        </div>

                        <button
                            type="button"
                            class="tle-dashboard__remove-program"
                            data-tle-remove-program
                        >
                            Remove program
                        </button>
                    </div>
                </template>

                <div class="tle-dashboard__field">
                    <label for="tle-cohort">Cohort</label>

                    <select id="tle-cohort" name="cohort">
                        <option>2026</option>
                        <option>2025</option>
                    </select>
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
                            data-tle-default="A major increase was seen here. This was very promising for the program."
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
                            <input
                                id="tle-insight-self-awareness"
                                type="text"
                                name="insight_self_awareness_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in self-awareness."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-social-awareness">Social awareness</label>
                            <input
                                id="tle-insight-social-awareness"
                                type="text"
                                name="insight_social_awareness_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in social awareness."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-situational-awareness">Situational awareness</label>
                            <input
                                id="tle-insight-situational-awareness"
                                type="text"
                                name="insight_situational_awareness_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in situational awareness."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-clarity-purpose">Clarity of purpose</label>
                            <input
                                id="tle-insight-clarity-purpose"
                                type="text"
                                name="insight_clarity_purpose_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in clarity of purpose."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-strategic-foresight">Strategic foresight</label>
                            <input
                                id="tle-insight-strategic-foresight"
                                type="text"
                                name="insight_strategic_foresight_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in strategic foresight."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-balanced-processing">Balanced processing</label>
                            <input
                                id="tle-insight-balanced-processing"
                                type="text"
                                name="insight_balanced_processing_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in balanced processing."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-insight-self-compassion">Self-compassion</label>
                            <input
                                id="tle-insight-self-compassion"
                                type="text"
                                name="insight_self_compassion_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in self-compassion."
                            >
                        </div>
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
                </div>

                <div class="tle-dashboard__text-section">
                    <h3 class="tle-dashboard__text-section-title">Influence</h3>

                    <div class="tle-dashboard__text-grid tle-dashboard__text-grid--three">
                        <div class="tle-dashboard__field">
                            <label for="tle-influence-ambiguity">Tolerance for ambiguity</label>
                            <input
                                id="tle-influence-ambiguity"
                                type="text"
                                name="influence_tolerance_ambiguity_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in tolerance for ambiguity."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-informal">Capacity for informal influence</label>
                            <input
                                id="tle-influence-informal"
                                type="text"
                                name="influence_informal_influence_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in capacity for informal influence."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-complexity">Tolerance for complexity</label>
                            <input
                                id="tle-influence-complexity"
                                type="text"
                                name="influence_tolerance_complexity_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in tolerance for complexity."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-collaboration">Capacity for collaboration</label>
                            <input
                                id="tle-influence-collaboration"
                                type="text"
                                name="influence_collaboration_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in capacity for collaboration."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-networks">Capacity to establish networks</label>
                            <input
                                id="tle-influence-networks"
                                type="text"
                                name="influence_networks_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in capacity to establish networks."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-influence-creative">Creative decision-making</label>
                            <input
                                id="tle-influence-creative"
                                type="text"
                                name="influence_creative_decision_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in creative decision-making."
                            >
                        </div>
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
                </div>

                <div class="tle-dashboard__text-section">
                    <h3 class="tle-dashboard__text-section-title">Impact</h3>

                    <div class="tle-dashboard__text-grid tle-dashboard__text-grid--three">
                        <div class="tle-dashboard__field">
                            <label for="tle-impact-belonging">Capacity to foster belonging</label>
                            <input
                                id="tle-impact-belonging"
                                type="text"
                                name="impact_belonging_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in capacity to foster belonging."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-motivation">Capacity to foster intrinsic motivation</label>
                            <input
                                id="tle-impact-motivation"
                                type="text"
                                name="impact_intrinsic_motivation_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in capacity to foster intrinsic motivation."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-place">Place-attachment</label>
                            <input
                                id="tle-impact-place"
                                type="text"
                                name="impact_place_attachment_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in place-attachment."
                            >
                        </div>

                        <div class="tle-dashboard__field">
                            <label for="tle-impact-extra-role">Extra-role behaviours</label>
                            <input
                                id="tle-impact-extra-role"
                                type="text"
                                name="impact_extra_role_text"
                                data-tle-auto-text
                                data-tle-default="Participants demonstrated positive growth in extra-role behaviours."
                            >
                        </div>
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
                    type="button"
                    class="tle-dashboard__action tle-dashboard__action--secondary"
                    disabled
                    aria-disabled="true"
                    title="Saved report functionality is not connected yet."
                >
                    Save
                </button>

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
