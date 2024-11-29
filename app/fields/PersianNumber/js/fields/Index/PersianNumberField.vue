<template>
    <span>{{ fieldValue }}</span>
</template>

<script>
import numeral from "numeral";

export default {
    props: ['resourceName', 'field'],

    computed: {
        fieldValue() {
            return typeof this.field.value === 'number' ? numeral(this.field.value).format(this.field.format) : this.field.value
        },
    }
}
</script>