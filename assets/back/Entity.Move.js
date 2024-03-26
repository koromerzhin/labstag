import Sortable from 'sortablejs'
export class EntityMove extends HTMLUListElement {
  constructor () {
    super()
    this.classList.add('entity-move')
    Sortable.create(
      this,
      {
        animation: 150,
        ghostClass: 'blue-background-class'
      }
    )
  }
}
