import { GuardElement } from './GuardElement'
export class GuardRoute extends GuardElement {
  fetchResponse (response) {
    this.responseFetch('guard-setroute', 'guard-refgrouproute', response)
  }
}
