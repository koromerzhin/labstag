import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
export class Wysiwyg extends HTMLTextAreaElement {
  connectedCallback() {
    ClassicEditor.create(this)
        .then((editor) => { window.editor = editor; })
        .catch((error) => {
          console.error("There was a problem initializing the editor.", error);
        });
  }
}