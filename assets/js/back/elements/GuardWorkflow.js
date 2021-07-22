import { GuardElement } from './GuardElement'
export class GuardWorkflow extends GuardElement {
  fetchResponse (response) {
    this.responseFetch('guard-setworkflow', 'guard-refgroupworkflow', response)
  }
}
