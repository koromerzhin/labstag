import { ElementHTML } from '../../global/elements/ElementHTML'
export class AttachmentImg extends ElementHTML {
  constructor () {
    super()
    const title = this.dataset.name
    this.classList.add('attachment-img')
    const fieldsetElement = document.createElement('fieldset')
    const legendElement = document.createElement('legend')
    legendElement.append(document.createTextNode(title))
    fieldsetElement.append(legendElement)
    const imgElement = document.createElement('img')
    fieldsetElement.append(imgElement)
    if (this.dataset.url !== '#') {
      const brElement = document.createElement('br')
      fieldsetElement.append(brElement)
      const attachmentDeleteElement = document.createElement('attachment-delete')
      fieldsetElement.append(attachmentDeleteElement)
    }

    this.append(fieldsetElement)

    const imgs = this.getElementsByTagName('img')
    const img = imgs[imgs.length - 1]

    img.setAttribute('src', this.dataset.src)
    if (this.dataset.url !== '#') {
      const btnDeletes = this.getElementsByTagName('attachment-delete')
      const btnDelete = btnDeletes[btnDeletes.length - 1]
      btnDelete.dataset.token = this.dataset.token
      btnDelete.dataset.url = this.dataset.url
    }
  }
}
