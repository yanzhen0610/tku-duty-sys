<template>
    <div>
        <div>
            <div class="field is-horizontal">
                <div class="field-label is-normal">
                    <label class="label">{{ i18n['date_from'] }}:</label>
                </div>
                <div class="field-body">
                    <datepicker v-model="from_date"
                        v-bind:format="'yyyy-MM-dd'"
                        v-bind:input-class="['input', 'input-width']"
                        v-bind:wrapper-class="['control']"
                    ></datepicker>

                <div class="field-label is-normal">
                    <label class="label">{{ i18n['date_to'] }}:</label>
                </div>
                    <div class="field">
                        <datepicker v-model="to_date"
                            v-bind:format="'yyyy-MM-dd'"
                            v-bind:input-class="['input', 'input-width']"
                            v-bind:wrapper-class="['control']"
                        ></datepicker>
                    </div>
                </div>
            </div>
            <div class="field is-horizontal">
                <div class="field-label"></div>
                <div class="field-body">
                    <div class="field">
                        <div class="control">
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
                </div>
            </div>
            <div class="field is-horizontal">
                <div class="field-label is-normal">
                    <label class="label">{{ i18n['on_duty_staff'] }}:</label>
                </div>
                <div class="field-body">
                    <div class="field">
                        <div class="select">
                            <select v-model="selectedUser">
                                <option v-bind:value="selectedUser"
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
        </div>

        <div>
            <table>
                <thead>
                    <tr>
                        <th>{{ i18n['week_days'] }}</th>
                        <th>{{ i18n['sunday'] }}</th>
                        <th>{{ i18n['monday'] }}</th>
                        <th>{{ i18n['tuesday'] }}</th>
                        <th>{{ i18n['wednesday'] }}</th>
                        <th>{{ i18n['thursday'] }}</th>
                        <th>{{ i18n['friday'] }}</th>
                        <th>{{ i18n['saturday'] }}</th>
                    </tr>
                </thead>

                <tr v-for="row_data in rows_data"
                    v-bind:key="row_data.key"
                    v-bind:class="{ 'hoverable-row': row_data.type == 'week-shifts' }">

                    <td v-if="row_data.type == 'week-shifts'">
                        {{ row_data.shift.shift_name }}
                    </td>
                    <td v-else-if="row_data.type == 'week-day-strings'">
                        {{ i18n['date'] }}
                    </td>

                    <td v-for="(day_data, index) in row_data.value"
                        v-bind:key="index"
                        v-on:click="arrangement_cell_on_click(row_data.shift, day_data.date)"
                        class="hoverable-data">
                        <div v-if="row_data.type == 'week-shifts'">
                            <p v-for="on_duty_staff in day_data.on_duty_staves"
                                v-bind:key="on_duty_staff.id">
                                {{ on_duty_staff.display_name }}
                            </p>
                        </div>
                        <div v-else-if="row_data.type == 'week-day-strings'">
                            {{ day_data }}
                        </div>
                    </td>

                </tr>
            </table>
        </div>
    </div>
</template>

<style>
th, td {
    padding: 0.5rem;
    border: 2px solid;
    white-space: nowrap;
}

.hoverable-row:hover td {
  background-color: rgba(0, 0, 0, 0.2);
}

.hoverable-row:hover .hoverable-data:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.input-width {
    width: auto
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
            return {
                selectedUser: '__selecting__',
                selectedArea: '__all__',
                from_date: this.$store.state.duration.from_date,
                to_date: this.$store.state.duration.to_date,
            };
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
                'i18n',
                'areas',
                'shifts',
                'staves',
                'duration',
            ]),
            ...mapGetters([
                'from_week',
                'to_week',
            ]),
            rows_data() {
                let rows_data = new Array();
                for (var week = this.$store.getters.from_week; week <= this.$store.getters.to_week; ++week)
                {
                    let week_days_strings = new Array();
                    for (var week_day = 0; week_day < 7; ++week_day)
                        week_days_strings.push(
                            this.format_date(
                                new Date(
                                    (week * 7 + week_day - 4) * 86400000
                                )
                            )
                        );

                    rows_data.push({
                        key: 'week-' + week,
                        type: 'week-day-strings',
                        value: week_days_strings,
                    });

                    var shifts = this.$store.state.shifts;
                    if (this.selectedArea && this.selectedArea != '__all__')
                        shifts = this.$store.getters.get_area_by_uuid(this.selectedArea).shifts;

                    for (let shift of shifts)
                    {
                        let week_shifts_arrangements = new Array();
                        for (var i = 0; i < 7; ++i)
                            week_shifts_arrangements.push({
                                date: week_days_strings[i],
                                on_duty_staves: new Array(),
                            });

                        let arrangements = this.$store.getters.get_shifts_arrangements_by_week_and_shift(week, shift.uuid);
                        for (let arrangement of arrangements)
                            week_shifts_arrangements[(arrangement.day + 4) % 7].on_duty_staves.push(arrangement.on_duty_staff);

                        rows_data.push({
                            key: 'week-shift-' + week + '-' + shift.uuid,
                            type: 'week-shifts',
                            shift: shift,
                            value: week_shifts_arrangements,
                        });
                    }
                }
                return rows_data;
            },
        },
        methods: {
            ...mapMutations([
            ]),
            arrangement_cell_on_click(shift, date) {
                if (shift && date && this.selectedUser && 
                        this.selectedUser != '__selecting__') {
                    let arrangement = this.$store.getters.get_shifts_arrangements_by_shift_and_date_and_staff(shift.uuid, date, this.selectedUser);
                    if (arrangement)
                        this.remove_shift_arrangement_request(arrangement);
                    else
                        this.add_shift_arrangement_request(shift, date);
                }
            },
            add_shift_arrangement_request(shift, date) {
                const outer = this;
                const handler = function() {
                    if (this.readyState == 4) {
                        try {
                            const response = JSON.parse(this.responseText);
                            if (this.status == 200 || this.status == 201) {
                                outer.append_shift_arrangement(response);
                            } else if (this.status == 400) {
                            } else {}
                        } catch (e) {}
                    }
                };

                this.$store.state.ajax(
                    this.$store.state.crud.create.method,
                    this.$store.state.crud.create.url,
                    handler,
                    {
                        shift: shift.uuid,
                        on_duty_staff: this.selectedUser,
                        date: date,
                    }
                );
            },
            remove_shift_arrangement_request(arrangement) {
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
                this.$store.commit('set_duration', {
                    from_date,
                    to_date,
                });
            },
        },
    };
</script>
