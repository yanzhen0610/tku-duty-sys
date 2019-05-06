<template>
    <div class="control">
        <div class="select">
            <select v-model="copiedValue.selected">
                <option v-for="option in options"
                    v-bind:key="option.key"
                    v-bind:selected="copiedValue.selected == option.key"
                    v-bind:value="option.key"
                >{{ option.display_name }}</option>
            </select>
        </div>
    </div>
</template>

<script>
    import { object_compare } from '../utils.js';
    import { mapGetters } from 'vuex';

    export default {
        props: {
            value: {
                type: Object,
                required: true,
            },
            keyName: {
                required: true,
            },
        },
        data() {
            return {
                'copiedValue': Object.assign({}, this.value),
            };
        },
        computed: {
            ...mapGetters([
                'editable',
                'fields',
                'i18n',
            ]),
            options() {
                if (this.value.options)
                    return this.value.options;
                return this.$store.state.fields[this.keyName].default;
            },
        },
        methods: {
            updateValue() {
                this.$emit('update-value', this.keyName, Object.assign({}, this.copiedValue));
            },
            ajax(method, url, handler, args) {
                this.$store.state.ajax(method, url, handler, args);
            },
        },
        watch: {
            value() {
                if (!object_compare(this.value, this.copiedValue))
                    this.$set(this, 'copiedValue', Object.assign({}, this.value));
            },
            copiedValue: {
                handler() {
                    this.updateValue();
                },
                deep: true,
            },
        },
    }
</script>
