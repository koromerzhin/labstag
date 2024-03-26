import { GuardElement } from '@back/Guard/Element'
export class GuardRoute extends GuardElement {
  fetchResponse (response) {
    this.responseFetch('guard-setroute', 'guard-refgrouproute', response)
  }
}
