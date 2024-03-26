import { AttachmentDelete } from '@back/Attachment/Delete'
import { AttachmentImg } from '@back/Attachment/Img'

import { SelectParagraph } from '@back/Select.Paragraph'

import { ModalConfirmClose } from '@back/Modal/ConfirmClose'
import { ModalConfirmDelete } from '@back/Modal/ConfirmDelete'
import { ModalConfirmDeleteAttachment } from '@back/Modal/ConfirmDeleteAttachment'
import { ModalConfirmDeleties } from '@back/Modal/ConfirmDeleties'
import { ModalConfirmDestroy } from '@back/Modal/ConfirmDestroy'
import { ModalConfirmEmpties } from '@back/Modal/ConfirmEmpties'
import { ModalConfirmEmpty } from '@back/Modal/ConfirmEmpty'
import { ModalConfirmEmptyAll } from '@back/Modal/ConfirmEmptyAll'
import { ModalConfirmRestore } from '@back/Modal/ConfirmRestore'
import { ModalConfirmRestories } from '@back/Modal/ConfirmRestories'
import { ModalConfirmWorkflow } from '@back/Modal/ConfirmWorkflow'

import { GuardAllRoute } from '@back/Guard/AllRoute'
import { GuardAllWorkflow } from '@back/Guard/AllWorkflow'
import { GuardChangeRoute } from '@back/Guard/ChangeRoute'
import { GuardChangeWorkflow } from '@back/Guard/ChangeWorkflow'
import { GuardRefGroupRoute } from '@back/Guard/RefGroupRoute'
import { GuardRefGroupWorkflow } from '@back/Guard/RefGroupWorkflow'
import { GuardRoute } from '@back/Guard/Route'
import { GuardSetRoute } from '@back/Guard/SetRoute'
import { GuardSetWorkflow } from '@back/Guard/SetWorkflow'
import { GuardWorkflow } from '@back/Guard/Workflow'

import { LinkAdd } from '@back/Link/Add'
import { LinkBtnAdmin } from '@back/Link/BtnAdmin'
import { LinkBtnAdminDelete } from '@back/Link/BtnAdminDelete'
import { LinkBtnAdminDeleties } from '@back/Link/BtnAdminDeleties'
import { LinkBtnAdminDestroy } from '@back/Link/BtnAdminDestroy'
import { LinkBtnAdminEmpties } from '@back/Link/BtnAdminEmpties'
import { LinkBtnAdminEmpty } from '@back/Link/BtnAdminEmpty'
import { LinkBtnAdminEmptyAll } from '@back/Link/BtnAdminEmptyAll'
import { LinkBtnAdminMove } from '@back/Link/BtnAdminMove'
import { LinkBtnAdminNewBlock } from '@back/Link/BtnAdminNewBlock'
import { LinkBtnAdminRestore } from '@back/Link/BtnAdminRestore'
import { LinkBtnAdminRestories } from '@back/Link/BtnAdminRestories'
import { LinkDelete } from '@back/Link/Delete'
import { LinkDestroy } from '@back/Link/Destroy'
import { LinkEdit } from '@back/Link/Edit'
import { LinkEmpty } from '@back/Link/Empty'
import { LinkGuard } from '@back/Link/Guard'
import { LinkMove } from '@back/Link/Move'
import { LinkRestore } from '@back/Link/Restore'
import { LinkShow } from '@back/Link/Show'
import { LinkTrash } from '@back/Link/Trash'

import { EntityMove } from '@back/Entity.Move'
import { WorkflowAction } from '@back/Workflow.Action'

import { TableDatatable } from '@back/Table.Datatable'

window.customElements.define('attachment-delete', AttachmentDelete)
window.customElements.define('attachment-img', AttachmentImg)

window.customElements.define('select-paragraph', SelectParagraph, { extends: 'select' })

window.customElements.define('confirm-close', ModalConfirmClose)
window.customElements.define('confirm-delete', ModalConfirmDelete)
window.customElements.define('confirm-deleteattachment', ModalConfirmDeleteAttachment)
window.customElements.define('confirm-deleties', ModalConfirmDeleties)
window.customElements.define('confirm-destroy', ModalConfirmDestroy)
window.customElements.define('confirm-empties', ModalConfirmEmpties)
window.customElements.define('confirm-empty', ModalConfirmEmpty)
window.customElements.define('confirm-emptyall', ModalConfirmEmptyAll)
window.customElements.define('confirm-restore', ModalConfirmRestore)
window.customElements.define('confirm-restories', ModalConfirmRestories)
window.customElements.define('confirm-workflow', ModalConfirmWorkflow)

window.customElements.define('guard-allroute', GuardAllRoute)
window.customElements.define('guard-allworkflow', GuardAllWorkflow)
window.customElements.define('guard-changeroute', GuardChangeRoute)
window.customElements.define('guard-changeworkflow', GuardChangeWorkflow)
window.customElements.define('guard-refgrouproute', GuardRefGroupRoute)
window.customElements.define('guard-refgroupworkflow', GuardRefGroupWorkflow)
window.customElements.define('guard-route', GuardRoute, { extends: 'table' })
window.customElements.define('guard-setroute', GuardSetRoute)
window.customElements.define('guard-setworkflow', GuardSetWorkflow)
window.customElements.define('guard-workflow', GuardWorkflow, { extends: 'table' })

window.customElements.define('link-add', LinkAdd)
window.customElements.define('link-btnadmin', LinkBtnAdmin)
window.customElements.define('link-btnadmindelete', LinkBtnAdminDelete)
window.customElements.define('link-btnadmindeleties', LinkBtnAdminDeleties)
window.customElements.define('link-btnadmindestroy', LinkBtnAdminDestroy)
window.customElements.define('link-btnadminempties', LinkBtnAdminEmpties)
window.customElements.define('link-btnadminempty', LinkBtnAdminEmpty)
window.customElements.define('link-btnadminemptyall', LinkBtnAdminEmptyAll)
window.customElements.define('link-btnadminmove', LinkBtnAdminMove)
window.customElements.define('link-btnadminnewblock', LinkBtnAdminNewBlock)
window.customElements.define('link-btnadminrestore', LinkBtnAdminRestore)
window.customElements.define('link-btnadminrestories', LinkBtnAdminRestories)
window.customElements.define('link-delete', LinkDelete)
window.customElements.define('link-destroy', LinkDestroy)
window.customElements.define('link-edit', LinkEdit)
window.customElements.define('link-empty', LinkEmpty)
window.customElements.define('link-guard', LinkGuard)
window.customElements.define('link-move', LinkMove)
window.customElements.define('link-restore', LinkRestore)
window.customElements.define('link-show', LinkShow)
window.customElements.define('link-trash', LinkTrash)

window.customElements.define('workflow-action', WorkflowAction)
window.customElements.define('entity-move', EntityMove, { extends: 'ul' })

customElements.define('table-datatable', TableDatatable, { extends: 'table' })
