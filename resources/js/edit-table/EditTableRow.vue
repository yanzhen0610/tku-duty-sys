<template>
  <tr>
    <edit-table-field
      v-for="(field, index) in fields"
      v-bind:key="index"
      v-model="editing_data[field.name]"
      v-bind:field_data="field"
      v-bind:errors="errors[field.name]"
      @refresh-row="refresh_row"
    ></edit-table-field>
    <td v-if="editable && (create_url || row_data.update_url)">
      <a class="button is-success"
        v-bind:class="{ 'is-loading': updating }"
        v-bind:disabled="!can_save"
        @click="can_save && save_row()"
      >
        <span class="icon is-small">
          <i class="fas fa-check"></i>
        </span>
        <span>{{ i18n['save'] }}</span>
      </a>
    </td>
    <td v-if="destroyable && row_data.destroy_url">
      <a class="button is-danger is-outlined"
        v-bind:class="{ 'is-loading': destroying }"
        v-bind:disabled="!can_destroy"
        @click="can_destroy && destroy_row()"
      >
        <span>{{ i18n['delete'] }}</span>
        <span class="icon is-small">
          <i class="fas fa-times"></i>
        </span>
      </a>
    </td>
  </tr>
</template>

<style scoped>
.input-text {
  min-width: 8rem;
}
</style>

<script>
import EditTableField from './EditTableField.vue';
import { mapGetters } from 'vuex';

export default {
  props: {
    value: {
      required: true,
      type: Object,
    },
    index: {
      required: true,
      type: Number,
    },
  },
  components: {
    'edit-table-field': EditTableField,
  },
  data() {
    // computed `this.fields` is not accessible here
    const fields = this.$store.getters.fields;

    // preventing from copying redundant fields
    let tmp = new Object();
    for (const field of fields) {
      tmp[field.name] =
        this.value.row_data[field.name];
    }

    // copy the entire object inorder to prevent
    // references to a common object(by looping through fields)
    tmp = JSON.parse(JSON.stringify(tmp));

    // assign field by field in order to avoid
    // redundant data
    const editing_data = new Object();
    for (const field of fields) {
      // use object to wrap data
      // in order to handle null values
      editing_data[field.name] = {
        data: tmp[field.name],
      }
    }

    const data = {
      editing_data,
      updating: false,
      destroying: false,
      requesting: false,
      errors: new Object(),
    };
    return data;
  },
  watch: {
    value: {
      handler(value) {
        let tmp = new Object();
        for (const field of this.fields)
          tmp[field.name] = value.row_data[field.name];

        tmp = JSON.parse(JSON.stringify(tmp));

        for (const field of this.fields)
          this.editing_data[field.name] = {
            data: tmp[field.name],
          }
      },
      deep: true,
    },
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
    row_data: {
      get() {
        return this.value.row_data;
      },
      set(value) {
        this.$set(this.value, 'row_data', value);
      },
    },
    show_url() {
      return this.value.row_data.show_url;
    },
    destroy_url() {
      return this.value.row_data.destroy_url;
    },
    update_url() {
      return this.value.row_data.update_url;
    },
    create_url() {
      return this.$store.getters.create_url;
    },
    primary_key() {
      return this.$store.getters.primary_key;
    },
    unsaved() {
      for (const field of this.fields) {
        const original = this.value.row_data[field.name];
        const current = this.editing_data[field.name].data;
        if (original != current)
          return true;
      }
      return false;
    },
    exists() {
      return this.row_data[this.primary_key] != undefined;
    },
    fields() {
      return this.$store.getters.fields;
    },
    can_save() {
      return !this.requesting && this.unsaved;
    },
    can_destroy() {
      return !this.requesting;
    },
  },
  methods: {
    remove_row() {
      this.$store.commit('remove_row', this.index);
    },
    ajax(method, url, handler, args) {
      this.$store.state.ajax(method, url, handler, args);
    },
    destroy_row() {
      const url = this.destroy_url;
      if (url == null) return;

      this.set_destroying(true);

      const outer = this;
      const method = 'DELETE';

      const handler = function() {
        if (this.readyState == 4) {
          if (this.status == 200 && this.responseURL == url)
            outer.remove_row()
          outer.set_destroying(false);
        }
      }
      this.ajax(method, url, handler);
    },
    save_row() {
      const url = this.exists ? this.update_url : this.create_url;
      if (url == null) return;

      this.set_updating(true);

      const outer = this;
      const method = this.exists ? 'PATCH' : 'POST';
      
      const edited_data = new Object();
      for (const field of this.fields)
        edited_data[field.name] =
          this.editing_data[field.name].data;

      const handler = function() {
        if (this.readyState == 4) {
          try {
            const response = JSON.parse(this.responseText);
            if (this.status == 200 && this.responseURL == url) {
              outer.clear_errors();
              outer.update_row_data(response);
            } else if (this.status == 400) {
              outer.set_errors(response);
            }
          } catch (e) {}
          outer.set_updating(false);
        }
      };
      this.ajax(method, url, handler, edited_data);
    },
    refresh_row() {
      const url = this.show_url;
      if (url == null) return;

      this.set_requesting(true);

      const outer = this;
      const method = 'GET';

      const handler = function() {
        if (this.readyState == 4) {
          try {
            const response = JSON.parse(this.responseText);
            if (this.status == 200 && this.responseURL == url) {
              outer.clear_errors();
              outer.update_row_data(response);
            } else if (this.status == 400) {
              outer.set_errors(response);
            }
          } catch (e) {}
          outer.set_requesting(false);
        }
      };
      this.ajax(method, url, handler);
    },
    update_row_data(new_row_data) {
      this.$set(this.value, 'row_data', new_row_data);
    },
    clear_errors() {
      this.errors = new Object();
    },
    set_errors(errors) {
      this.errors = errors;
    },
    set_requesting(status) {
      this.requesting = status;
    },
    set_updating(status) {
      this.set_requesting(status);
      this.updating = status;
    },
    set_destroying(status) {
      this.set_requesting(status);
      this.destroying = status;
    },
  },
};
</script>
