<template>
  <div>
    <div class="side-view">
      <div class="content configs">
        <div class="field is-horizontal">
          <div class="field-label is-normal">
            <label class="label">{{ i18n['date_from'] }}:</label>
          </div>
          <div class="field-body">
            <div class="field is-horizontal">
              <datepicker v-model="from_date"
                v-bind:format="'yyyy-MM-dd'"
                v-bind:input-class="['input', 'input-width']"
                v-bind:wrapper-class="['control', 'fix-datepicker']"
              ></datepicker>
            </div>

            <div class="field is-horizontal">
              <div class="field-label is-normal">
                <label class="label">{{ i18n['date_to'] }}:</label>
              </div>
              <datepicker v-model="to_date"
                v-bind:format="'yyyy-MM-dd'"
                v-bind:input-class="['input', 'input-width']"
                v-bind:wrapper-class="['control', 'fix-datepicker']"
              ></datepicker>
            </div>

            <div class="field">
              <div class="field-label">
                <button v-on:click="fetch_shifts_arrangements_data(from_date, to_date)"
                  class="button is-primary"
                >{{ i18n['fetch_new_data'] }}</button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="field is-horizontal">
          <div class="field-label is-normal">
            <label class="label">{{ i18n['area'] }}:</label>
          </div>
          <div class="field-body">
            <div class="field">
              <div class="select">
                <select v-model="selectedArea">
                  <option value="__all__">{{ i18n['all'] }}</option>
                  <option v-for="area in areas"
                    v-bind:key="area.uuid"
                    v-bind:selected="selectedArea == area.uuid"
                    v-bind:value="area.uuid"
                  >{{ area.area_name || area.uuid }}</option>
                </select>
              </div>
              
            </div>

            <div class="field-body">
              <a
                v-bind:href="download_table_url"
                class="button is-primary"
              >{{ i18n['download_shifts_arrangements_table'] }}</a>
            </div>
          </div>
        </div>
        <div v-if="current_user && is_admin"
          class="field is-horizontal"
        >
          <div class="field-label is-normal">
            <label class="label">{{ i18n['on_duty_staff'] }}:</label>
          </div>
          <div class="field-body">
            <div class="field">
              <div class="select">
                <select v-model="selectedUser">
                  <option value="__not_selected__"
                    disabled
                  >{{ i18n['select_on_duty_staff'] }}</option>
                  <option v-for="staff in staves"
                    v-bind:key="staff.username"
                    v-bind:selected="selectedUser == staff.username"
                    v-bind:value="staff.username"
                  >{{ staff.display_name || staff.username }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <!-- <div class="message-block">
          <div v-bind:class="{
              'is-info': false,
              'is-success': true,
              'is-warning': false,
            }"
            class="notification"
          >{{ message }}123</div>
        </div> -->
      </div>

      <div class="outer">
        <div class="shifts_arrangements_table inner">
          <table class="full-width">
            <thead>
              <tr>
                <th><div class="table-field week-days-title">{{ i18n['week_days'] }}</div></th>
                <th><div class="table-field weekend-title">{{ i18n['sunday'] }}</div></th>
                <th><div class="table-field weekday-title">{{ i18n['monday'] }}</div></th>
                <th><div class="table-field weekday-title">{{ i18n['tuesday'] }}</div></th>
                <th><div class="table-field weekday-title">{{ i18n['wednesday'] }}</div></th>
                <th><div class="table-field weekday-title">{{ i18n['thursday'] }}</div></th>
                <th><div class="table-field weekday-title">{{ i18n['friday'] }}</div></th>
                <th><div class="table-field weekend-title">{{ i18n['saturday'] }}</div></th>
              </tr>
            </thead>

            <tr v-for="row_data in rows_data"
              v-bind:key="row_data.key"
              v-bind:class="{ 'hoverable-row': row_data.type == 'week-shifts' }">

              <td v-if="row_data.type == 'week-shifts'">

                <div class="table-field table-field-side">
                  <span v-bind:class="{
                      'lock-open-icon-wrapper': !row_data.is_locked,
                      'lock-icon-wrapper': row_data.is_locked,
                      'cursor-pointer': !read_only,
                    }"
                    v-on:click="set_lock_state({
                      from_date: row_data.from_date,
                      to_date: row_data.to_date,
                      shift: row_data.shift.uuid,
                      lock: !row_data.is_locked,
                    })"
                    class="icon-wrapper"
                  >
                    <span v-bind:class="{hidden: !row_data.is_locked}"><i class="icon fas fa-lock"></i></span>
                    <span v-bind:class="{hidden: row_data.is_locked}"><i class="icon fas fa-lock-open"></i></span>
                  </span>
                  {{ row_data.shift.shift_name }}
                </div>

              </td>
              <td v-else-if="row_data.type == 'week-day-titles'"
                class="week-days-title">
                <div class="table-field week-days-title">{{ i18n['date'] }}</div>
              </td>

              <td v-for="(day_data, index) in row_data.value"
                v-bind:key="index"
                v-on:click="arrangement_cell_on_click(row_data.shift, day_data.date)"
                v-bind:class="{
                  'cursor-pointer': row_data.type == 'week-shifts' && selectedUser != '__not_selected__',
                  'weekday-title': row_data.type == 'week-day-titles' && day_data.type == 'weekday',
                  'weekend-title': row_data.type == 'week-day-titles' && day_data.type == 'weekend',
                }"
                class="hoverable-data">

                <div v-if="row_data.type == 'week-shifts'"
                  class="table-field"
                >
                  <div class="table-field-side">
                    <span v-bind:class="{
                        'lock-open-icon-wrapper': !day_data.is_locked,
                        'lock-icon-wrapper': day_data.is_locked,
                        'cursor-pointer': !read_only,
                      }"
                      v-on:click="set_lock_state({
                        date: day_data.date,
                        shift: row_data.shift.uuid,
                        lock: !day_data.is_locked,
                      })"
                      class="icon-wrapper"
                    >
                      <span v-bind:class="{hidden: !day_data.is_locked}"><i class="icon fas fa-lock"></i></span>
                      <span v-bind:class="{hidden: day_data.is_locked}"><i class="icon fas fa-lock-open"></i></span>
                    </span>
                  </div>
                  <div class="table-field-side">
                    <p v-for="on_duty_staff in day_data.on_duty_staves"
                      v-bind:key="on_duty_staff.id">
                      {{ on_duty_staff.display_name || on_duty_staff.username }}
                    </p>
                  </div>
                </div>
                <div v-else-if="row_data.type == 'week-day-titles'"
                  v-bind:class="{
                    'weekday-title': day_data.type == 'weekday',
                    'weekend-title': day_data.type == 'weekend',
                  }"
                  class="table-field table-field-side"
                >
                  <span v-bind:class="{
                      'lock-open-icon-wrapper': !day_data.is_locked,
                      'lock-icon-wrapper': day_data.is_locked,
                      'cursor-pointer': !read_only,
                    }"
                    v-on:click="set_lock_state({
                      date: day_data.title,
                      shifts: displaying_shifts_uuids,
                      lock: !day_data.is_locked,
                    })"
                    class="icon-wrapper"
                  >
                    <span v-bind:class="{hidden: !day_data.is_locked}"><i class="icon fas fa-lock"></i></span>
                    <span v-bind:class="{hidden: day_data.is_locked}"><i class="icon fas fa-lock-open"></i></span>
                  </span>
                  {{ day_data.title }}
                </div>

              </td>

            </tr>
          </table>
        </div>
      </div>

    </div>

    <div class="side-view">
      <table class="table">
        <thead>
          <th>{{ i18n['on_duty_staff'] }}</th>
          <th>{{ i18n['mobile_ext'] }}</th>
        </thead>
        <tfoot>
          <th>{{ i18n['on_duty_staff'] }}</th>
          <th>{{ i18n['mobile_ext'] }}</th>
        </tfoot>
        <tr v-for="staff in on_duty_staves"
          v-bind:key="staff.username"
        >
          <td>{{ staff.display_name ? staff.display_name : staff.username}}</td>
          <td>{{ staff.mobile_ext }}</td>
        </tr>
      </table>
    </div>
  </div>
</template>

<style>
.hidden {
  display: none;
}

.icon {
  color: rgba(0, 0, 0, 0.2);
  position: relative;
}

.fa-lock {
  left: 0.35rem;
}

.fa-lock-open {
  left: 0.25rem;
}

.icon-wrapper {
  border-radius: 50%;
  display: inline-block;
  width: 1.6rem;
  height: 1.6rem;
}

.lock-icon-wrapper {
  background: rgba(255, 188, 188, 0.7);
}

.lock-open-icon-wrapper {
  background: rgba(188, 255, 188, 0.7);
}

.table-field {
  height: 100%;
}

.table-field-side {
  float: none;
  display: inline-block;
  vertical-align: middle;
}

.shifts_arrangements_table th,
.shifts_arrangements_table td {
  border: 1px solid;
  border-color: dimgray;
  white-space: nowrap;
  vertical-align: middle;
}

.shifts_arrangements_table td .table-field,
.shifts_arrangements_table th .table-field {
  padding: 0.5rem;
}

.weekday-title {
  background-color: rgb(248, 205, 161);
}

.shifts_arrangements_table td .weekend-title,
.weekend-title {
  background-color: rgb(217, 217, 217);
}

.shifts_arrangements_table th .weekend-title {
  background-color: rgb(192, 192, 192);
}

.shifts_arrangements_table td .week-days-title,
.shifts_arrangements_table th .week-days-title,
.week-days-title {
  background-color: rgb(163, 203, 250);
}

.shifts_arrangements_table {
  margin: 1rem;
}

.outer {
  max-width: 100%;
  position: relative;
  overflow-x: auto;
}

.outer .inner {
  float: none;
  display: inline-block;
}

.message-block {
  margin: 1.5rem;
}

.configs {
  max-width: 48rem;
  white-space: nowrap;
}

.fix-datepicker {
  white-space: normal;
}

.cursor-pointer {
  cursor: pointer;
}

.hoverable-row:hover td {
  background-color: rgba(0, 0, 0, 0.2);
}

.hoverable-row:hover .hoverable-data:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.input-width {
  width: auto;
  max-width: 10rem;
}

.full-width {
  width: 100%;
}

.side-view {
  float: left;
  max-width: 100%;
}

.side-view + .side-view {
  margin-left: 3rem;
}
</style>

<script>
  import { mapState, mapGetters, mapMutations } from 'vuex';
  import Datepicker from 'vuejs-datepicker';

  export default {
    components: {
      Datepicker,
    },
    data() {
      var data = {
        selectedUser: '__not_selected__',
        selectedArea: '__all__',
        from_date: this.$store.state.duration.from_date,
        to_date: this.$store.state.duration.to_date,
      };
      if (this.$store.state.current_user &&
        !this.$store.state.is_admin) {
        data.selectedUser = this.$store.state.current_user.username;
      }
      return data;
    },
    watch: {
      from_date: {
        handler(value) {
          if (value instanceof Date)
            this.from_date = this.format_date(value);
        },
      },
      to_date: {
        handler(value) {
          if (value instanceof Date)
            this.to_date = this.format_date(value);
        },
      },
    },
    computed: {
      ...mapState([
        'is_admin',
        'current_user',
        'read_only',
        'i18n',
        'areas',
        'shifts',
        'staves',
        'duration',
      ]),
      ...mapGetters([
        'from_week',
        'to_week',
        'on_duty_staves',
      ]),
      read_only() {
        return this.$store.state.read_only;
      },
      displaying_shifts_uuids() {
        if (this.selectedArea == '__all__')
          return this.$store.state.shifts.map(x => x.uuid);
        let selected_area_uuid = this.selectedArea;
        let selected_area = this.$store.state.areas.find(
          x => x.uuid == selected_area_uuid
        );
        return selected_area.shifts.map(x => x.uuid);
      },
      download_table_url() {
        let params = {
          from_date: this.from_date,
          to_date: this.to_date,
        };
        if (this.selectedArea != '__all__')
          params.area = this.selectedArea;
        let query = Object.keys(params).map(
          k => encodeURIComponent(k) + '=' + encodeURIComponent(params[k])
        ).join('&');
        return this.$store.state.download_table_url + '?' + query;
      },
      rows_data() {
        let rows_data = new Array();
        let locks = this.$store.state.locks;

        for (var week = this.$store.getters.from_week; week <= this.$store.getters.to_week; ++week)
        {
          let shifts = this.$store.state.shifts;
          if (this.selectedArea && this.selectedArea != '__all__')
            shifts = this.$store.getters.get_area_by_uuid(this.selectedArea).shifts;

          let week_days_titles = new Array();
          for (var week_day = 0; week_day < 7; ++week_day)
          {
            let date = this.format_date(new Date((week * 7 + week_day - 4) * 86400000));
            week_days_titles.push({
              title: date,
              type: week_day == 0 || week_day == 6 ? 'weekend' : 'weekday',
              is_locked: shifts.every(x => locks[x.uuid][date]),
            });
          }

          rows_data.push({
            key: 'week-' + week,
            type: 'week-day-titles',
            value: week_days_titles,
            is_locked: week_days_titles.every(x => x.is_locked),
          });

          for (let shift of shifts)
          {
            let week_shifts_arrangements = new Array();
            for (var i = 0; i < 7; ++i)
            {
              let date = week_days_titles[i].title;
              let is_locked = locks[shift.uuid][date];

              week_shifts_arrangements.push({
                date,
                is_locked,
                on_duty_staves: new Array(),
              });
            }

            let arrangements = this.$store.getters.get_shifts_arrangements_by_week_and_shift(week, shift.uuid);
            for (let arrangement of arrangements)
              week_shifts_arrangements[(arrangement.day + 4) % 7].on_duty_staves.push(arrangement.on_duty_staff);

            rows_data.push({
              key: 'week-shift-' + week + '-' + shift.uuid,
              type: 'week-shifts',
              shift: shift,
              value: week_shifts_arrangements,
              from_date: week_shifts_arrangements[0].date,
              to_date: week_shifts_arrangements[week_shifts_arrangements.length - 1].date,
              is_locked: week_shifts_arrangements.every(x => x.is_locked),
            });
          }
        }
        return rows_data;
      },
    },
    methods: {
      ...mapMutations([
      ]),
      set_lock_state(data) {
        if (this.read_only) return;

        const outer = this;
        const method = this.$store.state.locks_crud.update.method;
        const url = this.$store.state.locks_crud.update.url;

        const handler = function() {
          if (this.readyState == 4 && this.responseURL == url) {
            try {
              const response = JSON.parse(this.responseText);
              if (this.status == 200) {
                outer.update_locks(response);
              }
            } catch (e) {}
          }
        };

        this.$store.state.ajax(
          method,
          url,
          handler,
          data
        );
      },
      update_locks(locks) {
        this.$store.commit('update_locks', locks);
      },
      arrangement_cell_on_click(shift, date) {
        if (this.read_only) return;

        if (shift && date && this.selectedUser && 
            this.selectedUser != '__not_selected__') {
          let arrangement = this.$store.getters.get_shifts_arrangements_by_shift_and_date_and_staff(shift.uuid, date, this.selectedUser);
          if (arrangement)
            this.remove_shift_arrangement_request(arrangement);
          else
            this.add_shift_arrangement_request(shift, date);
        }
      },
      add_shift_arrangement_request(shift, date) {
        if (this.read_only) return;

        const outer = this;
        const method = this.$store.state.crud.create.method;
        const url = this.$store.state.crud.create.url;

        const handler = function() {
          if (this.readyState == 4) {
            try {
              const response = JSON.parse(this.responseText);
              if ((this.status == 200 || this.status == 201)
                  && this.responseURL == url) {
                outer.append_shift_arrangement(response);
              } else {}
            } catch (e) {}
          }
        };

        this.$store.state.ajax(
          method,
          url,
          handler,
          {
            shift: shift.uuid,
            on_duty_staff: this.selectedUser,
            date: date,
          }
        );
      },
      remove_shift_arrangement_request(arrangement) {
        if (this.read_only) return;

        const outer = this;
        const handler = function() {
          if (this.readyState == 4) {
            if (this.status == 200 || this.status == 204) {
              outer.remove_shift_arrangement(arrangement.uuid);
            }
          }
        };
        this.$store.state.ajax(
          this.$store.state.crud.delete.method,
          this.$store.state.crud.delete.url + arrangement.uuid,
          handler
        );
      },
      append_shift_arrangement(arrangement) {
        this.$store.commit('append_shift_arrangement', arrangement);
      },
      remove_shift_arrangement(arrangement_uuid) {
        this.$store.commit('remove_shift_arrangement', arrangement_uuid);
      },
      get_shifts_arrangements_by_week_and_shift(week, shift_uuid) {
        return this.$store.state.shifts_arrangements.filter(v => v.week == week && v.shift.uuid == shift_uuid)
      },
      format_date(date_object) {
        let yyyy = '' + date_object.getFullYear();
        var mm = date_object.getMonth() + 1;
        var dd = date_object.getDate();
        if (mm < 10) mm = '0' + mm;
        if (dd < 10) dd = '0' + dd;
        return yyyy + '-' + mm + '-' + dd;
      },
      fetch_shifts_arrangements_data(from_date, to_date) {
        this.$store.commit('fetch_shifts_arrangements_data', {
          from_date,
          to_date,
        });
        this.$store.commit('fetch_locks_data', {
          from_date,
          to_date,
        });
        this.$store.commit('set_duration', {
          from_date,
          to_date,
        });
      },
    },
  };
</script>
