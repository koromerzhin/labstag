import { ElementHTML } from '@class/ElementHTML'
export class ModalConfirmDeleties extends ElementHTML {
  constructor () {
    super()
    this.classList.add('confirm-deleties')
    const title = this.getAttribute('title')
    const iElement = document.createElement('i')
    const spanElement = document.createElement('span')
    spanElement.append(document.createTextNode(title))
    this.append(iElement)
    this.append(spanElement)
    this.addEventListener('click', this.onClick)
  }

  onClick (event) {
    event.preventDefault()
    const element = event.currentTarget
    const url = element.getAttribute('url')
    const token = element.getAttribute('token')
    const redirect = element.getAttribute('redirect')
    const urlSearchParams = new URLSearchParams()
    urlSearchParams.append('_token', token)
    const selectElement = document.querySelectorAll("select-element>input[type='checkbox']:checked")
    const entities = []
    Array.from(selectElement).forEach(
      element => {
        entities.push(element.closest('select-element').getAttribute('id'))
      }
    )
    urlSearchParams.append('entities', entities)
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
