<template>
    <label>
        <input v-if="editable"
            v-model="data.data"
            type="checkbox"
            class="filled-in">
        <input v-else
            v-model="data.data"
            type="checkbox"
            onclick="return false">
        <span
            v-bind:class="{
                'cursor-default': !editable,
            }"
        ></span>
    </label>
</template>

<style scoped lang="scss">
@import '../../../sass/checkboxes.scss';

input[type=checkbox] + .cursor-default {
    cursor: default;
}
</style>

<script>
export default {
    props: {
        // v-model
        value: {
            required: true,
            type: Object,
        },
        field_data: {
            required: true,
            type: Object,
        },
        errors: {
            required: false,
            type: Array,
        },
    },
    data() {
        return {
            data: this.value,
        };
    },
    computed: {
        editable() {
            return 'editable' in this.field_data ?
                this.field_data.editable : this.$store.getters.editable;
        },
    },
    watch: {
        value: {
            handler(value) {
                this.data = value;
            },
            deep: true,
        },
        data: {
            handler(value) {
                // v-model
                this.$emit('input', value);
            },
            deep: true,
        },
    },
}
</script>
