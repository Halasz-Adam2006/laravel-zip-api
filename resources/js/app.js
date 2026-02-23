import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const initPostalSearch = () => {
	const form = document.getElementById('postal-search-form');
	const input = document.getElementById('postal-search-input');
	const resultsBody = document.getElementById('postal-search-results');
	const status = document.getElementById('postal-search-status');
	const exampleChips = document.querySelectorAll('.example-chip');

	if (!form || !input || !resultsBody || !status) {
		return;
	}

	let controller = null;

	const renderRows = (rows) => {
		resultsBody.innerHTML = '';

		if (rows.length === 0) {
			const emptyRow = document.createElement('tr');
			emptyRow.innerHTML = `
				<td class="px-4 py-4 text-slate-400" colspan="3">
					No results found.
				</td>
			`;
			resultsBody.appendChild(emptyRow);
			return;
		}

		rows.forEach((row) => {
			const tr = document.createElement('tr');
			tr.innerHTML = `
				<td class="px-4 py-3 font-semibold text-white">${row.postal_code}</td>
				<td class="px-4 py-3 text-slate-200">${row.place_name}</td>
				<td class="px-4 py-3 text-slate-300">${row.county}</td>
			`;
			resultsBody.appendChild(tr);
		});
	};

	const updateStatus = (message, tone = 'text-slate-400') => {
		status.textContent = message;
		status.className = `text-xs ${tone}`;
	};

	const search = async (query) => {
		const trimmed = query.trim();

		if (!trimmed) {
			resultsBody.innerHTML = `
				<tr>
					<td class="px-4 py-4 text-slate-400" colspan="3">
						Search results will appear here.
					</td>
				</tr>
			`;
			updateStatus('Start typing to search.');
			return;
		}

		if (controller) {
			controller.abort();
		}

		controller = new AbortController();
		updateStatus('Searching...', 'text-indigo-200');

		try {
			const response = await fetch(`/api/postal-codes?query=${encodeURIComponent(trimmed)}`,
				{ signal: controller.signal }
			);

			if (!response.ok) {
				throw new Error('Request failed');
			}

			const payload = await response.json();
			renderRows(payload.data ?? []);
			updateStatus(`Found ${payload.count ?? 0} result(s).`, 'text-emerald-300');
		} catch (error) {
			if (error.name === 'AbortError') {
				return;
			}
			updateStatus('Could not load results. Try again.', 'text-rose-300');
		}
	};

	form.addEventListener('submit', (event) => {
		event.preventDefault();
		search(input.value);
	});

	input.addEventListener('input', () => {
		search(input.value);
	});

	exampleChips.forEach((chip) => {
		chip.addEventListener('click', () => {
			const example = chip.getAttribute('data-example');
			if (!example) {
				return;
			}
			input.value = example;
			search(example);
		});
	});
};

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initPostalSearch);
} else {
	initPostalSearch();
}
