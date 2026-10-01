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

    <header class="tle-dashboard__header">
        <p class="tle-dashboard__eyebrow">Tasmanian Leaders</p>

        <h1 class="tle-dashboard__title">Evaluation Dashboard</h1>

        <p class="tle-dashboard__intro">
            Create and configure an evaluation report by selecting the program,
            cohort and evaluation points, then review the report workspace below.
        </p>
    </header>

    <nav class="tle-dashboard__navigation" aria-label="Evaluation dashboard sections">
        <a href="#tle-report-setup">Report Setup</a>
        <a href="#tle-appearance">Appearance</a>
        <a href="#tle-report-text">Report Text</a>
        <a href="#tle-report-workspace">Report Workspace</a>
    </nav>

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
                <p class="tle-dashboard__step">Step 1</p>

                <h2 id="tle-report-setup-heading" class="tle-dashboard__section-title">
                    Report Setup
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
                <p class="tle-dashboard__step">Step 2</p>

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
                class="tle-dashboard__card"
                aria-labelledby="tle-report-text-heading"
            >
                <p class="tle-dashboard__step">Step 3</p>

                <h2 id="tle-report-text-heading" class="tle-dashboard__section-title">
                    Report Text
                </h2>

                <div class="tle-dashboard__field">
                    <label for="tle-report-title">Title</label>

                    <input
                        id="tle-report-title"
                        type="text"
                        name="report_title"
                        value="Initial Leadership Capability Survey"
                    >
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-purpose-text">Purpose Paragraph</label>

                    <textarea
                        id="tle-purpose-text"
                        name="purpose_text"
                        rows="4"
                    >A comparison between pre-program and post-program.</textarea>
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-impact-text-1">Strongest Impact #1</label>

                    <textarea
                        id="tle-impact-text-1"
                        name="impact_text_1"
                        rows="3"
                    >Example text that you can customise.</textarea>
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-impact-text-2">Strongest Impact #2</label>

                    <textarea
                        id="tle-impact-text-2"
                        name="impact_text_2"
                        rows="3"
                    >A significant increase.</textarea>
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-impact-text-3">Strongest Impact #3</label>

                    <textarea
                        id="tle-impact-text-3"
                        name="impact_text_3"
                        rows="3"
                    >A substantial change is seen here.</textarea>
                </div>
            </section>

            <a class="tle-dashboard__workspace-link" href="#tle-report-workspace">
                View Report Workspace
            </a>

        </aside>

        <main
            id="tle-report-workspace"
            class="tle-dashboard__workspace"
            aria-labelledby="tle-report-workspace-heading"
        >
            <div class="tle-dashboard__workspace-header">
                <div>
                    <p class="tle-dashboard__step">Step 4</p>

                    <h2
                        id="tle-report-workspace-heading"
                        class="tle-dashboard__section-title"
                    >
                        Report Workspace
                    </h2>
                </div>

                <span class="tle-dashboard__status">
                    Prototype Data
                </span>
            </div>

            <section class="tle-dashboard__card">
                <h3 class="tle-dashboard__subheading">Report Overview</h3>

                <div class="tle-dashboard__summary-grid">
                    <div class="tle-dashboard__summary-item">
                        <span class="tle-dashboard__label">Programs</span>
                        <strong data-tle-summary-programs>No program selected</strong>
                    </div>

                    <div class="tle-dashboard__summary-item">
                        <span class="tle-dashboard__label">Cohort</span>
                        <strong data-tle-summary-cohort>2026</strong>
                    </div>

                    <div class="tle-dashboard__summary-item">
                        <span class="tle-dashboard__label">Evaluation Points</span>
                        <strong data-tle-summary-evaluation-points>
                            Select a program to view evaluation points
                        </strong>
                    </div>
                </div>

                <p class="tle-dashboard__note">
                    The selected report configuration above updates from the dashboard
                    controls. Capability results below remain prototype data until the
                    shared evaluation data service is connected.
                </p>
            </section>

            <section class="tle-dashboard__report-section">
                <div class="tle-dashboard__report-section-header">
                    <div>
                        <p class="tle-dashboard__section-kicker">ELF Capability</p>
                        <h3 class="tle-dashboard__subheading">Insight</h3>
                    </div>

                    <span class="tle-dashboard__placeholder-label">
                        Evaluation data area
                    </span>
                </div>

                <div class="tle-dashboard__placeholder">
                    Insight charts, capability results and reporting content will appear here.
                </div>
            </section>

            <section class="tle-dashboard__report-section">
                <div class="tle-dashboard__report-section-header">
                    <div>
                        <p class="tle-dashboard__section-kicker">ELF Capability</p>
                        <h3 class="tle-dashboard__subheading">Influence</h3>
                    </div>

                    <span class="tle-dashboard__placeholder-label">
                        Evaluation data area
                    </span>
                </div>

                <div class="tle-dashboard__placeholder">
                    Influence charts, capability results and reporting content will appear here.
                </div>
            </section>

            <section class="tle-dashboard__report-section">
                <div class="tle-dashboard__report-section-header">
                    <div>
                        <p class="tle-dashboard__section-kicker">ELF Capability</p>
                        <h3 class="tle-dashboard__subheading">Impact</h3>
                    </div>

                    <span class="tle-dashboard__placeholder-label">
                        Evaluation data area
                    </span>
                </div>

                <div class="tle-dashboard__placeholder">
                    Impact charts, capability results and reporting content will appear here.
                </div>
            </section>

            <section class="tle-dashboard__card tle-dashboard__export-area">
                <div>
                    <p class="tle-dashboard__section-kicker">Reporting</p>
                    <h3 class="tle-dashboard__subheading">PDF Export</h3>

                    <p class="tle-dashboard__note">
                        The existing PDF export functionality will later consume the
                        same reporting data displayed in this workspace.
                    </p>
                </div>

                <button
                    type="submit"
                    class="tle-dashboard__export-button"
                >
                    Export as PDF
                </button>
            </section>

        </main>

    </div>

</form>

</div>
