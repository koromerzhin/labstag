import { ElementHTML } from '@class/ElementHTML'
export class ModalConfirmClose extends ElementHTML {
  constructor () {
    super()
    this.classList.add('confirm-delete')
    const title = this.getAttribute('title')
    this.setAttribute('class', 'btn-cancel')
    this.dataset.bsDismiss = 'modal'
    const iElement = document.createElement('i')
    const spanElement = document.createElement('span')
    spanElement.append(document.createTextNode(title))
    this.append(iElement)
    this.append(spanElement)
  }
}
