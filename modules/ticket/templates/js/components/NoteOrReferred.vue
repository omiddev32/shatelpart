<template>
	<div class="w-full flex flex-wrap pt-5">
		<div class="w-full text-lg dark:text-white">
			{{ __("Note") }}
		</div>

		<div class="w-full mt-5">
			<label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
				{{ __("Description") }}
			</label>
			<textarea
				rows="4"
				v-model="message"
				class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
				:placeholder="__('Write your text...')"
			></textarea>
		</div>
		<div class="w-full my-5">
			<button 
				@click="sendNote"
				class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
			>
				{{ __("Note") }}
			</button>
		</div>
	</div>
</template>
<script>
	export default {

		data: () => ({
			message: ''
		}),

		props: [
			'resourceId'
		],

		methods: {
			sendNote() {

				let formData = new FormData()
				formData.append('message', this.message)
				formData.append('resourceId', this.resourceId)

				Nova.request().post('/panel-api/tickets/new-note', formData)
					.then(({ data }) => {
						this.message = ''
						if(typeof data.messages !== "undefined") {
							Nova.$emit('update-ticket-messages', data.messages)
						}
					})
					.catch(({ error }) => {

					})
			}
		}

	}
</script>