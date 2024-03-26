import { GuardSet } from '@back/Guard/Set'
export class GuardAll extends GuardSet {
  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes') {
      if (mutation.attributeName === 'state') {
        this.changeState()
      } else if (mutation.attributeName === 'check') {
        this.changeCheck()
      }
    }
  }
}
