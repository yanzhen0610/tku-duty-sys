
// /**
//  * First we will load all of this project's JavaScript dependencies which
//  * includes Vue and other libraries. It is a great starting point when
//  * building robust, powerful web applications using Vue and Laravel.
//  */

require('./bootstrap');

import Vue from 'vue';
import Vuex from 'vuex';

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.use(Vuex);

Vue.component('shifts-arrangements-table', require('./components/ShiftsArrangementsTable.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

window.make_shifts_arrangements_table = function (el, data) {
    const preprocess_arrangement = v => {
        v.day = Math.trunc(Date.parse(v.date) / 86400000);
        v.week = Math.trunc((v.day + 4) / 7);
    };
    data.shifts_arrangements.forEach(preprocess_arrangement);
    const store = new Vuex.Store({
        state: {
            ...data,
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
        },
        getters: {
            shifts_arrangements: (state) => state.shifts_arrangements,
            from_week: (state) => Math.trunc((Date.parse(
                    state.duration.from_date) / 86400000 + 4) / 7),
            to_week: (state) => Math.trunc((Date.parse(
                    state.duration.to_date) / 86400000 + 4) / 7),
            get_area_by_uuid: (state) => (area_uuid) => state.areas.find(v => v.uuid == area_uuid),
            get_shifts_arrangements_by_week_and_shift: (state) => (week, shift_uuid) => state.shifts_arrangements.filter(v => v.week == week && v.shift.uuid == shift_uuid),
            get_shifts_arrangements_by_shift_and_date_and_staff: (state) => (shift_uuid, date, staff_username) => state.shifts_arrangements.find(v => v.shift.uuid == shift_uuid && v.date == date && v.on_duty_staff.username == staff_username),
        },
        mutations: {
            set_duration(state, duration) {
                Vue.set(state, 'duration', duration);
            },
            append_shift_arrangement(state, arrangement) {
                preprocess_arrangement(arrangement);
                state.shifts_arrangements.push(arrangement);
            },
            remove_shift_arrangement(state, arrangement_uuid) {
                delete state.shifts_arrangements.splice(
                    state.shifts_arrangements.findIndex(
                        v => v.uuid == arrangement_uuid
                    ),
                    1
                );
            },
            fetch_shifts_arrangements_data(state, args) {
                const handler = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        try {
                            const response = JSON.parse(this.responseText);
                            response.forEach(preprocess_arrangement);
                            Vue.set(state, 'shifts_arrangements', response);
                        } catch (e) {}
                    }
                };

                state.ajax(
                    state.crud.read.method,
                    state.crud.read.url,
                    handler,
                    args
                );
            },
        },
    });
    return new Vue({
        el: el,
        store: store,
        template: `<shifts-arrangements-table></shifts-arrangements-table>`,
    });
}
