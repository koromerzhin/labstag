export class GuardElement extends HTMLTableElement {
  constructor () {
    super()
    const observer = new MutationObserver(mutations => { this.mutationObserver(mutations) })
    observer.observe(this, {
      attributes: true
    })
    this.fetchLaunch()
  }

  mutationObserver (mutations) {
    Array.from(mutations).forEach(mutation => { this.forEachMutationObserver(mutation) })
  }

  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'data-refresh') {
      this.fetchLaunch()
    }
  }

  async fetchLaunch () {
    try {
      const response = await fetch(this.dataset.url).then(response => response.json())
      this.fetchResponse(response)
    } catch (error) {
      console.error(error)
    }
  }

  responseFetch (guardSet, refgroup, response) {
    const guardsets = document.getElementsByTagName(guardSet)
    const refgroups = document.getElementsByTagName(refgroup)
    if (response.user !== undefined && response.group !== undefined) {
      if (response.user.length === 0) {
        Array.from(guardsets).forEach(
          guardset => {
            guardset.setAttribute('state', 0)
          }
        )
      } else {
        Array.from(guardsets).forEach(
          guardset => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.user.filter(element => (guardset.getAttribute('transition') === element.transition && guardset.getAttribute('entity') === element.entity)) : response.user.filter(element => (guardset.getAttribute('route') === element.route))
            guardset.setAttribute('state', (data.length === 1) ? 1 : 0)
          }
        )
      }
      if (response.group.length === 0) {
        Array.from(refgroups).forEach(
          guardset => {
            guardset.setAttribute('state', 0)
          }
        )
      } else {
        Array.from(refgroups).forEach(
          refgroup => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.group.filter(element => (refgroup.getAttribute('group') === element.groupe && refgroup.getAttribute('transition') === element.transition && refgroup.getAttribute('entity') === element.entity)) : response.group.filter(element => (refgroup.getAttribute('group') === element.groupe && refgroup.getAttribute('route') === element.route))
            refgroup.setAttribute('state', (data.length === 1) ? 1 : 0)
          }
        )
      }
    } else if (response.group !== undefined) {
      if (response.group.length === 0) {
        Array.from(guardsets).forEach(
          guardset => {
            guardset.setAttribute('state', 0)
          }
        )
        Array.from(refgroups).forEach(
          guardset => {
            guardset.setAttribute('state', 0)
          }
        )
      } else {
        Array.from(refgroups).forEach(
          refgroup => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.group.filter(element => (refgroup.getAttribute('group') === element.groupe && refgroup.getAttribute('transition') === element.transition && refgroup.getAttribute('entity') === element.entity)) : response.group.filter(element => (refgroup.getAttribute('group') === element.groupe && refgroup.getAttribute('route') === element.route))
            refgroup.setAttribute('state', (data.length === 1) ? 1 : 0)
          }
        )
        Array.from(guardsets).forEach(
          guardset => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.group.filter(element => (guardset.getAttribute('groupe') === element.groupe && guardset.getAttribute('transition') === element.transition && guardset.getAttribute('entity') === element.entity)) : response.group.filter(element => (guardset.getAttribute('groupe') === element.groupe && guardset.getAttribute('route') === element.route))
            guardset.setAttribute('state', (data.length === 1) ? 1 : 0)
          }
        )
      }
    }
  }
}
