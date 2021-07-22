import { ElementHTML } from '../../global/elements/ElementHTML'
export class ModalConfirmDeleteAttachment extends ElementHTML {
  constructor () {
    super()
    this.classList.add('confirm-deleteattachment')
    this.addEventListener('click', this.onClick)
  }

  onClick (event) {
    event.preventDefault()
    const element = event.currentTarget
    const url = element.dataset.url
    const token = element.dataset.token
    const redirect = element.dataset.redirect
    const urlSearchParams = new URLSearchParams()
    urlSearchParams.append('_token', token)
    const options = {
      method: 'DELETE',
      headers: {
        'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: urlSearchParams
    }
    this.fetchRedirect(url, options, redirect)
  }
}
