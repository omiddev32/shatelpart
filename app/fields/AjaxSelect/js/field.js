import FormAjaxSelectField from "./fields/Form/AjaxSelectField.vue"
import DetailAjaxSelectField from "./fields/Detail/AjaxSelectField.vue"
import IndexAjaxSelectField from "./fields/Index/AjaxSelectField.vue"

const ajaxSelectFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-ajax-select-field', FormAjaxSelectField)
    app.component('detail-ajax-select-field', DetailAjaxSelectField)
    app.component('index-ajax-select-field', IndexAjaxSelectField)

}

export { ajaxSelectFieldSetup }