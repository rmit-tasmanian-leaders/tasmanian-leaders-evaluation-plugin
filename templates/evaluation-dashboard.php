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

                <div class="tle-dashboard__field">
                    <label for="tle-program">Program</label>

                    <select id="tle-program" name="tle_program">
                        <option>I-LEAD Young Professionals</option>
                        <option>Emerging Leaders Program</option>
                    </select>
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-cohort">Cohort</label>

                    <select id="tle-cohort" name="tle_cohort">
                        <option>2026</option>
                        <option>2025</option>
                    </select>
                </div>

                <fieldset class="tle-dashboard__fieldset">
                    <legend>Evaluation Points</legend>

                    <p class="tle-dashboard__field-help">
                        Select one or more evaluation points to include in the report.
                    </p>

                    <label class="tle-dashboard__checkbox">
                        <input type="checkbox" name="tle_evaluation_points[]" value="pre-program" checked>
                        <span>Pre-program</span>
                    </label>

                    <label class="tle-dashboard__checkbox">
                        <input type="checkbox" name="tle_evaluation_points[]" value="completion" checked>
                        <span>Completion</span>
                    </label>

                    <label class="tle-dashboard__checkbox">
                        <input type="checkbox" name="tle_evaluation_points[]" value="three-month-delay">
                        <span>3-Month Delayed</span>
                    </label>

                    <label class="tle-dashboard__checkbox">
                        <input type="checkbox" name="tle_evaluation_points[]" value="manager">
                        <span>Manager Evaluation</span>
                    </label>
                </fieldset>
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

                <div class="tle-dashboard__field">
                    <label for="tle-cover-image">Cover Image</label>

                    <input
                        id="tle-cover-image"
                        type="file"
                        name="tle_cover_image"
                        accept="image/*"
                    >

                    <p class="tle-dashboard__field-help">
                        The selected image will later be used for the report cover
                        and colour scheme.
                    </p>
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
                    <label for="tle-purpose-text">Purpose</label>

                    <textarea
                        id="tle-purpose-text"
                        name="tle_purpose_text"
                        rows="5"
                    >Describe the purpose and context of this evaluation report.</textarea>
                </div>

                <div class="tle-dashboard__field">
                    <label for="tle-impact-text">Strongest Impact</label>

                    <textarea
                        id="tle-impact-text"
                        name="tle_impact_text"
                        rows="5"
                    >Add explanatory text for the strongest impact identified in the report.</textarea>
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
                        <span class="tle-dashboard__label">Program</span>
                        <strong>I-LEAD Young Professionals</strong>
                    </div>

                    <div class="tle-dashboard__summary-item">
                        <span class="tle-dashboard__label">Cohort</span>
                        <strong>2026</strong>
                    </div>

                    <div class="tle-dashboard__summary-item">
                        <span class="tle-dashboard__label">Evaluation Points</span>
                        <strong>Pre-program, Completion</strong>
                    </div>
                </div>

                <p class="tle-dashboard__note">
                    These values are representative only. The workspace will later
                    receive its configuration and results from the dashboard controls
                    and shared evaluation data service.
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

                <span class="tle-dashboard__status">
                    Existing export retained
                </span>
            </section>

        </main>

    </div>

</div>
