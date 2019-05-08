<template>
    <div v-if="editable"
        class="control"
    >
        <div class="select">
            <select v-model="copiedValue.selected">
                <option v-for="option in options"
                    v-bind:key="option.key"
                    v-bind:selected="copiedValue.selected == option.key"
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
            value: {
                type: Object,
                required: false,
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
                if (this.value && this.value.options)
                    return this.value.options;
                return this.$store.state.fields[this.keyName].default;
            },
            selected() {
                for (const index in this.options)
                    if (this.options[index].key == this.copiedValue.selected)
                        return this.options[index];
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
