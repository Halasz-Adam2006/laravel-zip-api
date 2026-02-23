<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Counties') }}
        </h2>
    </x-slot>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div>
        <a>
            {{ session('user_name') }}
            <br>
            {{ session('user_email') }}
        </a>
    </div>



    <div class="counties-wrapper">
        <p id="counties-loading">Loading counties…</p>
        <p id="counties-error" style="color: #b91c1c; display: none;"></p>

        <ul id="county-list" style="list-style: none; padding: 0; margin: .5rem 0;"></ul>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const apiUrl = '/api/counties';

            const loadingEl = document.getElementById('counties-loading');
            const errorEl = document.getElementById('counties-error');
            const listEl = document.getElementById('county-list');

            function showError(message) {
                loadingEl.style.display = 'none';
                errorEl.textContent = message;
                errorEl.style.display = '';
            }

            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    loadingEl.style.display = 'none';

                    if (!Array.isArray(data) || data.length === 0) {
                        showError('No counties returned from the API.');
                        return;
                    }

                    // Sort counties by name
                    data.sort((a, b) => (a.name || '').localeCompare(b.name || '', undefined, { sensitivity: 'base' }));

                    // Populate list view with clickable items
                    listEl.innerHTML = '';
                    data.forEach(county => {
                        const li = document.createElement('li');
                        li.textContent = county.name;
                        li.style.padding = '.5rem 1rem';
                        li.style.cursor = 'pointer';
                        li.style.borderBottom = '1px solid #e5e7eb';
                        li.style.transition = 'background-color 0.2s';

                        li.addEventListener('mouseenter', function () {
                            this.style.backgroundColor = '#f3f4f6';
                        });

                        li.addEventListener('mouseleave', function () {
                            this.style.backgroundColor = '';
                        });

                        li.addEventListener('click', function () {
                            window.location.href = '/counties/' + county.name + '/alphabet';
                        });

                        listEl.appendChild(li);
                    });
                })
                .catch(err => {
                    showError('Error fetching counties. See console for details.');
                });
        });
    </script>
</x-app-layout>