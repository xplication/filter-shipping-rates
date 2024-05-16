// wordpressApi.js

const apiUrl =  wpApiSettings.root + 'filter-shipping-rates/v1/settings';

export async function getOptions() {
	try {
		const response = await fetch(apiUrl, {
			method: 'GET',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wpApiSettings.nonce
			},
		});
		const options = await response.json();
		return options;
	} catch (error) {
		console.error('Eroare la obținerea opțiunilor:', error);
		return null;
	}
}

export async function saveOptions(newOptions) {
	try {
		const response = await fetch(apiUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wpApiSettings.nonce
			},
			body: JSON.stringify(newOptions),
		});
		return await response.json();
	} catch (error) {
		console.error('Eroare la salvarea opțiunilor:', error);
		return null;
	}
}
