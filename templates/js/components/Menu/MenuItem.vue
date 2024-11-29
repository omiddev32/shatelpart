<template>
  <div>
    <component
      :is="component"
      v-bind="linkAttributes"
      class="w-full flex min-h-8 px-1 py-2 rounded text-left text-menu-item dark:text-gray-500 cursor-pointer bg-menu-item-hover text-menu-item-hover dark:hover:bg-gray-800"
      :data-active-link="item.active"
      :class="{
        'font-bold border dark:text-primary-500': item.active,
      }"
      @click="handleClick"
    >
      <span class="inline-block shrink-0 w-6 h-6">
        <component
          :is="`heroicons-solid-${item.icon}`"
          height="24"
          width="24"
        />
      </span>
      <span class="flex-1 flex items-center w-full px-3 text-sm">
        {{ item.name }}
      </span>

      <span class="inline-block h-6 shrink-0">
        <Badge v-if="item.badge" :extra-classes="item.badge.typeClass">
          {{ item.badge.value }}
        </Badge>
      </span>
    </component>
  </div>
</template>

<script>
import identity from 'lodash/identity'
import isNull from 'lodash/isNull'
import omitBy from 'lodash/omitBy'
import pickBy from 'lodash/pickBy'
import { mapGetters, mapMutations } from 'vuex'

export default {
  props: {
    item: {
      type: Object,
      required: true,
    },
  },

  methods: {
    ...mapMutations(['toggleMainMenu']),

    handleClick() {
      if (this.mainMenuShown) {
        this.toggleMainMenu()
      }
    },
  },

  computed: {
    ...mapGetters(['mainMenuShown']),

    requestMethod() {
      return this.item.method || 'GET'
    },

    component() {
      if (this.requestMethod !== 'GET') {
        return 'FormButton'
      } else if (this.item.external !== true) {
        return 'Link'
      }

      return 'a'
    },

    linkAttributes() {
      let method = this.requestMethod

      return pickBy(
        omitBy(
          {
            href: this.item.path,
            method: method !== 'GET' ? method : null,
            headers: this.item.headers || null,
            data: this.item.data || null,
            rel: this.component === 'a' ? 'noreferrer noopener' : null,
            target: this.item.target || null,
          },
          isNull
        ),
        identity
      )
    },
  },
}
</script>
