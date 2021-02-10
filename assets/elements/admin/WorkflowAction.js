export class WorkflowAction extends HTMLElement {
  connectedCallback () {
    this.classList.add('workflow-action')
    const iElement = document.createElement('i')
    this.append(iElement)
    this.append(document.createTextNode(this.dataset.name))
    this.dataset.toggle = 'modal'
    this.dataset.target = '#workflowModal'
    this.addEventListener('click', this.onClick)
    const btnConfirm = document.querySelector('confirm-workflow')
    if (btnConfirm !== null) {
      return
    }
    this.remove()
  }

  onClick (element) {
    element.preventDefault()
    const url = element.currentTarget.dataset.url
    const token = element.currentTarget.dataset.token
    const redirect = element.currentTarget.dataset.redirect
    const btnConfirm = document.querySelector('confirm-workflow')
    if (btnConfirm === null) {
      return
    }
    btnConfirm.dataset.url = url
    btnConfirm.dataset.token = token
    btnConfirm.dataset.redirect = redirect
  }
}
