import FormTranslatableField from "./fields/Form/TranslatableField.vue"
import DetailTranslatableField from "./fields/Detail/TranslatableField.vue"
import IndexTranslatableField from "./fields/Index/TranslatableField.vue"
import LocaleTabs from "./fields/LocaleTabs.vue"

import FormLocaleSelectField from "./fields/Form/LocaleSelectField.vue"
import DetailLocaleSelectField from "./fields/Detail/LocaleSelectField.vue"
import LocaleSelect from "./fields/LocaleSelect.vue"



const translatableFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-translatable-field', FormTranslatableField)
    app.component('detail-translatable-field', DetailTranslatableField)
    app.component('index-translatable-field', IndexTranslatableField)
    app.component('translatable-locale-tabs', LocaleTabs)

    app.component('form-locale-select-field', FormTranslatableField)
    app.component('detail-locale-select-field', DetailTranslatableField)
    app.component('translatable-locale-select', LocaleSelect)

}

export { translatableFieldSetup }