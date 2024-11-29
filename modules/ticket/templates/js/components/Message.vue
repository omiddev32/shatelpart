<template>
	
    <form @submit.prevent class="grid md:grid-cols-8 px-3 pb-2 mt-4" v-if="(resourceId != 0)">
    	<input v-model="message" class="col-span-8 form-control form-input form-input-bordered py-3 h-auto" />
      <button type="submit" @click="sendMessage" :disabled="! readyForSend" class="bg-blue-500 w-full btn cursor-pointer hover:bg-blue-200 text-40 hover:text-90 mt-2 mx-auto p-3 rounded">
        {{ __("Send") }}
      </button>
    </form>

</template>

<script>
	export default{

	  props : {

	  	resourceId : {
	  		type : Number,
	  		default : 0
	  	}

	  },

	  data : () => ({

	    message : null,
	    loading : false,

	  }),

	  methods:{
	    sendMessage(){

	      let app = this 

	      if (! app.loading) {
	          if (app.readyForSend) {

	          	const formData = new FormData()
	          	formData.append('resourceId' , app.resourceId)
	          	formData.append('message' , app.message)

	          	Dolphin.request().post('/tickets/send-message' , formData)
	          		.then((res) => {
	          			app.message = null
	          			app.loading = false
				        Dolphin.success(res.data.message)
	          			Dolphin.$emit('refresh-data')
	          		}).catch((error) => {
	          			Dolphin.success("پیام ارسال نشد")
	          		})

	          }
	      }

	    }

	  },

	  computed:{

	    readyForSend(){
	      return (this.message != null && this.message != '' && this.message != ' ')
	    },

	  }

	};
</script>