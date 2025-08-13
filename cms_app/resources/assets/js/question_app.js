
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

import question_add from './components/admin/question/question_add'

const app = new Vue({
    el: '#app',
    components: {question_add},
    data: {
        drawer: null,
    },
    created(){
        
    },
    methods: {
        
    } //end of method
});
