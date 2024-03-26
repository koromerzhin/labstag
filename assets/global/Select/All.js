import { ElementHTML } from '@class/ElementHTML'
export class SelectAll extends ElementHTML {
  constructor () {
    super()
    this.classList.add('select-all')
    const title = this.dataset.title
    const checkbox = document.createElement('input')
    checkbox.setAttribute('type', 'checkbox')
    if (title !== undefined) {
      checkbox.setAttribute('title', title)
    }
    this.append(checkbox)
    checkbox.addEventListener('click', this.onClick)
  }

  onClick (event) {
    // element.preventDefault()
    const table = event.currentTarget.closest('table')
    const checked = event.currentTarget.checked
    const tbody = table.querySelector('tbody')
    const checkboxs = tbody.querySelectorAll("input[type='checkbox']")
    Array.from(checkboxs).forEach(
      element => {
        element.checked = checked
        element.closest('select-element').setAttribute('checked', checked ? 1 : 0)
      }
    )
  }
}
