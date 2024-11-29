<template>
    <panel-item :field="field">
        <template #value>  
            <p class="text-90 flex flex-wrap">
                <span
                    :style="{columnCount: this.field.columns}"
                    class="w-full"
                    v-if="field.withGroups"
                >
                    <div
                        v-for="(label, option) in field.options"
                        :key="option"
                        class="flex-auto"
                    >
                        <div class="row">
                            <div>
                                <h3 style="font-size: 18px">
                                {{ option }}
                                </h3>
                            </div>
                            <div>
                                <h3 class="my-2" style="font-size: 1.1rem">
                                    <span v-for="(permission, option , key) in label">
                                        <span
                                            class="inline-block rounded-full w-2 h-2 mx-1"
                                            :class="optionClass(permission)"
                                        />
                                        {{ permission.label }}
                                    </span> 
                                </h3>
                            </div>
                        </div>

                        <div class="w-full bg-gray-400 my-6"></div>

                    </div>
                </span>
            </p>
        </template>
    </panel-item>
</template>

<script>
export default {
    props: ['resource', 'resourceName', 'resourceId', 'field'],
    methods: {
        optionClass(option) {
            return {
                'bg-green-400': this.field.value ? this.field.value.includes(option.option) : false,
                'bg-red-400': this.field.value ? !this.field.value.includes(option.option) : true,
            }
        }
    },
}
</script>

<style>
    .max-col-2 {
        -moz-column-count: 2;
        -webkit-column-count: 2;
        column-count: 2;
        white-space: unset;
    }
</style>