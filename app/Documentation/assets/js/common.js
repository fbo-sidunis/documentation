function reloadTooltips() {
	const tooltipTriggerList = Array.from(
		document.querySelectorAll('[data-bs-toggle="tooltip"]')
	);
	tooltipTriggerList.forEach((tooltipTriggerEl) => {
		let tooltip = bootstrap.Tooltip.getOrCreateInstance(tooltipTriggerEl);
		tooltip.update();
	});
}
/* global bootstrap: false */
(() => reloadTooltips())();
