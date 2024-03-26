import mitt from 'mitt'
window.emitter = mitt()
document.addEventListener(
  'DOMContentLoaded',
  () => {
    const srcContextFront = require.context('@front', true, /.(runtime|style).(.*?)$/)
    srcContextFront.keys().forEach(srcContextFront)
    const srcContextGlobal = require.context('@global', true, /.(runtime|style).(.*?)$/)
    srcContextGlobal.keys().forEach(srcContextGlobal)

    const templatesContext = require.context('@components', true, /.(runtime|style).(.*?)$/)
    templatesContext.keys().forEach(templatesContext)
  },
  {
    once: true
  }
)
