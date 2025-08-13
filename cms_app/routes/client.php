<?php

Route::get('/student_profile', 'Client\ClientController@profile');
Route::post('/update_profile', 'Client\ClientController@update_profile');
Route::post('/update_password', 'Client\ClientController@update_password');
Route::post('/upload_image', 'Client\ClientController@upload_image');
Route::get('/get_schedule', 'Client\ScheduleController@get_schedule');
Route::get('/get_question/{question_type}/{limit}', 'Client\ScheduleController@get_question');
Route::get('/get_question_type/{schedule_id}', 'Client\ScheduleController@get_question_type');
Route::post('/upload_script', 'Client\ScheduleController@upload_script');
Route::post('/finish_mcq_exam', 'Client\ScheduleController@finish_mcq_exam');
Route::post('/submit_script', 'Client\ScheduleController@submit_script');
// attendance
Route::get('/save_attendance', 'Client\ScheduleController@save_attendance');