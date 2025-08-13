import Vue from 'vue'
import Router from 'vue-router'

import start from './components/create_host/create-host.vue'
import step1 from './components/create_host/create-host-1.vue'
import step2 from './components/create_host/create-host-2.vue'
import step3 from './components/create_host/create-host-3.vue'
import step4 from './components/create_host/create-host-4.vue'
import step5 from './components/create_host/create-host-5.vue'
import step6 from './components/create_host/create-host-6.vue'
import step7 from './components/create_host/create-host-7.vue'
import step8 from './components/create_host/create-host-8.vue'
import step9 from './components/create_host/create-host-9.vue'

Vue.use(Router)

var router = new Router({
    mode: 'history',
    base: '/host',
    linkActiveClass: 'active',
    routes: [
        { path: '/', name: 'host', component: start },
        { path: '/step1', name: 'step1', component: step1 },
        { path: '/step2', name: 'step2', component: step2 },
        { path: '/step3', name: 'step3', component: step3 },
        { path: '/step4', name: 'step4', component: step4 },
        { path: '/step5', name: 'step5', component: step5 },
        { path: '/step6', name: 'step6', component: step6 },
        { path: '/step7', name: 'step7', component: step7 },
        { path: '/step8', name: 'step8', component: step8 },
        { path: '/step9', name: 'step9', component: step9 },
        { path: '*', redirect: { name: 'host' } }
    ]
});

export default router