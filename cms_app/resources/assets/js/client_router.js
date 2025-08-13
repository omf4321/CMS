
import Vue from 'vue'
import Router from 'vue-router'

import not_found from './components/admin/404'
import student_home from './components/client/student_home'
import online_exam from './components/client/online_exam'
import student_profile from './components/client/student_profile'
import student_performance from './components/client/student_performance'
import student_material from './components/client/student_material'

Vue.use(Router)

var router = new Router({
    mode: 'history',
    base: '/client/home',
    linkActiveClass: 'active',
    routes: [
        { path: '/', name: 'student_home', component: student_home },
        { path: '/online_exam/:schedule_id', name: 'online_exam', component: online_exam },
        { path: '/profile', name: 'student_profile', component: student_profile },
        { path: '/performance', name: 'student_performance', component: student_performance },
        { path: '/material', name: 'student_material', component: student_material },
        { path: '/404', name: '404', component: not_found },
        
        { path: '*', redirect: { name: 'student_home' } }
    ]
});

export default router