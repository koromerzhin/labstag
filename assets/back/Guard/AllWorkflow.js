import { GuardAll } from '@back/Guard/All'
export class GuardAllWorkflow extends GuardAll {
  changeCheck () {
    this.checkChange('guard-setworkflow')
  }
}
