import { ElementHTML } from '@class/ElementHTML'
import { AttachmentDelete } from '@back/Attachment/Delete'
export class AttachmentImg extends ElementHTML {
  constructor () {
    super()
    this.classList.add('attachment-img')
    const element = document.createElement('fieldset')
    const pictureElement = document.createElement('picture')
    const imgElement = document.createElement('img')
    pictureElement.append(imgElement)
    element.append(pictureElement)
    if (this.getAttribute('url') !== '#') {
      const brElement = document.createElement('br')
      element.append(brElement)
      const divElement = document.createElement('div')
      divElement.classList.add('text-center')
      const attachmentDeleteElement = new AttachmentDelete()
      divElement.append(attachmentDeleteElement)
      element.append(divElement)
    }

    this.append(element)

    const imgs = this.getElementsByTagName('img')
    const img = imgs[imgs.length - 1]

    img.setAttribute('src', this.getAttribute('src'))
    if (this.getAttribute('url') !== '#') {
      const btnDeletes = this.getElementsByTagName('attachment-delete')
      const btnDelete = btnDeletes[btnDeletes.length - 1]
      btnDelete.setAttribute('token', this.getAttribute('token'))
      btnDelete.setAttribute('url', this.getAttribute('url'))
    }
  }
}
