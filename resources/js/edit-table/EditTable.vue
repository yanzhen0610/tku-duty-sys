<template>
  <div>
    <div class="scrollable-x">
      <div class="content">
        <table class="table">
          <thead>
            <tr>
              <th v-for="(field, index) in fields"
                v-bind:key="index"
              >{{ i18n[field.name] }}</th>
              <th v-if="editable"
              >{{ i18n['save'] }}</th>
              <th v-if="destroyable"
              >{{ i18n['delete'] }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th v-for="(field, index) in fields"
                v-bind:key="index"
              >{{ i18n[field.name] }}</th>
              <th v-if="editable"
              >{{ i18n['save'] }}</th>
              <th v-if="destroyable"
              >{{ i18n['delete'] }}</th>
            </tr>
          </tfoot>
          <edit-table-row
            v-for="(row_data, index) in rows"
            v-bind:key="row_data.id"
            v-bind:index="index"
            v-model="rows[index]"
          ></edit-table-row>
        </table>
      </div>
    </div>

    <a class="button is-success is-outlined is-rounded"
      v-if="create_url"
      @click="create_new_row()"
    >
      <span class="icon is-small">
        <i class="fas fa-plus"></i>
      </span>
      <span>{{ i18n['create'] }}</span>
    </a>
  </div>
</template>

<style scoped>
.table {
  white-space: nowrap;
}

.scrollable-x {
  max-width: 100%;
  position: relative;
  overflow-x: auto;
}

.scrollable-x .content {
  float: none;
  display: inline-block;
}
</style>

<script>
import EditTableRow from './EditTableRow.vue';
import { mapGetters, mapMutations } from 'vuex';

export default {
  components: {
    'edit-table-row': EditTableRow,
  },
  computed: {
    ...mapGetters([
      'i18n',
      'rows',
      'fields',
      'editable',
      'destroyable',
      'create_url',
    ]),
  },
  methods: {
    ...mapMutations([
      'create_new_row',
    ]),
  },
};
</script>
