import TomSelect from 'tom-select'
import Sortable from 'sortablejs'
export class SelectParagraph extends HTMLSelectElement {
  connectedCallback () {
    const idElement = this.getAttribute('id')
    this.url = this.closest('fieldset').getAttribute('data-url')
    this.select = new TomSelect(`#${idElement}`)
    this.select.on('change', element => { this.onChange(element) })
    this.initShow()
  }

  changeParagraphInputPosition () {
    console.log(document.querySelectorAll('.paragraph_input').length)
    document.querySelectorAll('.paragraph_input').forEach(
      (element, position) => {
        element.setAttribute('value', position + 1)
      }
    )
    document.getElementById('paragraphs-list').classList.add('sortable-save')
  }

  sortableElement () {
    const $elementSortable = document.getElementById('paragraphs-list')
    if ($elementSortable !== undefined) {
      Sortable.create(
        $elementSortable,
        {
          onChange: (event) => {
            this.changeParagraphInputPosition()
          }
        }
      )
    }
  }

  initShow () {
    document.querySelectorAll('.paragraph_show').forEach(
      element => {
        element.addEventListener('click', () => {
          document.getElementById('iframe_paragaph').setAttribute('src', element.getAttribute('href'))
        })
      }
    )
    this.sortableElement()
  }

  async onChange (element) {
    if (element !== '') {
      const params = {
        data: element
      }

      const html = await fetch(this.url + '?' + new URLSearchParams(params)).then(response => response.text())
      document.getElementById('paragraph-list').innerHTML = html
      this.select.clear()
    }
  }
}
