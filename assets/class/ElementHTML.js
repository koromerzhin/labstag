export class ElementHTML extends HTMLElement {
  setMutations () {
    this.changeState()
    const observer = new MutationObserver(mutations => { this.mutationObserver(mutations) })
    observer.observe(this, {
      attributes: true
    })
  }

  mutationObserver (mutations) {
    Array.from(mutations).forEach(mutation => { this.forEachMutationObserver(mutation) })
  }

  async fetchRedirect (url, options, redirect) {
    try {
      await fetch(url, options)
      window.location.href = redirect
    } catch (error) {
      console.error(error)
    }
  }
}
