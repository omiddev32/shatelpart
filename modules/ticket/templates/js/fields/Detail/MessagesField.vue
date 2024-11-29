<template>
    <div id="chat-messages" class="p-6 rounded-sm w-full relative" >

        <div class="bubbleWrapper" v-if="messages.length" v-for="message in messages">
            <div class="inlineContainer" :class="typeClass(message)">
                <div :class="bubbleClass(message)" v-html="message.text" />
                <img class="inlineIcon" :src="message.logo">
            </div>
            <span :class="timeClass(message)">
                <div class="my-1" v-if="message.modelable_type != 'user' && message.message_type == 'message'">
                    {{ __("Message From") }}: {{ message.admin }}
                </div>

                <div class="my-1" v-if="message.message_type != 'message'">
                    <div class="flex flex-wrap" v-if="message.message_type == 'referred'">
                        <div>
                            {{ __("Referred") }}: {{ message.referredTo }}
                        </div>
                        <div class="ml-4 pl-4 border-l-2">
                          {{ __("Referrer") }}: {{ message.referredFrom }}
                        </div>
                    </div>
                    <div v-else>
                        {{ __("Note") }} - {{ __("Writer") }}: {{ message.admin }}
                    </div>
                </div>
                <div 
                    class="w-full"
                    :class="{
                        'justify-end flex': message.modelable_type == 'user'
                    }"
                >
                    {{ message.created_at }}
                </div>

                <div class="w-full flex-wrap gap-y-1.5 flex mt-2">
                    <a
                        :href="file.path"
                        target="_blank"
                        v-for="(file, index) in message.files"
                        :key="index"
                        :class="{
                            'flex-row-reverse': message.modelable_type == 'user'
                        }"
                        class="flex w-full justify-start gap-2 text-dynamic-secondary"
                    >
                        <img :src="file.path" class="w-8 h-8 rounded-lg">
                        <p class="text-xs line-clamp-1 items-center flex">{{ getFileName(file.path) }}</p>
                    </a>
                </div>
            </span>
        </div>

    </div>

    <div v-if="canSendMessage" class="bg-gray-100 border-2 grid grid-cols-1 md:gap-x-5 md:grid-cols-5 md:px-[50px] my-2 rounded-lg shadow-lg w-full">
        
        <Reply
            :resourceId="resourceId"
            class="md:border-r-2 md:pr-4 col-span-2"
        />
        <NoteOrReferred
            :class="{
                'md:border-r-2 md:pr-4': field.canClose || field.canReferral
            }"
            class="col-span-2"
            :resourceId="resourceId"
        />

        <div class="flex" v-if="field.canClose || field.canReferral">
            <button
                v-if="field.canReferral"
                @click.stop="showAction('referred-to-action')"
                type="button"
                class="bg-blue-400 border border-gray-200 dark:border-gray-600 font-medium h-14 hover:bg-gray-100 hover:text-blue-700 mx-auto my-auto px-5 py-2.5 rounded-lg text-white text-sm">
                {{ __('Ticket referral') }}
            </button>
            <button
                v-if="field.canClose"
                @click.stop="showAction('close-ticket-action')"
                type="button"
                class="bg-blue-400 border border-gray-200 dark:border-gray-600 font-medium h-14 hover:bg-gray-100 hover:text-blue-700 mx-auto my-auto px-5 py-2.5 rounded-lg text-white text-sm">
                {{ __('Close Ticket') }}
            </button>

        </div>

    </div>


</template>

<script>

import Reply from "../../components/Reply"
import NoteOrReferred from "../../components/NoteOrReferred"

export default {

  data : () => ({
    loading : true,
    messages: []
  }),

  props: {
    field: {
      type: Object,
      required: true,
    },
    fieldName: {
      type: String,
      default: '',
    },
  },

  components :{
    Reply, NoteOrReferred
  },

  methods:{

    getFileName(url) {
        const index = url.indexOf("message");
        return url.substring(index + 8);
    },

    showAction(key) {
        Nova.$emit('open-action-model', key)
    },

    typeClass(record){
        return record.modelable_type == 'user' ? 'other' : 'own'
    },

    bubbleClass(record){
        if(record.modelable_type == 'user') {
            return 'otherBubble other';
        }
        if(record.message_type == 'message') {
            return 'ownBubble own';
        }        
        if(record.message_type == 'note') {
            return 'bg-red-400 border-1 border-red-400 noteBubble own';
        }
        return 'bg-yellow-300 border-1 border-yellow-300 text-gray-600 referredBubble own';
    },

    timeClass(record){
        return record.modelable_type == 'user' ? 'other otherTime' : 'own ownTime'
    },

  },

  mounted(){
    this.messages = this.field.options

    Nova.$on('update-ticket-messages', (messages) => {
        this.messages = messages
    })
    // let chatWrapper = document.querySelector('#chat-messages');
    // chatWrapper.scrollTo(0, chatWrapper.offsetHeight );

  },

  beforeUnmount() {
    Nova.$off('update-ticket-messages')
  },

  computed: {

    resourceId(){
      return Number(this.$attrs["resource-id"])
    },

    label() {
      return this.fieldName || this.field.name
    },

    canSendMessage(){
      return this.field.canSendMessage
    },

    fieldValue() {
      if (
        this.field.value === '' ||
        this.field.value === null ||
        this.field.value === undefined
      ) {
        return false
      }

      return String(this.field.value)
    },

    shouldDisplayAsHtml() {
      return this.field.asHtml
    },
  },
};
</script>
<style scoped>
    

.ownTime{
    margin-right: 45px;
}

.otherTime{
    margin-left: 45px;
}

.bubbleWrapper {
    padding: 10px 10px;
    display: flex;
    justify-content: flex-end;
    flex-direction: column;
    align-self: flex-end;
  color: #fff;
}
.inlineContainer {
  display: inline-flex;
}
.inlineContainer.own {
  flex-direction: row-reverse;
}
.inlineIcon {
  width:40px;
  object-fit: contain;
}
.ownBubble {
    min-width: 60px;
    max-width: 700px;
    padding: 14px 18px;
    margin: 6px 8px;
    line-height: 25px;
    background-color: #242D42;
    border-radius: 16px 16px 0 16px;
    border: 1px solid #242D42;
}

.noteBubble {
    min-width: 60px;
    max-width: 700px;
    padding: 14px 18px;
    margin: 6px 8px;
    line-height: 25px;
    border-radius: 16px 16px 0 16px;
}


.referredBubble {
    min-width: 60px;
    max-width: 700px;
    padding: 14px 18px;
    margin: 6px 8px;
    line-height: 25px;
    border-radius: 16px 16px 0 16px; 
}

.otherBubble {
    min-width: 60px;
    max-width: 700px;
    padding: 14px 18px;
    margin: 6px 8px;
    line-height: 25px;
    background-color: #6C8EA4;
    border-radius: 16px 16px 16px 0;
    border: 1px solid #54788e;
  
}
.own {
    align-self: flex-start;
}
.other {
    align-self: flex-end;
}
span.own,
span.other{
  font-size: 14px;
  color: grey;
}

</style>