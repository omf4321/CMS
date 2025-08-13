
import Vue from 'vue'
import Router from 'vue-router'

import not_found from './components/admin/404'
import teacher_home from './components/user/user_home'
import profile from './components/user/profile'

import user_question from './components/user/user_question'
import user_question_list from './components/user/user_question_list'
import question_setup from './components/admin/question/question_setup'
import question_select from './components/admin/question/question_select'
import question_check from './components/admin/question/question_check'

import user_schedule from './components/user/user_schedule'
import authentication from './components/user/authentication'

import user_invigilator_home from './components/user/user_invigilator_home'

import user_script_home from './components/user/user_script_home'

import user_payment from './components/user/user_payment'

Vue.use(Router)

var router = new Router({
    mode: 'history',
    base: '/user/home',
    linkActiveClass: 'active',
    routes: [
        { path: '/', name: 'teacher_dashboard', component: teacher_home },
        { path: '/404', name: '404', component: not_found },
        
        { path: '/profile', name: 'profile', component: profile },

        { path: '/user_question', name: 'user_question', component: user_question },
        { path: '/user_question_list', name: 'user_question_list', component: user_question_list },
        { path: '/question_setup/:id', name: 'question_setup', component: question_setup },
        { path: '/question_select/:id/:type_id', name: 'question_select', component: question_select },
        { path: '/question_check', name: 'question_check', component: question_check },

        { path: '/schedule', name: 'user_schedule', component: user_schedule },
        { path: '/authentication', name: 'authentication', component: authentication },

        { path: '/user_invigilator_home', name: 'user_invigilator_home', component: user_invigilator_home },

        { path: '/user_script_home', name: 'user_script_home', component: user_script_home },

        { path: '/user_payment', name: 'user_payment', component: user_payment },
        
        { path: '*', redirect: { name: 'teacher_dashboard' } }
    ]
});

export default router