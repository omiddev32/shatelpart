<template>
    <PanelItem :index="index" :field="field" >
        <template #value>
            <div>{{ formattedField.value }}</div>
            <p>{{ numbersText }}</p>
        </template>
    </PanelItem>
</template>

<script>
import numeral from "numeral";
import pn from "persian-number";

export default {
  props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    mounted() {
        if(typeof this.field.value === 'number') {
            this.numbersText = pn.convert(this.field.value) + ' ' + this.field.moneyUnit;
            this.formattedField.value = numeral(this.field.value).format(this.field.format);
        }
    },

    data() {
        return {
            numbersText: this.numbersText,
            formattedField: this.field
        };
    }
}
</script>