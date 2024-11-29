import FormPersianNumberField from "./fields/Form/PersianNumberField.vue"
import DetailPersianNumberField from "./fields/Detail/PersianNumberField.vue"
import IndexPersianNumberField from "./fields/Index/PersianNumberField.vue"

const persianNumberFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-persian-number-field', FormPersianNumberField)
    app.component('detail-persian-number-field', DetailPersianNumberField)
    app.component('index-persian-number-field', IndexPersianNumberField)

}

export { persianNumberFieldSetup }