import TomSelect from 'tom-select'
export class SelectSelector extends HTMLSelectElement {
  connectedCallback () {
    const idElement = this.getAttribute('id')
    this.url = this.dataset.url
    this.noresult = this.dataset.noresult
    this.addmessage = this.dataset.addmessage
    this.add = (this.dataset.add === '1')
    if (this.classList.contains('tomselected') === false) {
      if (undefined !== this.url) {
        this.select = new TomSelect(
          `#${idElement}`,
          {
            plugins: ['remove_button'],
            create: this.add,
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
              option_create: (data, escape) => {
                return this.optionCreate(data, escape)
              },
              option: (item, escape) => {
                return this.optionItem(item, escape)
              },
              item: (item, escape) => {
                return this.optionItem(item, escape)
              },
              no_results: (data, escape) => {
                return this.noResults(data, escape)
              }
            }
          }
        )
      } else {
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

  optionCreate (data, escape) {
    return `<div class="create">${this.addmessage.replace('%result%', `<b>${escape(data.input)}</b>`)}</div>`
  }

  noResults (data, escape) {
    return `<div class="no-results">${this.noresult.replace('%result%', `<b>${escape(data.input)}</b>`)}</div>`
  }

  optionItem (item, escape) {
    return `<div>${escape(item.text)}</div>`
  }
}
