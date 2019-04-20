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
        <td v-for="(type, key) in fields"
            v-bind:key="key"
        >
            <div v-if="editable || type == 'button-link'">
                <span v-if="type == 'button-link'">
                    <a v-if="rowData[key]"
                        v-bind:disabled="!rowData[key].url"
                        v-bind:class="{ 'is-loading': rowData[key].isLoading }"
                        class="button is-primary"
                        @click="visitButtonLink(rowData[key])"
                    >{{ i18n[key] }}</a>
                </span>
                <check-box v-else-if="type == 'checkbox'"
                    v-bind:keyName="key"
                    v-bind:value="copiedRowData[key]"
                    @update-value="updateValue"
                >
                </check-box>
                <input v-else
                    v-model="copiedRowData[key]"
                    v-bind:type="type"
                    v-bind:class="{ input: type == 'text', 'is-danger': errors[key] }"
                >
                <p class="help is-danger"
                    v-for="error in errors[key]"
                    v-bind:key="error"
                >{{ error }}</p>
            </div>
            <check-box v-else-if="type == 'checkbox'"
                v-bind:keyName="key"
                v-bind:value="copiedRowData[key]"
            >
            </check-box>
            <span v-else>{{ rowData[key] }}</span>
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
    import EditTableCheckBox from './EditTableCheckBox.vue';
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
            'check-box': EditTableCheckBox,
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
                return !this.updating && !this.destroying && this.changed;
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
            updateValue(key, value, handler) {
                this.copiedRowData[key] = value;
            },
            ajax(_method, url, handler, args) {
                const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const request = new XMLHttpRequest();

                request.onreadystatechange = handler;
                request.open('POST', url, true);
                request.setRequestHeader('Content-Type', 'application/json');
                request.send(JSON.stringify({
                    _method,
                    _token,
                    ...args,
                }));
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
            emptyStringToNull() {
                for (const field in this.fields)
                    if (this.fields[field] == 'text' && this.copiedRowData[field] === '')
                        this.copiedRowData[field] = null;
            },
            updateChanged() {
                for (const key in this.fields)
                    if (this.copiedRowData[key] != this.rowData[key])
                        return this.changed = true;
                this.changed = this.rowData.created && this.copiedRowData[this.$store.getters.primary_key];
            },
        },
    }
</script>
