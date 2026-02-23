<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $county->name }}
        </h2>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <div style="margin-bottom: 2rem;">
            <a href="/counties" style="color: #3b82f6; text-decoration: none;">&larr; Back to Counties</a>
        </div>

        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; margin-bottom: 1.5rem;">
            <a id="export-csv" href="#"
                style="display: inline-block; padding: .35rem .75rem; border: 1px solid #111827; text-decoration: none; color: #111827;">
                Export CSV
            </a>
            <a id="export-pdf" href="#"
                style="display: inline-block; padding: .35rem .75rem; border: 1px solid #111827; text-decoration: none; color: #111827;">
                Export PDF
            </a>

            @auth
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input id="export-email" type="email" placeholder="Email address"
                        style="border: 1px solid #d1d5db; padding: .35rem .5rem; min-width: 220px;" />
                    <button id="export-email-btn" type="button"
                        style="border: 1px solid #111827; background: #111827; color: #fff; padding: .35rem .75rem;">
                        Email CSV
                    </button>
                </div>
            @endauth
        </div>

        <p id="export-status" style="display: none; margin-bottom: 1rem;"></p>

        <div style="margin-bottom: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">Select a letter:</h2>
            <div id="alphabet-grid"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 0.5rem; max-width: 800px;">
            </div>
        </div>

        <div id="cities-section" style="display: none;">
            <h2 style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">
                Cities starting with <span id="selected-letter"></span>:
            </h2>
            <p id="cities-loading" style="display: none;">Loading cities...</p>
            <p id="cities-error" style="color: #b91c1c; display: none;"></p>
            <ul id="cities-list"
                style="list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 0.75rem;">

            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countyName = '{{ $county->name }}';

            const alphabetGrid = document.getElementById('alphabet-grid');
            const citiesSection = document.getElementById('cities-section');
            const selectedLetterEl = document.getElementById('selected-letter');
            const citiesLoadingEl = document.getElementById('cities-loading');
            const citiesErrorEl = document.getElementById('cities-error');
            const citiesListEl = document.getElementById('cities-list');
            const exportCsvLink = document.getElementById('export-csv');
            const exportPdfLink = document.getElementById('export-pdf');
            const exportStatusEl = document.getElementById('export-status');
            const exportEmailInput = document.getElementById('export-email');
            const exportEmailButton = document.getElementById('export-email-btn');
            let currentLetter = '';

            // Generate alphabet buttons (A-Z)
            const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('');
            alphabet.forEach(letter => {
                const button = document.createElement('button');
                button.textContent = letter;
                button.style.fontSize = '1.25rem';
                button.style.fontWeight = '600';
                button.style.backgroundColor = '#ffffff';
                button.style.cursor = 'pointer';
                button.style.border = '1px solid #e5e7eb';

                button.addEventListener('click', function () {
                    loadCitiesByLetter(letter);
                });

                alphabetGrid.appendChild(button);
            });

            const setExportLinks = (letter) => {
                currentLetter = letter;
                const encodedCounty = encodeURIComponent(countyName);
                exportCsvLink.href = `/counties/${encodedCounty}/alphabet/${letter}/export/csv`;
                exportPdfLink.href = `/counties/${encodedCounty}/alphabet/${letter}/export/pdf`;
            };

            const setExportStatus = (message, isError = false) => {
                exportStatusEl.textContent = message;
                exportStatusEl.style.display = message ? 'block' : 'none';
                exportStatusEl.style.color = isError ? '#b91c1c' : '#047857';
            };

            function loadCitiesByLetter(letter) {
                selectedLetterEl.textContent = letter;
                citiesSection.style.display = 'block';
                citiesLoadingEl.style.display = 'block';
                citiesErrorEl.style.display = 'none';
                citiesListEl.innerHTML = '';
                setExportLinks(letter);
                setExportStatus('');

                fetch(`/api/county/${encodeURIComponent(countyName)}/${letter}`, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.status + ' ' + response.statusText);
                        return response.json();
                    })
                    .then(cities => {
                        citiesLoadingEl.style.display = 'none';

                        if (cities.error) {
                            citiesErrorEl.textContent = cities.error;
                            citiesErrorEl.style.display = 'block';
                            return;
                        }

                        if (!Array.isArray(cities) || cities.length === 0) {
                            citiesErrorEl.textContent = 'No cities found starting with ' + letter;
                            citiesErrorEl.style.display = 'block';
                            return;
                        }

                        citiesListEl.innerHTML = '';
                        cities.forEach(city => {
                            const nameEl = document.createElement('div');
                            nameEl.textContent = city.city;

                            citiesListEl.appendChild(nameEl);
                        });
                    })
                    .catch(err => {
                        citiesLoadingEl.style.display = 'none';
                        citiesErrorEl.textContent = 'Error fetching cities. See console for details.';
                        citiesErrorEl.style.display = 'block';
                    });
            }

            if (exportEmailButton) {
                exportEmailButton.addEventListener('click', async () => {
                    if (!currentLetter) {
                        setExportStatus('Select a letter first.', true);
                        return;
                    }

                    setExportStatus('Sending email...');
                    try {
                        const response = await fetch(`/counties/${encodeURIComponent(countyName)}/alphabet/${currentLetter}/export/email`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ email: exportEmailInput?.value.trim() || null }),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw new Error(payload.error || payload.message || 'Email export failed.');
                        }

                        setExportStatus(payload.message || 'Email export sent.');
                    } catch (error) {
                        setExportStatus(error.message || 'Email export failed.', true);
                    }
                });
            }
        });
    </script>
</x-app-layout>