import { LinkBtnAdmin } from '@back/Link/BtnAdmin'

export class LinkBtnAdminRestore extends LinkBtnAdmin {
  constructor () {
    super()
    this.dataset.bsToggle = 'modal'
    this.dataset.bsTarget = '#restore-modal'
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-restore')
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
    const btnConfirm = document.querySelector('confirm-restore')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.setAttribute('url', url)
    btnConfirm.setAttribute('token', token)
    btnConfirm.setAttribute('redirect', redirect)
  }
}
