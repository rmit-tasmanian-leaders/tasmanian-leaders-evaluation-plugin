document.addEventListener('DOMContentLoaded', function () {
    const dashboard = document.querySelector('.tle-dashboard');

    if (!dashboard) {
        return;
    }

    const programsContainer = dashboard.querySelector('[data-tle-programs]');
    const addProgramButton = dashboard.querySelector('[data-tle-add-program]');
    const programTemplate = dashboard.querySelector('#tle-program-row-template');

    if (!programsContainer || !addProgramButton || !programTemplate) {
        return;
    }

    let nextProgramIndex = programsContainer.querySelectorAll('[data-tle-program-row]').length;

    function initialiseProgramRow(row) {
        const searchInput = row.querySelector('[data-tle-program-search]');
        const suggestions = row.querySelector('[data-tle-program-suggestions]');
        const evaluationButton = row.querySelector('[data-tle-evaluation-toggle]');
        const evaluationMenu = row.querySelector('[data-tle-evaluation-menu]');
        const removeButton = row.querySelector('[data-tle-remove-program]');

        if (searchInput && suggestions) {
            const suggestionButtons = suggestions.querySelectorAll('[data-tle-program-option]');

            function updateSuggestions() {
                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                suggestionButtons.forEach(function (button) {
                    const value = button.dataset.tleProgramOption || '';
                    const matches = query === '' || value.toLowerCase().includes(query);

                    button.hidden = !matches;

                    if (matches) {
                        visibleCount++;
                    }
                });

                suggestions.hidden = visibleCount === 0;
            }

            searchInput.addEventListener('focus', updateSuggestions);
            searchInput.addEventListener('input', function () {
                if (evaluationButton) {
                    evaluationButton.hidden = true;
                }

                if (evaluationMenu) {
                    evaluationMenu.hidden = true;
                }

                updateSuggestions();
            });

            suggestionButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    searchInput.value = button.dataset.tleProgramOption || '';
                    suggestions.hidden = true;

                    if (evaluationButton) {
                        evaluationButton.hidden = false;
                    }
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
                evaluationButton.setAttribute('aria-expanded', String(!isOpen));
            });
        }

        if (removeButton) {
            removeButton.addEventListener('click', function () {
                row.remove();
            });
        }
    }

    programsContainer.querySelectorAll('[data-tle-program-row]').forEach(initialiseProgramRow);

    addProgramButton.addEventListener('click', function () {
        const fragment = programTemplate.content.cloneNode(true);
        const row = fragment.querySelector('[data-tle-program-row]');

        row.innerHTML = row.innerHTML.replaceAll('__INDEX__', String(nextProgramIndex));
        nextProgramIndex++;

        programsContainer.appendChild(row);
        initialiseProgramRow(row);

        const searchInput = row.querySelector('[data-tle-program-search]');

        if (searchInput) {
            searchInput.focus();
        }
    });
});