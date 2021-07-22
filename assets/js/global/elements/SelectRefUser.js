import TomSelect from 'tom-select'
export class SelectRefUser extends HTMLSelectElement {
  connectedCallback () {
    const idElement = this.getAttribute('id')
    this.url = this.dataset.url
    this.select = new TomSelect(
      `#${idElement}`,
      {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        load: async (query, callback) => {
          const params = {
            name: query
          }
          const response = await fetch(this.url + '?' + new URLSearchParams(params)).then(response => response.json())
          callback(response.results)
        },
        render: {
          option: (item, escape) => {
            return this.optionItem(item, escape)
          },
          item: (item, escape) => {
            return this.optionItem(item, escape)
          }
        }
      }
    )
  }

  optionItem (item, escape) {
    return `<span>${escape(item.text)}</span>`
  }
}
