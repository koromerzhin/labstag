import { ElementHTML } from '../../global/elements/ElementHTML'
export class LinkDelete extends ElementHTML {
  constructor () {
    super()
    const title = this.dataset.title
    this.classList.add('link-delete')
    this.dataset.bsToggle = 'modal'
    this.dataset.bsTarget = '#delete-modal'
    const iElement = document.createElement('i')
    iElement.setAttribute('title', title)
    const spanElement = document.createElement('span')
    spanElement.append(document.createTextNode(title))
    this.append(iElement)
    this.append(spanElement)
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-delete')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.dataset.url
    const token = element.currentTarget.dataset.token
    const redirect = element.currentTarget.dataset.redirect
    const btnConfirm = document.querySelector('confirm-delete')
    if (btnConfirm === null) {
      return
    }

    btnConfirm.dataset.url = url
    btnConfirm.dataset.token = token
    btnConfirm.dataset.redirect = redirect
  }
}
