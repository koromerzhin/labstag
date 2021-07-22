import { LinkBtnAdmin } from './LinkBtnAdmin'

export class LinkBtnAdminRestore extends LinkBtnAdmin {
  constructor () {
    super()
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-restore')
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
    const btnConfirm = document.querySelector('confirm-restore')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.dataset.url = url
    btnConfirm.dataset.token = token
    btnConfirm.dataset.redirect = redirect
  }
}
