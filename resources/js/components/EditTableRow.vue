<template>
    <tr>
        <th>
            <div v-if="rowData.created">
                <input
                    v-model="copiedRowData[primary_key]"
                    v-bind:class="{ 'is-danger': errors[primary_key] }"
                    class="input"
                    type="text"
                    required
                >
                <p class="help is-danger"
                    v-for="error in errors[primary_key]"
                    v-bind:key="error"
                >{{ error }}</p>
            </div>
            <a v-else-if="rowData.show_url"
                v-bind:href="rowData.show_url"
            >{{ rowData.key }}</a>
            <span v-else>{{ rowData.key }}</span>
        </th>
        <td v-for="(fieldData, key) in fields"
            v-bind:key="key"
        >
            <button-link v-if="fieldData.type == 'button-link'"
                v-bind:keyName="key"
                v-bind:value="copiedRowData[key]"
                @update-row="updateRow"
            >
            </button-link>
            <check-box v-else-if="fieldData.type == 'checkbox'"
                v-bind:keyName="key"
                v-bind:value="copiedRowData[key]"
                @update-value="updateValue"
            >
            </check-box>
            <dropdown v-else-if="fieldData.type == 'dropdown'"
                v-bind:keyName="key"
                v-bind:value="copiedRowData[key]"
                @update-value="updateValue"
            >
            </dropdown>
            <div v-else-if="editable">
                <input
                    v-model="copiedRowData[key]"
                    v-bind:type="fieldData.type"
                    v-bind:class="{ input: fieldData.type == 'text', 'is-danger': errors[key] }"
                >
            </div>
            <span v-else>{{ rowData[key] }}</span>
            <p class="help is-danger"
                v-for="error in errors[key]"
                v-bind:key="error"
            >{{ error }}</p>
        </td>
        <td v-if="editable && (rowData.created || rowData.update_url)">
            <a class="button is-success"
                v-bind:class="{ 'is-loading': updating }"
                v-bind:disabled="!canSave"
                @click="save()"
            >
                <span class="icon is-small">
                    <i class="fas fa-check"></i>
                </span>
                <span>{{ i18n['save'] }}</span>
            </a>
        </td>
        <td v-if="destroyable && rowData.destroy_url">
            <a class="button is-danger is-outlined"
                v-bind:class="{ 'is-loading': destroying }"
                v-bind:disabled="!canDestroy"
                @click="destroy()"
            >
                <span>{{ i18n['delete'] }}</span>
                <span class="icon is-small">
                    <i class="fas fa-times"></i>
                </span>
            </a>
        </td>
    </tr>
</template>

<script>
    import { object_compare } from '../utils.js';

    import EditTableButtonLink from './EditTableButtonLink.vue';
    import EditTableCheckBox from './EditTableCheckBox.vue';
    import EditTableDropdown from './EditTableDropdown.vue';
    import { mapGetters, mapMutations } from 'vuex';

    export default {
        props: {
            rowKey: {
                type: Number,
                required: true,
            },
            rowData: {
                type: Object,
                required: true,
            },
        },
        components: {
            'button-link': EditTableButtonLink,
            'check-box': EditTableCheckBox,
            'dropdown': EditTableDropdown,
        },
        data() {
            var copiedRowData = {};
            for (const key in this.$store.getters.fields)
                copiedRowData[key] = this.rowData[key];
            return {
                copiedRowData,
                changed: false,
                updating: false,
                destroying: false,
                errors: {},
            };
        },
        computed: {
            ...mapGetters([
                'i18n',
                'fields',
                'editable',
                'destroyable',
                'create_url',
                'primary_key',
            ]),
            fields() {
                return this.$store.getters.fields;
            },
            canSave() {
                return !this.updating && !this.destroying &&
                    (this.changed || this.rowData.created);
            },
            canDestroy() {
                return !this.updating && !this.destroying;
            },
        },
        watch: {
            copiedRowData: {
                handler() {
                    this.emptyStringToNull();
                    this.updateChanged();
                },
                deep: true,
            },
        },
        methods: {
            ...mapMutations([
                'removeRow',
            ]),
            removeThis() {
                this.$store.commit('removeRow', this.rowKey);
            },
            updateValue(key, value) {
                this.$set(this.copiedRowData, key, value);
            },
            ajax(_method, url, handler, args) {
                this.$store.state.ajax(_method, url, handler, args);
            },
            destroy() {
                if (this.canDestroy) {
                    this.destroying = true;

                    const outer = this;
                    const method = 'DELETE';
                    const url = this.rowData.destroy_url;

                    const handler = function() {
                        if (this.readyState == 4) {
                            if (this.status == 200 && this.responseURL == url) {
                                outer.removeThis()
                            }
                            outer.destroying = false;
                        }
                    }
                    this.ajax(method, url, handler);
                }
            },
            save() {
                if (this.canSave) {
                    this.updating = true;

                    const outer = this;
                    const primary_key = this.$store.getters.primary_key;
                    const url = this.rowData.created ? this.$store.getters.create_url : this.rowData.update_url;
                    const method = this.rowData.created ? 'POST' : 'PATCH';

                    const handler = function() {
                        if (this.readyState == 4) {
                            try {
                                const response = JSON.parse(this.responseText);
                                outer.errors = {};
                                if (this.status == 200 && this.responseURL == url) {
                                    if (outer.rowData.created) {
                                        outer.$delete(outer.rowData, 'created')
                                        outer.rowData.key = outer.copiedRowData[primary_key];
                                        outer.$delete(outer.copiedRowData, primary_key);
                                    }
                                    outer.updateRow(response);
                                } else if (this.status == 400) {
                                    outer.errors = response;
                                }
                            } catch (e) {}
                            outer.updating = false;
                        }
                    };
                    this.ajax(method, url, handler, this.copiedRowData);
                }
            },
            updateRow(newRowData) {
                this.rowData = Object.assign(this.rowData, newRowData);
                for (const key in this.fields)
                    this.$set(this.copiedRowData, key, this.rowData[key]);
                this.updateChanged()
            },
            emptyStringToNull() {
                for (const field in this.fields)
                    if (this.fields[field].type == 'text' && this.copiedRowData[field] === '')
                        this.copiedRowData[field] = null;
            },
            updateChanged() {
                for (const key in this.fields)
                    if (!object_compare(this.copiedRowData[key], this.rowData[key]))
                        return this.changed = true;
                this.changed = false;
            },
        },
    }
</script>
