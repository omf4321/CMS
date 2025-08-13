
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

Vue.use(require('vue-cookies'))
import VueCookies from 'vue-cookies'
Vue.use(VueCookies)
// set default config
VueCookies.config('7d')

// Vue.component('admin-home', require('./components/admin/admin-home.vue'));

import router from './user_router'

Vue.mixin({
    data: function() {
        return {
            
        }
    },
    methods: {
        // those method only works on tamplate not in another vue component method
        check_permission(permission){
            let p = window_admin_permissions.findIndex(x => x.name == permission);
            if (p>-1 || window_admin_role == "superadmin" || window_admin_role == "admin") {return true} else {return false}
        },
        is_admin(){
            if (window_admin_role=="superadmin" || window_admin_role == "admin") {return true} else{return false}
        },
        is_not_admin(){
            if (window_admin_role == "superadmin" || window_admin_role == "admin") {return false} else {return true}
        },
        is_super_admin(){
            if (window_admin_role=="superadmin") {return true} else {return false}
        },
        has_role(role){
            var r = window_user_role.findIndex(x => x == role);
            if (r > -1) {return true}
            return false
        }
    }//end of method
})

const app = new Vue({
    el: '#app',
    router,
    data: {
        drawer: null,
          csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          user_role: window_user_role,
          menus: [],
          teacher_menu: [
                            {name:'Home Work',icon:'person',route_name:'home_work'},
                            {name:'Document',icon:'list',route_name:'document'},
                            {name:'Activity',icon:'search',route_name:'teacher_activity'}
                        ],
          student_menu: [
                            {name:'Home Work',icon:'person',route_name:'home_work'},
                            {name:'Document',icon:'list',route_name:'document'}
                        ],
          invigilator_menu: [
                                {name:'Document',icon:'list',route_name:'document'}
                            ]
    },
    created(){
        if (this.user_role.name=='teacher') {
            this.menus = this.teacher_menu;
        }
        else if (this.user_role.name=='student') {
            this.menus = this.student_menu;
        }
        else if (this.user_role.name=='invigilator') {
            this.menus = this.invigilator_menu;
        }
    },
    methods: {
        logout(){
            this.$refs.logout_form.submit()
        },
        check_permission(permission){
            let p = window_admin_permissions.findIndex(x => x.name==permission);
            if (p>-1) {return true} else{return false}
        },
        get_route(permission){
            let p = window_admin_permissions.findIndex(x => x.name==permission);
            if (p>-1) {return window_admin_permissions[p].route_name}
        }

    }
});
