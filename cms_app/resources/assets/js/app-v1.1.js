
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

import Vue from 'vue'

import Vuetify from 'vuetify'
import 'vuetify/dist/vuetify.min.css'
Vue.use(Vuetify)

import axios from 'axios'
import VueAxios from 'vue-axios'
Vue.use(VueAxios, axios)

Vue.use(require('vue-moment'));

import VueCookies from 'vue-cookies'
Vue.use(VueCookies)
// set default config
VueCookies.config('7d')

// import htmlEditor from './components/admin/question/html-editor'

import router from './router'



Vue.mixin({

    data: function() {
        return {
            reg_no_label: window_reg_no_label
        }
    },
    methods: {
        // those method only works on tamplate not in another vue component method
        check_permission(permission){
            let p = window_admin_permissions.findIndex(x => x.name==permission);
            if (p>-1 || window_admin_role=="superadmin" || window_admin_role == "admin") {return true} else {return false}
        },
        is_admin(){
            if (window_admin_role=="superadmin" || window_admin_role == "admin") {return true} else{return false}
        },
        is_not_admin(){
            if (window_admin_role=="superadmin" || window_admin_role == "admin") {return false} else {return true}
        },
        is_super_admin(){
            if (window_admin_role=="superadmin") {return true} else {return false}
        },        
        has_role(role){
            return false
        },
        allow_field(field){
            if (window_allow_field.includes(field)) {return true}
            return false
        }
    }//end of method
})

const app = new Vue({
    el: '#app',
    router,
    vuetify: new Vuetify(),
    data: {
        drawer: null,
        menus: [],
        role: window_admin_role,
        random: '',
        menuVisible: false
    },
    created(){
        this.change_random()        
    },
    methods: {
        logout(){
            this.$refs.logout_form.submit()
        },
        change_random(){
            Vue.axios.get('/admin/home/change_random_number').then(response => {
                 this.random = response.data.random_number;
            });
        },

    } //end of method
});
