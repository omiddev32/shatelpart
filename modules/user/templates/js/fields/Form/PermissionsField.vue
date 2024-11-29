<template>
  <DefaultField
    :field="field"
    :errors="errors"
    :show-help-text="showHelpText"
    :full-width-content="fullWidthContent"
  >
    <template #field>
      <div class="w-full">

        <div class="w-full">
          
          <button
            @click="selectAll"
            type="button"
            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
            {{ __("Select All") }}
          </button>

          <button
            @click="deselectAll"
            type="button" 
            class="py-2.5 px-5 me-2 mb-2 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
            {{ __("Deselect All") }}
          </button>

        </div>

        <div v-for="(permissions, group) in field.options" :key="group" class="mb-4">
          <h1 class="font-normal text-lg mb-3 my-2">
            <!-- <checkbox :checked="isGroupChecked(group)" @click="toggleGroup(group)"/> -->
            <label class="w-full mx-1 cursor-pointer" @click="toggleGroup(group)">
              {{ __(group) }}
            </label>
          </h1>
          <div class="flex grid md:grid-cols-4 gap-4 break-words" :class="{
            'border-b pb-4': lastKey != group
          }">
            <div v-for="(permission, option) in permissions" :key="permission.option">
              <checkbox
                :value="permission.option"
                :checked="isChecked(permission.option)"
                @input="toggleOption(permission.option)"
              />
              <label
                :for="field.name"
                v-text="permission.label"
                @click="toggleOption(permission.option)"
                class="w-full mx-2"
              ></label>
              </div>
          </div>
        </div>
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from '@/mixins'

export default {
  mixins: [
    FormField,
    HandlesValidationErrors
  ],
  props: [
    'resourceName',
    'resourceId',
    'field'
  ],
  data: {
    checkedGroups: [],
  },

  computed: {
    lastKey() {
      let keys = Object.keys(this.field.options)
      return keys[keys.length - 1]
    },
  },

  methods: {

    selectAll() {
      let app = this
      _.forEach(app.field.options, (group) => {
        _.forEach(group, (permission) => {
          this.check(permission.option)
        })
      })
    },

    deselectAll() {
      let app = this
      _.forEach(app.field.options, (group) => {
        _.forEach(group, (permission) => {
          this.uncheck(permission.option)
        })
      })
    },

    avaiableOptions(group) {
      return this.field.options[group];
    },

    checkAll(group) {
      this.avaiableOptions(group).forEach(
        (permission) => this.check(permission.option)
      );
    },

    uncheckAll(group) {
      this.avaiableOptions(group).forEach(
        (permission) => this.uncheck(permission.option)
      );
    },

    isChecked(option) {
      return this.value && this.value.includes(option);
    },

    isGroupChecked(group) {
      return this.checkedGroups.includes(group);
    },

    check(option) {
      if (!this.isChecked(option)) {
        this.value.push(option);
      }
    },

    uncheck(option) {
      if (this.isChecked(option)) {
        this.value = this.value.filter(item => item != option);
      }
    },

    toggleGroup(group) {
      const index = this.checkedGroups.indexOf(group);
      const checked = index > -1;

      if (checked) {
        this.checkedGroups.splice(index, 1);
      } else {
        this.checkedGroups.push(group)
      }

      this.avaiableOptions(group).forEach(
        (permission) => checked
          ? this.uncheck(permission.option)
          : this.check(permission.option)
      )
    },

    toggleOption(option) {
      this.isChecked(option) ? this.uncheck(option) : this.check(option);
    },

    setInitialValue() {
      this.value = this.field.value || [];
    },

    fill(formData) {
      formData.append(this.field.attribute, this.value || []);
    },

    handleChange(value) {
      this.value = value;
    }
  }
};
</script>