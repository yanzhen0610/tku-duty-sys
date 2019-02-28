<template>
    <tr>
        <th>
            <input v-if="created"
                class="input"
                type="text"
                v-model="copiedRowData[primary_key]"
                required
            >
            <a v-else
                v-bind:href="row.show_url"
            >{{ row.key }}</a>
        </th>
        <td v-for="(type, key) in fields"
            v-bind:key="key"
        >
            <input
                v-if="editable || type == 'checkbox'"
                v-model="copiedRowData[key]"
                v-bind:type="type"
                v-bind:class="{ input: type == 'text' }"
                v-bind:disabled="!editable"
            >
            <span v-else
            >{{ row[key] }}</span>
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
    import { mapGetters } from 'vuex';

    export default {
        props: {
            row: {
                type: Object,
                required: true,
            },
        },
        data() {
            var copiedRowData = {};
            for (const key in this.$store.getters.fields)
                copiedRowData[key] = this.row[key];
            return {
                copiedRowData,
                updating: false,
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
            changed() {
                for (const key in this.$store.getters.fields) {
                    if (this.copiedRowData[key] === '')
                        this.copiedRowData[key] = null;
                    if (this.copiedRowData[key] != this.row[key])
                        return true;
                }
                return this.row.created && this.copiedRowData[this.$store.getters.primary_key];
            },
            canSave() {
                return !this.updating && this.changed;
            },
            created() {
                return this.row.created === true;
            },
        },
        methods: {
            save() {
                if (this.canSave) {
                    this.updating = true;
                    const outer = this;
                    const request = new XMLHttpRequest();
                    request.onreadystatechange = function() {
                        if (this.readyState === 4) {
                            if (this.status === 200) {
                                if (outer.row.created) {
                                    outer.row.created = false;
                                    delete outer.row.created;
                                    const primary_key = outer.$store.getters.primary_key;
                                    outer.row.key = outer.copiedRowData[primary_key];
                                    delete outer.copiedRowData[primary_key];
                                }
                                Object.assign(outer.row, JSON.parse(this.responseText));
                                for (const key in outer.$store.getters.fields)
                                    outer.copiedRowData[key] = outer.row[key];
                            }
                            outer.updating = false;
                        }
                    };
                    request.open('POST', this.row.created ? this.$store.getters.create_url : this.row.update_url, true);
                    request.setRequestHeader('Content-Type', 'application/json');
                    for (const field in this.$store.getters.fields)
                        if (this.$store.getters.fields[field] == 'text' && !this.copiedRowData[field])
                            this.copiedRowData[field] = null;
                    request.send(JSON.stringify({
                        _method: this.row.created ? 'POST' : 'PATCH',
                        _token: document.querySelector("meta[name='csrf-token']").getAttribute('content'),
                        ...this.copiedRowData,
                    }));
                }
            },
        },
    }
</script>
