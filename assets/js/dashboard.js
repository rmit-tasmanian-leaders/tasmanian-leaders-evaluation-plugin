document.addEventListener('DOMContentLoaded', function () {
    const dashboard = document.querySelector('.tle-dashboard');

    if (!dashboard) {
        return;
    }

    const programsContainer = dashboard.querySelector('[data-tle-programs]');
    const primaryProgramRow = dashboard.querySelector(
        '[data-tle-primary-program-row]'
    );
    const primaryProgramInput = primaryProgramRow
        ? primaryProgramRow.querySelector('[data-tle-program-search]')
        : null;

    const primaryProgramValue = primaryProgramRow
        ? primaryProgramRow.querySelector(
            '[data-tle-primary-program-value]'
        )
        : null;

    const primaryProgramSuggestions = primaryProgramRow
        ? primaryProgramRow.querySelector('[data-tle-program-suggestions]')
        : null;
    const managerOption = primaryProgramRow
        ? primaryProgramRow.querySelector('[data-tle-manager-option]')
        : null;
    const managerCheckbox = managerOption
        ? managerOption.querySelector('input[value="manager"]')
        : null;

    const comparisonRows = dashboard.querySelector('[data-tle-comparison-rows]');
    const addComparisonButton = dashboard.querySelector(
        '[data-tle-add-comparison]'
    );
    const comparisonTemplate = dashboard.querySelector(
        '#tle-comparison-row-template'
    );
    const comparisonTitle = dashboard.querySelector(
        '.tle-dashboard__comparison-title'
    );
    const comparisonLabel = dashboard.querySelector(
        '[data-tle-comparison-label]'
    );
    const comparisonEvaluation = dashboard.querySelector(
        '[data-tle-comparison-evaluation]'
    );
    const comparisonPoints = Array.from(
        dashboard.querySelectorAll('[data-tle-comparison-point]')
    );

    const normalEvaluationStageValues = [
        'pre-program',
        'completion',
        'three-month-delay'
    ];

    const normalEvaluationStageLabels = {
        'pre-program': 'Pre-program',
        'completion': 'Post-program',
        'three-month-delay': 'Delayed'
    };

    const backendEvaluationStageMap = {
        pre_program: 'pre-program',
        completion: 'completion',
        delay: 'three-month-delay'
    };

    let primaryAvailableStageValues = [];
    let primaryStageAvailabilityLoaded = false;
    let primaryStageAvailabilityRequest = 0;
    let currentCapabilityReport = null;
    const cohortSelect = dashboard.querySelector('#tle-cohort');

    const exportForm = dashboard.querySelector('#tle-pdf-export-form');
    const exportButton = exportForm
        ? exportForm.querySelector('button[type="submit"]')
        : null;    let comparisonRequirementNotice = null;

    let comparisonOptions = [];
    let comparisonOptionsLoaded = false;
    let comparisonOptionsPromise = null;

    const summaryPrograms = dashboard.querySelector('[data-tle-summary-programs]');
    const summaryCohort = dashboard.querySelector('[data-tle-summary-cohort]');
    const summaryEvaluationPoints = dashboard.querySelector(
        '[data-tle-summary-evaluation-points]'
    );

    if (
        !programsContainer ||
        !primaryProgramRow ||
        !primaryProgramInput ||
        !comparisonRows ||
        !addComparisonButton ||
        !comparisonTemplate
    ) {
        return;
    }

    function getPrimaryProgramName() {
        return primaryProgramInput.dataset.tleProgramSelected || '';
    }

    function getComparisonSelections() {
        return Array.from(
            comparisonRows.querySelectorAll('[data-tle-comparison-row]')
        ).map(function (row) {
            const input = row.querySelector(
                '[data-tle-comparison-search]'
            );

            if (!input) {
                return null;
            }

            const program =
                input.dataset.tleProgramSelected || '';

            const cohort =
                input.dataset.tleCohortSelected || '';

            if (!program || !cohort) {
                return null;
            }

            return {
                program: program,
                cohort: cohort,
                label: input.value.trim(),
                key: program + '|' + cohort
            };
        }).filter(Boolean);
    }

    function getComparisonProgramNames() {
        return getComparisonSelections().map(function (selection) {
            return selection.label;
        });
    }

    function getUnavailableComparisonPrograms(currentRow) {
        const unavailable = new Set();

        const primaryProgram = getPrimaryProgramName();
        const primaryCohort = cohortSelect
            ? cohortSelect.value
            : '';

        if (primaryProgram && primaryCohort) {
            unavailable.add(
                primaryProgram + '|' + primaryCohort
            );
        }

        comparisonRows
            .querySelectorAll('[data-tle-comparison-row]')
            .forEach(function (row) {
                if (row === currentRow) {
                    return;
                }

                const input = row.querySelector(
                    '[data-tle-comparison-search]'
                );

                const selectedProgram = input
                    ? input.dataset.tleProgramSelected || ''
                    : '';

                const selectedCohort = input
                    ? input.dataset.tleCohortSelected || ''
                    : '';

                if (selectedProgram && selectedCohort) {
                    unavailable.add(
                        selectedProgram + '|' + selectedCohort
                    );
                }
            });

        return unavailable;
    }

    function clearComparisonSelection(input) {
        if (!input) {
            return;
        }

        input.value = '';
        input.dataset.tleProgramSelected = '';
        input.dataset.tleCohortSelected = '';

        const row = input.closest(
            '[data-tle-comparison-row]'
        );

        if (!row) {
            return;
        }

        const programField = row.querySelector(
            '[data-tle-comparison-program-value]'
        );

        const cohortField = row.querySelector(
            '[data-tle-comparison-cohort-value]'
        );

        if (programField) {
            programField.value = '';
        }

        if (cohortField) {
            cohortField.value = '';
        }
    }

    function syncComparisonProgramSelections() {
        const primaryProgram = getPrimaryProgramName();

        const primaryCohort = cohortSelect
            ? cohortSelect.value
            : '';

        const primaryKey =
            primaryProgram && primaryCohort
                ? primaryProgram + '|' + primaryCohort
                : '';

        const seenSelections = new Set();

        comparisonRows
            .querySelectorAll('[data-tle-comparison-row]')
            .forEach(function (row) {
                const input = row.querySelector(
                    '[data-tle-comparison-search]'
                );

                if (!input) {
                    return;
                }

                const selectedProgram =
                    input.dataset.tleProgramSelected || '';

                const selectedCohort =
                    input.dataset.tleCohortSelected || '';

                if (!selectedProgram || !selectedCohort) {
                    return;
                }

                const selectionKey =
                    selectedProgram + '|' + selectedCohort;

                const conflictsWithPrimary =
                    primaryKey !== '' &&
                    selectionKey === primaryKey;

                const duplicateComparison =
                    seenSelections.has(selectionKey);

                if (
                    conflictsWithPrimary ||
                    duplicateComparison
                ) {
                    clearComparisonSelection(input);
                    return;
                }

                seenSelections.add(selectionKey);
            });
    }

    function getAvailableComparisonStageValues() {
        if (!primaryStageAvailabilityLoaded) {
            return normalEvaluationStageValues.slice();
        }

        let availableStages =
            primaryAvailableStageValues.slice();

        const selections =
            getComparisonSelections();

        selections.forEach(function (selection) {
            const option =
                comparisonOptions.find(
                    function (candidate) {
                        return (
                            candidate.program ===
                                selection.program &&
                            candidate.cohort ===
                                selection.cohort
                        );
                    }
                );

            if (
                !option ||
                !Array.isArray(option.stages)
            ) {
                availableStages = [];
                return;
            }

            availableStages =
                availableStages.filter(
                    function (stage) {
                        return option.stages.includes(
                            stage
                        );
                    }
                );
        });

        return availableStages;
    }

    // Program comparisons now always span Pre-program through Delayed.
    function getComparisonEvaluationPoint() {
        return 'Pre-program to Delayed';
    }
    function hasNumericScore(value) {
        if (
            typeof value === 'number' &&
            Number.isFinite(value)
        ) {
            return true;
        }

        if (Array.isArray(value)) {
            return value.some(hasNumericScore);
        }

        if (value && typeof value === 'object') {
            return Object.keys(value).some(function (key) {
                return hasNumericScore(value[key]);
            });
        }

        return false;
    }

    function deriveAvailableStageValues(report) {
        const availableStages = new Set();

        const groups =
            report && Array.isArray(report.groups)
                ? report.groups
                : [];

        groups.forEach(function (group) {
            const series =
                group && Array.isArray(group.series)
                    ? group.series
                    : [];

            series.forEach(function (item) {
                if (
                    !item ||
                    !item.evaluation_stage ||
                    !hasNumericScore(item.capability_averages)
                ) {
                    return;
                }

                const frontendStage =
                    backendEvaluationStageMap[
                        item.evaluation_stage
                    ];

                if (frontendStage) {
                    availableStages.add(frontendStage);
                }
            });
        });

        return normalEvaluationStageValues.filter(
            function (stage) {
                return availableStages.has(stage);
            }
        );
    }

    function deriveComparisonOptions(report) {
        const options = [];
        const seen = new Set();

        const groups =
            report && Array.isArray(report.groups)
                ? report.groups
                : [];

        groups.forEach(function (group) {
            const context =
                group && group.context
                    ? group.context
                    : {};

            const program =
                String(context.program || '').trim();

            const cohort =
                String(context.cohort || '').trim();

            if (!program || !cohort) {
                return;
            }

            const series =
                group && Array.isArray(group.series)
                    ? group.series
                    : [];

            const availableStages = [];

            series.forEach(function (item) {
                if (
                    !item ||
                    !backendEvaluationStageMap[
                        item.evaluation_stage
                    ] ||
                    !hasNumericScore(
                        item.capability_averages
                    )
                ) {
                    return;
                }

                const frontendStage =
                    backendEvaluationStageMap[
                        item.evaluation_stage
                    ];

                if (
                    !availableStages.includes(
                        frontendStage
                    )
                ) {
                    availableStages.push(
                        frontendStage
                    );
                }
            });

            if (availableStages.length === 0) {
                return;
            }

            const key =
                program + '|' + cohort;

            if (seen.has(key)) {
                return;
            }

            seen.add(key);

            options.push({
                program: program,
                cohort: cohort,
                key: key,
                label: program + ' ' + cohort,
                stages: availableStages
            });
        });

        options.sort(function (first, second) {
            const firstYear = Number(first.cohort);
            const secondYear = Number(second.cohort);

            if (
                Number.isFinite(firstYear) &&
                Number.isFinite(secondYear) &&
                firstYear !== secondYear
            ) {
                return firstYear - secondYear;
            }

            const cohortOrder =
                first.cohort.localeCompare(
                    second.cohort
                );

            if (cohortOrder !== 0) {
                return cohortOrder;
            }

            return first.program.localeCompare(
                second.program
            );
        });

        return options;
    }

    function loadComparisonOptions() {
        if (comparisonOptionsLoaded) {
            return Promise.resolve(
                comparisonOptions
            );
        }

        if (comparisonOptionsPromise) {
            return comparisonOptionsPromise;
        }

        if (!window.tleDashboardConfig) {
            return Promise.resolve([]);
        }

        const endpoint =
            window.tleDashboardConfig
                .comparisonOptionsUrl ||
            window.tleDashboardConfig
                .evaluationsUrl;

        if (!endpoint) {
            return Promise.resolve([]);
        }

        const headers = {
            Accept: 'application/json'
        };

        if (
            window.tleDashboardConfig.restNonce
        ) {
            headers['X-WP-Nonce'] =
                window.tleDashboardConfig
                    .restNonce;
        }

        comparisonOptionsPromise = fetch(
            endpoint,
            {
                method: 'GET',
                credentials: 'same-origin',
                headers: headers
            }
        )
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(
                        'Comparison option request failed with HTTP ' +
                        response.status
                    );
                }

                return response.json();
            })
            .then(function (payload) {
                /*
                 * New lightweight endpoint already returns
                 * Program + Cohort + stage metadata.
                 *
                 * Keep the old full-report parser as a fallback
                 * for backwards compatibility.
                 */
                if (
                    !payload ||
                    !Array.isArray(payload.options)
                ) {
                    comparisonOptions =
                        deriveComparisonOptions(
                            payload
                        );

                    comparisonOptionsLoaded = true;

                    return comparisonOptions;
                }

                const seen = new Set();
                const options = [];

                payload.options.forEach(
                    function (option) {
                        if (!option) {
                            return;
                        }

                        const program =
                            String(
                                option.program || ''
                            ).trim();

                        const cohort =
                            String(
                                option.cohort || ''
                            ).trim();

                        if (
                            !program ||
                            !cohort
                        ) {
                            return;
                        }

                        const stages =
                            Array.isArray(
                                option.stages
                            )
                                ? option.stages
                                    .map(
                                        function (
                                            stage
                                        ) {
                                            return (
                                                backendEvaluationStageMap[
                                                    stage
                                                ] ||
                                                stage
                                            );
                                        }
                                    )
                                    .filter(
                                        function (
                                            stage
                                        ) {
                                            return normalEvaluationStageValues
                                                .includes(
                                                    stage
                                                );
                                        }
                                    )
                                : [];

                        if (
                            stages.length === 0
                        ) {
                            return;
                        }

                        const key =
                            program +
                            '|' +
                            cohort;

                        if (seen.has(key)) {
                            return;
                        }

                        seen.add(key);

                        options.push({
                            program: program,
                            cohort: cohort,
                            key: key,
                            label:
                                program +
                                ' ' +
                                cohort,
                            stages:
                                Array.from(
                                    new Set(
                                        stages
                                    )
                                )
                        });
                    }
                );

                options.sort(
                    function (
                        first,
                        second
                    ) {
                        const firstYear =
                            Number(
                                first.cohort
                            );

                        const secondYear =
                            Number(
                                second.cohort
                            );

                        if (
                            Number.isFinite(
                                firstYear
                            ) &&
                            Number.isFinite(
                                secondYear
                            ) &&
                            firstYear !==
                                secondYear
                        ) {
                            return (
                                firstYear -
                                secondYear
                            );
                        }

                        const cohortOrder =
                            first.cohort
                                .localeCompare(
                                    second.cohort
                                );

                        if (
                            cohortOrder !== 0
                        ) {
                            return cohortOrder;
                        }

                        return first.program
                            .localeCompare(
                                second.program
                            );
                    }
                );

                comparisonOptions = options;
                comparisonOptionsLoaded = true;

                return comparisonOptions;
            })
            .catch(function (error) {
                comparisonOptions = [];
                comparisonOptionsLoaded = false;

                console.warn(
                    'Unable to load comparison program/year options.',
                    error
                );

                return [];
            })
            .finally(function () {
                comparisonOptionsPromise = null;
            });

        return comparisonOptionsPromise;
    }

    function clearPrimaryStageAvailability() {
        primaryStageAvailabilityRequest++;
        primaryAvailableStageValues = [];
        primaryStageAvailabilityLoaded = false;
        currentCapabilityReport = null;

        comparisonPoints.forEach(function (checkbox) {
            checkbox.checked = false;
        });

        syncComparisonPointState();
    }

    async function loadPrimaryStageAvailability() {
        const primaryProgram = getPrimaryProgramName();

        if (!primaryProgram) {
            clearPrimaryStageAvailability();
            updateReportOverview();
            return;
        }

        if (
            !window.tleDashboardConfig ||
            !window.tleDashboardConfig.evaluationsUrl
        ) {
            return;
        }

        const requestId =
            ++primaryStageAvailabilityRequest;

        primaryStageAvailabilityLoaded = false;
        currentCapabilityReport = null;
        syncReportTextState();

        try {
            const url = new URL(
                window.tleDashboardConfig.evaluationsUrl,
                window.location.origin
            );

            url.searchParams.set('program', primaryProgram);

            if (cohortSelect && cohortSelect.value) {
                url.searchParams.set(
                    'cohort',
                    cohortSelect.value
                );
            }

            const headers = {
                Accept: 'application/json'
            };

            if (window.tleDashboardConfig.restNonce) {
                headers['X-WP-Nonce'] =
                    window.tleDashboardConfig.restNonce;
            }

            const response = await fetch(
                url.toString(),
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: headers
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Stage availability request failed with HTTP ' +
                    response.status
                );
            }

            const report = await response.json();

            if (
                requestId !==
                primaryStageAvailabilityRequest
            ) {
                return;
            }

            currentCapabilityReport = {
                program: primaryProgram,
                cohort: cohortSelect ? cohortSelect.value : '',
                report: report
            };

            primaryAvailableStageValues =
                deriveAvailableStageValues(report);

            primaryStageAvailabilityLoaded = true;

            comparisonPoints.forEach(
                function (checkbox) {
                    if (
                        !primaryAvailableStageValues.includes(
                            checkbox.value
                        )
                    ) {
                        checkbox.checked = false;
                    }
                }
            );

            syncComparisonPointState();
            updateReportOverview();
            syncReportTextState();
        } catch (error) {
            if (
                requestId !==
                primaryStageAvailabilityRequest
            ) {
                return;
            }

            primaryAvailableStageValues = [];
            primaryStageAvailabilityLoaded = false;

            syncComparisonPointState();

            console.warn(
                'Unable to load evaluation stage availability.',
                error
            );
        }
    }

    function getPrimaryEvaluationPoints() {
        const stageValues =
            primaryStageAvailabilityLoaded
                ? primaryAvailableStageValues
                : normalEvaluationStageValues;

        const points = stageValues.map(
            function (stage) {
                return normalEvaluationStageLabels[stage] || stage;
            }
        );

        if (
            managerCheckbox &&
            managerCheckbox.checked
        ) {
            points.push('Manager');
        }

        return points;
    }

    function ensureComparisonRequirementNotice() {
        if (comparisonRequirementNotice) {
            return comparisonRequirementNotice;
        }

        comparisonRequirementNotice =
            document.createElement('p');

        comparisonRequirementNotice.className =
            'tle-dashboard__comparison-requirement';

        comparisonRequirementNotice.setAttribute(
            'role',
            'status'
        );

        comparisonRequirementNotice.setAttribute(
            'aria-live',
            'polite'
        );

        comparisonRequirementNotice.hidden = true;

        addComparisonButton.parentNode.insertBefore(
            comparisonRequirementNotice,
            addComparisonButton
        );

        return comparisonRequirementNotice;
    }

    function getExportRequirementMessage() {
        const primaryProgram = getPrimaryProgramName();
        if (!primaryProgram || !primaryStageAvailabilityLoaded) {
            return '';
        }

        // All comparison programs need three real evaluation stages.
        if (primaryAvailableStageValues.length < 3) {
            return 'Select a program with Pre-program, Post-program and Delayed results.';
        }

        const comparisons = getComparisonProgramNames();
        if (comparisons.length > 2) {
            return 'Select no more than two comparison programs.';
        }

        return '';
    }
    function syncComparisonRequirementLabel() {
        if (comparisonTitle) {
            comparisonTitle.textContent = 'Comparison (optional)';
        }
    }
    function syncExportRequirements() {
        syncComparisonRequirementLabel();
        const notice =
            ensureComparisonRequirementNotice();

        const message =
            getExportRequirementMessage();

        notice.textContent = message;
        notice.hidden = message === '';

        if (exportButton) {
            exportButton.disabled =
                message !== '';

            exportButton.setAttribute(
                'aria-disabled',
                message !== ''
                    ? 'true'
                    : 'false'
            );
        }
    }
    function updateReportOverview() {
        const primaryProgram = getPrimaryProgramName();
        const comparisons = getComparisonProgramNames();
        const comparisonPoint = getComparisonEvaluationPoint();

        if (summaryPrograms) {
            if (!primaryProgram) {
                summaryPrograms.textContent = 'No program selected';
            } else if (comparisons.length) {
                summaryPrograms.textContent =
                    primaryProgram + ' | Compare to: ' + comparisons.join(', ');
            } else {
                summaryPrograms.textContent = primaryProgram;
            }
        }

        if (summaryCohort) {
            summaryCohort.textContent = cohortSelect
                ? cohortSelect.value
                : '—';
        }

        if (summaryEvaluationPoints) {
            if (!primaryProgram) {
                summaryEvaluationPoints.textContent =
                    'Select a program to view evaluation points';
            } else {
                let text =
                    primaryProgram + ': ' +
                    getPrimaryEvaluationPoints().join(', ');

                if (comparisons.length) {
                    text += ' | Comparison: ' +
                        (comparisonPoint || 'Select an evaluation point');
                }

                summaryEvaluationPoints.textContent = text;
            }
        }

        syncExportRequirements();
    }

    function syncManagerOption() {
        const hasPrimaryProgram = Boolean(getPrimaryProgramName());

        if (managerOption) {
            managerOption.hidden = !hasPrimaryProgram;
        }

        if (!hasPrimaryProgram && managerCheckbox) {
            managerCheckbox.checked = false;
        }
    }

    function syncComparisonPointState() {
        const availableStages =
            getAvailableComparisonStageValues();

        comparisonPoints.forEach(function (checkbox) {
            const unavailable =
                primaryStageAvailabilityLoaded &&
                !availableStages.includes(
                    checkbox.value
                );

            if (unavailable) {
                checkbox.checked = false;
            }
        });

        const selectedPoint =
            comparisonPoints.find(
                function (checkbox) {
                    return checkbox.checked;
                }
            );

        comparisonPoints.forEach(function (checkbox) {
            const unavailable =
                primaryStageAvailabilityLoaded &&
                !availableStages.includes(
                    checkbox.value
                );

            const lockedBySelection =
                Boolean(selectedPoint) &&
                checkbox !== selectedPoint;

            checkbox.disabled =
                unavailable ||
                lockedBySelection;

            const label =
                checkbox.closest('label');

            if (label) {
                label.classList.toggle(
                    'tle-dashboard__checkbox--disabled',
                    checkbox.disabled
                );

                label.setAttribute(
                    'aria-disabled',
                    checkbox.disabled
                        ? 'true'
                        : 'false'
                );
            }
        });
    }

    function syncComparisonState() {
        const hasComparisonRows =
            comparisonRows.querySelectorAll(
                '[data-tle-comparison-row]'
            ).length > 0;

        const hasSelectedComparisons =
            getComparisonProgramNames().length > 0;

        // Maximum two comparisons, giving three programs including the primary.
        addComparisonButton.disabled =
            comparisonRows.querySelectorAll('[data-tle-comparison-row]').length >= 2;
        if (comparisonLabel) {
            comparisonLabel.hidden =
                !hasComparisonRows;
        }

        if (comparisonEvaluation) {
            comparisonEvaluation.hidden =
                !hasSelectedComparisons;
        }

        if (!hasSelectedComparisons) {
            comparisonPoints.forEach(function (checkbox) {
                checkbox.checked = false;
            });
        }

        if (!hasComparisonRows) {
            comparisonPoints.forEach(function (checkbox) {
                checkbox.disabled = false;
            });
        } else {
            syncComparisonPointState();
        }
    }

    function initialisePrimaryProgramSearch() {
        if (!primaryProgramSuggestions) {
            return;
        }

        function clearPrimarySelection() {
            primaryProgramInput.dataset.tleProgramSelected = '';
            primaryProgramInput.dataset.tleCohortSelected = '';

            if (primaryProgramValue) {
                primaryProgramValue.value = '';
            }

            if (cohortSelect) {
                cohortSelect.value = '';
            }
        }

        async function updateSuggestions() {
            const options =
                await loadComparisonOptions();

            const query =
                primaryProgramInput.value
                    .trim()
                    .toLowerCase();

            primaryProgramSuggestions.innerHTML = '';

            let visibleCount = 0;

            options.forEach(function (option) {
                if (
                    !option ||
                    !option.program ||
                    !option.cohort
                ) {
                    return;
                }

                const label =
                    option.label ||
                    (
                        option.program +
                        ' ' +
                        option.cohort
                    );

                const matches =
                    query === '' ||
                    label
                        .toLowerCase()
                        .includes(query);

                if (!matches) {
                    return;
                }

                const button =
                    document.createElement(
                        'button'
                    );

                button.type = 'button';
                button.textContent = label;

                button.addEventListener(
                    'click',
                    function () {
                        primaryProgramInput.value =
                            label;

                        primaryProgramInput.dataset
                            .tleProgramSelected =
                            option.program;

                        primaryProgramInput.dataset
                            .tleCohortSelected =
                            option.cohort;

                        if (primaryProgramValue) {
                            primaryProgramValue.value =
                                option.program;
                        }

                        if (cohortSelect) {
                            cohortSelect.value =
                                option.cohort;
                        }

                        primaryProgramSuggestions.hidden =
                            true;

                        syncManagerOption();
                        syncComparisonProgramSelections();
                        syncComparisonState();
                        updateReportOverview();
                        syncReportTextState();
                        loadPrimaryStageAvailability();
                    }
                );

                primaryProgramSuggestions.appendChild(
                    button
                );

                visibleCount++;
            });

            primaryProgramSuggestions.hidden =
                visibleCount === 0;
        }

        primaryProgramInput.addEventListener(
            'focus',
            function () {
                updateSuggestions();
            }
        );

        primaryProgramInput.addEventListener(
            'input',
            function () {
                clearPrimarySelection();
                clearPrimaryStageAvailability();

                updateSuggestions();

                syncManagerOption();
                syncComparisonProgramSelections();
                syncComparisonState();
                updateReportOverview();
                syncReportTextState();
            }
        );

        document.addEventListener(
            'click',
            function (event) {
                if (
                    !primaryProgramRow.contains(
                        event.target
                    )
                ) {
                    primaryProgramSuggestions.hidden =
                        true;
                }
            }
        );
    }

    function initialiseComparisonRow(row) {
        const searchInput = row.querySelector(
            '[data-tle-comparison-search]'
        );

        const suggestions = row.querySelector(
            '[data-tle-comparison-suggestions]'
        );

        const programField = row.querySelector(
            '[data-tle-comparison-program-value]'
        );

        const cohortField = row.querySelector(
            '[data-tle-comparison-cohort-value]'
        );

        const removeButton = row.querySelector(
            '[data-tle-remove-comparison]'
        );

        if (searchInput && suggestions) {
            async function updateSuggestions() {
                await loadComparisonOptions();

                const query =
                    searchInput.value
                        .trim()
                        .toLowerCase();

                const unavailableSelections =
                    getUnavailableComparisonPrograms(row);

                suggestions.innerHTML = '';

                let visibleCount = 0;

                comparisonOptions.forEach(
                    function (option) {
                        const matchesQuery =
                            query === '' ||
                            option.label
                                .toLowerCase()
                                .includes(query);

                        const available =
                            !unavailableSelections.has(
                                option.key
                            );

                        if (
                            !matchesQuery ||
                            !available
                        ) {
                            return;
                        }

                        const button =
                            document.createElement(
                                'button'
                            );

                        button.type = 'button';
                        button.textContent =
                            option.label;

                        button.addEventListener(
                            'click',
                            function () {
                                searchInput.value =
                                    option.label;

                                searchInput.dataset.tleProgramSelected =
                                    option.program;

                                searchInput.dataset.tleCohortSelected =
                                    option.cohort;

                                if (programField) {
                                    programField.value =
                                        option.program;
                                }

                                if (cohortField) {
                                    cohortField.value =
                                        option.cohort;
                                }

                                suggestions.hidden = true;

                                syncComparisonProgramSelections();
                                syncComparisonState();
                                updateReportOverview();
                                syncReportTextState();
                            }
                        );

                        suggestions.appendChild(
                            button
                        );

                        visibleCount++;
                    }
                );

                suggestions.hidden =
                    visibleCount === 0;
            }

            searchInput.addEventListener(
                'focus',
                updateSuggestions
            );

            searchInput.addEventListener(
                'input',
                function () {
                    searchInput.dataset.tleProgramSelected = '';
                    searchInput.dataset.tleCohortSelected = '';

                    if (programField) {
                        programField.value = '';
                    }

                    if (cohortField) {
                        cohortField.value = '';
                    }

                    updateSuggestions();
                    syncComparisonState();
                    updateReportOverview();
                    syncReportTextState();
                }
            );

            document.addEventListener(
                'click',
                function (event) {
                    if (!row.contains(event.target)) {
                        suggestions.hidden = true;
                    }
                }
            );
        }

        if (removeButton) {
            removeButton.addEventListener(
                'click',
                function () {
                    row.remove();

                    syncComparisonState();
                    updateReportOverview();
                    syncReportTextState();
                }
            );
        }
    }

    initialisePrimaryProgramSearch();

    if (exportForm) {
        exportForm.addEventListener(
            'submit',
            function (event) {
                const message =
                    getExportRequirementMessage();

                if (!message) {
                    return;
                }

                event.preventDefault();
                syncExportRequirements();

                if (comparisonRequirementNotice) {
                    comparisonRequirementNotice.scrollIntoView({
                        block: 'center',
                        behavior: 'smooth'
                    });
                }
            }
        );
    }

    if (managerCheckbox) {
        managerCheckbox.addEventListener('change', function () {
            updateReportOverview();
            syncReportTextState();
        });
    }

    comparisonPoints.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            syncComparisonPointState();
            updateReportOverview();
        });
    });

    addComparisonButton.addEventListener('click', function () {
        if (comparisonRows.querySelectorAll('[data-tle-comparison-row]').length >= 2) {
            syncComparisonState();
            return;
        }
        const fragment = comparisonTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-tle-comparison-row]');

        if (!row) {
            return;
        }

        comparisonRows.appendChild(row);
        initialiseComparisonRow(row);

        syncComparisonState();
        updateReportOverview();

        const searchInput = row.querySelector(
            '[data-tle-comparison-search]'
        );

        if (searchInput) {
            searchInput.focus();
        }
    });

    syncManagerOption();
    syncComparisonState();
    updateReportOverview();
    const coverInput = dashboard.querySelector('[data-tle-cover-input]');
    const coverPreview = dashboard.querySelector('[data-tle-cover-preview]');
    const coverPlaceholder = dashboard.querySelector('[data-tle-cover-placeholder]');
    const imageColourRadios = Array.from(
        dashboard.querySelectorAll('[data-tle-image-colour-radio]')
    );
    const imageColourSwatches = Array.from(
        dashboard.querySelectorAll('[data-tle-image-colour-swatch]')
    );
    const customColourInput = dashboard.querySelector(
        '[data-tle-report-colour-custom]'
    );
    const brandColourRadios = Array.from(
        dashboard.querySelectorAll(
            'input[name="report_colour"]:not([data-tle-image-colour-radio])'
        )
    );

    function resetImageColours() {
        imageColourRadios.forEach(function (radio, index) {
            radio.checked = false;
            radio.disabled = true;
            radio.dataset.tleColour = '';

            const swatch = imageColourSwatches[index];

            if (swatch) {
                swatch.style.removeProperty('--tle-swatch');
                swatch.classList.add(
                    'tle-dashboard__palette-swatch--unavailable'
                );
            }
        });

        if (customColourInput) {
            customColourInput.value = '';
        }
    }

    function rgbToHex(red, green, blue) {
        return '#' + [red, green, blue].map(function (value) {
            return value.toString(16).padStart(2, '0');
        }).join('');
    }

    function colourDistance(first, second) {
        const red = first[0] - second[0];
        const green = first[1] - second[1];
        const blue = first[2] - second[2];

        return Math.sqrt(
            (red * red) +
            (green * green) +
            (blue * blue)
        );
    }

    function extractImageColours(image) {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d', {
            willReadFrequently: true
        });

        if (!context) {
            return [];
        }

        const maxDimension = 90;
        const scale = Math.min(
            maxDimension / image.naturalWidth,
            maxDimension / image.naturalHeight,
            1
        );

        canvas.width = Math.max(
            1,
            Math.round(image.naturalWidth * scale)
        );
        canvas.height = Math.max(
            1,
            Math.round(image.naturalHeight * scale)
        );

        context.drawImage(
            image,
            0,
            0,
            canvas.width,
            canvas.height
        );

        const pixels = context.getImageData(
            0,
            0,
            canvas.width,
            canvas.height
        ).data;

        const buckets = new Map();

        for (let index = 0; index < pixels.length; index += 4) {
            const alpha = pixels[index + 3];

            if (alpha < 180) {
                continue;
            }

            const red = pixels[index];
            const green = pixels[index + 1];
            const blue = pixels[index + 2];

            const brightness = red + green + blue;

            if (brightness > 735 || brightness < 35) {
                continue;
            }

            const quantised = [
                Math.min(255, Math.round(red / 32) * 32),
                Math.min(255, Math.round(green / 32) * 32),
                Math.min(255, Math.round(blue / 32) * 32)
            ];

            const key = quantised.join(',');

            buckets.set(
                key,
                (buckets.get(key) || 0) + 1
            );
        }

        const candidates = Array.from(buckets.entries())
            .sort(function (first, second) {
                return second[1] - first[1];
            })
            .map(function (entry) {
                return entry[0].split(',').map(Number);
            });

        const selected = [];

        candidates.forEach(function (colour) {
            if (selected.length >= 5) {
                return;
            }

            const sufficientlyDifferent = selected.every(function (existing) {
                return colourDistance(existing, colour) >= 65;
            });

            if (sufficientlyDifferent) {
                selected.push(colour);
            }
        });

        if (selected.length < 5) {
            candidates.forEach(function (colour) {
                if (selected.length >= 5) {
                    return;
                }

                const alreadySelected = selected.some(function (existing) {
                    return colourDistance(existing, colour) < 20;
                });

                if (!alreadySelected) {
                    selected.push(colour);
                }
            });
        }

        return selected.slice(0, 5).map(function (colour) {
            return rgbToHex(
                colour[0],
                colour[1],
                colour[2]
            );
        });
    }

    function applyImageColours(colours) {
        imageColourRadios.forEach(function (radio, index) {
            const swatch = imageColourSwatches[index];
            const colour = colours[index] || '';

            radio.checked = false;
            radio.disabled = colour === '';
            radio.dataset.tleColour = colour;

            if (!swatch) {
                return;
            }

            if (colour) {
                swatch.style.setProperty('--tle-swatch', colour);
                swatch.classList.remove(
                    'tle-dashboard__palette-swatch--unavailable'
                );
            } else {
                swatch.style.removeProperty('--tle-swatch');
                swatch.classList.add(
                    'tle-dashboard__palette-swatch--unavailable'
                );
            }
        });

        if (customColourInput) {
            customColourInput.value = '';
        }
    }

    brandColourRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked && customColourInput) {
                customColourInput.value = '';
            }
        });
    });

    imageColourRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (
                radio.checked &&
                customColourInput
            ) {
                customColourInput.value =
                    radio.dataset.tleColour || '';
            }
        });
    });

    if (coverInput && coverPreview && coverPlaceholder) {
        coverInput.addEventListener('change', function () {
            const file = coverInput.files && coverInput.files[0]
                ? coverInput.files[0]
                : null;

            resetImageColours();

            if (!file) {
                coverPreview.hidden = true;
                coverPreview.removeAttribute('src');
                coverPlaceholder.hidden = false;
                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', function () {
                const image = new Image();

                image.addEventListener('load', function () {
                    coverPreview.src = reader.result;
                    coverPreview.hidden = false;
                    coverPlaceholder.hidden = true;

                    applyImageColours(
                        extractImageColours(image)
                    );
                });

                image.src = reader.result;
            });

            reader.readAsDataURL(file);
        });
    }

    const reportAutoTextFields = Array.from(
        dashboard.querySelectorAll('[data-tle-auto-text]')
    );

    const capabilityFields = reportAutoTextFields.filter(
        field => Boolean(field.closest('.tle-dashboard__text-grid--three'))
    );

    function automaticCapabilitySummary(field) {
        const current = currentCapabilityReport;

        if (!current ||
            current.program !== getPrimaryProgramName() ||
            current.cohort !== (cohortSelect ? cohortSelect.value : '')) {
            return '';
        }

        const sectionElement = field.closest(
            '.tle-dashboard__text-section'
        );
        const section = sectionElement?.querySelector(
            '.tle-dashboard__text-section-title'
        )?.textContent.trim().toLowerCase();

        const capability = field.closest(
            '.tle-dashboard__field'
        )?.querySelector('label')?.textContent.trim();

        const scores = current.report.capability_averages
            ?.[section]?.[capability];

        if (!scores) return '';

        const stages = ['pre_program', 'completion', 'delay']
            .filter(stage =>
                typeof scores[stage] === 'number' &&
                Number.isFinite(scores[stage])
            );

        if (!stages.length) return '';

        const labels = {
            pre_program: 'Pre-program',
            completion: 'Post-program',
            delay: 'Delayed'
        };

        if (stages.length === 1) {
            return labels[stages[0]] + ' average score: ' +
                scores[stages[0]].toFixed(2) + ' out of 7.';
        }

        const first = scores[stages[0]];
        const last = scores[stages[stages.length - 1]];
        const change = (last - first) / 7 * 100;
        const amount = Math.abs(Math.round(change));
        const units = amount === 1
            ? 'percentage point'
            : 'percentage points';

        if (change > 0.05) {
            return 'Participants demonstrated positive growth, increasing by ' +
                amount + ' ' + units + '.';
        }

        if (change < -0.05) {
            return "Participants' average score decreased by " +
                amount + ' ' + units + '.';
        }

        return "Participants' average score was unchanged between the first and final available evaluation points.";
    }
    const reportTitleField = dashboard.querySelector('[data-tle-auto-title]');
    const purposeField = dashboard.querySelector('[data-tle-auto-purpose]');
    const overviewIntroField = dashboard.querySelector('[data-tle-overview-intro]');
    const managerTextSection = dashboard.querySelector(
        '[data-tle-manager-text-section]'
    );

    const comparisonTextSections = Array.from(
        dashboard.querySelectorAll(
            '[data-tle-comparison-text-section]'
        )
    );

    const comparisonTextFields = Array.from(
        dashboard.querySelectorAll(
            '[data-tle-comparison-text]'
        )
    );

    reportAutoTextFields.forEach(function (field) {
        field.dataset.tleUserEdited = 'false';

        field.addEventListener('input', function () {
            field.dataset.tleUserEdited = 'true';
        });
    });

    function getSelectedProgramNames() {
        return Array.from(
            programsContainer.querySelectorAll('[data-tle-program-row]')
        ).map(function (row) {
            const input = row.querySelector('[data-tle-program-search]');

            return input
                ? input.dataset.tleProgramSelected || ''
                : '';
        }).filter(Boolean);
    }

    function buildAutomaticReportTitle(programs) {
        if (programs.length !== 1) {
            return 'Program Evaluation';
        }

        const program = programs[0];

        return program.toLowerCase().endsWith('program')
            ? program + ' Evaluation'
            : program + ' Program Evaluation';
    }

    function hasManagerEvaluationSelected() {
        return Boolean(managerCheckbox && managerCheckbox.checked);
    }

    function buildAutomaticComparisonText() {
        const comparisonCount = getComparisonSelections().length;
        const programCount = comparisonCount + 1;
        const countLabel = programCount === 3 ? 'three' : 'two';

        return (
            'This graph compares growth in participants\u2019 self-assessed ' +
            'leadership capabilities across ' + countLabel +
            ' programs, measured by the difference between pre-program ' +
            'and delayed follow-up assessments.'
        );
    }

    // Phase 3B: editable per-program growth summaries. Field names
    // match TLE_Comparison_Summary::field_key() on the server.
    const growthEditorSections = Array.from(
        dashboard.querySelectorAll('[data-tle-growth-editor-section]')
    );
    const originalCapabilityGrids = Array.from(
        dashboard.querySelectorAll('.tle-dashboard__text-section .tle-dashboard__text-grid--three')
    );
    const growthEditorResponses = new Map();
    const growthEditorEdits = new Map();
    let growthEditorSelectionSignature = '';
    let growthEditorRequestId = 0;

    function growthEditorFieldKey(value) {
        return encodeURIComponent(String(value)).replace(/[!'()*]/g, function (char) {
            return '%' + char.charCodeAt(0).toString(16).toUpperCase();
        });
    }

    function growthEditorAutomaticText(capability, growth) {
        const amount = Math.abs(growth).toFixed(2);
        if (growth > 0) {
            return capability + ' improved by ' + amount +
                '% from Pre-program to Delayed.';
        }
        if (growth < 0) {
            return capability + ' declined by ' + amount +
                '% from Pre-program to Delayed.';
        }
        return capability + ' was unchanged from Pre-program to Delayed.';
    }

    function growthEditorSelectedPrograms() {
        const program = getPrimaryProgramName();
        const cohort = cohortSelect ? cohortSelect.value : '';
        const comparisons = getComparisonSelections();
        if (!program || !cohort || comparisons.length === 0) {
            return [];
        }
        const rows = [{ program: program, cohort: cohort, is_primary: true }];
        comparisons.forEach(function (other) {
            if (!other.program || !other.cohort) return;
            if (rows.some(function (row) {
                return row.program === other.program && row.cohort === other.cohort;
            })) return;
            rows.push({ program: other.program, cohort: other.cohort, is_primary: false });
        });
        rows.sort(function (a, b) {
            const firstYear = Number(a.cohort);
            const secondYear = Number(b.cohort);
            if (Number.isInteger(firstYear) && Number.isInteger(secondYear) &&
                firstYear !== secondYear) {
                return firstYear - secondYear;
            }
            const yearOrder = a.cohort.localeCompare(b.cohort);
            if (yearOrder) return yearOrder;
            if (a.is_primary !== b.is_primary) return a.is_primary ? 1 : -1;
            return a.program.localeCompare(b.program);
        });
        return rows;
    }

    async function growthEditorLoadReport(selection) {
        const key = selection.program + '|' + selection.cohort;
        if (!growthEditorResponses.has(key)) {
            const pending = (async function () {
                if (!window.tleDashboardConfig || !window.tleDashboardConfig.evaluationsUrl) {
                    throw new Error('Evaluation API address is unavailable.');
                }
                const url = new URL(window.tleDashboardConfig.evaluationsUrl, window.location.origin);
                url.searchParams.set('program', selection.program);
                url.searchParams.set('cohort', selection.cohort);
                const headers = { Accept: 'application/json' };
                if (window.tleDashboardConfig.restNonce) {
                    headers['X-WP-Nonce'] = window.tleDashboardConfig.restNonce;
                }
                const response = await fetch(url.toString(), {
                    method: 'GET', credentials: 'same-origin', headers: headers
                });
                if (!response.ok) {
                    throw new Error('Could not retrieve ' + key + ' (HTTP ' + response.status + ').');
                }
                const report = await response.json();
                if (!report || !Array.isArray(report.groups) || report.groups.length !== 1 ||
                    !report.groups[0].capability_averages) {
                    throw new Error('No unique scored program report for ' + key + '.');
                }
                const context = report.groups[0].context || {};
                if (context.program !== selection.program ||
                    String(context.cohort) !== String(selection.cohort)) {
                    throw new Error('Program context mismatch for ' + key + '.');
                }
                return report.groups[0].capability_averages;
            })();
            growthEditorResponses.set(key, pending);
        }
        try {
            return await growthEditorResponses.get(key);
        } catch (error) {
            growthEditorResponses.delete(key);
            throw error;
        }
    }

    function growthEditorTopCapabilities(capabilities, section, limit) {
        const values = capabilities[section];
        if (!values || typeof values !== 'object') {
            throw new Error('No ' + section + ' capability scores found.');
        }
        const growth = Object.keys(values).map(function (name) {
            const stages = values[name] || {};
            const initial = stages.pre_program;
            const final = stages.delay;
            if (typeof initial !== 'number' || !Number.isFinite(initial) ||
                initial <= 0 || typeof final !== 'number' ||
                !Number.isFinite(final) || final < 0) {
                return null;
            }
            return {
                capability: name,
                growth: Math.round((((final - initial) / initial) * 100 + Number.EPSILON) * 100) / 100
            };
        }).filter(Boolean);
        growth.sort(function (a, b) {
            return b.growth - a.growth || a.capability.localeCompare(b.capability);
        });
        if (growth.length < limit) {
            throw new Error('Insufficient ' + section + ' growth scores for this selection.');
        }
        return growth.slice(0, limit);
    }

    function growthEditorRender(rows, reports) {
        const limit = 5 - rows.length;
        growthEditorSections.forEach(function (container) {
            const section = container.dataset.tleGrowthEditorSection;
            const slot = container.querySelector('[data-tle-growth-editor-programs]');
            const status = container.querySelector('[data-tle-growth-editor-status]');
            slot.replaceChildren();
            status.hidden = true;

            rows.forEach(function (program, programIndex) {
                const card = document.createElement('div');
                card.className = 'tle-dashboard__growth-editor-card';
                const heading = document.createElement('h5');
                heading.className = 'tle-dashboard__growth-editor-program-name';
                heading.textContent = program.program + ' ' + program.cohort;
                card.appendChild(heading);

                const capabilities = growthEditorTopCapabilities(reports[programIndex], section, limit);
                capabilities.forEach(function (item, itemIndex) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'tle-dashboard__field tle-dashboard__growth-editor-field';
                    const name = document.createElement('label');
                    const id = 'tle-growth-' + section + '-' + programIndex + '-' + itemIndex;
                    name.setAttribute('for', id);
                    name.textContent = item.capability + ' (' +
                        (item.growth > 0 ? '+' : '') + item.growth.toFixed(2) + '%)';

                    const textarea = document.createElement('textarea');
                    const programKey = growthEditorFieldKey(program.program + '|' + program.cohort);
                    const capabilityKey = growthEditorFieldKey(item.capability);
                    const editKey = section + '|' + programKey + '|' + capabilityKey;
                    const automatic = growthEditorAutomaticText(item.capability, item.growth);
                    textarea.id = id;
                    textarea.name = 'growth_summary_text[' + section + '][' +
                        programKey + '][' + capabilityKey + ']';
                    textarea.rows = 2;
                    textarea.maxLength = 500;
                    textarea.value = growthEditorEdits.has(editKey)
                        ? growthEditorEdits.get(editKey) : automatic;
                    textarea.addEventListener('input', function () {
                        growthEditorEdits.set(editKey, textarea.value);
                    });

                    const reset = document.createElement('button');
                    reset.type = 'button';
                    reset.className = 'tle-dashboard__reset-auto';
                    reset.textContent = 'Reset to Automatic';
                    reset.setAttribute('aria-label', 'Reset ' + item.capability +
                        ' for ' + program.program + ' ' + program.cohort);
                    reset.addEventListener('click', function () {
                        growthEditorEdits.delete(editKey);
                        textarea.value = automatic;
                        textarea.focus();
                    });
                    wrapper.appendChild(name);
                    wrapper.appendChild(textarea);
                    wrapper.appendChild(reset);
                    card.appendChild(wrapper);
                });
                slot.appendChild(card);
            });
        });
    }

    function growthEditorSync() {
        const rows = growthEditorSelectedPrograms();
        const active = rows.length >= 2 && rows.length <= 4;
        originalCapabilityGrids.forEach(function (grid) {
            grid.hidden = active;
            grid.querySelectorAll('textarea').forEach(function (field) {
                field.disabled = active;
            });
        });
        growthEditorSections.forEach(function (container) {
            container.hidden = !active;
        });
        if (!active) {
            growthEditorSelectionSignature = '';
            growthEditorRequestId++;
            growthEditorSections.forEach(function (container) {
                container.querySelector('[data-tle-growth-editor-programs]').replaceChildren();
            });
            return;
        }

        const signature = JSON.stringify(rows);
        if (signature === growthEditorSelectionSignature) return;
        growthEditorSelectionSignature = signature;
        const requestId = ++growthEditorRequestId;
        growthEditorSections.forEach(function (container) {
            const status = container.querySelector('[data-tle-growth-editor-status]');
            status.textContent = 'Loading program growth summariesâ€¦';
            status.hidden = false;
            container.querySelector('[data-tle-growth-editor-programs]').replaceChildren();
        });
        Promise.all(rows.map(growthEditorLoadReport)).then(function (reports) {
            if (requestId !== growthEditorRequestId) return;
            growthEditorRender(rows, reports);
        }).catch(function (error) {
            if (requestId !== growthEditorRequestId) return;
            growthEditorSections.forEach(function (container) {
                const status = container.querySelector('[data-tle-growth-editor-status]');
                status.textContent = 'Unable to load editable summaries: ' + error.message +
                    '. The PDF can still use its automatic summaries.';
                status.hidden = false;
            });
            console.warn('Comparison summary editor could not load:', error);
            // Retry when the user changes a selection or triggers another sync.
            growthEditorSelectionSignature = '';
        });
    }
    function syncReportTextState() {
        const programs = getSelectedProgramNames();
        const hasProgram = programs.length > 0;

        const hasComparisons =
            getComparisonSelections().length > 0;

        growthEditorSync();

        comparisonTextSections.forEach(
            function (section) {
                section.hidden =
                    !hasComparisons;
            }
        );

        comparisonTextFields.forEach(
            function (field) {
                field.disabled =
                    !hasComparisons;
            }
        );

        if (managerTextSection) {
            const showManagerSection = hasManagerEvaluationSelected();
            const managerSummary = managerTextSection.querySelector(
                '[name="manager_summary_text"]'
            );

            managerTextSection.hidden = !showManagerSection;

            if (managerSummary) {
                managerSummary.disabled = !showManagerSection;
            }
        }

        reportAutoTextFields.forEach(function (field) {
            if (field.dataset.tleUserEdited === 'true') {
                return;
            }

            if (!hasProgram) {
                field.value = '';
                return;
            }

            if (field === reportTitleField) {
                field.value = buildAutomaticReportTitle(programs);
                return;
            }

            if (field === overviewIntroField) {
                const primaryProgram = getPrimaryProgramName();
                const cohort = cohortSelect ? cohortSelect.value : '';
                const programLabel = [primaryProgram, cohort]
                    .filter(Boolean).join(' ');

                field.value =
                    'This report evaluates ' + programLabel +
                    ' using available participant feedback from pre-program, ' +
                    'post-program and delayed follow-up surveys. The graph ' +
                    'shows changes in self-assessed leadership capabilities ' +
                    'across three domains: Insight, Influence and Impact, ' +
                    'helping readers understand development over time.';
                return;
            }
            if (field === purposeField) {
                if (hasComparisons) {
                    const primaryProgram = getPrimaryProgramName();
                    const primaryCohort = cohortSelect ? cohortSelect.value : '';
                    const primaryLabel = [primaryProgram, primaryCohort]
                        .filter(Boolean).join(' ');

                    field.value =
                        'An evaluation of ' + primaryLabel +
                        '. This report also includes a comparison of ' +
                        'leadership capabilities with other selected ' +
                        'programs and cohorts.';
                } else {
                    // Preserve the existing single-program cover wording.
                    field.value = programs.length === 1
                        ? 'An evaluation of ' + programs[0] +
                            '. Showcasing progress before and after the program.'
                        : 'An evaluation of the selected programs. Showcasing progress before and after the programs.';
                }
                return;
            }

            if (
                field.hasAttribute(
                    'data-tle-comparison-text'
                )
            ) {
                field.value =
                    hasComparisons
                        ? buildAutomaticComparisonText()
                        : '';

                return;
            }

            if (capabilityFields.includes(field)) {
                field.value = automaticCapabilitySummary(field);
                return;
            }

            field.value = field.dataset.tleDefault || '';
        });
    }

    // Allow individual capability summaries to return to live defaults.
    capabilityFields.forEach(function (field) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'tle-dashboard__reset-auto';
        button.textContent = 'Reset to Automatic';

        const label = field.closest('.tle-dashboard__field')
            ?.querySelector('label')?.textContent.trim();

        button.setAttribute(
            'aria-label',
            'Reset ' + (label || 'capability') + ' to automatic'
        );

        button.addEventListener('click', function () {
            field.dataset.tleUserEdited = 'false';
            syncReportTextState();
            field.focus();
        });

        field.insertAdjacentElement('afterend', button);
    });
    if (cohortSelect) {
        cohortSelect.addEventListener(
            'change',
            function () {
                loadPrimaryStageAvailability();
            }
        );
    }
    resetImageColours();
    syncManagerOption();
    syncComparisonState();
    updateReportOverview();
    syncReportTextState();
    loadComparisonOptions();
    loadPrimaryStageAvailability();
});