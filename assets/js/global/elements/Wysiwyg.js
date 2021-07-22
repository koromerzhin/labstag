import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
export class Wysiwyg extends HTMLTextAreaElement {
  async connectedCallback () {
    try {
      const editor = await ClassicEditor.create(this)
      window.editor = editor
    } catch (error) {
      console.error('There was a problem initializing the editor.', error)
    }
  }
}
