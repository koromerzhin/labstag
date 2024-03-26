import { BtnAddCollection } from '@global/Btn/AddCollection'
import { BtnDelete } from '@global/Btn/Delete'
import { BtnToggleFieldset } from '@global/Btn/Toggglefieldset'

import { InputCity } from '@global/Input/City'
import { InputCodePostal } from '@global/Input/Postalcode'
import { InputEmail } from '@global/Input/Email'
import { InputGps } from '@global/Input/Gps'
import { InputPhone } from '@global/Input/Phone'
import { InputUrl } from '@global/Input/Url'

import { SelectAll } from '@global/Select/All'
import { SelectCountry } from '@global/Select/Country'
import { SelectElement } from '@global/Select/Element'
import { SelectSelector } from '@global/Select/Selector'

window.customElements.define('btn-addcollection', BtnAddCollection)
window.customElements.define('btn-delete', BtnDelete)
window.customElements.define('btn-togglefieldset', BtnToggleFieldset)

window.customElements.define('input-city', InputCity, { extends: 'input' })
window.customElements.define('input-codepostal', InputCodePostal, { extends: 'input' })
window.customElements.define('input-email', InputEmail, { extends: 'input' })
window.customElements.define('input-gps', InputGps, { extends: 'input' })
window.customElements.define('input-phone', InputPhone, { extends: 'input' })
window.customElements.define('input-url', InputUrl, { extends: 'input' })

window.customElements.define('select-all', SelectAll)
window.customElements.define('select-country', SelectCountry, { extends: 'select' })
window.customElements.define('select-element', SelectElement)
window.customElements.define('select-selector', SelectSelector, { extends: 'select' })
