import FormJsonTagsField from "./fields/Form/JsonTagsField.vue"
import DetailJsonTagsField from "./fields/Detail/JsonTagsField.vue"
import IndexJsonTagsField from "./fields/Index/JsonTagsField.vue"

const jsonTagsFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-json-tags-field', FormJsonTagsField)
    app.component('detail-json-tags-field', DetailJsonTagsField)
    app.component('index-json-tags-field', IndexJsonTagsField)

}

export { jsonTagsFieldSetup }