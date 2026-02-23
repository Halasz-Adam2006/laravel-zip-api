<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Postal Codes') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 space-y-6">
            <div class="border border-gray-200 p-4">
                <h3 class="text-lg font-semibold text-gray-900">Add postal code</h3>
                <p class="text-sm text-gray-600 mt-1">Create a new postal code and city entry.</p>

                <form id="add-form" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label for="add-postal" class="block text-sm font-medium text-gray-700">Postal code</label>
                        <input id="add-postal" name="postal_code" type="text" maxlength="10" required
                            class="mt-1 w-full border border-gray-300 px-2 py-1" />
                    </div>
                    <div>
                        <label for="add-city" class="block text-sm font-medium text-gray-700">City</label>
                        <input id="add-city" name="place_name" type="text" required
                            class="mt-1 w-full border border-gray-300 px-2 py-1" />
                    </div>
                    <div>
                        <label for="add-county" class="block text-sm font-medium text-gray-700">County</label>
                        <input id="add-county" name="county" type="text" required
                            class="mt-1 w-full border border-gray-300 px-2 py-1" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit"
                            class="w-full border border-gray-900 bg-gray-900 px-3 py-1 text-sm font-semibold text-green-500">
                            Add entry
                        </button>
                    </div>
                </form>
            </div>

            <div class="border border-gray-200 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Edit or delete entries</h3>
                        <p class="text-sm text-gray-600 mt-1">Search and update existing postal codes.</p>
                    </div>
                    <div class="flex w-full sm:w-auto gap-2">
                        <input id="search-query" type="text" placeholder="Search"
                            class="w-full sm:w-72 border border-gray-300 px-2 py-1" />
                        <button id="search-btn" type="button"
                            class="border border-gray-900 px-3 py-1 text-sm font-semibold text-gray-900">
                            Search
                        </button>
                    </div>
                </div>

                <div id="status" class="mt-3 hidden border px-3 py-2 text-sm"></div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border border-gray-200 px-2 py-1 text-left">Postal code</th>
                                <th class="border border-gray-200 px-2 py-1 text-left">City</th>
                                <th class="border border-gray-200 px-2 py-1 text-left">County</th>
                                <th class="border border-gray-200 px-2 py-1 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="postal-codes-body">
                            <tr>
                                <td colspan="4" class="border border-gray-200 px-2 py-4 text-center text-gray-500">
                                    Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div id="results-summary" class="mt-3 text-sm text-gray-500"></div>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const addForm = document.getElementById('add-form');
        const searchInput = document.getElementById('search-query');
        const searchButton = document.getElementById('search-btn');
        const tableBody = document.getElementById('postal-codes-body');
        const resultsSummary = document.getElementById('results-summary');
        const statusBox = document.getElementById('status');

        const setStatus = (message, type = 'info') => {
            statusBox.textContent = message;
            statusBox.classList.remove('hidden', 'border-green-200', 'border-red-200', 'border-blue-200', 'bg-green-50', 'bg-red-50', 'bg-blue-50', 'text-green-700', 'text-red-700', 'text-blue-700');

            if (type === 'success') {
                statusBox.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
            } else if (type === 'error') {
                statusBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            } else {
                statusBox.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-700');
            }
        };

        const clearStatus = () => {
            statusBox.classList.add('hidden');
            statusBox.textContent = '';
        };

        const fetchPostalCodes = async () => {
            clearStatus();
            const query = searchInput.value.trim();
            const url = new URL('/api/postal-codes', window.location.origin);
            if (query !== '') {
                url.searchParams.set('query', query);
            }
            url.searchParams.set('limit', '50');

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-3 py-6 text-center text-gray-500">Loading...</td>
                </tr>
            `;

            try {
                const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const payload = await response.json();

                renderRows(payload.data || []);
                resultsSummary.textContent = `${payload.count ?? 0} results${query ? ` for "${query}"` : ''}.`;
            } catch (error) {
                renderRows([]);
                setStatus('Unable to load postal codes. Please try again.', 'error');
            }
        };

        const renderRows = (rows) => {
            if (!rows.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">No results found.</td>
                    </tr>
                `;
                return;
            }

            tableBody.innerHTML = rows.map((row) => `
                <tr data-id="${row.id}">
                    <td class="border border-gray-200 px-2 py-1">
                        <input type="text" value="${row.postal_code}" maxlength="10"
                            class="w-full border border-gray-300 px-2 py-1" />
                    </td>
                    <td class="border border-gray-200 px-2 py-1">
                        <input type="text" value="${row.place_name}"
                            class="w-full border border-gray-300 px-2 py-1" />
                    </td>
                    <td class="border border-gray-200 px-2 py-1">
                        <input type="text" value="${row.county}"
                            class="w-full border border-gray-300 px-2 py-1" />
                    </td>
                    <td class="border border-gray-200 px-2 py-1">
                        <div class="flex flex-wrap gap-2">
                            <button type="button" data-action="save"
                                class="border border-gray-900 bg-gray-900 px-2 py-1 text-xs font-semibold text-green">
                                Save
                            </button>
                            <button type="button" data-action="delete"
                                class="border border-red-600 px-2 py-1 text-xs font-semibold text-red-600">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');
        };

        const getRowData = (row) => {
            const inputs = row.querySelectorAll('input');
            return {
                postal_code: inputs[0]?.value.trim() ?? '',
                place_name: inputs[1]?.value.trim() ?? '',
                county: inputs[2]?.value.trim() ?? '',
            };
        };

        addForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            clearStatus();

            const payload = {
                postal_code: document.getElementById('add-postal').value.trim(),
                place_name: document.getElementById('add-city').value.trim(),
                county: document.getElementById('add-county').value.trim(),
            };

            try {
                const response = await fetch('/api/postal-codes', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to create entry.');
                }

                addForm.reset();
                setStatus(data.message || 'Postal code created.', 'success');
                fetchPostalCodes();
            } catch (error) {
                setStatus(error.message || 'Unable to create entry.', 'error');
            }
        });

        tableBody.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-action]');
            if (!button) {
                return;
            }

            const row = button.closest('tr');
            const id = row?.dataset?.id;
            if (!id) {
                return;
            }

            const action = button.dataset.action;

            if (action === 'delete') {
                const confirmed = confirm('Are you sure you want to delete this entry?');
                if (!confirmed) {
                    return;
                }

                try {
                    const response = await fetch(`/api/postal-codes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to delete entry.');
                    }

                    setStatus(data.message || 'Postal code deleted.', 'success');
                    fetchPostalCodes();
                } catch (error) {
                    setStatus(error.message || 'Unable to delete entry.', 'error');
                }

                return;
            }

            if (action === 'save') {
                const payload = getRowData(row);

                try {
                    const response = await fetch(`/api/postal-codes/${id}`, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || 'Unable to update entry.');
                    }

                    setStatus(data.message || 'Postal code updated.', 'success');
                    fetchPostalCodes();
                } catch (error) {
                    setStatus(error.message || 'Unable to update entry.', 'error');
                }
            }
        });

        searchButton.addEventListener('click', fetchPostalCodes);
        searchInput.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                fetchPostalCodes();
            }
        });

        fetchPostalCodes();
    </script>
</x-app-layout>