import FormAttachManyField from "./fields/Form/SelectPlusField.vue"
import DetailAttachManyField from "./fields/Detail/SelectPlusField.vue"
import IndexAttachManyField from "./fields/Index/SelectPlusField.vue"

const selectPlusFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-select-plus-field', FormAttachManyField)
    app.component('detail-select-plus-field', DetailAttachManyField)
    app.component('index-select-plus-field', IndexAttachManyField)

}

export { selectPlusFieldSetup }