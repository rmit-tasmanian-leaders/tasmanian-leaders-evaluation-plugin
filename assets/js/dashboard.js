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

    resetImageColours();
    syncProgramRows();
});