import { ElementHTML } from '@class/ElementHTML'
export class BtnAddCollection extends ElementHTML {
  connectedCallback () {
    this.classList.add('btn-addcollection')
    const title = this.getAttribute('title')
    const iElement = document.createElement('i')
    const spanElement = document.createElement('span')
    spanElement.appendChild(document.createTextNode(title))
    this.append(iElement)
    this.append(spanElement)
    this.addEventListener('click', this.onClick)
    this.disconnectedCallback(this)
  }

  disconnectedCallback (element) {
    const fieldcollection = element.closest('.fieldcollection')
    const limit = fieldcollection.getAttribute('data-limit')
    const counter = fieldcollection.querySelectorAll('.collection-row').length
    element.style.display = (limit === null || counter < limit) ? 'block' : 'none'
  }

  onClick (element) {
    element.preventDefault()
    const fieldcollection = element.currentTarget.closest('.fieldcollection')
    const limit = fieldcollection.getAttribute('data-limit')
    const counter = fieldcollection.querySelectorAll('.collection-row').length
    const html = fieldcollection.dataset.prototype.replace(/__name__/g, counter)
    const fieldRow = fieldcollection.querySelector('.field-row')
    if (fieldRow !== null && (limit === null || counter <= limit)) {
      fieldRow.innerHTML += html
    }
    this.disconnectedCallback(this)
  }
}
