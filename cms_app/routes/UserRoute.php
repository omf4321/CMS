<?php


/* ============================================================================
						User UserController 
===============================================================================*/
Route::get('/user/teacher_profile', 'User\UserController@profile');
Route::post('/user/update_profile', 'User\UserController@update_profile');
Route::post('/user/update_password', 'User\UserController@update_password');

Route::post('/user/update_authentication/{code}', 'User\HomeController@update_authentication');

/* ============================================================================
						User ScheduleController 
===============================================================================*/
Route::get('/user/schedule/get_discontented_schedule', 'User\ScheduleController@get_discontented_schedule');
Route::get('/user/schedule/get_user_alert', 'User\ScheduleController@get_user_alert');
Route::get('/user/schedule/get_discontented_subject_schedule/{schedule_id}/{subject_id}', 'User\ScheduleController@get_discontented_subject_schedule');
Route::get('/user/schedule/get_contented_schedule', 'User\ScheduleController@get_contented_schedule');
Route::get('/user/schedule/get_contented_schedule_by_date/{date}', 'User\ScheduleController@get_contented_schedule_by_date');
Route::post('/user/schedule/save_discontented_schedule', 'User\ScheduleController@save_discontented_schedule');
Route::post('/user/schedule/save_contented_schedule', 'User\ScheduleController@save_contented_schedule');
Route::get('/user/schedule/get_planner_schedule/{chapter_id}', 'User\ScheduleController@get_planner_schedule');
Route::get('/user/schedule/get_single_contented_schedule/{id}', 'User\ScheduleController@get_single_contented_schedule');

Route::get('/user/schedule/get_dashboard_contented_schedule', 'User\ScheduleController@get_dashboard_contented_schedule');
Route::get('/user/schedule/get_teacher_schedule_summary/{date_from}/{date_to?}', 'User\ScheduleController@get_teacher_schedule_summary');

Route::post('/user/schedule/get_batch_attendance/{batch_id}', 'User\ScheduleController@get_batch_attendance');
Route::post('/user/schedule/save_batch_attendance', 'User\ScheduleController@save_batch_attendance');
Route::post('/user/schedule/save_online_class_url', 'User\ScheduleController@save_online_class_url');
Route::post('/user/schedule/change_online_class_status', 'User\ScheduleController@change_online_class_status');

Route::post('/user/schedule/save_teacher_attendance', 'User\ScheduleController@save_teacher_attendance');
Route::post('/user/schedule/confirm_lecture_sheet/{id}', 'User\ScheduleController@confirm_lecture_sheet');

Route::get('/user/schedule/get_user_message', 'User\ScheduleController@get_user_message');
Route::post('/user/schedule/read_user_message/{id}', 'User\ScheduleController@read_user_message');
// exam
Route::get('/user/exam/get_invigilator_dashboard_exam', 'User\ExamController@get_invigilator_dashboard_exam');
Route::post('/user/exam/save_setup_exam', 'User\ExamController@save_setup_exam');
Route::post('/user/exam/save_invigilator_attendance', 'User\ExamController@save_invigilator_attendance');
Route::get('/user/exam/get_student_exam_entry_list/{schedule_id}/{batch_id}/{date?}', 'User\ExamController@get_student_exam_entry_list');
Route::post('/user/exam/save_exam_mark', 'User\ExamController@save_exam_mark');
Route::post('/user/exam/import_exam', 'User\ExamController@import_exam_mark');
// question
Route::get('/user/question/question_makable_list_by_date/{date?}', 'User\ExamController@question_makable_list_by_date');
Route::get('/user/question/exam_schedule_by_class_date/{echelon_id}', 'User\ExamController@exam_schedule_by_class_date');
Route::post('/user/question/save_question_list', 'User\ExamController@save_question_list');
Route::post('/user/question/edit_question_list/{quesion_id}', 'User\ExamController@edit_question_list');
Route::post('/user/question/question_delete/{quesion_id}', 'User\ExamController@question_delete');
// scripter
Route::get('/user/exam/get_scripter_dashboard_exam', 'User\ExamController@get_scripter_dashboard_exam');
Route::post('/user/exam/take_script', 'User\ExamController@take_script');
Route::post('/user/exam/submit_script/{id}/{code}', 'User\ExamController@submit_script');
Route::get('/user/exam/get_invigilator_minutes_summary/{date_from?}/{date_to?}', 'User\ExamController@get_invigilator_minutes_summary');
Route::get('/user/exam/get_script_summary/{date_from}/{date_to?}', 'User\ExamController@get_script_summary');

Route::get('/user/payment/get_payment_list', 'User\PaymentController@get_payment_list');
Route::post('/user/payment/get_payment_detail', 'User\PaymentController@get_payment_detail');
Route::post('/user/payment/confirm_payment/{id}', 'User\PaymentController@confirm_payment');
// Route::get('/user/home', 'User\HomeController@index');
Route::get('user/home/{vue?}', 'User\HomeController@index')->where('vue', '[\/\w\.-]*');