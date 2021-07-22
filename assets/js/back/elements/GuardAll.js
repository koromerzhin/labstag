import { GuardSet } from './GuardSet'
export class GuardAll extends GuardSet {
  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes') {
      if (mutation.attributeName === 'data-state') {
        this.changeState()
      } else if (mutation.attributeName === 'data-check') {
        this.changeCheck()
      }
    }
  }
}
