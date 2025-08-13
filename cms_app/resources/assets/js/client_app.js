
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

import router from './client_router'

Vue.mixin({
    data: function() {
        return {
            
        }
    },
    methods: {
        // those method only works on tamplate not in another vue component method
        
    }//end of method
})

const app = new Vue({
    el: '#app',
    router,
    data: {
        drawer: null,
    },
    created(){
        
    },
    mounted(){
        // Echo.channel('chat')
        // .listen('App\\Events\\MessageSent', (e) => {
        //     console.log(e);
        // });
    },
    methods: {
        logout(){
            this.$refs.logout_client_form.submit()
        },
    }
});
