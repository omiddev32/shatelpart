<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
    <template #field>
      <template v-if="!isInReorderMode">
        <vue-select
          class="nova-select-plus-vs"
          v-model="selected"
          :options="optionsList"
          :placeholder="placeholder"
          :loading="isLoading"
          :disabled="currentField.readonly"
          :multiple="true"
          :selectable="selectable"
          :filterable="filterable"
          :dir="isRtl ? 'rtl' : 'ltr'"
          @search="handleSearch"
          @option:selected="select"
          @option:deselected="deselect"
          append-to-body
          :calculate-position="vueSelectCalculatePosition"
        >
          <template #open-indicator="{ attributes }">
            <svg
              v-bind="attributes"
              class="flex-shrink-0 pointer-events-none form-select-arrow"
              xmlns="http://www.w3.org/2000/svg"
              width="10"
              height="6"
              viewBox="0 0 10 6"
            >
              <path
                class="fill-current"
                d="M8.292893.292893c.390525-.390524 1.023689-.390524 1.414214 0 .390524.390525.390524 1.023689 0 1.414214l-4 4c-.390525.390524-1.023689.390524-1.414214 0l-4-4c-.390524-.390525-.390524-1.023689 0-1.414214.390525-.390524 1.023689-.390524 1.414214 0L5 3.585786 8.292893.292893z"
              ></path>
            </svg>
          </template>
          <template #no-options>
            <span v-if="currentField.isAjaxSearchable">
              {{ __("Type to search...") }}
              <span v-if="ajaxSearchNoResults">{{ __("Nothing found.") }}</span>
            </span>
            <span v-else>
              {{ __("Sorry, no matching options!") }}
            </span>
          </template>
          <template #option="option">
            <span :dir="isRtl ? 'rtl' : 'ltr'" v-html="option.label" />
          </template>
          <template #selected-option="option">
            <span :dir="isRtl ? 'rtl' : 'ltr'" v-html="option.label" />
          </template>
        </vue-select>
      </template>
      <template v-else>
        <vue-draggable
          class="nova-select-plus-vd"
          v-model="selected"
          @start="isDragging = true"
          @end="isDragging = false"
        >
          <span
            class="vd__item"
            v-for="(item, index) in selected"
            :key="item.id"
          >
            {{ index + 1 }}. <span v-html="item.label"></span>
            <svg width="16" class="vd__item_drag_icon" aria-hidden="true" focusable="false" data-prefix="far" data-icon="grip-lines" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
              <path fill="currentColor" d="M432 288H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm0-112H16c-8.8 0-16 7.2-16 16v16c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16z"></path>
            </svg>
          </span>
        </vue-draggable>
      </template>

      <span
        v-if="currentField.isReorderable"
        class="float-right text-sm ml-3 border-1 mt-2 mr-4"
      >
        <a
          v-if="!isInReorderMode"
          class="text-primary dim no-underline"
          href="#"
          @click.prevent="isInReorderMode = true"
        >
          {{ __("Reorder") }}
        </a>
        <a
          v-else
          class="text-primary dim no-underline"
          href="#"
          @click.prevent="isInReorderMode = false"
        >
          {{ __("Finish Reordering") }}
        </a>
      </span>
    </template>
  </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from '@/mixins'

import vueSelect from 'vue-select'
import { debounce } from 'lodash'
import { VueDraggableNext as vueDraggable } from 'vue-draggable-next'

export default {
  components: {
    vueSelect,
    vueDraggable
  },

  mixins: [DependentFormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data () {
    return {
      isDragging: false,
      selected: [],
      options: [],
      isLoading: true,
      isRtl: false,
      filterable: true,
      ajaxSearchNoResults: false,
      isInReorderMode: false,
      placeholder: ''
    }
  },

  // watch: {
  //   'selected': function(newValue, oldValue) {

  //     console.log({newValue})

  //     this.value = newValue
  //     // this.$emit('field-changed')
  //     // this.emitFieldValueChange(this.fieldAttribute, this.value)
  //   }
  // },

  computed: {

    optionsList() {
      if(this.options.length > 0) {
        if(this.currentField['withSelectAll'] && this.options.length > 2) {
          // if(this.selected.length > 0 && this.options.length === this.selected.length) {
          //   return [{id: 'deselect_all', label: this.__("Deselect All")}, ...this.options]
          // }
          return [{id: 'all', label: this.__("Select All")}, ...this.options]
        } 
        return this.options
      }
      return []
    }
  },

  methods: {

    select(value) {
      if(this.currentField['withSelectAll']) {
        let selectedAll = undefined
        selectedAll = _.find(value, (item) => item.id === "all" || item.id === "deselect_all")
        if(typeof selectedAll !== 'undefined') {
            if(this.value.length == this.options.length) {
              this.value = value
              _.remove(this.selected, (item) => item.id === "all")
              _.remove(this.value, (item) => item.id === "all")
            } else {
              this.value = this.options
              this.selected = [{id: 'all', label: this.__("Select All")}]
            }

        } else {
          this.value = value
          _.remove(this.value, (item) => item.id === "all")
          if(value.length == this.options.length ) {
              this.selected = [{id: 'all', label: this.__("Select All")}]
          }
        }
      } else {
        this.value = value
      }

      this.$emit('field-changed')
      this.emitFieldValueChange(this.fieldAttribute, this.value)
    },

    deselect(value) {
      if(value.id === 'all') {
        this.value = []
      } else {
        this.value = this.selected
      }
      this.$emit('field-changed')
      this.emitFieldValueChange(this.fieldAttribute, this.value)
    },

    onSyncedField() {
       this.setup()
    },

    setInitialValue () {
      this.selected = this.currentField.value || []
      this.value = this.currentField.value || []
    },

    vueSelectCalculatePosition (dropdownList, component, { width, top, left }) {
      // default built-in logic
      dropdownList.style.top = top
      dropdownList.style.left = left
      dropdownList.style.width = width

      // add our custom class to the node that is appended to body, see the stylesheet field.css
      dropdownList.classList.add('nova-select-plus-vs')
      dropdownList.classList.add('text-left')
    },

    setup () {

      this.isRtl = Nova.config('rtlEnabled')
      this.placeholder = this.currentField?.extraAttributes?.placeholder

      // if there is no options (not yet supported), but needs the full list via ajax
      if (this.currentField['isAjaxSearchable'] === false
        || (this.currentField['isAjaxSearchable'] === true && this.currentField['isAjaxSearchableEmptySearch'] === true)
      ) {
        const params = {}

        if (this.currentField.dependsOn) {
          Object.assign(params, this.currentField.dependsOn)
        }


        this.setInitialValue()

        Object.assign(params, { resourceId: this.resourceId})

        Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.currentField['relationshipName'], { params })
          .then(resp => {
            this.options = resp.data
            if(this.selected.length == this.options.length ) {
                this.selected = [{id: 'all', label: this.__("Select All")}]
            }
            this.isLoading = false
          })

        return
      }

      this.isLoading = false
      this.filterable = false
    },

    fill (formData) {
      this.fillIfVisible(formData, this.currentField.attribute, JSON.stringify(this.value))
    },

    selectable () {
      if (this.currentField['maxSelections'] <= 0) {
        return true
      }

      return this.selected.length < this.currentField['maxSelections']
    },

    handleSearch: debounce(function (search, loading) {
      if (this.currentField['isAjaxSearchable'] === false) {
        return
      }

      if (this.currentField['isAjaxSearchableEmptySearch'] === false && !search) {
        this.ajaxSearchNoResults = false

        return
      }

      loading(true)

      const params = {}

      if (this.currentField.dependsOn) {
        Object.assign(params, this.currentField.dependsOn)
      }

      Object.assign(params, { search: search, resourceId: this.resourceId })

      Nova.request().get('/nova-vendor/select-plus/' + this.resourceName + '/' + this.currentField['relationshipName'], { params })
        .then(resp => {
          this.options = resp.data

          if (this.options.length === 0) {
            this.ajaxSearchNoResults = true
          }

          loading(false)
        })
        .catch(err => {
          console.error(err)

          loading(false)
        })

      return true
    }, 500)
  },

  mounted () {
    this.setup()
  }
}
</script>
<style>
@import "vue-select/dist/vue-select.css";

  


    .nova-select-plus-vs.vs__dropdown-menu {
        @apply border p-0 my-1 rounded-lg shadow divide-y bg-white border-gray-200 divide-gray-100 dark:bg-gray-900 dark:border-gray-700 dark:divide-gray-800 text-left;
    }

    /* Sub-elements of .nova-select-plus-vs */

    .nova-select-plus-vs .vs__selected {
        @apply min-h-6 border-0 text-xs font-bold rounded px-2 py-1 space-x-1 bg-primary-50 text-primary-600 dark:bg-primary-500 dark:text-gray-900;
    }

    .nova-select-plus-vs .vs__selected:hover {
        opacity: inherit;
    }

    :is(.dark .nova-select-plus-vs .vs__selected:hover) {
        opacity: inherit;
    }

    .nova-select-plus-vs .vs__dropdown-toggle {
        @apply p-[3px_3px_7px_5px] rounded focus-within:outline-none focus-within:ring bg-white border-gray-300 focus-within:border-primary-300 ring-primary-100 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-400 dark:focus-within:border-gray-500 dark:ring-gray-700;
    }

    .nova-select-plus-vs .vs__dropdown-toggle:focus-within {
        @apply border-primary-300 dark:border-gray-500;
    }

    .nova-select-plus-vs .vs__open-indicator {
        @apply fill-current;
    }

    .nova-select-plus-vs .vs__open-indicator:hover {
        @apply opacity-50;
    }

    .nova-select-plus-vs .vs__actions {
        padding: 4px 11px 0 3px;
    }

    .nova-select-plus-vs .vs__deselect {
        @apply fill-current hover:opacity-75 dark:hover:opacity-50 mx-1;
    }

    .nova-select-plus-vs .vs__dropdown-option {
        @apply text-sm leading-normal font-semibold px-3 py-1.5 hover:bg-gray-100 dark:hover:bg-gray-800;
    }

    .nova-select-plus-vs .vs__dropdown-option--selected,
    .nova-select-plus-vs .vs__dropdown-option--highlight {
        @apply bg-primary-500 hover:bg-primary-500 text-white dark:hover:bg-primary-500 dark:text-gray-900 dark:hover:text-gray-900;
    }

    /* Vue Draggable Customization */

    .nova-select-plus-vd .vd__item {
        @apply block font-bold mt-1 mx-0.5 p-2 rounded-lg cursor-pointer bg-primary-50 text-primary-600 dark:bg-primary-500 dark:text-gray-900;
    }

    .nova-select-plus-vd .vd__item_drag_icon {
        @apply float-right fill-current;
    }

</style>
