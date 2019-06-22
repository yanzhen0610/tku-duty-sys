<template>
    <span>
        <a v-if="value"
            v-bind:disabled="!value.url"
            class="button is-primary"
            @click="openPopupWindow()"
        >{{ i18n[keyName] }}</a>
    </span>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            value: {
                type: Object,
            },
            keyName: {
                required: true,
            },
        },
        data() {
            return {
                'window': null,
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
            openPopupWindow() {
                if (!this.window || this.window.closed) {
                    this.window = window.open(this.copiedValue.url, '_blank', 'width=400,height=400,resiable,scrollbars');
                    let outer = this;
                    
                    let set_up_window = function() {
                        outer.window.return_function = function(value) {
                            if (value == 'success') {
                                outer.copiedValue.url = null;
                                outer.updateValue();
                            }
                        };
                        outer.window.onunload = function() {
                            setTimeout(set_up_window, 0);
                        };
                    };
                    set_up_window();
                } else {
                    this.window.focus();
                }
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
            updateValue() {
                this.$emit('update-value', this.keyName, this.copiedValue);
            },
        },
        watch: {
            value() {
                this.copiedValue = this.value;
            },
        },
    }
</script>
