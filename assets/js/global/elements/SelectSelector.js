import TomSelect from 'tom-select'
export class SelectSelector extends HTMLSelectElement {
  connectedCallback () {
    const idElement = this.getAttribute('id')
    if (this.classList.contains('tomselected') === false) {
      this.select = new TomSelect(
        `#${idElement}`,
        {
          sortField: {
            field: 'text',
            direction: 'asc'
          }
        })
    }
  }
}
