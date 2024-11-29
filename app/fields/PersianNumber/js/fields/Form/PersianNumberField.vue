<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText">
        <template #field>
            <input
                :id="field.attribute"
                type="text"
                class="w-full form-control form-input form-input-bordered"
                :class="errorClasses"
                :placeholder="field.name"
                v-model="formattedNumber"
                @input="handleChange"
            />
            <p>{{ numbersText }}</p>
        </template>
    </DefaultField>
</template>

<script>
import { DependentFormField, HandlesValidationErrors } from '@/mixins'
import numeral from "numeral";
import pn from "persian-number";

export default {
    mixins: [DependentFormField, HandlesValidationErrors],

    props: ['resourceName', 'resourceId', 'field'],

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || ''
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            formData.append(
                this.field.attribute,
                numeral(this.formattedNumber).value() || ''
            );
        },

        /**
         * Update the field's internal value.
         */
        handleChange(value) {
            this.value = value.target.value || '';
            this.numbersText = pn.convert(numeral(value.target.value).value()) + ' ' + this.field.moneyUnit;

            this.value = value.target.value;

        }
    },

    computed: {
        defaultAttributes() {
            return {
                type: this.field.type || 'text',
                class: this.errorClasses,
            }
        },

        formattedNumber(number) {
            this.numbersText = pn.convert(numeral(number.value).value()) + ' ' + this.field.moneyUnit;

            return numeral(number.value).format(this.field.format);
        },

        extraAttributes() {
            const attrs = this.field.extraAttributes

            return {
                // Leave the default attributes even though we can now specify
                // whatever attributes we like because the old number field still
                // uses the old field attributes
                ...this.defaultAttributes,
                ...attrs,
            }
        },
    }
}
</script>