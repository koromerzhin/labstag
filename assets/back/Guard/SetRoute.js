import { GuardSet } from '@back/Guard/Set'
export class GuardSetRoute extends GuardSet {
  changeState () {
    const allrouteElement = this.closest('tr').querySelector('guard-allroute')
    if (allrouteElement !== null) {
      allrouteElement.setAttribute('check', 1)
    }
    super.changeState()
  }
}
