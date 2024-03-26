import TomSelect from 'tom-select'
export class SelectCountry extends HTMLSelectElement {
  connectedCallback () {
    const idElement = this.getAttribute('id')
    if (this.classList.contains('tomselected') === false) {
      this.select = new TomSelect(
        `#${idElement}`, {
          sortField: {
            field: 'text',
            direction: 'asc'
          },
          render: {
            option: function (data, escape) {
              return '<div><span class="fi fi-' + data.value.toLowerCase() + '"></span>' + escape(data.text) + '</div>'
            },
            item: function (data, escape) {
              return '<div><span class="fi fi-' + data.value.toLowerCase() + '"></span>' + escape(data.text) + '</div>'
            }
          }
        })
    }
  }
}
