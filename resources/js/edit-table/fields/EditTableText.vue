<template>
    <input v-if="editable"
        v-model="data.data"
        v-bind:placeholder="i18n[field_data.name]"
        v-bind:class="{
            'is-danger': errors,
        }"
        class="input input-text"
        type="text"
    >
    <span v-else
    >{{ value.data }}</span>
</template>

<style scoped>
.input-text {
    min-width: 8rem;
}
</style>

<script>
import { mapGetters } from 'vuex';

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
    watch: {
        value: {
            handler(value) {
                this.data = value;
            },
            deep: true,
        },
        data: {
            handler(value) {
                if (this.data.data == '') this.data.data = null;
                // v-model
                this.$emit('input', value);
            },
            deep: true,
        },
    },
    computed: {
        ...mapGetters([
            'i18n',
        ]),
        editable() {
            return 'editable' in this.field_data ?
                this.field_data.editable : this.$store.getters.editable;
        },
    }
}
</script>
