export class GuardRoute extends HTMLTableElement {
  constructor () {
    super()
    const observer = new MutationObserver(this.mutationObserver.bind(this))
    observer.observe(this, {
      attributes: true
    })
    this.fetchLaunch()
  }

  mutationObserver (mutations) {
    mutations.forEach(this.forEachMutationObserver.bind(this))
  }

  forEachMutationObserver (mutation) {
    if (mutation.type === 'attributes' && mutation.attributeName === 'data-refresh') {
      this.fetchLaunch()
    }
  }

  fetchLaunch () {
    fetch(this.dataset.url)
      .then(response => response.json())
      .then(this.fetchResponse.bind(this))
      .catch(this.fetchCatch)
  }

  fetchCatch (err) {
    console.log(err)
  }

  fetchResponse (response) {
    const guardsets = document.getElementsByTagName('guard-setroute')
    if (response.group !== undefined) {
      const refgroups = document.getElementsByTagName('guard-refgrouproute')
      if (refgroups.length !== 0 && response.group.length !== 0) {
        refgroups.forEach(
          refgroup => {
            const data = response.group.filter(element => (refgroup.dataset.group === element.groupe && refgroup.dataset.route === element.route))
            refgroup.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
      if (response.group.length !== 0) {
        guardsets.forEach(
          guardset => {
            const data = response.group.filter(element => (guardset.dataset.groupe === element.groupe && guardset.dataset.route === element.route))
            guardset.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
    }
    if (response.user !== undefined) {
      if (response.user.length !== 0) {
        guardsets.forEach(
          guardset => {
            const data = response.user.filter(element => (guardset.dataset.route === element.route))
            guardset.dataset.state = (data.length === 1) ? 1 : 0
          }
        )
      }
    }
  }
}