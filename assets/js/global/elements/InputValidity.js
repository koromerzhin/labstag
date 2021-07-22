export class InputValidity extends HTMLInputElement {
  connectedCallback () {
    this.timeout = null
    this.addEventListener('keydown', this.onKeydown)
    this.onKeydown()
  }

  traitement () {
    this.classList.remove('is-valid')
    this.classList.remove('is-invalid')
    this.classList.add(this.checkValidity() ? 'is-valid' : 'is-invalid')
  }

  onKeydown () {
    clearTimeout(this.timeout)
    this.timeout = setTimeout(() => { this.traitement() }, 500)
  }
}
