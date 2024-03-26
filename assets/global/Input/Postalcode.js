import { PostalCode } from '@class/PostalCode'
export class InputCodePostal extends PostalCode {
  connectedCallback () {
    this.setData()
    this.addEventListener('keydown', this.onKeydown)
    this.onKeydown()
  }
}
