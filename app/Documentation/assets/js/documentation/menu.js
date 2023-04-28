export let Menu = (() => {
	let self = {
		container: document.querySelector("#list_menu"),
		datas: {},
		refresh: () => {
			sendGET({
				route: "menu_get",
				callback: response => {
					self.datas = response;
					self.buildMenu();
				},
			});
		},
		buildMenu: () => {
			let template = Twig.twig({ data: document.querySelector("#twig_template_menu").innerHTML });
			self.container.innerHTML = template.render({ menus: self.datas, editmode: parseInt(EDITMENU) ? true : false });
			self.editItem.applyEvents();
			self.setActive(Page.data.fullPath);
		},
		pageLink: () => [...document.querySelectorAll(".menu-entry")],
		setActive: fullPath => {
			self.pageLink().forEach(link => {
				link.classList.remove("active");
				if (link.dataset.fullPath == fullPath) {
					link.classList.add("active");
				}
			});
		},
		editItem: {
			pageLink: () => [...document.querySelectorAll(".menu-entry:not([data-event-applied])")],
			editButtons: () => [...document.querySelectorAll(".button-edit-menu-item:not([data-event-applied])")],
			deleteButtons: () => [...document.querySelectorAll(".button-delete-menu-item:not([data-event-applied])")],
			moveButtons: () => [...document.querySelectorAll(".button-move-menu-item:not([data-event-applied])")],
			addButtons: () => [...document.querySelectorAll(".button-add-menu-item:not([data-event-applied])")],
			modal: {
				get: () => bootstrap.Modal.getOrCreateInstance(document.querySelector("#modal_edit_item")),
				show() {
					this.get().show();
				},
				hide() {
					this.get().hide();
				},
				setTitle: title => (document.querySelector("#modal_edit_item_title").innerText = title),
			},
			form: new Form({
				formEl: "#form_item",
				btnSave: "#button_save_item",
				route: "menu_save_item",
				callBackAfterSaveSuccess: response => {
					self.datas = response.data.menus;
					self.buildMenu();
					self.editItem.modal.hide();
				},
				callBackAfterFill(datas) {},
			}),
			applyEvents() {
				this.editButtons().forEach(btn => {
					btn.addEventListener("click", () => {
						let itemName;
						let fullPath = btn.dataset.fullPath ?? "";
						let item = null;
						let fullPathSplit = fullPath.split(".");
						fullPathSplit.forEach((name, index) => {
							if (index == 0) {
								item = self.datas[name];
							} else {
								item = item.items[name];
							}
							itemName = name;
						});
						console.log(itemName, fullPath, item);
						this.modal.setTitle(itemName ? "Modifier un élément" : "Ajouter un élément");
						this.modal.show();
						this.form.fill({
							fullPath: fullPath,
							itemName: itemName ?? "",
							title: item.title ?? "",
							action: "edit",
						});
					});
					btn.dataset.eventApplied = true;
				});
				this.deleteButtons().forEach(btn => {
					btn.addEventListener("click", () => {
						let fullPath = btn.dataset.fullPath ?? "";
						return Swal.fire({
							title: "Êtes-vous sûr ?",
							text: "Vous ne pourrez pas revenir en arrière !",
							icon: "warning",
							showCancelButton: true,
							confirmButtonText: "Oui, supprimer !",
							cancelButtonText: "Non, annuler",
						}).then(result => {
							if (result.isConfirmed) {
								sendPOST({
									route: "menu_delete_item",
									data: { fullPath: fullPath },
									callback: response => {
										if (response.result != 1) {
											return;
										}
										self.datas = response.data.menus;
										self.buildMenu();
									},
								});
							}
						});
					});
					btn.dataset.eventApplied = true;
				});
				this.moveButtons().forEach(btn => {
					btn.addEventListener("click", () => {
						let fullPath = btn.dataset.fullPath ?? "";
						let direction = btn.dataset.direction ?? "";
						sendPOST({
							route: "menu_move_item",
							data: { fullPath: fullPath, direction: direction },
							callback: response => {
								if (response.result != 1) {
									return;
								}
								self.datas = response.data.menus;
								self.buildMenu();
							},
						});
					});
					btn.dataset.eventApplied = true;
				});
				this.addButtons().forEach(btn => {
					btn.addEventListener("click", () => {
						let path = btn.dataset.path ?? "";
						this.modal.setTitle("Ajouter un élément");
						this.modal.show();
						this.form.fill({
							path: path,
							itemName: "",
							title: "",
							action: "add",
						});
					});
					btn.dataset.eventApplied = true;
				});
				this.pageLink().forEach(btn => {
					btn.addEventListener("click", () => {
						let fullPath = btn.dataset.fullPath ?? "";
						Page.show(fullPath);
					});
					btn.dataset.eventApplied = true;
				});
			},
			init() {
				this.applyEvents();
				this.form.init();
			},
		},
		init() {
			self.refresh();
			self.editItem.init();
		},
	};
	return self;
})();
