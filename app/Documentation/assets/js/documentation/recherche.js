export let Recherche = (() => {
	let self = {
		input: document.querySelector("#input_search"),
		autocomplete: null,
		initAutocomplete() {
			self.autocomplete = new Autocomplete({
				input: self.input,
				mode: "dropdown", // "dropdown" ou "list
				route: "doc_search",
				callbackValue: (choice) => choice.fullPath,
				callbackLabel: (choice) => choice.title,
				callbackOnSelect: (value, choice, input) => {
					input.value = "";
					Page.show(value);
				},
			});
			self.autocomplete.init();
		},
		init() {
			loadClass("Autocomplete", LIBRARY_URL + "helpers/js/Autocomplete.js", () => {
				self.initAutocomplete();
			});
		},
	};
	return self;
})();
