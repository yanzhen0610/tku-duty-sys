
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

Vue.component('edit-table', require('./edit-table/EditTable.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

window.make_edit_table = function (el, edit_table_data) {
    // don't manipulate the input data
    const data = JSON.parse(JSON.stringify(edit_table_data));
    // preprocess data
    data.rows.forEach((value, index, array) => array[index] = {
        // for the `v-for` binding key
        id: index,
        // the original data
        row_data: value,
    });

    const store = new Vuex.Store({
        state: {
            // read only or editable
            editable: data.editable,

            // show delete button
            destroyable: data.destroyable,

            // show create button and allow to send create request
            create_url: data.create_url,

            // to determine is row exists in database
            primary_key: data.primary_key,

            // fields for showing
            fields: data.fields,

            // rows of data
            rows: data.rows,

            // lang
            ui_i18n: data.ui_i18n,

            // ID for new row
            next_id: data.rows.length,

            /**
             * request function
             * the caller should not use the fields `_method` and `_token`
             * @param {String} method request method
             * @param {String} url request url
             * @param {Function} handler handler that passed to onreadystatechange
             * @param {Object} args request arguments
             */
            ajax(method, url, handler, args) {
                // get the CSRF token from the metadata
                const csrf_token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const request = new XMLHttpRequest();

                // set the handler
                request.onreadystatechange = handler;
                // use POST method to send JSON data
                request.open('POST', url, true);
                // set the request `content-type` header
                request.setRequestHeader('Content-Type', 'application/json');
                // send the request here
                request.send(JSON.stringify({
                    // expanded the request arguments
                    ...args,
                    // CSRF token
                    _token: csrf_token,
                    // the real request method
                    _method: method,
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
            create_new_row(state) {
                state.rows.push({
                    id: state.next_id++,
                    row_data: new Object(),
                });
            },
            update_row_data(state, args) {
                Vue.set(state.rows, args.index, {
                    // the original data
                    ...state.rows[args.index],
                    // overwrite them by keys
                    ...args.new_row_data,
                });
            },
            remove_row(state, index) {
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
