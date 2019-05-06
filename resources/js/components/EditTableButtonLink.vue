<template>
    <span>
        <a v-if="value"
            v-bind:disabled="!value.url"
            v-bind:class="{ 'is-loading': value.isLoading }"
            class="button is-primary"
            @click="visitButtonLink(value)"
        >{{ i18n[keyName] }}</a>
    </span>
</template>

<script>
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
                'copiedValue': this.value,
            };
        },
        computed: {
            ...mapGetters([
                'editable',
                'i18n',
            ]),
        },
        methods: {
            ajax(method, url, handler, args) {
                this.$store.state.ajax(method, url, handler, args);
            },
            visitButtonLink(buttonLinkData) {
                if (buttonLinkData.url && !buttonLinkData.isLoading) {
                    buttonLinkData.isLoading = true;
                    const outer = this;
                    const handler = function() {
                        if (this.readyState == 4) {
                            if (this.status == 200 && this.responseURL == buttonLinkData.url) {
                                try {
                                    const response = JSON.parse(this.responseText);
                                    outer.updateRow(response);
                                } catch (e) {}
                            }
                            buttonLinkData.isLoading = false;
                        }
                    };

                    this.ajax(
                        buttonLinkData.method,
                        buttonLinkData.url,
                        handler
                    )
                }
            },
            updateRow(rowData) {
                this.$emit('update-row', rowData);
            },
        },
        watch: {
            value() {
                this.copiedValue = this.value;
            },
        },
    }
</script>
