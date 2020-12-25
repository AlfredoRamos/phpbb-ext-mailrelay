(function() {
	'use strict';

	// Show/hide API key
	document.body.addEventListener('click', function(e) {
		let button = e.target.closest('.toggle-api-key');

		if (!button) {
			return;
		}

		let field = document.body.querySelector('#mailrelay-api-key');
		let icon = button.querySelector('.icon');

		if (!field || !icon) {
			return;
		}

		let isHidden = (field.getAttribute('type').trim() === 'password');
		field.setAttribute('type', (isHidden ? 'text' : 'password'));
		icon.classList.toggle('fa-eye-slash', isHidden);
		icon.classList.toggle('fa-eye', !isHidden);
	});
})();