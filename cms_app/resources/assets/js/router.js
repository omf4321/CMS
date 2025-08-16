
import Vue from 'vue'
import Router from 'vue-router'

import admin_home from './components/admin/admin_home'
// import admin_home from './components/admin/schedule/schedule_manage'
import branch_manager from './components/admin/branch_manager'
import not_found from './components/admin/404'
import admin_user from './components/admin/admin_user'
import admin_role from './components/admin/admin_role'

import setting_echelon from './components/admin/setting/setting_echelon'
import setting_batch from './components/admin/setting/setting_batch'
import setting_branch from './components/admin/setting/setting_branch'
import setting_course from './components/admin/setting/setting_course'
import setting_group from './components/admin/setting/setting_group'
import setting_subject from './components/admin/setting/setting_subject'
import setting_institution from './components/admin/setting/setting_institution'

import student_list from './components/admin/student/student_list'
import student_photo from './components/admin/student/student_upload_photo'
import student_visiting_list from './components/admin/student/student_visiting_list'

import attendance_list from './components/admin/attendance/attendance_list'
// import absent_analysis from './components/admin/attendance/absent_analysis'
// import attendance_analysis from './components/admin/attendance/attendance_analysis'
import individual_attendance from './components/admin/attendance/individual_attendance'

import teacher_list from './components/admin/teacher/teacher_list'

import schedule_manage from './components/admin/schedule/schedule_manage'
// import schedule_planner from './components/admin/schedule/schedule_planner'
// import schedule_chapter from './components/admin/schedule/schedule_chapter'
import schedule_weekly from './components/admin/schedule/schedule_weekly'
import schedule_daily from './components/admin/schedule/schedule_daily'
import schedule_generate from './components/admin/schedule/schedule_generate'
// import schedule_view from './components/admin/schedule/schedule_view'
import schedule_update from './components/admin/schedule/schedule_update'
import schedule_view from './components/admin/schedule/schedule_view'
import schedule_weekly_list from './components/admin/schedule/schedule_weekly_list'
import edit_schedule from './components/admin/schedule/add_exam'
import teacher_schedule from './components/admin/schedule/teacher_schedule'

import question_makable_list from './components/admin/question/question_makable_list'
import question_setup from './components/admin/question/question_setup'
import question_select from './components/admin/question/question_select'
import question_view from './components/admin/question/question_view'
// import question_print from './components/admin/question/question_print'

import fee_scheme from './components/admin/payment/fee_scheme'
import student_payment_list from './components/admin/payment/student_payment_list'
import student_payment_report from './components/admin/payment/student_payment_report'
import student_payment from './components/admin/payment/student_payment'
import teacher_payment from './components/admin/payment/teacher_payment'
import student_unpaid_report from './components/admin/payment/student_unpaid_report'

// import fine_rule from './components/admin/fine/fine_rule'

import transaction from './components/admin/payment/transaction'
import transaction_list from './components/admin/payment/transaction_list'
import bill_list from './components/admin/payment/bill_list'
import biller_list from './components/admin/payment/biller_list'

import salary from './components/admin/payment/salary'
// import bank_transaction from './components/admin/payment/bank_transaction'
// import fund_list from './components/admin/payment/fund_list'
// import fund_transaction from './components/admin/payment/fund_transaction'
// import balance_transaction from './components/admin/payment/balance_transaction'
import balance_report from './components/admin/payment/balance_report'
// import budget_analysis from './components/admin/payment/budget_analysis'

import send_sms from './components/admin/sms/send_sms'
import sms_report from './components/admin/sms/sms_report'

import exam_list from './components/admin/exam/exam_list'
// import exam_question_list from './components/admin/exam/exam_question_list'
import exam_mark_edit from './components/admin/exam/exam_mark_edit'
import exam_merit_list from './components/admin/exam/exam_merit_list'
import exam_individual_list from './components/admin/exam/exam_individual_list'
import exam_question_setup from './components/admin/exam/question_setup'
import combine_subject_rule from './components/admin/exam/combine_subject_rule'
// import online_question_add from './components/admin/exam/question_add'
// import online_question_edit from './components/admin/exam/online_question_edit'
// import add_online_exam from './components/admin/exam/add_online_exam'
// import online_exam_subject from './components/admin/exam/online_exam_subject'
import billing_invoice from './components/admin/billing/invoice'
import recharge_history from './components/admin/sms/recharge_history'

import user_message from './components/admin/setting/user_message'


// test
import test_data from './components/admin/test/test_data'

Vue.use(Router)

var router = new Router({
    mode: 'history',
    base: "/admin/home",
    linkActiveClass: 'active',
    routes: [        
        { path: '/', name: 'admin_dashboard', component: admin_home },
        { path: '/branch_manager', name: 'branch_manager_dashboard', component: branch_manager },
        { path: '/404', name: '404', component: not_found },

        { path: '/admin_user', name: 'admin_user', component: admin_user },
        { path: '/admin_role', name: 'admin_role', component: admin_role },

        { path: '/setting_echelon', name: 'setting_echelon', component: setting_echelon },
        { path: '/setting_batch', name: 'setting_batch', component: setting_batch },
        { path: '/setting_branch', name: 'setting_branch', component: setting_branch },
        { path: '/setting_course', name: 'setting_course', component: setting_course },
        { path: '/setting_group', name: 'setting_group', component: setting_group },
        { path: '/setting_subject', name: 'setting_subject', component: setting_subject },
        { path: '/setting_institution', name: 'setting_institution', component: setting_institution },

        { path: '/student_list/:visiting_list_id?', name: 'student_list', component: student_list },
        { path: '/student_photo', name: 'student_photo', component: student_photo },        
        { path: '/student_visiting_list', name: 'student_visiting_list', component: student_visiting_list },

        { path: '/attendance_list', name: 'attendance_list', component: attendance_list },
        // { path: '/absent_analysis', name: 'absent_analysis', component: absent_analysis },
        // { path: '/attendance_analysis', name: 'attendance_analysis', component: attendance_analysis },
        { path: '/individual_attendance', name: 'individual_attendance', component: individual_attendance },
        
        { path: '/teacher_list', name: 'teacher_list', component: teacher_list },

        { path: '/schedule_manage', name: 'schedule_manage', component: schedule_manage },
        // { path: '/schedule_planner', name: 'schedule_planner', component: schedule_planner },
        // { path: '/schedule_chapter', name: 'schedule_chapter', component: schedule_chapter },
        { path: '/schedule_weekly', name: 'schedule_weekly', component: schedule_weekly },
        { path: '/schedule_daily', name: 'schedule_daily', component: schedule_daily },
        { path: '/schedule_generate', name: 'schedule_generate', component: schedule_generate },
        { path: '/schedule_view', name: 'schedule_view', component: schedule_view },
        { path: '/schedule_list/:id', name: 'schedule_update', component: schedule_update },
        // { path: '/schedule_view/:id', name: 'schedule_view', component: schedule_view },
        { path: '/schedule_weekly_list', name: 'schedule_weekly_list', component: schedule_weekly_list },
        { path: '/edit_schedule', name: 'edit_schedule', component: edit_schedule },
        { path: '/teacher_schedule', name: 'teacher_schedule', component: teacher_schedule },

        { path: '/question_makable_list', name: 'question_makable_list', component: question_makable_list },
        { path: '/question_setup/:id', name: 'question_setup', component: question_setup },
        { path: '/question_select/:id/:type_id', name: 'question_select', component: question_select },
        { path: '/question_view/:id', name: 'question_view', component: question_view },
        // { path: '/question_print/:id', name: 'question_print', component: question_print },

        { path: '/fee_scheme', name: 'fee_scheme', component: fee_scheme },
        { path: '/student_payment_list', name: 'student_payment_list', component: student_payment_list },
        { path: '/student_payment_report', name: 'student_payment_report', component: student_payment_report },
        { path: '/student_payment', name: 'student_payment', component: student_payment },
        { path: '/teacher_payment', name: 'teacher_payment', component: teacher_payment },
        { path: '/student_unpaid_report', name: 'student_unpaid_report', component: student_unpaid_report },

        // { path: '/fine_rule', name: 'fine_rule', component: fine_rule },
        { path: '/transaction', name: 'transaction', component: transaction },
        { path: '/transaction_list', name: 'transaction_list', component: transaction_list },
        { path: '/bill_list', name: 'bill_list', component: bill_list },
        { path: '/biller_list', name: 'biller_list', component: biller_list },

        { path: '/salary', name: 'salary', component: salary },
        { path: '/balance_report', name: 'balance_report', component: balance_report },
        // { path: '/budget_analysis', name: 'budget_analysis', component: budget_analysis },

        { path: '/send_sms', name: 'send_sms', component: send_sms },
        { path: '/sms_report', name: 'sms_report', component: sms_report },

        // { path: '/exam_question_list', name: 'exam_question_list', component: exam_question_list },
        { path: '/exam_list', name: 'exam_list', component: exam_list },
        { path: '/exam_mark_edit', name: 'exam_mark_edit', component: exam_mark_edit },
        { path: '/exam_merit_list', name: 'exam_merit_list', component: exam_merit_list },
        { path: '/exam_individual_list', name: 'exam_individual_list', component: exam_individual_list },
        { path: '/exam_question_setup/:id', name: 'exam_question_setup', component: exam_question_setup },
        { path: '/combine_subject_rule', name: 'combine_subject_rule', component: combine_subject_rule },
        // { path: '/online_question_add', name: 'online_question_add', component: online_question_add },
        // { path: '/online_question_edit', name: 'online_question_edit', component: online_question_edit },
        // { path: '/add_online_exam/', name: 'add_online_exam', component: add_online_exam },
        // { path: '/online_exam_subject/', name: 'online_exam_subject', component: online_exam_subject },
        { path: '/billing_invoice', name: 'billing_invoice', component: billing_invoice },
        { path: '/recharge_history', name: 'recharge_history', component: recharge_history },

        { path: '/user_message', name: 'user_message', component: user_message },

        // test
        { path: '/test_data', name: 'test_data', component: test_data },

        { path: '*', redirect: { name: 'admin_dashboard' } }
    ]
});

export default router