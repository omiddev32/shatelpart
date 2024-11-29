<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
        <template #field>
            <vue-tags-input
                v-model="tag"
                :tags="tags"
                @tags-changed="tagsChanged"
                :placeholder="field.placeholder"
                :autocompleteItems="filteredItems"
                :add-on-key="field.addOnKeys"
                :separators="field.separators"
                :add-from-paste="field.addFromPaste"
                :add-on-blur="field.addOnBlur"
                :add-only-from-autocomplete="field.addOnlyFromAutocomplete"
                :allow-edit-tags="field.allowEditTags"
                :autocomplete-always-open="field.autocompleteAlwaysOpen"
                :autocomplete-filter-duplicates="field.autocompleteFilterDuplicates"
                :autocomplete-min-length="field.autocompleteMinLength"
                :avoid-adding-duplicates="field.avoidAddingDuplicates"
                :delete-on-backspace="field.deleteOnBackspace"
                :disabled="field.disabled"
                :max-tags="field.maxTags"
            />
        </template>
    </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from '@/mixins'
import VueTagsInput from '@sipec/vue3-tags-input';

export default {
    data() {
        return {
            tag: '',
            tags: [],
            autocompleteItems: [],
            showHelpText: true,
        }
    },
    mixins: [DependentFormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    mounted() {
        // Set up default parameters
        this.autocompleteItems = (this.field.autocompleteItems) ? this.field.autocompleteItems : this.autocompleteItems;
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || '';
            if (this.value !== '') {
                let tags = [];
                this.value.forEach(function (item, index) {
                    // Check if the data needs conversion
                    tags.push((typeof item === "object" && item.hasOwnProperty('text')) ? item : {'text': item});
                });
                // Store the tags array
                this.tags = tags;
            }
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(this.field.attribute, JSON.stringify(this.tags) || '')
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value
        },

        /**
         * Handles the tagChanged event
         * @param newTags
         */
        tagsChanged(newTags) {
            this.tags = newTags;
        }
    },

    computed: {
        filteredItems() {
            return this.autocompleteItems.filter(i => {
                return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
            });
        },
    },

    components: { VueTagsInput }
}
</script>
<style lang="scss">
/**
* Nova Field styles
*/
.nti-tags-wrapper-index {
    display: flex;
    flex-wrap: wrap;
}

.nti-tags-wrapper {
    margin-bottom: -5px;

    .nti-tag {
        display: inline-block;
        margin: 2px;
        color: rgba(var(--colors-white)) !important;
        background-color: rgba(var(--colors-primary-500), var(--tw-bg-opacity)) !important;
        padding: 3px 4px !important;
        border-radius: 0.2rem !important;
        font-size: .875rem !important;
        line-height: 1.25rem !important;
        font-weight: 700 !important;
    }
}

// Dark variant
.dark {
    .nti-tags-wrapper {
        .nti-tag {
            color: rgba(var(--colors-slate-900)) !important;
        }
    }
}


/**
* Plugin styles
*/
.vue-tags-input {
    max-width: none !important;
    background-color: transparent !important;
}

.ti-input {
    --tw-ring-color: rgba(var(--colors-primary-100)) !important;
    --tw-border-opacity: 1 !important;
    --tw-bg-opacity: 1 !important;
    --tw-text-opacity: 1 !important;
    font-size: .875rem !important;
    background-color: rgba(var(--colors-white), var(--tw-bg-opacity)) !important;
    border-color: rgba(var(--colors-slate-300), var(--tw-border-opacity));
    border-radius: 0.25rem !important;
    border-width: 1px !important;
    color: rgba(var(--colors-slate-600)) !important;
    padding-left: 0.75rem !important;
    padding-right: 0.75rem !important;
    box-sizing: border-box !important;
    line-height: normal !important;
}

.dark .ti-input, .dark .ti-new-tag-input {
    --tw-ring-color: rgba(var(--colors-slate-700), var(--tw-ring-opacity)) !important;
    background-color: rgba(var(--colors-slate-900), var(--tw-bg-opacity)) !important;
    color: rgba(var(--colors-slate-400)) !important;
    border-color: rgba(var(--colors-slate-700), var(--tw-border-opacity)) !important;
}

.ti-tag {
    font-size: .875rem !important;
    line-height: 1.25rem !important;
    font-weight: 700 !important;
    padding: 3px 4px 3px 7px !important;
    border-radius: 0.2rem !important;

    &.ti-valid {
        color: rgba(var(--colors-white)) !important;
        background-color: rgba(var(--colors-primary-500), var(--tw-bg-opacity)) !important;
    }

    &.ti-deletion-mark {
        background-color: rgba(var(--colors-red-500), var(--tw-bg-opacity)) !important;
    }
}

.ti-autocomplete {
    .ti-item {
        &.ti-selected-item {
            background-color: var(--colors-primary-500) !important;
            color: rgba(var(--colors-white)) !important;
        }
    }
}

// Dark variant
.dark {
    .ti-tag {
        &.ti-valid {
            color: rgba(var(--colors-slate-900)) !important;
        }
    }

    .ti-autocomplete {
        .ti-item {
            &.ti-selected-item {
                color: rgba(var(--colors-slate-900)) !important;
            }
        }
    }
}



</style>
