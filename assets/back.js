import mitt from 'mitt'
window.emitter = mitt()
document.addEventListener(
  'DOMContentLoaded',
  () => {
    const srcContextBack = require.context('@back', true, /.(runtime|style).(.*?)$/)
    srcContextBack.keys().forEach(srcContextBack)
    const srcContextGlobal = require.context('@global', true, /.(runtime|style).(.*?)$/)
    srcContextGlobal.keys().forEach(srcContextGlobal)

    const templatesContext = require.context('@components', true, /.(runtime|style).(.*?)$/)
    templatesContext.keys().forEach(templatesContext)
  },
  {
    once: true
  }
)

function clickFormSave (event) {
  event.preventDefault()
  const formId = event.currentTarget.getAttribute('form')
  const formElement = document.querySelector("form[name='" + formId + "']")
  if (formElement !== null) {
    formElement.submit()
  }
}

function clickReset (event) {
  event.preventDefault()
  const element = event.currentTarget
  window.location.href = element.closest('form').getAttribute('action')
}

const saveForm = document.querySelector('#SaveForm')
if (saveForm !== null) {
  saveForm.addEventListener('click', clickFormSave)
}
const resetForm = document.querySelector('#reset')
if (resetForm !== null) {
  resetForm.addEventListener('click', clickReset)
}
