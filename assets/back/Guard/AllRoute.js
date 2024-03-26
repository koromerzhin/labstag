import { GuardAll } from '@back/Guard/All'
export class GuardAllRoute extends GuardAll {
  changeCheck () {
    this.checkChange('guard-setroute')
  }
}
