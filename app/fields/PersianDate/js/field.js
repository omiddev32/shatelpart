import FormPersianDateField from "./fields/Form/PersianDateField.vue"
import DetailPersianDateField from "./fields/Detail/PersianDateField.vue"
import IndexPersianDateField from "./fields/Index/PersianDateField.vue"
import FormPersianDateTimeField from "./fields/Form/PersianDateTimeField.vue"
import DetailPersianDateTimeField from "./fields/Detail/PersianDateTimeField.vue"
import IndexPersianDateTimeField from "./fields/Index/PersianDateTimeField.vue"

const persianDateFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-persian-date-field', FormPersianDateField)
    app.component('detail-persian-date-field', DetailPersianDateField)
    app.component('index-persian-date-field', IndexPersianDateField)
    app.component('form-persian-date-time-field', FormPersianDateTimeField)
    app.component('detail-persian-date-time-field', DetailPersianDateTimeField)
    app.component('index-persian-date-time-field', IndexPersianDateTimeField)

}

export { persianDateFieldSetup }