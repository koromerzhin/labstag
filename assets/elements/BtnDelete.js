export class BtnDelete extends HTMLElement{
  connectedCallback()
  {
    this.classList.add('btn-delete');
    this.innerHTML = '<i></i>';
    this.addEventListener('click', this.onclick);
  }

  onclick(element) {
    element.preventDefault();
    let CollectionRow = element.currentTarget.closest(".CollectionRow");
    CollectionRow.parentNode.removeChild(CollectionRow);
  }
}