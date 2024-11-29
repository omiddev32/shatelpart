<template>
  <default-field
    :field="field"
    :errors="errors"
  >
    <template #field>
      <vue-select
        :id="field.name"
        v-model="value"
        class="nova-select-plus-vs"
        :class="errorClasses"
        :placeholder="field.name"
        :options="availableOptions"
        :label="labelKey"
        :multiple="field.multiple"
        :reduce="reduceOption"
        :filterable="filterable"
        @search="inputChange"
        @input="inputSelected"
        :dir="isRtl ? 'rtl' : 'ltr'"
      >

          <template #no-options>
            <span>
              {{ __("Type to search...") }}
              <span v-if="ajaxSearchNoResults">{{ __("Nothing found.") }}</span>
            </span>
          </template>
          <template #option="option">
            <span :dir="isRtl ? 'rtl' : 'ltr'" v-html="option[labelKey]" />
          </template>

      </vue-select>

    </template>
  </default-field>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from '@/mixins'
import VueSelect from 'vue-select';
import { isArray } from 'util';

export default {
	
	components: {
		VueSelect,
	},
	mixins: [DependentFormField, HandlesValidationErrors],

	props: ['resourceName', 'resourceId', 'field'],

	data () {
		return {
			options: [],
			loading: false,
			isRtl: false,
			ajaxSearchNoResults: false,
			labelKey: 'name',
			parentVal: null,
			selectedOptions: [],
			value: null,
			filterable: true,
		};
	},

	computed: {
		parentComponent() {
			if(!this.field.parent_field) {
				return false;
			}

			let targetField = this.field.parent_field;
			let currentField =  this.field.attribute;

			// If component is inside a flexible, key is prefixed with an id
			if( currentField.indexOf('__') ) {
				targetField = currentField.substr(0, currentField.indexOf('__')) + '__' + targetField; 
			}

			//  Find the component the parent value references
			return this.$parent.$children.find(component => {
				return component.field !== undefined
					&& component.field.attribute == targetField;
			})
		},

		availableOptions () {
			return _.uniq(this.options.concat(this.selectedOptions), 'value');
		},
	},

	mounted () {
		this.isRtl = Nova.config('rtlEnabled')

		this.parseInitialValue();
		if(this.parentComponent) {
			this.parentComponent.$watch('value', (value) => {
				this.parentVal = value;
				this.prepareField();
			}, { immediate: true });
		} else {
			this.prepareField();
		}
	},

	methods: {
		prepareField() {
			//Check whether any filterable data was set, otherwise stay true
			if(this.field.filterable !== 'undefined') {
				this.filterable = this.field.filterable;
			}

			// If field is not responsive, load initial options
			if(!this.field.responsive) {
				return this.loadInitialOptions();
			}

			// If field is responsive, do we have any initial values
			if(this.field.value ) {
				return this.loadInitialOptions(this.field.value);
			}
		},

		/**
		 * Fill the given FormData object with the field's internal value.
		 */
		fill(formData) {
			formData.append(this.field.attribute, this.value || '')
		},

		/**
		 * Update the field's internal value.
		 */
		handleChange(value) {
			this.value = value
		},

		/*
		* Load initial Options
		*/ 
		loadInitialOptions (value) {
			let url = this.buildParamString(null, value);

			if(Array.isArray(value) && value.length === 1 && !value[0]) {
				this.value = [];
				return;
			}

			Nova.request().post( url ).then(({data}) => {
				this.options = data;

				this.options.forEach(option => {
					if (isArray(this.value)) {
						this.value.forEach(v => {
							if (v == option.value) {
								this.selectedOptions.push(option);
							}
						})
						return;
					}
					if (this.value == option.value) {
						this.selectedOptions.push(option);
					}
				})
			});
		},

		/*
		* Dynamic search with the input value
		*/ 
		search: _.debounce((loading, searchVal, vm) => {
			vm.ajaxSearchNoResults = false
			let url = vm.buildParamString(searchVal)
			Nova.request().post( url ).then(({data}) => {
				vm.options = data;
          if (vm.options.length === 0) {
            vm.ajaxSearchNoResults = true
          }
				loading(false);
			});
		}, 350),

		/*
		* When multiselect input changes, determine if ready to query
		*/
		inputChange (input, loading) {
			if( input.length < 3 &&  !/^\d+$/.test(input)) {
				return;
			}
			loading(true);

			this.search(loading, input, this);
		},

		reduceOption(option) {
			const valueKey = this.field.valueKey || 'value';
			return option ? option[valueKey] : null;
		},

		buildParamString(searchVal, fieldVal) {
			let params = {}
			let url = this.field.url;

			if(this.parentVal) {
				params[this.field.parent_field] = this.parentVal;
			}

			if(this.field.responsive && searchVal) {
				params.search = searchVal
			}

			if(fieldVal) {
				params.value = fieldVal
			}

			const paramString = new URLSearchParams(params).toString();

			return url = url + '?' + paramString;
		},

		parseInitialValue () {
			let value = this.field.value ? this.field.value : null;
			if (!value) {
				this.value = value;
				return;
			}
			if (!this.field.multiple) {
				value = this._parseValue(value);
			} else {
				if (!Array.isArray(value)) {
					value = value.split(',');
				}
				!value[0] ? value = [] : value = value.map(this._parseValue);
			}
			this.value = value;
		},

		_parseValue(value) {
			if (this.field.type === 'int') {
				value = parseInt(value);
			}
			if (this.field.type === 'float') {
				value = parseFloat(value);
			}

			return value;
		},

		inputSelected(value) {
			if (!value) {
				return;
			}
			if (Array.isArray(value)) {
				value.forEach(v => {
					if (!v) {
						return;
					}
					const selectedOption = this.options.find(option => option.value === v);
					this.selectedOptions.push(selectedOption);
				});
			} else {
				const selectedOption = this.options.find(option => option.value === value);
				this.selectedOptions.push(selectedOption);
			}
		}
	},
}
</script>