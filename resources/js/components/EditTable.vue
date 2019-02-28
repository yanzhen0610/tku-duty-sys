<template>
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
</template>

<script>
    import EditTableRow from './EditTableRow.vue';
    import { mapGetters } from 'vuex';

    export default {
        components: {
            'edit-table-row': EditTableRow,
        },
        computed: mapGetters([
            'i18n',
            'rows',
            'fields',
            'editable',
            'primary_key',
        ]),
    };
</script>
