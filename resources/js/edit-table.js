
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
    const store = new Vuex.Store({
        state: {
            'data': data,
        },
        getters: {
            data: (state) => state.data,
            i18n: (state) => state.data.ui_i18n,
            rows: (state) => state.data.rows,
            fields: (state) => state.data.fields,
            editable: (state) => state.data.editable,
            primary_key: (state) => state.data.primary_key,
        }
    });
    return new Vue({
        el: el,
        store: store,
        template: `<edit-table></edit-table>`,
    });
}
