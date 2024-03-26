import Wysiwyg from '@/wysiwyg'
function loadGlobal () {
  document.querySelectorAll('.wysiwyg').forEach(element => {
    // const idinput = element.getAttribute('id')

    // if (global.CKEDITOR.instances[idinput]) {
    //   global.CKEDITOR.instances[idinput].destroy(true)
    //   delete global.CKEDITOR.instances[idinput]
    // }

    // global.CKEDITOR.replace(idinput)

    Wysiwyg.create(
      element,
      {
        language: 'fr'
      }
    ).then(editor => {
      console.log('Editor was initialized', editor)
    })
      .catch(err => {
        console.error(err)
      })
  })
}
window.addEventListener('load', () => loadGlobal())
