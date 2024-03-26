import { ElementHTML } from '@class/ElementHTML'
export class LinkDestroy extends ElementHTML {
  constructor () {
    super()
    const title = this.getAttribute('title')
    this.classList.add('link-destroy')
    this.dataset.bsToggle = 'modal'
    this.dataset.bsTarget = '#destroy-modal'
    const iElement = document.createElement('i')
    iElement.setAttribute('title', title)
    const spanElement = document.createElement('span')
    spanElement.append(document.createTextNode(title))
    this.append(iElement)
    this.append(spanElement)
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-destroy')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.getAttribute('url')
    const token = element.currentTarget.getAttribute('token')
    const redirect = element.currentTarget.getAttribute('redirect')
    const btnConfirm = document.querySelector('confirm-destroy')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.setAttribute('url', url)
    btnConfirm.setAttribute('token', token)
    btnConfirm.setAttribute('redirect', redirect)
  }
}
