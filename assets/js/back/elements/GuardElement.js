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
    mutations.forEach(mutation => { this.forEachMutationObserver(mutation) })
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
    if (response.group !== undefined) {
      const refgroups = document.getElementsByTagName(refgroup)
      if (refgroups.length !== 0 && response.group.length !== 0) {
        refgroups.forEach(
          refgroup => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.group.filter(element => (refgroup.dataset.group === element.groupe && refgroup.dataset.transition === element.transition && refgroup.dataset.entity === element.entity)) : response.group.filter(element => (refgroup.dataset.group === element.groupe && refgroup.dataset.route === element.route))
            refgroup.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
      if (response.group.length !== 0) {
        guardsets.forEach(
          guardset => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.group.filter(element => (guardset.dataset.groupe === element.groupe && guardset.dataset.transition === element.transition && guardset.dataset.entity === element.entity)) : response.group.filter(element => (guardset.dataset.groupe === element.groupe && guardset.dataset.route === element.route))
            guardset.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
    }
    if (response.user !== undefined) {
      if (response.user.length !== 0) {
        guardsets.forEach(
          guardset => {
            const data = (guardSet === 'guard-setworkflow' && refgroup === 'guard-refgroupworkflow') ? response.user.filter(element => (guardset.dataset.transition === element.transition && guardset.dataset.entity === element.entity)) : response.user.filter(element => (guardset.dataset.route === element.route))
            guardset.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
    }
  }
}
