import { ElementHTML } from '../../global/elements/ElementHTML'
export class ModalConfirmRestories extends ElementHTML {
  constructor () {
    super()
    this.classList.add('confirm-restories')
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
    const selectElement = document.querySelectorAll("select-element>input[type='checkbox']:checked")
    const entities = []
    selectElement.forEach(
      element => {
        entities.push(element.closest('select-element').dataset.id)
      }
    )
    urlSearchParams.append('entities', entities)
    const options = {
      method: 'POST',
      headers: {
        'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: urlSearchParams
    }
    this.fetchRedirect(url, options, redirect)
  }
}
