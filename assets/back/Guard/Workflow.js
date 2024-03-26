import { GuardElement } from '@back/Guard/Element'
export class GuardWorkflow extends GuardElement {
  fetchResponse (response) {
    this.responseFetch('guard-setworkflow', 'guard-refgroupworkflow', response)
  }
}
