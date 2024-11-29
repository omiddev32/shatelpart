import DetailMessagesField from "./fields/Detail/MessagesField.vue"

const ticketModuleSetup = (app) => {

    /**
     * Register Fields
     */
    app.component('detail-messages-field', DetailMessagesField)

}

export { ticketModuleSetup }