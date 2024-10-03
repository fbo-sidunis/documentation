export let Page = (() => {
  let self = {
    title: document.querySelector("#h2_page_title > span"),
    content: document.querySelector("#div_page_content"),
    btnEdition: document.querySelector("#button_page_edit"),
    parser: new edjsParser(),
    changeUrl: p => {
      let url = Route.get("doc", { p: p });
      window.history.pushState({ path: url }, "", url);
      Menu.setActive(p);
    },
    edition: {
      form: new Form({
        formEl: "#form_page",
        btnSave: "#button_save_page",
        route: "page_save",
        callBackBeforeSaveUseCallback: true,
        callBackBeforeSave: (formData, formEl, followUp) => {
          self.edition.editeur.save().then(outputData => {
            formData.append("page[content]", JSON.stringify(outputData));
            followUp();
          });
        },
        callBackAfterSaveSuccess() {
          self.show(self.data.fullPath);
          self.edition.modal.hide();
        },
      }),
      modal: {
        get: () => bootstrap.Modal.getOrCreateInstance(document.querySelector("#modal_edit_page")),
        show() {
          this.get().show();
        },
        hide() {
          this.get().hide();
        },
      },
      editeur: null,

      init() {
        self.btnEdition.addEventListener("click", () => {
          self.edition.form.fill({
            title: self.data.title,
            fullPath: self.data.fullPath,
          });
          if (self.data.content && (self.data.content.blocks || []).length) {
            console.log(self.data.content.blocks);
            this.editeur.render(self.data.content);
          }else{
            console.log("clear");
            this.editeur.blocks.clear();
          }
          this.modal.show();
        });
        //On transforme un textarea en éditeur, en le cachant et en créant un div à la place
        let textarea = document.querySelector("#textarea_page_content");
        let div = createElement({
          tagName: "div",
          attrs: {
            id: "editorjs_page_content",
            className: "border rounded p-2",
          },
          parent: textarea.parentNode,
        });
        textarea.style.display = "none";
        div.id = "editorjs_page";

        let tools = {
          header: {
            class: Header,
            config: {
              placeholder: "Enter a header",
              levels: [1, 2, 3, 4],
              defaultLevel: 3,
            },
          },
          list: {
            class: List,
            inlineToolbar: true,
            config: {
              defaultStyle: "unordered",
            },
          },
          image: {
            class: ImageTool,
            config: {
              endpoints: {
                byFile: Route.get("image_upload_file"),
                byUrl: Route.get("image_upload_url"),
              },
            },
          },
          table: Table,
          code: CodeTool,
          raw: RawTool,
        };
        let editeur = new EditorJS({
          holder: div,
          tools: tools,
          onReady: () => {},
        });
        this.editeur = editeur;
        this.form.init();
      },
    },
    data: {
      fullPath: null,
      title: null,
      content: null,
    },
    show(fullPath) {
      sendGET({
        route: "page_get",
        vars: {
          fullPath: fullPath,
        },
        callback: response => {
          self.data.fullPath = fullPath;
          self.data.title = response.datas.title;
          self.data.content = response.datas.page;
          self.title.innerText = response.datas.title ?? "Aucun titre";
          self.content.innerHTML = response.datas.page ? self.parser.parse(response.datas.page) : "Aucun contenu";
          hljs.highlightAll();
          if (parseInt(EDITPAGE)){
            self.btnEdition.dataset.fullPath = fullPath;
          }
          self.changeUrl(fullPath);
        },
      });
    },
    init() {
      if (parseInt(EDITPAGE)){
        this.edition.init();
      }
      let page = STATE.page ?? "pour-commencer";
			this.show(page);
    },
  };
  return self;
})();
