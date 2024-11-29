import FormPermissionsField from "./fields/Form/PermissionsField.vue"
import DetailPermissionsField from "./fields/Detail/PermissionsField.vue"

const userModuleSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-permissions-field', FormPermissionsField)
    app.component('detail-permissions-field', DetailPermissionsField)

}

export { userModuleSetup }