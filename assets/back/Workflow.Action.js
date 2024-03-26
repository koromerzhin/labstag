import { ElementHTML } from '@class/ElementHTML'
export class WorkflowAction extends ElementHTML {
  connectedCallback () {
    this.classList.add('workflow-action')
    const iElement = document.createElement('i')
    this.innerHTML = '&nbsp;'
    this.prepend(iElement)
    this.append(document.createTextNode(this.getAttribute('name')))
    this.dataset.bsToggle = 'modal'
    this.dataset.bsTarget = '#workflow-modal'
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-workflow')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.getAttribute('url')
    const token = element.currentTarget.getAttribute('token')
    const redirect = element.currentTarget.getAttribute('redirect')
    const btnConfirm = document.querySelector('confirm-workflow')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.setAttribute('url', url)
    btnConfirm.setAttribute('token', token)
    btnConfirm.setAttribute('redirect', redirect)
  }
}
