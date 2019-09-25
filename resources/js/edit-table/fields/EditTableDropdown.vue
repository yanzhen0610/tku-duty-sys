<template>
    <div v-if="editable"
        class="control"
    >
        <div class="select">
            <select v-model="data.data">
                <option v-for="option in options"
                    v-bind:key="option.key"
                    v-bind:selected="data == option.key"
                    v-bind:value="option.key"
                >{{ option.display_name || option.key }}</option>
            </select>
        </div>
    </div>
    <div v-else>
        <span>{{ selected.display_name }}</span>
    </div>
</template>

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
    computed: {
        ...mapGetters([
            'i18n',
        ]),
        fields() {
            return this.$store.getters.fields;
        },
        editable() {
            return 'editable' in this.field_data ?
                this.field_data.editable : this.$store.getters.editable;
        },
        options() {
            return this.field_data.options;
        },
        selected() {
            for (const option of this.options)
                if (option.key == this.data.data)
                    return option;
        },
    },
    methods: {
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
