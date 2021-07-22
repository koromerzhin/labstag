import { ElementHTML } from '../../global/elements/ElementHTML'
export class GuardSet extends ElementHTML {
  constructor () {
    super()
    this.classList.add('guard-set')
    const uuid = this.uuidv4()
    const inputElement = document.createElement('input')
    inputElement.setAttribute('type', 'checkbox')
    inputElement.setAttribute('id', uuid)
    const labelElement = document.createElement('label')
    labelElement.setAttribute('for', uuid)
    labelElement.innerHTML = '&nbsp;'
    this.append(inputElement)
    this.append(labelElement)
    const checkboxs = this.getElementsByTagName('input')
    this.checkbox = checkboxs[0]
    this.token = this.dataset.token
    this.checkbox.addEventListener('change', element => { this.onChange(element) })
    this.setMutations()
  }

  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'data-state') {
      this.changeState()
    }
  }

  changeState () {
    const input = this.querySelector('input')
    input.checked = (this.dataset.state === '1')
  }

  async onChange (element) {
    element.preventDefault()
    const urlSearchParams = new URLSearchParams()
    urlSearchParams.append('_token', this.dataset.token)
    urlSearchParams.append('state', (this.checkbox.checked === false) ? 0 : 1)
    const options = {
      method: 'POST',
      headers: {
        'Content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
      },
      body: urlSearchParams
    }
    try {
      await fetch(this.dataset.url, options)
      this.refresh()
    } catch (error) {
      console.error(error)
    }
  }

  refresh () {
    this.closest('table').dataset.refresh = 1
  }

  uuidv4 () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      const r = Math.random() * 16 | 0; const v = c === 'x' ? r : (r & 0x3 | 0x8)
      return v.toString(16)
    })
  }

  checkChange (name) {
    if (this.dataset.check === '1') {
      const setRouteElement = this.closest('tr').querySelectorAll('guard-setworkflow')
      let state = 0
      setRouteElement.forEach(
        element => {
          state += parseInt(element.dataset.state)
        }
      )
      if (state === setRouteElement.length) {
        this.dataset.state = 1
      } else {
        this.dataset.state = 0
      }
      this.dataset.check = 0
    }
  }
}
