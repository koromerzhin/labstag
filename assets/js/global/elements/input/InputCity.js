import { PostalCode } from '../PostalCode'
export class InputCity extends PostalCode {
  connectedCallback () {
    this.setAttribute('autocomplete', 'off')
    this.setData()
    this.addEventListener('keydown', this.onKeydown)
    this.onKeydown()
  }
}
