import { GuardAll } from './GuardAll'
export class GuardAllWorkflow extends GuardAll {
  changeCheck () {
    this.checkChange('guard-setworkflow')
  }
}
