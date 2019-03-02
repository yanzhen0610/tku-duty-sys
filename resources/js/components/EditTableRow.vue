<template>
    <tr>
        <th>
            <div v-if="row.created">
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
            <a v-else-if="row.show_url"
                v-bind:href="row.show_url"
            >{{ row.key }}</a>
            <span v-else>{{ row.key }}</span>
        </th>
        <td v-for="(type, key) in fields"
            v-bind:key="key"
        >
            <div v-if="editable || type == 'button-link'">
                <span v-if="type == 'button-link'">
                    <a v-if="row[key]"
                        class="button is-primary"
                        @click="ajax(row[key].method, row[key].url)"
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
            <span v-else>{{ row[key] }}</span>
        </td>
        <td v-if="editable && (row.created || row.update_url)">
            <a class="button is-success"
                v-bind:class="{ 'is-loading': updating }"
                v-bind:disabled="!canSave"
                @click="save()">
                <span class="icon is-small">
                    <i class="fas fa-check"></i>
                </span>
                <span>{{ i18n['save'] }}</span>
            </a>
        </td>
    </tr>
</template>

<script>
    import EditTableCheckBox from './EditTableCheckBox.vue';
    import { mapGetters } from 'vuex';

    export default {
        props: {
            row: {
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
                copiedRowData[key] = this.row[key];
            return {
                copiedRowData,
                canSave: false,
                changed: false,
                updating: false,
                errors: {},
            };
        },
        computed: {
            ...mapGetters([
                'i18n',
                'fields',
                'editable',
                'create_url',
                'primary_key',
            ]),
            fields() {
                return this.$store.getters.fields;
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
            row: {
                handler() {
                    this.updateChanged();
                },
                deep: true,
            },
            changed() {
                this.updateCanSave();
            },
            updating() {
                this.updateCanSave();
            },
        },
        methods: {
            updateValue(key, value) {
                this.copiedRowData[key] = value;
            },
            ajax(_method, url) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const request = new XMLHttpRequest();
                request.open('POST', url, true);
                request.setRequestHeader('Content-Type', 'application/json');
                request.send(JSON.stringify({
                    _method,
                    _token: csrfToken,
                }));
            },
            save() {
                if (this.canSave) {
                    this.updating = true;
                    const outer = this;
                    const primary_key = this.$store.getters.primary_key;
                    const url = this.row.created ? this.$store.getters.create_url : this.row.update_url;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const _method = this.row.created ? 'POST' : 'PATCH';
                    const request = new XMLHttpRequest();

                    request.onreadystatechange = function() {
                        if (this.readyState == 4) {
                            try {
                                const response = JSON.parse(this.responseText);
                                outer.errors = {};
                                if (this.status == 200 && this.responseURL == url) {
                                    if (outer.row.created) {
                                        delete outer.row.created;
                                        outer.row.key = outer.copiedRowData[primary_key];
                                        delete outer.copiedRowData[primary_key];
                                    }
                                    outer.row = Object.assign(outer.row, response);
                                    for (const key in outer.fields)
                                        outer.copiedRowData[key] = outer.row[key];
                                } else if (this.status == 400) {
                                    outer.errors = response;
                                }
                            } catch(e) {}
                            outer.updating = false;
                        }
                    };
                    request.open('POST', url, true);
                    request.setRequestHeader('Content-Type', 'application/json');
                    request.send(JSON.stringify({
                        _method: _method,
                        _token: csrfToken,
                        ...this.copiedRowData,
                    }));
                }
            },
            emptyStringToNull() {
                for (const field in this.fields)
                    if (this.fields[field] == 'text' && this.copiedRowData[field] === '')
                        this.copiedRowData[field] = null;
            },
            updateChanged() {
                for (const key in this.fields)
                    if (this.copiedRowData[key] != this.row[key])
                        return this.changed = true;
                this.changed = this.row.created && this.copiedRowData[this.$store.getters.primary_key];
            },
            updateCanSave() {
                this.canSave = !this.updating && this.changed;
            },
        },
    }
</script>
