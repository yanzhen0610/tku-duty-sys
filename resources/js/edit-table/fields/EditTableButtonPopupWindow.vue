<template>
    <span>
        <a v-if="value"
            v-bind:disabled="!value.data"
            class="button is-primary"
            @click="value.data && openPopupWindow()"
        >{{ i18n[field_data.name] }}</a>
    </span>
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
            'window': null,
        };
    },
    computed: {
        ...mapGetters([
            'editable',
            'i18n',
        ]),
    },
    methods: {
        openPopupWindow() {
            if (!this.window || this.window.closed) {
                const url = this.value.data;
                const outer = this;
                const options = this.field_data.options ?
                    this.field_data.options :
                    'width=400,height=400,resiable,scrollbars';

                this.window = window.open(url, '_blank', options);

                const set_up_window = function() {
                    outer.window.return_function = function(value) {
                        outer.refresh_row()
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
        refresh_row() {
            this.$emit('refresh-row');
        },
    },
}
</script>
