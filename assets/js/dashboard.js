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
    const cohortSelect = dashboard.querySelector('#tle-cohort');

    const exportForm = dashboard.querySelector('#tle-pdf-export-form');
    const exportButton = exportForm
        ? exportForm.querySelector('button[type="submit"]')
        : null;

    let comparisonRequirementNotice = null;

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

    function getComparisonProgramNames() {
        return Array.from(
            comparisonRows.querySelectorAll('[data-tle-comparison-row]')
        ).map(function (row) {
            const input = row.querySelector('[data-tle-comparison-search]');

            return input
                ? input.dataset.tleProgramSelected || ''
                : '';
        }).filter(Boolean);
    }

    function getUnavailableComparisonPrograms(currentRow) {
        const unavailable = new Set();
        const primaryProgram = getPrimaryProgramName();

        if (primaryProgram) {
            unavailable.add(primaryProgram);
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

                if (selectedProgram) {
                    unavailable.add(selectedProgram);
                }
            });

        return unavailable;
    }

    function syncComparisonProgramSelections() {
        const primaryProgram = getPrimaryProgramName();
        const seenPrograms = new Set();

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

                if (!selectedProgram) {
                    return;
                }

                const conflictsWithPrimary =
                    primaryProgram &&
                    selectedProgram === primaryProgram;

                const duplicateComparison =
                    seenPrograms.has(selectedProgram);

                if (
                    conflictsWithPrimary ||
                    duplicateComparison
                ) {
                    input.value = '';
                    input.dataset.tleProgramSelected = '';
                    return;
                }

                seenPrograms.add(selectedProgram);
            });
    }

    function getComparisonEvaluationPoint() {
        const selectedPoint = comparisonPoints.find(function (checkbox) {
            return checkbox.checked;
        });

        if (!selectedPoint) {
            return '';
        }

        const label = selectedPoint.closest('label');
        const text = label ? label.querySelector('span') : null;

        return text ? text.textContent.trim() : selectedPoint.value;
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

    function clearPrimaryStageAvailability() {
        primaryStageAvailabilityRequest++;
        primaryAvailableStageValues = [];
        primaryStageAvailabilityLoaded = false;

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

        if (
            !primaryProgram ||
            !primaryStageAvailabilityLoaded
        ) {
            return '';
        }

        const comparisons =
            getComparisonProgramNames();

        const comparisonPoint =
            getComparisonEvaluationPoint();

        if (
            primaryAvailableStageValues.length === 1 &&
            comparisons.length === 0
        ) {
            return 'This program has one available evaluation point. Select at least one comparison program to provide a basis for growth.';
        }

        if (
            comparisons.length > 0 &&
            !comparisonPoint
        ) {
            return 'Select one evaluation point for the comparison before exporting.';
        }

        return '';
    }

    function syncComparisonRequirementLabel() {
        if (!comparisonTitle) {
            return;
        }

        const comparisonRequired =
            Boolean(getPrimaryProgramName()) &&
            primaryStageAvailabilityLoaded &&
            primaryAvailableStageValues.length === 1;

        comparisonTitle.textContent =
            comparisonRequired
                ? 'Comparison (required)'
                : 'Comparison (optional)';
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
        comparisonPoints.forEach(function (checkbox) {
            const unavailable =
                primaryStageAvailabilityLoaded &&
                !primaryAvailableStageValues.includes(
                    checkbox.value
                );

            if (unavailable) {
                checkbox.checked = false;
            }
        });

        const selectedPoint = comparisonPoints.find(
            function (checkbox) {
                return checkbox.checked;
            }
        );

        comparisonPoints.forEach(function (checkbox) {
            const unavailable =
                primaryStageAvailabilityLoaded &&
                !primaryAvailableStageValues.includes(
                    checkbox.value
                );

            const lockedBySelection =
                Boolean(selectedPoint) &&
                checkbox !== selectedPoint;

            checkbox.disabled =
                unavailable ||
                lockedBySelection;

            const label = checkbox.closest('label');

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

        const optionButtons = primaryProgramSuggestions.querySelectorAll(
            '[data-tle-program-option]'
        );

        function updateSuggestions() {
            const query = primaryProgramInput.value.trim().toLowerCase();
            let visibleCount = 0;

            optionButtons.forEach(function (button) {
                const value = button.dataset.tleProgramOption || '';
                const matches =
                    query === '' ||
                    value.toLowerCase().includes(query);

                button.hidden = !matches;

                if (matches) {
                    visibleCount++;
                }
            });

            primaryProgramSuggestions.hidden = visibleCount === 0;
        }

        primaryProgramInput.addEventListener('focus', updateSuggestions);

        primaryProgramInput.addEventListener('input', function () {
            primaryProgramInput.dataset.tleProgramSelected = '';

            clearPrimaryStageAvailability();
            updateSuggestions();
            syncManagerOption();
            updateReportOverview();
            syncReportTextState();
        });

        optionButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const selectedProgram =
                    button.dataset.tleProgramOption || '';

                primaryProgramInput.value = selectedProgram;
                primaryProgramInput.dataset.tleProgramSelected =
                    selectedProgram;
                primaryProgramSuggestions.hidden = true;

                syncManagerOption();
                syncComparisonProgramSelections();
                syncComparisonState();
                updateReportOverview();
                syncReportTextState();
                loadPrimaryStageAvailability();
            });
        });

        document.addEventListener('click', function (event) {
            if (!primaryProgramRow.contains(event.target)) {
                primaryProgramSuggestions.hidden = true;
            }
        });
    }

    function initialiseComparisonRow(row) {
        const searchInput = row.querySelector('[data-tle-comparison-search]');
        const suggestions = row.querySelector(
            '[data-tle-comparison-suggestions]'
        );
        const removeButton = row.querySelector(
            '[data-tle-remove-comparison]'
        );

        if (searchInput && suggestions) {
            const optionButtons = suggestions.querySelectorAll(
                '[data-tle-comparison-option]'
            );

            function updateSuggestions() {
                const query = searchInput.value.trim().toLowerCase();
                const unavailablePrograms =
                    getUnavailableComparisonPrograms(row);

                let visibleCount = 0;

                optionButtons.forEach(function (button) {
                    const value =
                        button.dataset.tleComparisonOption || '';

                    const matchesQuery =
                        query === '' ||
                        value.toLowerCase().includes(query);

                    const available =
                        !unavailablePrograms.has(value);

                    const visible =
                        matchesQuery && available;

                    button.hidden = !visible;

                    if (visible) {
                        visibleCount++;
                    }
                });

                suggestions.hidden = visibleCount === 0;
            }

            searchInput.addEventListener('focus', updateSuggestions);

            searchInput.addEventListener('input', function () {
                searchInput.dataset.tleProgramSelected = '';

                updateSuggestions();
                syncComparisonState();
                updateReportOverview();
            });

            optionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const selectedProgram =
                        button.dataset.tleComparisonOption || '';

                    const unavailablePrograms =
                        getUnavailableComparisonPrograms(row);

                    if (
                        !selectedProgram ||
                        unavailablePrograms.has(selectedProgram)
                    ) {
                        return;
                    }

                    searchInput.value = selectedProgram;
                    searchInput.dataset.tleProgramSelected =
                        selectedProgram;
                    suggestions.hidden = true;

                    syncComparisonProgramSelections();
                    syncComparisonState();
                    updateReportOverview();
                });
            });

            document.addEventListener('click', function (event) {
                if (!row.contains(event.target)) {
                    suggestions.hidden = true;
                }
            });
        }

        if (removeButton) {
            removeButton.addEventListener('click', function () {
                row.remove();

                syncComparisonState();
                updateReportOverview();
            });
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

    const reportTitleField = dashboard.querySelector('[data-tle-auto-title]');
    const purposeField = dashboard.querySelector('[data-tle-auto-purpose]');
    const managerTextSection = dashboard.querySelector(
        '[data-tle-manager-text-section]'
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

    function syncReportTextState() {
        const programs = getSelectedProgramNames();
        const hasProgram = programs.length > 0;

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

            if (field === purposeField) {
                field.value = programs.length === 1
                    ? 'An evaluation of ' + programs[0] +
                        '. Showcasing progress before and after the program.'
                    : 'An evaluation of the selected programs. Showcasing progress before and after the programs.';
                return;
            }

            field.value = field.dataset.tleDefault || '';
        });
    }

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
    loadPrimaryStageAvailability();
});