import { ElementHTML } from '@class/ElementHTML'
export class BtnDelete extends ElementHTML {
  connectedCallback () {
    if (this.innerHTML.trim() === '') {
      this.append(document.createElement('i'))
    }
    this.classList.add('btn-delete')
    this.addEventListener('click', this.onClick)
  }

  onClick (element) {
    element.preventDefault()
    const collectionRow = element.currentTarget.closest('.collection-row')
    collectionRow.parentNode.removeChild(collectionRow)
  }
}
