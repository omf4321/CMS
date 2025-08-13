<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/hello', 'question\QuestionController@question_fix');


Route::group(['prefix' => 'admin'], function () {
  Route::get('/login', 'AdminAuth\LoginController@showLoginForm')->name('admin.login');
  Route::post('/login', 'AdminAuth\LoginController@login');
  Route::post('/logout', 'AdminAuth\LoginController@logout')->name('admin.logout');
  Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail')->name('admin.password.request');
  Route::post('/password/reset', 'AdminAuth\ResetPasswordController@reset')->name('admin.password.email');
  Route::get('/password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm')->name('admin.password.reset');
  Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

Route::get('/admin', function () {
    return redirect(route('admin.home'));
});

Route::get('/admin/clear', 'Admin\AdminController@clear');
Route::get('/admin/get_confirmation', 'Admin\HomeController@get_confirmation');
Route::get('/admin/setup', 'Admin\HomeController@firstItemSetup')->name('admin.setup');
Route::post('/admin/setup/save', 'Admin\HomeController@SetupSave')->name('admin.setup.save');


Route::get('/user', function () {
    return redirect(route('user.home'));
});

Route::get('/confirm-payment', 'HomeController@payment_confirm_form')->name('confirm-payment');
Route::post('/confirm-payment', 'HomeController@payment_confirm');

Route::get('/admin/test', 'Admin\AdminController@test');
Route::get('/admin/recharge_sms/{amount}', 'Admin\AdminController@recharge_sms');
// Route::get('/admin/test', 'payment\PaymentController@get_unpaid_student');

/* ============================================================================
						Test
===============================================================================*/
Route::post('/admin/test/save_test_data', 'Admin\HomeController@save_test_data');
Route::get('/test/test_data', 'HomeController@test_data');
Route::get('/user/test', 'User\HomeController@test');

/* ============================================================================
						      User Auth 
===============================================================================*/
Auth::routes();
Route::get('/user/home', 'User\HomeController@index')->name('user.home');
Route::get('/user/act_as_teacher', 'User\HomeController@act_as_teacher');
Route::get('/logout', 'Auth\LoginController@userlogout')->name('user.logout');

Route::get('/question/{random}', 'HomeController@add_question');

/* ============================================================================
						AdminController
===============================================================================*/
// ========== Admin User ===============
Route::get('/admin/request/admin_role_list', 'Admin\AdminController@admin_role_list');
Route::get('/admin/request/admin_user_list', 'Admin\AdminController@admin_user_list');
Route::post('/admin/request/admin_user_add', 'Admin\AdminController@admin_user_add');
Route::post('/admin/request/admin_user_edit/{id}', 'Admin\AdminController@admin_user_edit');
Route::post('/admin/request/admin_user_delete/{id}', 'Admin\AdminController@admin_user_delete');

// ========== Admin Role ===============
Route::get('/admin/request/admin_permission_list', 'Admin\AdminController@admin_permission_list');
Route::get('/admin/request/admin_permissions_by_role/{id}', 'Admin\AdminController@admin_permissions_by_role');
Route::post('/admin/request/admin_role_add', 'Admin\AdminController@admin_role_add');
Route::post('/admin/request/admin_role_edit/{id}', 'Admin\AdminController@admin_role_edit');
Route::post('/admin/request/admin_role_delete/{id}', 'Admin\AdminController@admin_role_delete');
// ======== act as teacher =====================
Route::get('/admin/act_as_teacher', 'Admin\AdminController@act_as_teacher');
/* ============================================================================
						Admin HomeController
===============================================================================*/
// *********Index function putted on Admin Auth Section
Route::get('/admin/request/role_permission', 'Admin\HomeController@role_permission');
Route::get('/admin/request/menu_role_permission', 'Admin\HomeController@menu_role_permission');
Route::get('/admin/get_admin_dashboard/{branch_id}/{batch_id}', 'Admin\HomeController@get_admin_dashboard');
Route::get('/admin/home/change_random_number', ['uses' => 'Admin\HomeController@change_random_number']);

/* ============================================================================
						Settings
===============================================================================*/
// settingControler
Route::get('/admin/request/branch_list_by_role', 'setting\SettingController@branch_list_by_role');
Route::get('/admin/request/echelon_list_by_branch/{id}', 'setting\SettingController@echelon_list_by_branch');
Route::get('/admin/request/course_list_by_branch/{id}', 'setting\SettingController@course_list_by_branch');
Route::get('/admin/request/group_list_by_branch/{id}', 'setting\SettingController@group_list_by_branch');
Route::get('/admin/request/subject_list_by_branch_and_echelon/{branch_id}/{echelon_id}', 'setting\SettingController@subject_list_by_branch_and_echelon');
Route::get('/admin/request/subject_list_by_branch/{branch_id}', 'setting\SettingController@subject_list_by_branch');
// 
Route::get('/admin/request/teacher_list_by_branch/{branch_id}', 'setting\SettingController@teacher_list_by_branch');
Route::get('/admin/request/chapter_list_by_branch/{id}', 'setting\SettingController@chapter_list_by_branch');
Route::get('/admin/request/chapter_list_by_class/{id}', 'setting\SettingController@chapter_list_by_class');
Route::get('/admin/request/chapter_list_by_subject/{id}', 'setting\SettingController@chapter_list_by_subject');
Route::get('/admin/request/chapter_topic', 'setting\SettingController@chapter_topic');
Route::get('/admin/request/chapter_topic_by_class/{id}', 'setting\SettingController@chapter_topic_by_class');
Route::get('/admin/request/chapter_topic_by_subject/{id}', 'setting\SettingController@chapter_topic_by_subject');
Route::get('/admin/request/exam_type_list', 'setting\SettingController@exam_type_list');

Route::get('/admin/request/message_list', 'setting\MessageController@message_list');
Route::post('/admin/request/message_add', 'setting\MessageController@message_add');
Route::post('/admin/request/message_edit/{id}', 'setting\MessageController@message_edit');
Route::post('/admin/request/message_delete/{id}', 'setting\MessageController@message_delete');
// branchController
Route::get('/admin/request/admin_branch_list', 'setting\BranchController@admin_branch_list');
Route::post('/admin/request/admin_branch_add', 'setting\BranchController@admin_branch_add');
Route::post('/admin/request/admin_branch_edit/{id}', 'setting\BranchController@admin_branch_edit');
Route::post('/admin/request/admin_branch_delete/{id}', 'setting\BranchController@admin_branch_delete');
// echelonController
Route::get('/admin/request/admin_echelon_list', 'setting\EchelonController@admin_echelon_list');
Route::post('/admin/request/admin_echelon_add', 'setting\EchelonController@admin_echelon_add');
Route::post('/admin/request/admin_echelon_edit/{id}', 'setting\EchelonController@admin_echelon_edit');
Route::post('/admin/request/admin_echelon_delete/{id}', 'setting\EchelonController@admin_echelon_delete');
//course Controller 
Route::get('/admin/request/admin_course_list', 'setting\CourseController@admin_course_list');
Route::post('/admin/request/admin_course_add', 'setting\CourseController@admin_course_add');
Route::post('/admin/request/admin_course_edit/{id}', 'setting\CourseController@admin_course_edit');
Route::post('/admin/request/admin_course_delete/{id}', 'setting\CourseController@admin_course_delete');
//group Controller 
Route::get('/admin/request/admin_group_list', 'setting\GroupController@admin_group_list');
Route::post('/admin/request/admin_group_add', 'setting\GroupController@admin_group_add');
Route::post('/admin/request/admin_group_edit/{id}', 'setting\GroupController@admin_group_edit');
Route::post('/admin/request/admin_group_delete/{id}', 'setting\GroupController@admin_group_delete');

//Subject Controller 
Route::get('/admin/request/admin_subject_list', 'setting\SubjectController@admin_subject_list');
Route::post('/admin/request/admin_subject_add', 'setting\SubjectController@admin_subject_add');
Route::post('/admin/request/admin_subject_edit/{id}', 'setting\SubjectController@admin_subject_edit');
Route::post('/admin/request/admin_subject_delete/{id}', 'setting\SubjectController@admin_subject_delete');

//Institution Controller 
Route::get('/admin/request/admin_institution_list', 'setting\InstitutionController@admin_institution_list');
Route::post('/admin/request/admin_institution_add', 'setting\InstitutionController@admin_institution_add');
Route::post('/admin/request/admin_institution_edit/{id}', 'setting\InstitutionController@admin_institution_edit');
Route::post('/admin/request/admin_institution_delete/{id}', 'setting\InstitutionController@admin_institution_delete');

//Batch Controller 
Route::get('/admin/request/admin_batch_list', 'setting\BatchController@admin_batch_list');
Route::post('/admin/request/admin_batch_add', 'setting\BatchController@admin_batch_add');
Route::post('/admin/request/admin_batch_edit/{id}', 'setting\BatchController@admin_batch_edit');
Route::post('/admin/request/admin_batch_delete/{id}', 'setting\BatchController@admin_batch_delete');
Route::post('/admin/request/inactive_student/{id}', 'setting\BatchController@inactive_student');

/* ============================================================================
						StudentController
===============================================================================*/
// get branch list from EchelonController
// get echelon list from BatchController
Route::get('/admin/request/batch_list_by_branch_and_echelon/{branch_id}/{class_id?}', 'setting\SettingController@batch_list_by_branch_and_echelon');
Route::get('/admin/request/institution_list_by_branch/{id}', 'setting\SettingController@institution_list_by_branch');
Route::get('/admin/request/batch_list_by_branch/{id}', 'setting\SettingController@batch_list_by_branch');
Route::get('/admin/request/batch_list_by_echelon/{id}', 'setting\SettingController@batch_list_by_echelon');

Route::get('/admin/request/initialize-form', 'student\StudentController@initialize_student_form');
Route::get('/admin/request/admin_student_list/{branch}/{search_by_id}/{student_filter}', 'student\StudentController@admin_student_list');
Route::get('/admin/request/admin_student_list_desc', 'student\StudentController@admin_student_list_desc');
Route::post('/admin/request/admin_student_add', 'student\StudentController@admin_student_add');
Route::post('/admin/request/admin_student_edit/{id}', 'student\StudentController@admin_student_edit');
Route::post('/admin/request/admin_student_delete/{id}', 'student\StudentController@admin_student_delete');
Route::post('/admin/student/upload_image', 'student\StudentController@upload_image');

Route::get('/admin/request/get_reg_droped/{id}', 'student\StudentController@get_reg_droped');
Route::get('/admin/request/get_reg_max/{id}', 'student\StudentController@get_reg_max');

Route::post('/admin/request/admin_student_active/{id}', 'student\StudentController@admin_student_active');
Route::post('/admin/request/admin_student_transfer/{id}', 'student\StudentController@admin_student_transfer');
// visitng list
Route::get('/admin/student/get_visiting_list/{branch_id}', 'student\StudentController@get_visiting_list');
Route::post('/admin/student/add_visiting_list', 'student\StudentController@add_visiting_list');
Route::post('/admin/student/edit_visiting_list/{id}', 'student\StudentController@edit_visiting_list');
Route::post('/admin/student/delete_visiting_list/{id}', 'student\StudentController@delete_visiting_list');

Route::get('/admin/student/new_student_from_visiting_list/{visiting_list_id}', 'student\StudentController@new_student_from_visiting_list');
Route::post('/admin/student/send_visiting_list_sms', 'student\StudentController@send_visiting_list_sms');


/* ============================================================================
						TeacherController
===============================================================================*/

Route::get('/admin/teacher/teacher_list', 'teacher\TeacherController@teacher_list');
Route::post('/admin/teacher/teacher_add', 'teacher\TeacherController@teacher_add');
Route::post('/admin/teacher/teacher_edit/{id}', 'teacher\TeacherController@teacher_edit');
Route::post('/admin/teacher/teacher_delete/{id}', 'teacher\TeacherController@teacher_delete');


/* ============================================================================
						Schedule ChapterController
===============================================================================*/

Route::get('/admin/schedule/planner_list/{id}', 'schedule\ChapterController@planner_list');
Route::post('/admin/schedule/planner_add', 'schedule\ChapterController@planner_add');
Route::post('/admin/schedule/planner_edit/{id}', 'schedule\ChapterController@planner_edit');
Route::post('/admin/schedule/planner_delete/{id}', 'schedule\ChapterController@planner_delete');
Route::post('/admin/schedule/planner_add_before', 'schedule\ChapterController@planner_add_before');

Route::get('/admin/schedule/chapter_list/{id}', 'schedule\ChapterController@chapter_list');
Route::post('/admin/schedule/chapter_add', 'schedule\ChapterController@chapter_add');
Route::post('/admin/schedule/chapter_edit/{id}', 'schedule\ChapterController@chapter_edit');
Route::post('/admin/schedule/chapter_delete/{id}', 'schedule\ChapterController@chapter_delete');

/* ============================================================================
						Schedule ScheduleController
===============================================================================*/
Route::get('/admin/schedule/weekly_schedule/{batch_id}', 'schedule\ScheduleController@weekly_schedule');
Route::get('/admin/schedule/weekly_schedule_list/{id}', 'schedule\ScheduleController@weekly_schedule_list');
Route::post('/admin/schedule/weekly_schedule_add', 'schedule\ScheduleController@weekly_schedule_add');
Route::post('/admin/schedule/weekly_schedule_list_add', 'schedule\ScheduleController@weekly_schedule_list_add');
Route::post('/admin/schedule/weekly_schedule_edit/{id}', 'schedule\ScheduleController@weekly_schedule_edit');
Route::post('/admin/schedule/weekly_schedule_list_edit/{id}', 'schedule\ScheduleController@weekly_schedule_list_edit');
Route::post('/admin/schedule/weekly_schedule_delete/{id}', 'schedule\ScheduleController@weekly_schedule_delete');
Route::post('/admin/schedule/weekly_schedule_list_delete/{id}', 'schedule\ScheduleController@weekly_schedule_list_delete');
Route::post('/admin/schedule/duplicate_weekly_schedule', 'schedule\ScheduleController@duplicate_weekly_schedule');
Route::post('/admin/schedule/delete_weekly_schedule/{batch_id}', 'schedule\ScheduleController@delete_weekly_schedule');

Route::get('/admin/schedule/daily_schedule_list', 'schedule\ScheduleController@daily_schedule_list');
Route::get('/admin/schedule/daily_schedule/{from_date}/{to_date?}', 'schedule\ScheduleController@daily_schedule');
Route::get('/admin/schedule/get_daily_schedule_by_id/{id}', 'schedule\ScheduleController@get_daily_schedule_by_id');
Route::get('/admin/schedule/daily_schedule_by_id/{id}', 'schedule\ScheduleController@daily_schedule_by_id');
Route::post('/admin/schedule/daily_schedule_add', 'schedule\ScheduleController@daily_schedule_add');
Route::post('/admin/schedule/daily_schedule_edit/{id}', 'schedule\ScheduleController@daily_schedule_edit');
Route::post('/admin/schedule/daily_schedule_delete/{id}', 'schedule\ScheduleController@daily_schedule_delete');
Route::post('/admin/schedule/daily_schedule_add_before', 'schedule\ScheduleController@daily_schedule_add_before');
Route::post('/admin/schedule/lecture_sheet_update/{id}', 'schedule\ScheduleController@lecture_sheet_update');
Route::post('/admin/schedule/exchange_class', 'schedule\ScheduleController@exchange_class');
Route::post('/admin/schedule/delete_by_date', 'schedule\ScheduleController@delete_by_date');
Route::post('/admin/schedule/daily_schedule_by_date', 'schedule\ScheduleController@daily_schedule_by_date');

Route::post('/admin/schedule/generate_schedule', 'schedule\ScheduleController@generate_schedule');
Route::post('/admin/schedule/check_schedule', 'schedule\ScheduleController@check_schedule');

Route::get('/admin/schedule/schedule_list/{id}', 'schedule\ScheduleController@schedule_list');
Route::post('/admin/schedule/schedule_list_delete/{id}', 'schedule\ScheduleController@schedule_list_delete');
Route::get('/admin/schedule/schedule_list_by_id/{id}', 'schedule\ScheduleController@schedule_list_by_id');

Route::post('/admin/schedule/update_schedule', 'schedule\ScheduleController@update_schedule');
Route::get('/admin/schedule/get_dashboard_schedule/{date}', 'schedule\ScheduleController@get_dashboard_schedule');
Route::post('/admin/schedule/edit_dashboard_schedule', 'schedule\ScheduleController@edit_dashboard_schedule');
Route::post('/admin/schedule/add_dashboard_schedule', 'schedule\ScheduleController@add_dashboard_schedule');
Route::get('/admin/schedule/get_dashboard_exchangable_schedule/{id}', 'schedule\ScheduleController@get_dashboard_exchangable_schedule');
Route::post('/admin/schedule/save_dashboard_exchange_schedule', 'schedule\ScheduleController@save_dashboard_exchange_schedule');
Route::get('/admin/schedule/get_dashboard_lecture_schedule', 'schedule\ScheduleController@get_dashboard_lecture_schedule');
Route::post('/admin/schedule/save_teacher_sign_dashboard_schdule', 'schedule\ScheduleController@save_teacher_sign_dashboard_schdule');
Route::post('/admin/schedule/confirm_teacher/{daily_schedule_id}', 'schedule\ScheduleController@confirm_teacher');

Route::post('/admin/schedule/get_schedule_exam_list', 'schedule\ScheduleController@get_schedule_exam_list');
Route::post('/admin/schedule/search_schedule_exam_list', 'schedule\ScheduleController@search_schedule_exam_list');
Route::post('/admin/schedule/save_schedule_exam_list', 'schedule\ScheduleController@save_schedule_exam_list');


Route::post('/admin/schedule/get_teacher_schedule_list', 'schedule\ScheduleController@get_teacher_schedule_list');
Route::post('/admin/schedule/save_teacher_schedule_list', 'schedule\ScheduleController@save_teacher_schedule_list');

Route::get('/admin/schedule/get_dashboard_exam_schedule', 'schedule\ScheduleController@get_dashboard_exam_schedule');

Route::post('/admin/schedule/change_online_class_status', 'schedule\ScheduleController@change_online_class_status');

/* ============================================================================
						Attendance AttendanceController
===============================================================================*/
Route::get('/admin/attendance/get_attendance_schedule/{date}', 'attendance\AttendanceController@get_attendance_schedule');
Route::post('/admin/attendance/get_batch_attendance/{batch_id}', 'attendance\AttendanceController@get_batch_attendance');
Route::post('/admin/attendance/save_batch_attendance', 'attendance\AttendanceController@save_batch_attendance');
Route::post('/admin/attendance/delete_batch_attendance/{batch_id}', 'attendance\AttendanceController@delete_batch_attendance');
Route::post('/admin/attendance/save_random_attendance', 'attendance\AttendanceController@save_random_attendance');
Route::get('/admin/attendance/get_student_absent_list/{days}', 'attendance\AttendanceController@get_student_absent_list');
Route::post('/admin/attendance/save_attendance_status', 'attendance\AttendanceController@save_attendance_status');

Route::post('/admin/attendance/send_sms', 'attendance\AttendanceController@send_sms');

Route::get('/admin/attendance/get_student_attendance_list/{date}', 'attendance\AttendanceController@get_student_attendance_list');

Route::get('/admin/attendance/get_dashboard_attendance_list/{date}', 'attendance\AttendanceController@get_dashboard_attendance_list');
Route::post('/admin/attendance/send_attendance_sms', 'attendance\AttendanceController@send_attendance_sms');

Route::post('/admin/attendance/get_individual_student_attendance', 'attendance\AttendanceController@get_individual_student_attendance');
/* ============================================================================
						Question QuestionController
===============================================================================*/
Route::post('/admin/question/question_add', 'question\QuestionController@question_add');
Route::get('/admin/question/relative_text_list/{id}', 'question\QuestionController@relative_text_list');
Route::post('/admin/question/relative_text_by_chapter/{id}', 'question\QuestionController@relative_text_by_chapter');
Route::get('/admin/question/question_list_by_chapter/{subject_id}', 'question\QuestionController@question_list_by_chapter');
Route::post('/admin/question/question_edit/{id}', 'question\QuestionController@question_edit');
Route::post('/admin/question/question_check_list', 'question\QuestionController@question_check_list');
Route::get('/admin/question/question_check_list_for_relative/{id}/{type}', 'question\QuestionController@question_check_list_for_relative');
Route::post('/admin/question/question_check', 'question\QuestionController@question_check');
Route::post('/admin/question/question_check_edit/{id}', 'question\QuestionController@question_check_edit');
Route::post('/admin/question/question_delete/{id}/{relative_id?}', 'question\QuestionController@question_delete');
Route::post('/admin/question/question_delete_all', 'question\QuestionController@question_delete_all');
Route::post('/admin/question/question_delete_by_chapter', 'question\QuestionController@question_delete_by_chapter');

Route::get('/admin/question/question_makable_list/{id}', 'question\QuestionController@question_makable_list');
Route::get('/admin/question/exam_question_list', 'question\QuestionController@exam_question_list');
Route::get('/admin/question/question_makable_list_by_date/{date}', 'question\QuestionController@question_makable_list_by_date');
Route::get('/admin/question/question_makable_list_with_intruction/{id}', 'question\QuestionController@question_makable_list_with_intruction');
Route::post('/admin/question/question_makable_delete/{id}', 'question\QuestionController@question_makable_delete');

Route::get('/admin/question/exam_schedule_by_class_date/{subject_id}', 'question\QuestionController@exam_schedule_by_class_date');
Route::post('/admin/question/save_question_list', 'question\QuestionController@save_question_list');
Route::post('/admin/question/edit_question_list/{id}', 'question\QuestionController@edit_question_list');
Route::post('/admin/question/finish_make_question/{id}', 'question\QuestionController@finish_make_question');
Route::get('/admin/question/question_printable_list/{id}', 'question\QuestionController@question_printable_list');

Route::post('/admin/question/question_type_setup', 'question\QuestionController@add_exam_question_type');
Route::get('/admin/question/get_select_question_item/{id}/{type_id}', 'question\QuestionController@get_select_question_item');
Route::post('/admin/question/select_question_list', 'question\QuestionController@select_question_list');
Route::post('/admin/question/select_question', 'question\QuestionController@select_question');

Route::post('/admin/question/generate_question', 'question\QuestionController@generate_question');

Route::post('/admin/question/question_print', 'question\QuestionController@question_print');
Route::post('/admin/question/question_print_update', 'question\QuestionController@question_print_update');

/* ============================================================================
						Payment PaymentController
===============================================================================*/
Route::post('/admin/payment/get_student_payment_list', 'payment\PaymentController@get_student_payment_list');
Route::post('/admin/payment/edit_student_payment_list/{payment_id}', 'payment\PaymentController@edit_student_payment_list');
Route::get('/admin/payment/get_student_payment/{reg_no}/{status}', 'payment\PaymentController@get_student_payment');
Route::post('/admin/payment/save_student_payment', 'payment\PaymentController@save_student_payment');
Route::post('/admin/payment/delete_student_payment/{id}', 'payment\PaymentController@delete_student_payment');

Route::post('/admin/payment/get_teacher_payment/{teacher_id}', 'payment\PaymentController@get_teacher_payment');
Route::post('/admin/payment/save_teacher_payment', 'payment\PaymentController@save_teacher_payment');
// unpaid student
Route::get('/admin/payment/get_unpaid_student', 'payment\PaymentController@get_unpaid_student');
Route::post('/admin/payment/send_sms_to_unpaid', 'payment\PaymentController@send_sms_to_unpaid');
// assign payment
Route::get('/assign/{invoice}/{admin}', 'payment\PaymentController@payment_assign_to_admin');
// transaction
Route::get('/admin/transaction/get_todays_transaction_list', 'payment\TransactionController@get_todays_transaction_list');
Route::post('/admin/transaction/add_bill', 'payment\TransactionController@add_bill');
Route::post('/admin/transaction/add_transaction', 'payment\TransactionController@add_transaction');
Route::post('/admin/transaction/delete_transaction/{id}', 'payment\TransactionController@delete_transaction');
Route::post('/admin/transaction/get_transaction_list', 'payment\TransactionController@get_transaction_list');
Route::post('/admin/transaction/edit_transaction_list/{id}', 'payment\TransactionController@edit_transaction_list');

Route::post('/admin/transaction/get_bill_list', 'payment\TransactionController@get_bill_list');
Route::post('/admin/transaction/edit_bill_list/{id}', 'payment\TransactionController@edit_bill_list');
Route::post('/admin/transaction/delete_bill_list/{id}', 'payment\TransactionController@delete_bill_list');

Route::get('/admin/transaction/get_biller_list', 'payment\TransactionController@get_biller_list');
Route::post('/admin/transaction/edit_biller_list/{id}', 'payment\TransactionController@edit_biller_list');
Route::post('/admin/transaction/delete_biller_list/{id}', 'payment\TransactionController@delete_biller_list');

// salary==================
Route::get('/admin/transaction/get_admin', 'payment\TransactionController@get_admin');
Route::get('/admin/transaction/get_salary/{id}', 'payment\TransactionController@get_salary');
Route::post('/admin/transaction/add_salary', 'payment\TransactionController@add_salary');
Route::post('/admin/transaction/delete_salary/{id}', 'payment\TransactionController@delete_salary');
// bank transaction
Route::get('/admin/transaction/get_bank_account', 'payment\TransactionController@get_bank_account');
Route::get('/admin/transaction/get_bank_transaction/{id}', 'payment\TransactionController@get_bank_transaction');
Route::post('/admin/transaction/add_bank_transaction', 'payment\TransactionController@add_bank_transaction');
Route::post('/admin/transaction/delete_bank_transaction/{id}', 'payment\TransactionController@delete_bank_transaction');
// fund
Route::get('/admin/transaction/get_fund_list', 'payment\TransactionController@get_fund_list');
Route::post('/admin/transaction/add_fund', 'payment\TransactionController@add_fund');
Route::post('/admin/transaction/edit_fund/{id}', 'payment\TransactionController@edit_fund');
Route::post('/admin/transaction/delete_fund/{id}', 'payment\TransactionController@delete_fund');
Route::get('/admin/transaction/get_fund_transaction/{id}', 'payment\TransactionController@get_fund_transaction');
Route::post('/admin/transaction/add_fund_transaction', 'payment\TransactionController@add_fund_transaction');
Route::post('/admin/transaction/delete_fund_transaction/{id}', 'payment\TransactionController@delete_fund_transaction');

Route::post('/admin/transaction/get_balance_report', 'payment\TransactionController@get_balance_report');
Route::post('/admin/transaction/balance_signature', 'payment\TransactionController@balance_signature');
Route::post('/admin/transaction/get_balance_analysis', 'payment\TransactionController@get_balance_analysis');

/* ============================================================================
						Fine FineController
===============================================================================*/
Route::get('/admin/fine/get_fine_rule', 'fine\FineController@get_fine_rule');
Route::post('/admin/fine/add_fine_rule', 'fine\FineController@add_fine_rule');
Route::post('/admin/fine/update_fine_rule/{id}', 'fine\FineController@update_fine_rule');
Route::post('/admin/fine/delete_fine_rule/{id}', 'fine\FineController@delete_fine_rule');

/* ============================================================================
						exam ExamController
===============================================================================*/
Route::get('/admin/exam/get_exam_question_list/{date?}', 'exam\ExamController@get_exam_question_list');
Route::get('/admin/exam/get_exam_question_type/{id}', 'exam\ExamController@get_exam_question_type');
Route::post('/admin/question/exam_question_add', 'exam\ExamController@exam_question_add');
Route::post('/admin/question/exam_question_edit/{id}', 'exam\ExamController@exam_question_edit');
Route::post('/admin/exam/scheduled_exam_edit/{id}', 'exam\ExamController@scheduled_exam_edit');
Route::get('/admin/exam/get_exam_list/{date?}', 'exam\ExamController@get_exam_list');
Route::get('/admin/exam/get_question_exam_list/{date?}', 'exam\ExamController@get_question_exam_list');
Route::get('/admin/exam/get_exam_edit_list/{date_from}/{date_to}', 'exam\ExamController@get_exam_edit_list');
Route::post('/admin/exam/save_exam_setup_list', 'exam\ExamController@save_exam_setup_list');
Route::get('/admin/exam/get_student_exam_entry_list/{schedule_id}/{batch_id}', 'exam\ExamController@get_student_exam_entry_list');
Route::get('/admin/exam/get_all_student_exam_entry_list/{id}', 'exam\ExamController@get_all_student_exam_entry_list');
Route::post('/admin/exam/save_exam_mark', 'exam\ExamController@save_exam_mark');
Route::post('/admin/exam/edit_list_exam_mark', 'exam\ExamController@edit_list_exam_mark');
Route::post('/admin/exam/edit_exam_mark', 'exam\ExamController@edit_exam_mark');
Route::post('/admin/exam/exam_list_edit/{id}', 'exam\ExamController@exam_list_edit');
Route::post('/admin/exam/exam_list_delete/{id}', 'exam\ExamController@exam_list_delete');
Route::post('/admin/exam/send_exam_sms/{id}/{destination}', 'exam\ExamController@send_exam_sms');
Route::post('/admin/exam/finish_mark_entry/{id}', 'exam\ExamController@finish_mark_entry');

Route::post('/admin/exam/get_mark_list', 'exam\ExamController@get_mark_list');
Route::post('/admin/exam/get_individual_mark', 'exam\ExamController@get_individual_mark');

Route::post('/admin/exam/done_exam_list/{id}', 'exam\ExamController@done_exam_list');
Route::post('/admin/exam/save_teacher_singature/{id}/{singnature_type}', 'exam\ExamController@save_teacher_singature');

Route::get('/admin/exam/get_combine_subject_rule/{branch_id}', 'exam\ExamController@get_combine_subject_rule');
Route::post('/admin/exam/add_combine_subject_rule', 'exam\ExamController@add_combine_subject_rule');
Route::post('/admin/exam/edit_combine_subject_rule/{id}', 'exam\ExamController@edit_combine_subject_rule');
Route::post('/admin/exam/delete_combine_subject_rule/{id}', 'exam\ExamController@delete_combine_subject_rule');

/* ============================================================================
						exam OnlineExamController
===============================================================================*/
Route::get('/admin/exam/get_online_exam/{branch_id}/{date}', 'exam\OnlineExamController@get_online_exam');
Route::post('/admin/exam/add_online_exam', 'exam\OnlineExamController@add_online_exam');
Route::post('/admin/exam/edit_online_exam/{id}', 'exam\OnlineExamController@edit_online_exam');
Route::post('/admin/exam/delete_online_exam/{id}', 'exam\OnlineExamController@delete_online_exam');

Route::get('/admin/exam/get_question_tag', 'exam\OnlineExamController@get_question_tag');
Route::get('/admin/exam/get_online_exam_subject', 'exam\OnlineExamController@get_online_exam_subject');
Route::post('/admin/exam/add_online_exam_subject', 'exam\OnlineExamController@add_online_exam_subject');
Route::post('/admin/exam/edit_online_exam_subject/{id}', 'exam\OnlineExamController@edit_online_exam_subject');
Route::post('/admin/exam/delete_online_exam_subject/{id}', 'exam\OnlineExamController@delete_online_exam_subject');

Route::get('/admin/exam/get_online_question/{search_by}', 'exam\OnlineExamController@get_online_question');
Route::get('/admin/exam/get_selection_online_exam_question/{search_by}/{online_exam_id}', 'exam\OnlineExamController@get_selection_online_exam_question');


/* ============================================================================
						smsm SMSController
===============================================================================*/
Route::post('/admin/sms/send_sms', 'sms\SMSController@send_sms');
Route::post('/admin/sms/get_sms_report', 'sms\SMSController@get_sms_report');
/* ============================================================================
						Root HomeController 
===============================================================================*/
Route::get('/', 'HomeController@index')->name('home');

/* ============================================================================
						Admin Panel 
===============================================================================*/
Route::group(['namespace' => 'Admin'],function(){
	
	
});

/* ============================================================================
						other Routes
===============================================================================*/
Route::get('admin/home/{vue?}', 'Admin\HomeController@index')->where('vue', '[\/\w\.-]*');
Route::get('user/home/{vue?}', 'User\HomeController@index')->where('vue', '[\/\w\.-]*');
Route::get('client/home/{vue?}', 'Client\HomeController@index')->where('vue', '[\/\w\.-]*');

Route::group(['prefix' => 'student'], function () {
  Route::get('/login', 'ClientAuth\LoginController@showLoginForm')->name('client.login');
  Route::post('/login', 'ClientAuth\LoginController@login');
  Route::post('/logout', 'ClientAuth\LoginController@logout')->name('client.logout');

  Route::get('/register', 'ClientAuth\RegisterController@showRegistrationForm')->name('client.register');
  Route::post('/register', 'ClientAuth\RegisterController@register');

  Route::post('/password/email', 'ClientAuth\ForgotPasswordController@sendResetLinkEmail')->name('client.password.request');
  Route::post('/password/reset', 'ClientAuth\ResetPasswordController@reset')->name('client.password.email');
  Route::get('/password/reset', 'ClientAuth\ForgotPasswordController@showLinkRequestForm')->name('client.password.reset');
  Route::get('/password/reset/{token}', 'ClientAuth\ResetPasswordController@showResetForm');
});

// // bkash route
// Route::post('token', 'payment\BkashController@token')->name('token');
// Route::get('createpayment', 'payment\BkashController@createpayment')->name('createpayment');
// Route::get('executepayment', 'payment\BkashController@executepayment')->name('executepayment');

// // Refund Routes for bKash
// Route::get('bkash/refund', 'payment\BkashRefundController@index')->name('bkash-refund');
// Route::post('bkash/refund', 'payment\BkashRefundController@refund')->name('bkash-refund');
Route::get('/bkash-payment/{invoice_id}', 'payment\BkashController@index')->name('bkash.payment');
Route::post('/get-token', 'payment\BkashController@getToken')->name('bkash.get.token');
Route::get('/make-payment', 'payment\BkashController@createPayment')->name('bkash.make.payment');
 Route::post('/execute-payment', 'payment\BkashController@executePayment')->name('bkash.execute.payment');
Route::get('/query-payment', 'payment\BkashController@queryPayment')->name('bkash.query.payment');
Route::post('/success', 'payment\BkashController@bkashSuccess')->name('bkash.success');
Route::get('/admin/get_invoice', 'payment\BkashController@get_invoice');
Route::get('/admin/invoice/{id}', 'payment\BkashController@get_invoice_by_id');
Route::get('/payment-complete', 'payment\BkashController@payment_complete');
Route::get('/payment-successful', 'payment\BkashController@payment_complete_for_trxid')->name('bkash.payment.success');
// Route::get('/admin/get_confirmation', 'Admin\HomeController@get_confirmation');
Route::get('/bkash-payment-sms', 'payment\BkashController@sms_payment')->name('bkash.payment');
Route::get('/admin/get_sms_recharge_history', 'payment\BkashController@get_sms_recharge_history');
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
