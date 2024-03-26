export class InputValidity extends HTMLInputElement {
  connectedCallback () {
    this.timeout = null
    this.addEventListener('keydown', this.onKeydown)
    this.onKeydown()
  }

  traitement (element) {
    this.classList.remove('is-valid')
    this.classList.remove('is-invalid')
    if (element.value() !== '') {
      this.classList.add(this.checkValidity() ? 'is-valid' : 'is-invalid')
    }
  }

  onKeydown () {
    clearTimeout(this.timeout)
    this.timeout = setTimeout(() => { this.traitement(this) }, 500)
  }
}
