<template>
  <button
    type="button"
    class="inline-flex items-center justify-center focus:ring focus:ring-primary-200 focus:outline-none rounded"
    :class="buttonClasses"
    :disabled="disabled"
  >
    <Icon :type="iconType" class="hover:opacity-50" v-bind="{ solid: solid }" />
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  iconType: { type: String, default: 'dots-horizontal' },
  small: { type: Boolean },
  medium: { type: Boolean },
  large: { type: Boolean },
  solid: { type: Boolean, default: true },
  disabled: { type: Boolean, default: false },
})

const buttonClasses = computed(() => [
  props.small && 'w-6 h-6',
  props.medium && 'w-8 h-8',
  props.large && 'w-9 h-9',
])
</script>
