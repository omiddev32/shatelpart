import FormTabsField from "./fields/FormTabs.vue"
import DetailTabsField from "./fields/DetailTabs.vue"

const tabsFieldSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('form-tabs-field', FormTabsField)
    app.component('detail-tabs-field', DetailTabsField)

}

export { tabsFieldSetup }