export class PostalCode extends HTMLInputElement {
  fetchResponse (response) {
    if (response.length === 0) {
      return
    }
    const data = response[0]
    if (response.length === 1) {
      this.codepostal.value = data.postalCode
      this.city.value = data.placeName
      this.gps.value = data.latitude + ',' + data.longitude
    }
  }

  async ajax () {
    const params = {
    }
    if (this.country.value !== '') {
      params.country = this.country.value
    }
    if (this.city.value !== '') {
      params.placeName = this.city.value
    }
    if (this.codepostal.value !== '') {
      params.postalCode = this.codepostal.value
    }

    try {
      const response = await fetch(this.url + '?' + new URLSearchParams(params)).then(response => response.json())
      this.fetchResponse(response)
    } catch (error) {
      console.error(error)
    }
  }

  onKeydown (element) {
    clearTimeout(this.timeout)
    this.timeout = setTimeout(() => { this.ajax() }, 500)
  }

  setData () {
    this.row = this.closest('.row')
    this.inputs = this.row.getElementsByTagName('input')
    const selects = this.row.getElementsByTagName('select')
    this.country = null
    this.codepostal = null
    this.city = null
    this.gps = null
    Array.from(selects).forEach(
      element => {
        const isInput = element.getAttribute('is')
        if (isInput === 'select-country') {
          this.country = element
        }
      }
    )
    Array.from(this.inputs).forEach(
      element => {
        const isInput = element.getAttribute('is')
        if (isInput === 'input-codepostal') {
          this.codepostal = element
        } else if (isInput === 'input-city') {
          this.city = element
        } else if (isInput === 'input-gps') {
          this.gps = element
        }
      }
    )

    this.url = this.row.dataset.url
    this.timeout = null
  }
}
