import { ElementHTML } from './ElementHTML'
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
  }

  onClick (element) {
    element.preventDefault()
    const fieldcollection = element.currentTarget.closest('.fieldcollection')
    const counter = fieldcollection.querySelectorAll('.CollectionRow').length
    const html = fieldcollection.dataset.prototype.replace(/__name__/g, counter)
    const FieldRow = fieldcollection.querySelector('.FieldRow')
    if (FieldRow !== null) {
      FieldRow.innerHTML += html
    }
  }
}
