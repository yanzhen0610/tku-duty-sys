<template>
    <td>
        <button-link v-if="field_data.type == 'button-link'"
            v-model="data"
            v-bind:field_data="field_data"
            v-bind:errors="errors"
        ></button-link>
        <button-popup-window v-if="field_data.type == 'button-popup-window'"
            v-model="data"
            v-bind:field_data="field_data"
            v-bind:errors="errors"
            @refresh-row="refresh_row"
        ></button-popup-window>
        <check-box v-else-if="field_data.type == 'checkbox'"
            v-model="data"
            v-bind:field_data="field_data"
            v-bind:errors="errors"
        ></check-box>
        <dropdown v-else-if="field_data.type == 'dropdown'"
            v-model="data"
            v-bind:field_data="field_data"
            v-bind:errors="errors"
        ></dropdown>
        <edit-table-text v-else-if="field_data.type == 'text'"
            v-model="data"
            v-bind:field_data="field_data"
            v-bind:errors="errors"
        ></edit-table-text>
        <div v-else-if="editable">
            <input
                v-model="data.data"
                v-bind:type="field_data.type"
                v-bind:class="{
                    'is-danger': errors,
                }"
            >
        </div>
        <span v-else>{{ value }}</span>
        <span v-if="errors">
            <p class="help is-danger"
                v-for="error in errors"
                v-bind:key="error"
            >{{ error }}</p>
        </span>
    </td>
</template>

<script>
import EditTableButtonLink from './fields/EditTableButtonLink.vue';
import EditTableButtonPopupWindow from './fields/EditTableButtonPopupWindow.vue';
import EditTableCheckBox from './fields/EditTableCheckBox.vue';
import EditTableDropdown from './fields/EditTableDropdown.vue';
import EditTableText from './fields/EditTableText.vue';
import { mapGetters } from 'vuex';

export default {
    props: {
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
    components: {
        'button-link': EditTableButtonLink,
        'button-popup-window': EditTableButtonPopupWindow,
        'check-box': EditTableCheckBox,
        'dropdown': EditTableDropdown,
        'edit-table-text': EditTableText,
    },
    data() {
        return {
            data: this.value,
        };
    },
    computed: {
        ...mapGetters([
            'editable',
        ]),
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
                this.$emit('input', value);
            },
            deep: true,
        },
    },
    methods: {
        refresh_row() {
            this.$emit('refresh-row');
        },
    },
};
</script>
