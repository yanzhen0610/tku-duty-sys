<template>
    <tr>
        <th><a :href="row.show_url">{{ row.key }}</a></th>
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
        <td v-if="editable && row.update_url">
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
            ]),
            changed() {
                for (const key in this.$store.getters.fields)
                    if (this.copiedRowData[key] != this.row[key])
                        return true;
                return false;
            },
            canSave() {
                return !this.updating && this.changed;
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
                            outer.updating = false;
                            if (this.status === 200) {
                                Object.assign(outer.row, outer.copiedRowData);
                            }
                        }
                    }
                    request.open('POST', this.row.update_url, true);
                    request.setRequestHeader('Content-Type', 'application/json');
                    request.send(JSON.stringify({
                        _method: 'PATCH',
                        _token: document.querySelector("meta[name='csrf-token']").getAttribute('content'),
                        ...this.copiedRowData,
                    }));
                }
            }
        },
    }
</script>
