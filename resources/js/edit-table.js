
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

Vue.component('edit-table-row', require('./components/EditTableRow.vue').default);
Vue.component('edit-table', require('./components/EditTable.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

window.make_edit_table = function (el, data) {
    data.rows.forEach((v, i) => v.editTableRowIndex = i);
    const store = new Vuex.Store({
        state: {
            ...data,
            rowsLastId: data.rows.length,
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
            i18n: (state) => state.ui_i18n,
            rows: (state) => state.rows,
            fields: (state) => state.fields,
            editable: (state) => state.editable,
            destroyable: (state) => state.destroyable,
            create_url: (state) => state.create_url,
            primary_key: (state) => state.primary_key,
        },
        mutations: {
            createNewRow(state) {
                state.rows.push({editTableRowIndex: state.rowsLastId++, key: undefined, created: true});
            },
            removeRow(state, index) {
                delete state.rows.splice(index, 1);
            },
        },
    });
    return new Vue({
        el: el,
        store: store,
        template: `<edit-table></edit-table>`,
    });
}
