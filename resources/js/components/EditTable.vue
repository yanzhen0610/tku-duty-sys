<template>
    <div>
    <table class="table">
        <thead>
            <tr>
                <th>{{ i18n[primary_key] }}</th>
                <th v-for="(_, key) in fields"
                    v-bind:key="key"
                >{{ i18n[key] }}</th>
                <th v-if="editable"
                >{{ i18n['save'] }}</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>{{ i18n[primary_key] }}</th>
                <th v-for="(_, key) in fields"
                    v-bind:key="key"
                >{{ i18n[key] }}</th>
                <th v-if="editable"
                >{{ i18n['save'] }}</th>
            </tr>
        </tfoot>
        <edit-table-row
            v-for="row in rows"
            v-bind:key="row.key"
            v-bind:row="row"
        ></edit-table-row>
    </table>

    <a class="button is-success is-outlined is-rounded"
        v-if="create_url"
        @click="createNewRow()"
    >
        <span class="icon is-small">
            <i class="fas fa-plus"></i>
        </span>
        <span>{{ i18n['create'] }}</span>
    </a>

    </div>
</template>

<script>
    import EditTableRow from './EditTableRow.vue';
    import { mapGetters, mapMutations } from 'vuex';

    export default {
        components: {
            'edit-table-row': EditTableRow,
        },
        computed: mapGetters([
            'i18n',
            'rows',
            'fields',
            'editable',
            'create_url',
            'primary_key',
        ]),
        methods: {
            ...mapMutations([
                'createNewRow',
            ]),
        },
    };
</script>
