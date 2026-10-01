document.addEventListener('DOMContentLoaded', function () {
    const dashboard = document.querySelector('.tle-dashboard');

    if (!dashboard) {
        return;
    }

    const programsContainer = dashboard.querySelector('[data-tle-programs]');
    const addProgramButton = dashboard.querySelector('[data-tle-add-program]');
    const programTemplate = dashboard.querySelector('#tle-program-row-template');
    const cohortSelect = dashboard.querySelector('#tle-cohort');

    const summaryPrograms = dashboard.querySelector('[data-tle-summary-programs]');
    const summaryCohort = dashboard.querySelector('[data-tle-summary-cohort]');
    const summaryEvaluationPoints = dashboard.querySelector(
        '[data-tle-summary-evaluation-points]'
    );

    if (!programsContainer || !addProgramButton || !programTemplate) {
        return;
    }

    let nextProgramIndex =
        programsContainer.querySelectorAll('[data-tle-program-row]').length;

    function normaliseProgramFieldNames() {
        const rows = Array.from(
            programsContainer.querySelectorAll('[data-tle-program-row]')
        );

        rows.forEach(function (row, index) {
            const searchInput = row.querySelector('[data-tle-program-search]');

            if (searchInput) {
                searchInput.name = index === 0
                    ? 'program'
                    : 'additional_programs[' + index + '][program]';
            }

            row.querySelectorAll(
                '[data-tle-evaluation-menu] input[type="checkbox"]'
            ).forEach(function (checkbox) {
                checkbox.name = index === 0
                    ? 'evaluation_points[]'
                    : 'additional_programs[' + index + '][evaluation_points][]';
            });
        });
    }

    function updateProgramRemovalControls() {
        const rows = Array.from(
            programsContainer.querySelectorAll('[data-tle-program-row]')
        );

        rows.forEach(function (row) {
            const removeButton = row.querySelector('[data-tle-remove-program]');

            if (removeButton) {
                removeButton.hidden = rows.length <= 1;
            }
        });
    }

    function syncProgramRows() {
        normaliseProgramFieldNames();
        updateProgramRemovalControls();
        updateReportOverview();
    }

    function getSelectedEvaluationPoints(row) {
        return Array.from(
            row.querySelectorAll(
                '[data-tle-evaluation-menu] input[type="checkbox"]:checked'
            )
        ).map(function (checkbox) {
            const label = checkbox.closest('label');
            const text = label ? label.querySelector('span') : null;

            return text ? text.textContent.trim() : checkbox.value;
        });
    }

    function updateReportOverview() {
        const selectedPrograms = Array.from(
            programsContainer.querySelectorAll('[data-tle-program-row]')
        ).map(function (row) {
            const searchInput = row.querySelector('[data-tle-program-search]');
            const programName = searchInput
                ? searchInput.dataset.tleProgramSelected || ''
                : '';

            if (!programName) {
                return null;
            }

            return {
                name: programName,
                evaluationPoints: getSelectedEvaluationPoints(row)
            };
        }).filter(Boolean);

        if (summaryPrograms) {
            summaryPrograms.textContent = selectedPrograms.length
                ? selectedPrograms.map(function (program) {
                    return program.name;
                }).join(', ')
                : 'No program selected';
        }

        if (summaryCohort) {
            summaryCohort.textContent = cohortSelect
                ? cohortSelect.value
                : '—';
        }

        if (summaryEvaluationPoints) {
            summaryEvaluationPoints.textContent = selectedPrograms.length
                ? selectedPrograms.map(function (program) {
                    const points = program.evaluationPoints.length
                        ? program.evaluationPoints.join(', ')
                        : 'None selected';

                    return program.name + ': ' + points;
                }).join(' | ')
                : 'Select a program to view evaluation points';
        }
    }

    function initialiseProgramRow(row) {
        const searchInput = row.querySelector('[data-tle-program-search]');
        const suggestions = row.querySelector('[data-tle-program-suggestions]');
        const evaluationButton = row.querySelector('[data-tle-evaluation-toggle]');
        const evaluationMenu = row.querySelector('[data-tle-evaluation-menu]');
        const removeButton = row.querySelector('[data-tle-remove-program]');

        if (searchInput && suggestions) {
            const suggestionButtons =
                suggestions.querySelectorAll('[data-tle-program-option]');

            function updateSuggestions() {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                suggestionButtons.forEach(function (button) {
                    const value = button.dataset.tleProgramOption || '';
                    const matches =
                        query === '' ||
                        value.toLowerCase().includes(query);

                    button.hidden = !matches;

                    if (matches) {
                        visibleCount++;
                    }
                });

                suggestions.hidden = visibleCount === 0;
            }

            searchInput.addEventListener('focus', updateSuggestions);

            searchInput.addEventListener('input', function () {
                searchInput.dataset.tleProgramSelected = '';

                if (evaluationButton) {
                    evaluationButton.hidden = true;
                }

                if (evaluationMenu) {
                    evaluationMenu.hidden = true;
                    evaluationButton.setAttribute('aria-expanded', 'false');
                }

                updateSuggestions();
                updateReportOverview();
            });

            suggestionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const selectedProgram =
                        button.dataset.tleProgramOption || '';

                    searchInput.value = selectedProgram;
                    searchInput.dataset.tleProgramSelected = selectedProgram;
                    suggestions.hidden = true;

                    if (evaluationButton) {
                        evaluationButton.hidden = false;
                    }

                    updateReportOverview();
                });
            });

            document.addEventListener('click', function (event) {
                if (!row.contains(event.target)) {
                    suggestions.hidden = true;
                }
            });
        }

        if (evaluationButton && evaluationMenu) {
            evaluationButton.addEventListener('click', function () {
                const isOpen = !evaluationMenu.hidden;

                evaluationMenu.hidden = isOpen;
                evaluationButton.setAttribute(
                    'aria-expanded',
                    String(!isOpen)
                );
            });
        }

        row.querySelectorAll(
            '[data-tle-evaluation-menu] input[type="checkbox"]'
        ).forEach(function (checkbox) {
            checkbox.addEventListener('change', updateReportOverview);
        });

        if (removeButton) {
            removeButton.addEventListener('click', function () {
                const rowCount =
                    programsContainer.querySelectorAll('[data-tle-program-row]').length;

                if (rowCount <= 1) {
                    return;
                }

                row.remove();
                syncProgramRows();
            });
        }
    }

    programsContainer
        .querySelectorAll('[data-tle-program-row]')
        .forEach(initialiseProgramRow);

    if (cohortSelect) {
        cohortSelect.addEventListener('change', updateReportOverview);
    }

    addProgramButton.addEventListener('click', function () {
        const fragment = programTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-tle-program-row]');

        row.innerHTML = row.innerHTML.replaceAll(
            '__INDEX__',
            String(nextProgramIndex)
        );

        nextProgramIndex++;

        programsContainer.appendChild(row);
        initialiseProgramRow(row);

        const searchInput = row.querySelector('[data-tle-program-search]');

        if (searchInput) {
            searchInput.focus();
        }

        syncProgramRows();
    });

    syncProgramRows();
});