import { GuardSet } from './GuardSet'
export class GuardSetWorkflow extends GuardSet {
  changeState () {
    const allworkflowElement = this.closest('tr').querySelector('guard-allworkflow')
    if (allworkflowElement !== null) {
      allworkflowElement.setAttribute('check', 1)
    }
    super.changeState()
  }
}
