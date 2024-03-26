import { ElementHTML } from '@class/ElementHTML'
export class SelectElement extends ElementHTML {
  constructor () {
    super()
    const title = this.getAttribute('title')
    this.setAttribute('checked', 0)
    const checkbox = document.createElement('input')
    checkbox.setAttribute('type', 'checkbox')
    if (title !== undefined) {
      checkbox.setAttribute('title', title)
    }
    this.append(checkbox)
    checkbox.addEventListener('change', this.onChange)
    const observer = new MutationObserver(mutations => { this.mutationObserver(mutations) })
    observer.observe(this, {
      attributes: true
    })
  }

  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'checked') {
      this.changeChecked()
    }
  }

  changeChecked () {
    const selectElement = document.querySelectorAll("select-element>input[type='checkbox']:checked")
    const linkBtnAdminEmptiesElement = document.querySelector('link-btnadminempties')
    if (linkBtnAdminEmptiesElement !== null) {
      linkBtnAdminEmptiesElement.style.display = (selectElement.length !== 0) ? 'block' : 'none'
    }
    const linkBtnAdminDeletiesElement = document.querySelector('link-btnadmindeleties')

    if (linkBtnAdminDeletiesElement !== null) {
      linkBtnAdminDeletiesElement.style.display = (selectElement.length !== 0) ? 'block' : 'none'
    }
    const linkBtnAdminRestoriesElement = document.querySelector('link-btnadminrestories')
    if (linkBtnAdminRestoriesElement !== null) {
      linkBtnAdminRestoriesElement.style.display = (selectElement.length !== 0) ? 'block' : 'none'
    }
  }

  onChange (event) {
    const target = event.currentTarget
    const checked = target.checked
    const selectElement = target.closest('select-element')
    selectElement.setAttribute('checked', checked ? 1 : 0)
  }
}
