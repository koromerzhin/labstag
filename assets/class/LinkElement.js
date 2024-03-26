import { ElementHTML } from '@class/ElementHTML'
export class LinkElement extends ElementHTML {
  init (className) {
    const title = this.getAttribute('title')
    const href = this.getAttribute('href')
    this.aElement = document.createElement('a')
    this.iElement = document.createElement('i')
    this.spanElement = document.createElement('span')
    this.aElement.classList.add(className)
    this.aElement.setAttribute('href', href)
    this.iElement.setAttribute('title', title)
    this.spanElement.append(document.createTextNode(title))

    this.aElement.append(this.iElement)
    this.appendChild(this.aElement)
  }
}
