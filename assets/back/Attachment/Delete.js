import { ElementHTML } from '@class/ElementHTML'
export class AttachmentDelete extends ElementHTML {
  constructor () {
    super()
    this.classList.add('attachment-delete')
    this.append(document.createElement('i'))
    this.dataset.bsToggle = 'modal'
    this.dataset.bsTarget = '#delete-attachment-modal'
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-deleteattachment')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.getAttribute('url')
    const token = element.currentTarget.getAttribute('token')
    const redirect = document.location.href
    const btnConfirm = document.querySelector('confirm-deleteattachment')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.setAttribute('url', url)
    btnConfirm.setAttribute('token', token)
    btnConfirm.setAttribute('redirect', redirect)
  }
}
