import { ElementHTML } from '../../global/elements/ElementHTML'
export class AttachmentDelete extends ElementHTML {
  constructor () {
    super()
    this.classList.add('attachment-delete')
    this.append(document.createElement('i'))
    this.dataset.toggle = 'modal'
    this.dataset.target = '#delete-attachment-modal'

    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-deleteattachment')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.dataset.url
    const token = element.currentTarget.dataset.token
    const redirect = document.location.href
    const btnConfirm = document.querySelector('confirm-deleteattachment')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.dataset.url = url
    btnConfirm.dataset.token = token
    btnConfirm.dataset.redirect = redirect
  }
}
