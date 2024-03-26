import { ElementHTML } from '@class/ElementHTML'
export class GuardRefGroup extends ElementHTML {
  constructor () {
    super()
    this.setMutations()
  }

  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'state') {
      this.changeState()
    }
  }

  changeState () {
    this.setAttribute('class', (this.getAttribute('state') === '1') ? 'check-ok' : 'check-ko')
  }
}
