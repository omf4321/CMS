<template>
    <div class="user_home">
        <v-container fluid>
            <h3 class="fs-20 m-t-10 dash_title">Invigilator Dashboard <v-icon class="m-l-10 cur-p pos-rel" style="top: 3px" @click="get_invigilator_dashboard_exam">refresh</v-icon></h3> 
            <div>
                <router-link :to="{ name: 'user_script_home'}" v-if="has_role('script_checker')">
                    <v-btn flat color="primary" class="mr-2 mid-btn pos-rel" style="top: 8px"> Script Check </v-btn>
                </router-link>
            </div>           
            <v-divider class="my-3"></v-divider>
            <v-alert :value="schedule_alert" transition="scale-transition" :color="alert_type" icon="info" outline> New discontented schedule has arrived. Please resolve them.
                <router-link :to="{name: 'user_schedule'}">
                    <v-btn flat color="info" class="tiny-btn m-t-0 m-b-0 p-0">Let's Check</v-btn>
                </router-link>
            </v-alert>
            <!-- todays schedule -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="exam_schedule.length>0 && step=='none'">
                <h5 class="fw-600 dash_card_title">Today's Exam</h5>
                <hr class="m-t-5 m-b-5">
                <div v-for="(schedule, s_i) in exam_schedule">
                    <p class="dash_card_subtitle">{{schedule.echelon_name}}</p>
                    <div class="m-b-8" v-for="(data, b_i) in schedule.schedule_data">
                        <div class="row">
                            <div class="col-xs-5 p-r-5">{{data.batches.name}} - {{data.id}}</div>
                            <div :class="{'col-xs-2':true, 'p-l-5':true, 'p-r-5':true, 'lecture_color': data.lecture_sheet}">{{data.subjects.name | first_letter_word}}</div>
                            <div class="col-xs-5 p-l-5">
                                <v-btn outline color="primary" class="tiny-btn m-t-0 m-b-0 p-5" @click="get_batch_attendance(data.batches.id, data.batches.name, s_i, b_i)">Att</v-btn>
                                <v-btn outline :color="takable_exam(s_i, data.batches.id) ? 'warning' : 'primary'" v-if="check_exam_setupable(s_i, b_i)" class="tiny-btn m-t-0 m-b-0 p-5" @click="prepare_setup_exam(s_i, b_i)">Set</v-btn>
                                <!-- <v-btn outline v-if="check_invigilator_completence(s_i, b_i)" :color="check_exam_complete_entry(s_i, b_i) ? 'success' : 'primary'" class="tiny-btn m-t-0 m-b-0 p-5" @click="prepare_exam_entry(s_i, b_i, data.batches.id, data.batches.name, data.subjects.name, data.id)">Ent</v-btn> -->
                                <v-btn outline v-if="check_invigilator_completence(s_i, b_i)" class="tiny-btn m-t-0 m-b-0 p-5" @click="prepare_import_exam(s_i, b_i, data.batches.id, data.batches.name, data.subjects.name, data.id)" :color="check_exam_complete_entry(s_i, b_i) ? 'success' : 'primary'">Ent</v-btn>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
                <div class="text-center m-t-15">
                    <v-text-field v-model="exam_setup_item.authorise_code" label="Authorise Code" style="width: 100px; display: inline-block" type="number"></v-text-field>
                    <v-btn small outline color="success" @click="save_invigilator_attendance">Done</v-btn>
                </div>
            </div>
                        
            <!-- attednace of a batch student -->
            <div class="card take_attendance m-t-10" v-if="step=='batch_attendance'">
                <v-container>
                    <h4 class="m-b-20 fw-600 fs-15">Attendance of {{current_batch_name}} <span class="float-right cur-p" @click="step='none'"><v-icon>close</v-icon></span></h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th class="pos-rel"><span class="" style="top: 5px; left: 76px"><v-checkbox @change="change_check_all" v-model="check_all"></v-checkbox></span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(student, st_i) in batch_student_list">
                                <td>{{student.reg_no}}</td>
                                <td>{{student.name.substring(0,25)}}</td>
                                <td class="p-t-5"><v-checkbox v-model="student.attendance"></v-checkbox></td>
                            </tr>
                        </tbody>
                    </table>  
                    <v-btn class="float-right" color="success" @click="save_batch_attendance">Submit</v-btn>
                </v-container>
            </div>
            <!-- import exam -->
            <div class="card m-t-10" v-if="step=='import_exam'">
                <v-container>
                    <h4 class="m-b-20 fw-600 fs-15">Import Exam of {{exam_schedule[echelon_index].schedule_data[schedule_index].id}} <span class="float-right cur-p" @click="step='none', exam_excel_file = ''"><v-icon>close</v-icon></span></h4>
                    <input type="file" id="file_input" ref="file" name="exam_excel_file" @change="get_exam_file">
                    <v-btn color="success" @click="import_exam">Submit</v-btn>
                </v-container>
                <div class="text-center">
                    <v-btn outline v-if="check_invigilator_completence(echelon_index, schedule_index)" class="m-t-0 m-b-20" @click="prepare_exam_entry" color="primary">Entry One By One</v-btn>
                </div>
            </div>
            <!-- Entry Exam -->
            <div class="card setup_exam m-t-10" v-if="step=='entry_exam'">
                <v-container>
                    <h4 class="m-b-20 fw-600 fs-15">Marks Entry of {{current_batch_name}} - {{current_subject_name | first_letter_word}} ({{student_exam_list.length}}) <span class="float-right cur-p pos-rel" style="top: -3px;" @click="close_mark_entry_card"><span class="m-r-15 pos-rel" style="top:-3px;">{{count_un_entry.length}}</span><v-icon>close</v-icon></span></h4>
                    <v-layout>
                        <v-select v-model="exam_entry.marks_type" :items="marks_type" label="Marks Type" class="m-r-15"></v-select>
                        <v-text-field v-model="exam_entry.base_reg_no" label="Base Reg No" type="number"></v-text-field>
                        <v-checkbox class="pos-rel m-l-10" v-model="exam_entry.serial" label="SL" style="top: 20px"></v-checkbox>
                    </v-layout>
                    <v-layout>                        
                        <v-text-field v-model="exam_entry.mark" label="Mark" :clearable=true type="text"></v-text-field>
                        <v-btn  outline color="primary" class="tiny-btn m-l-10 m-t-5 m-b-0 pos-rel" @click="backspace" style="top: 8px; height: 36px; padding: 0px 15px!important"><v-icon>backspace</v-icon></v-btn>
                    </v-layout>
                    <div class="text-center m-b-15">
                        <v-btn color="success" class="tiny-btn" @click="save_exam_mark('finish')">Finish Entry</v-btn>
                    </div> 
                    <div class="text-danger">{{exam_error_message}}</div>
                    <div class="row m-t-15">
                        <div v-for="n in 9" class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="press_digit(n)" style="min-width: 0px; height: auto; padding: 2px 26px">{{n}}</v-btn>
                        </div>
                    </div>
                    <div class="row">
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="space" style="min-width: 0px; height: auto; padding: 9px 21px"><v-icon>space_bar</v-icon></v-btn>
                        </div>
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="press_digit(0)" style="min-width: 0px; height: auto; padding: 2px 26px">0</v-btn>
                        </div>
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="success" class="block" @click="go_for_exam_entry" style="min-width: 0px; height: auto; padding: 9px 21px"><v-icon>done_outline</v-icon></v-btn>
                        </div>
                    </div>
                     
                    <table class="table table-bordered fs-11 m-t-15">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Mark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(list, std_i) in originial_updated_exam_list">
                                <td @click="get_student_mark(list.reg_no)">{{list.reg_no}}</td>
                                <td @click="get_student_mark(list.reg_no)">{{list.name.substring(0,25)}}</td>
                                <td @click="get_student_mark(list.reg_no)">{{list.mark}}</td>
                            </tr>
                        </tbody>
                    </table>            
                </v-container>
            </div>
            <!-- setup exam -->
            <div class="card setup_exam m-t-10" v-if="step=='setup_exam'">
                <v-container>
                    <h4 class="m-b-20 fw-600 fs-15">Setup Exam of {{current_batch_name}} - {{current_subject_name | first_letter_word}} <span class="float-right cur-p" @click="step='none'"><v-icon>close</v-icon></span></h4>
                    <v-text-field v-model="exam_setup_item.sub_full_mark" label="Subjective Full" type="number"></v-text-field>
                    <v-text-field v-model="exam_setup_item.ob_full_mark" label="Objective Full" type="number"></v-text-field>
                    <v-text-field v-model="exam_setup_item.start_time" mask="time-with-seconds" label="Start Time (H:m:s)" type="text"></v-text-field>
                    <v-text-field v-model="exam_setup_item.full_time" label="Full Time in Minutes" type="number"></v-text-field>
                    <v-text-field v-model="exam_setup_item.script_quantity" label="Script Quantity" type="number"></v-text-field>
                    <v-btn class="float-right" color="success" @click="save_setup_exam">Submit</v-btn>
                </v-container>
            </div>
            
            <!-- Teacher Minutes Detail -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-20 m-b-15 step_edit" v-if="step == 'none'">
                <h5 class="fw-600 dash_card_title">Minutes Detail <span class="m-r-10" v-if="invigilator_minutes_summary.length">({{total_minutes}})</span></h5>
                <div class="schedule_add">
                    <div class="col-xs-5 p-r-5 p-l-5">
                        <template>
                            <v-dialog v-model="date_from_dialog" full-width lazy width="290px">
                                <v-text-field label="Date From" readonly slot="activator" v-model="date_from"></v-text-field>
                                <v-date-picker @change="date_from_dialog = false" v-model="date_from"></v-date-picker>
                            </v-dialog>
                        </template>
                    </div>
                    <div class="col-xs-4 p-r-5 p-l-5">
                        <template>
                            <v-dialog v-model="date_to_dialog" full-width lazy width="290px">
                                <v-text-field label="Date To" readonly slot="activator" v-model="date_to"></v-text-field>
                                <v-date-picker @change="date_to_dialog = false" v-model="date_to"></v-date-picker>
                            </v-dialog>
                        </template>
                    </div>
                    <div class="col-xs-3 p-l-5">
                        <v-btn outline color="primary" class="tiny-btn" @click="get_invigilator_minutes_summary">GO</v-btn>
                    </div>
                </div>
                <hr class="m-t-5 m-b-5" style="float: none; clear: both">
                <table class="table table-bordered fs-12 m-t-10">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch</th>
                            <th>Minutes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="summary in invigilator_minutes_summary">
                            <td>{{summary.schedules.date | moment("D MMM YY")}}</td>
                            <td>{{summary.batches.name}} - {{summary.schedules.subjects.name | first_letter_word}}</td>
                            <td>{{summary.invigilator_minute}}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="float: none; clear: both"></div>
            </div>
        </v-container>
        <v-dialog max-width="600px" persistent v-model="user_message_dialog">
            <v-card v-if="user_messages.length">
                <v-container>
                    <v-card-title class="p-t-5 p-b-15 p-l-0"> 
                        <span class="fw-600" style="font-size: 4vw">A Message From Sohopath</span>
                    </v-card-title>
                    <hr class="m-r-5 m-b-5">
                    <div>
                        <div class="text-justify fs-12" style="white-space: pre-line; word-wrap: break-word;">{{user_messages[0].message}}</div>
                        <hr class="m-t-10 m-b-10">
                        <div class="text-center"><v-btn small color="primary" @click="read_user_message(user_messages[0].id)">I Understand</v-btn></div>
                    </div>
                </v-container>
            </v-card>
        </v-dialog>
        <v-dialog v-model="dialog" hide-dialog persistent width="300">
            <v-card color="#009688" dark>
                <v-card-text>
                    Please Wait..
                    <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    drawer: null,
                    csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    exam_schedule: [],
                    today_schedule: [],
                    next_schedule: [],
                    tommorrow_schedule: [],
                    dialog: false,
                    schedule_alert: false,
                    alert_type: '',
                    check_schedule_dialog: false,
                    schedule_done_item: '',
                    done_status: 'none',
                    tommorrow_reminder: false,
                    check_schedule: [],
                    check_all: false,
                    batch_student_list: [],
                    step: 'none',
                    current_batch_id: '',
                    current_batch_name: '',
                    current_batch_index: '',
                    current_schedule_id: '',
                    teacher_id: window_teacher_id,
                    user_message_dialog: false,
                    user_messages: [],
                    exam_setup_item: {},
                    exam_type: [],
                    marks_type: [{'value': 'mcq', 'text': 'MCQ'}, {'value': 'cq', 'text': 'CQ'}],
                    exam_entry: {marks_type: 'mcq', mark: ''},
                    student_exam_list: [],
                    updated_exam_list: [],
                    originial_updated_exam_list: [],
                    exam_error_message: '',   
                    count_un_entry: [],
                    date_from: '',
                    date_to: '',
                    invigilator_minutes_summary: [],
                    total_minutes: 0,
                    current_subject_name: '',
                    schedule_index: '',
                    echelon_index: '',
                    date_from_dialog: false,
                    date_to_dialog: false,
                    exam_excel_file: ''           
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) { 
                if (window_authorise != 'authorised') {return next('/authentication')}               
                let role = window_user_role.findIndex(x => x == 'invigilator');
                if (role > -1) {
                    next()
                } else {
                    role = window_user_role.findIndex(x => x == 'teacher');
                    if (role>-1) {next('/')}
                    else {
                       next('/user_script_home') 
                    }
                }
            },
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            created() {
                this.manage_alert()
                this.get_invigilator_dashboard_exam()
            },
            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                },
                ordinal_number: function(value) {
                    if (value == 1) {return value = value + 'st'}
                    if (value == 2) {return value = value + 'nd'}
                    if (value == 3) {return value = value + 'rd'}
                    if (value > 3) {return value = value + 'th'}
                }
            },
            methods: {                    
                    get_invigilator_dashboard_exam() {
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_invigilator_dashboard_exam').then(response => {
                            this.exam_schedule = response.data.daily_schedule;
                            var items = this.exam_schedule
                            var data = []
                            for (var i = 0; i < items.length; i++) {
                                var object = {echelon_name: '', echelon_id: '', status: '' , schedule_data: [] }
                                if (i == 0) {
                                    object.echelon_name = items[i].batches.echelons.name
                                    object.echelon_id = items[i].batches.echelon_id
                                    object.schedule_data.push(items[i])
                                    data.push(object)
                                } else {
                                    var index = data.findIndex(x => x.echelon_id == items[i].batches.echelon_id)
                                    if (index > -1) {
                                        data[index].schedule_data.push(items[i])
                                    } else {
                                        object.echelon_name = items[i].batches.echelons.name
                                        object.echelon_id = items[i].batches.echelon_id
                                        object.schedule_data.push(items[i])
                                        data.push(object)
                                    }
                                }
                            }
                            this.exam_schedule = data
                            this.exam_type_list()
                            this.get_user_message()
                            this.save_exam_mark_from_cookies()
                        });
                    },
                    exam_type_list(){
                        Vue.axios.get('/admin/request/exam_type_list').then(response => {
                            this.exam_type = response.data.exam_type_list
                            this.exam_type = this.exam_type.map(arr => { return { value: arr['id'], text: arr['name'] } })
                            this.dialog = false 
                        });
                    },                    
                    prepare_setup_exam(s_i, b_i){
                        this.echelon_index = s_i
                        this.schedule_index = b_i
                        this.current_batch_name = this.exam_schedule[s_i].schedule_data[b_i].batches.name
                        this.current_subject_name = this.exam_schedule[s_i].schedule_data[b_i].subjects.name 
                        this.step = 'setup_exam'
                        this.exam_setup_item = {}
                        this.exam_setup_item.schedule_id = this.exam_schedule[s_i].schedule_data[b_i].id
                        this.exam_setup_item.batch_id = this.exam_schedule[s_i].schedule_data[b_i].batch_id
                        this.exam_setup_item.subject_id = this.exam_schedule[s_i].schedule_data[b_i].subject_id
                        this.exam_setup_item.script_quantity = this.exam_schedule[s_i].schedule_data[b_i].batches.attendance_lists[0].present

                        if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists) {
                            this.exam_setup_item.id = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.id                        
                            this.exam_setup_item.sub_full_mark = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.sub_full_mark
                            this.exam_setup_item.ob_full_mark = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.ob_full_mark
                            this.exam_setup_item.sub_full_mark = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.sub_full_mark
                            this.exam_setup_item.full_time = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.full_time ? this.exam_schedule[s_i].schedule_data[b_i].exam_lists.full_time : this.exam_setup_item.full_time = this.exam_schedule[s_i].schedule_data[b_i].full_time
                            this.exam_setup_item.start_time = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.start_time
                            this.exam_setup_item.script_quantity = this.exam_schedule[s_i].schedule_data[b_i].exam_lists.script_quantity
                        }
                    },
                    prepare_exam_entry(){
                        this.step = 'entry_exam'
                        // this.current_batch_name = batch_name
                        // this.current_batch_id = batch_id
                        // this.current_batch_name = batch_name
                        // this.current_subject_name = subject_name
                        // this.echelon_index = s_i
                        // this.schedule_index = b_i
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_student_exam_entry_list/' + this.current_schedule_id + '/' + this.current_batch_id).then(response => {
                            var item = response.data.student_list                         
                            item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                            this.student_exam_list = item
                            var type = this.exam_entry.marks_type
                            this.originial_updated_exam_list = item.map(x => {return {id: x.id, schedule_id: this.current_schedule_id, reg_no: x.reg_no, name: x.name, batch_id: x.batch_id, mark: type == 'mcq' ? x.exams[0].ob_mark : x.exams[0].sub_mark}})
                            this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.mark)
                            this.dialog = false
                            this.step = 'entry_exam'
                            var index = this.exam_schedule[s_i].exam_lists.findIndex(x => x.batch_id == batch_id)
                            this.exam_setup_item.ob_full_mark = this.exam_schedule[s_i].exam_lists[index].ob_full_mark
                            this.exam_setup_item.sub_full_mark = this.exam_schedule[s_i].exam_lists[index].sub_full_mark
                        });
                    },
                    press_digit(n){
                        this.exam_entry.mark = this.exam_entry.mark ? this.exam_entry.mark + '' + (n) : (n)
                    },
                    go_for_exam_entry(){
                        this.exam_error_message = ''
                        if (!this.exam_entry.mark) {return false}
                        var data = this.exam_entry.mark.replace(/\s\s+/g, ' ')                        
                        data = this.exam_entry.mark.split(' ')
                        if (data.length!=2) {return alert('Entry in wrong way')}
                        if (this.exam_entry.marks_type == 'mcq' && parseInt(data[1])>this.exam_setup_item.ob_full_mark) {return alert('Mark greather than full marks')}
                        if (this.exam_entry.marks_type == 'cq' && parseInt(data[1])>this.exam_setup_item.sub_full_mark) {return alert('Mark greather than full marks')}
                        var index = this.student_exam_list.findIndex(x => x.reg_no == data[0])
                        if (index==-1) {
                            this.exam_error_message = "No Student Found"
                            return false
                        }
                        // add list
                        var index2 = this.updated_exam_list.length ? this.updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1 
                        var index3 = this.originial_updated_exam_list.length ? this.originial_updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1
                        var object = {id: '', reg_no: '', name: '', batch_id: '', mark: ''}                     
                        object.id = this.student_exam_list[index].id
                        object.reg_no = this.student_exam_list[index].reg_no
                        object.name = this.student_exam_list[index].name
                        object.batch_id = this.student_exam_list[index].batch_id
                        object.schedule_id = this.exam_schedule[this.echelon_index].schedule_data[this.schedule_index].id
                        object.mark = data[1]
                        if (index2>-1) {                            
                            this.updated_exam_list[index2] = object
                        }
                        else {
                            this.updated_exam_list.push(object)
                        }
                        if (index3>-1) {
                            this.originial_updated_exam_list[index3] = object
                        }else{
                            this.originial_updated_exam_list.push(object)
                        }
                        this.originial_updated_exam_list = this.originial_updated_exam_list.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })

                        // count unentry
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.mark)

                        // go to next reg no                        
                        this.exam_entry.mark = ''
                        if (this.exam_entry.serial && this.student_exam_list.length > index + 1) {
                            this.exam_entry.mark = this.student_exam_list[index + 1].reg_no + ' '
                        }
                        if (!this.exam_entry.serial && this.exam_entry.base_reg_no) {
                            this.exam_entry.mark = this.exam_entry.base_reg_no
                        }
                        // save database
                        var x = JSON.stringify(this.updated_exam_list)
                        this.$cookies.set('unsaved_exam_mark', x);

                        if (this.updated_exam_list.length==5) {
                            this.save_exam_mark()
                        }
                    },
                    get_student_mark(reg_no){
                        this.exam_entry.mark = reg_no + ' '
                        $('html,body').animate({ scrollTop: 0 }, 'slow');
                    },
                    backspace(){
                        if (this.exam_entry.mark) {
                            this.exam_entry.mark = this.exam_entry.mark.substring(0, this.exam_entry.mark.length-1)
                        }
                    },
                    space(){
                        if (this.exam_entry.mark) {
                            this.exam_entry.mark = this.exam_entry.mark + ' '
                        }
                    },
                    save_exam_mark(finish){
                        var item = {}
                        item.mark_type = this.exam_entry.marks_type ? this.exam_entry.marks_type : 'mcq'
                        item.student_mark_list = this.updated_exam_list
                        if (finish == 'finish') {
                            item.entry_completed = 'true'
                            if (!this.updated_exam_list.length) {
                                item.student_mark_list = this.originial_updated_exam_list
                            }
                        }
                        Vue.axios.post('/user/exam/save_exam_mark', item).then(response => {
                            var ids = response.data.ids
                            for (var i = 0; i < ids.length; i++) {
                                var index = this.updated_exam_list.findIndex(x => x.id == ids[i])
                                if (index>-1) {this.updated_exam_list.splice(index,1)}
                            }
                            if (this.count_un_entry==0) {this.get_invigilator_dashboard_exam()}
                            var x = JSON.stringify(this.updated_exam_list)
                            this.$cookies.set('unsaved_exam_mark', x);
                            if (finish == 'finish') {this.step = 'none'}
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    save_exam_mark_from_cookies(){
                        var unsaved_exam_mark = this.$cookies.get('unsaved_exam_mark')
                        if (unsaved_exam_mark) {
                            var x = JSON.parse(unsaved_exam_mark)
                            if (x.length) {
                                this.updated_exam_list = x
                                this.save_exam_mark()
                            }
                        }
                    },
                    close_mark_entry_card(){
                        this.step='none'
                        if (this.updated_exam_list.length>0) {
                            this.save_exam_mark()
                        }
                        this.originial_updated_exam_list = []
                    },
                    save_setup_exam() {                        
                        this.dialog = true
                        Vue.axios.post('/user/exam/save_setup_exam', this.exam_setup_item).then(response => {
                            $('.user_nav').addClass('successful')
                            this.dialog = false;
                            this.exam_schedule[this.echelon_index].schedule_data[this.schedule_index].exam_lists = response.data.exam_list
                            this.step = 'none'
                            setTimeout(function() {
                                $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            if (error.response.status == 419) {
                                alert('Session Expired. This page is going to reload')
                                location.reload();
                            }
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    save_invigilator_attendance() {                        
                        this.dialog = true
                        Vue.axios.post('/user/exam/save_invigilator_attendance', this.exam_setup_item).then(response => {
                            $('.user_nav').addClass('successful')
                            this.dialog = false;                            
                            this.step = 'none'
                            setTimeout(function() {
                                $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            if (error.response.status == 419) {
                                alert('Session Expired. This page is going to reload')
                                location.reload();
                            }
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    check_invigilator_completence(s_i, b_i) {    
                        if (!this.exam_schedule[s_i].schedule_data[b_i].exam_lists) {
                            return false
                        }                    
                        // if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id == window_teacher_id || this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id == null) {
                        //     if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists.status == 'setup') {
                        //         return true
                        //     }
                        // }                                          
                        return true
                    },
                    check_exam_setupable(s_i, b_i){
                        if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists) {
                            if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id == window_teacher_id || !this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id) {
                                return true
                            }
                        }
                        if (!this.exam_schedule[s_i].schedule_data[b_i].exam_lists && this.exam_schedule[s_i].schedule_data[b_i].batches.attendance_lists.length && this.exam_schedule[s_i].schedule_data[b_i].batches.attendance_lists[0].status == 'completed'){
                            return true
                        }
                        return false;   
                    },
                    check_exam_complete_entry(s_i, b_i){
                        if (!this.exam_schedule[s_i].schedule_data[b_i].exam_lists) {
                            return false
                        }                    
                        if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id == window_teacher_id || !this.exam_schedule[s_i].schedule_data[b_i].exam_lists.invigilator_id) {
                            if (this.exam_schedule[s_i].schedule_data[b_i].exam_lists.status != 'setup') {
                                return true
                            }
                        }
                        return false;   
                    },
                    takable_exam(s_i, batch_id){
                        if (!this.exam_schedule[s_i].exam_lists) {
                            return false
                        }                    
                        if (!this.exam_schedule[s_i].exam_lists.invigilator_id) {
                            return true
                        }
                        return false;   
                    },
                    check_student_attendance(item){
                        if (item.length &&  item[0].status == 'done') {
                            return true
                        }
                        if (item.length && item[0].teacher_id && item[0].teacher_id != this.teacher_id) {
                            return true
                        }
                        return false
                    },
                    manage_alert() {
                        // this.get_discontented_schedule_alert()
                    },                                        
                    
                    get_batch_attendance(batch_id, batch_name, s_i, b_i) {
                        this.current_batch_id = batch_id
                        this.current_batch_name = batch_name
                        this.current_batch_index = b_i
                        this.echelon_index = s_i
                        this.dialog = true
                        Vue.axios.post('/user/schedule/get_batch_attendance/' + batch_id).then(response => {
                            this.batch_student_list = response.data.student_list
                            var item = this.batch_student_list.map(x => {return {
                                attendance_id: x.todays_attendance[0].id,
                                student_id: x.id,
                                reg_no: x.reg_no,
                                name: x.name,
                                attendance: x.todays_attendance[0].attendance,
                                batch_id: x.batch_id,
                                attendance_list_id: x.todays_attendance[0].attendance_list_id
                            }})
                            item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                            this.batch_student_list = item
                            this.dialog = false
                            this.step = 'batch_attendance'
                        }, error => {
                            if (error.response.status == 419) {
                                alert('Session Expired. This page is going to reload')
                                location.reload();
                            }
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    },
                    save_batch_attendance(){
                        this.dialog = true
                        var item = {}
                        item.batch_id = this.current_batch_id
                        item.schedule_type = this.exam_schedule[this.echelon_index].schedule_type
                        item.schedule_id = this.exam_schedule[this.echelon_index].id
                        item.student_attendance = this.batch_student_list
                        Vue.axios.post('/user/schedule/save_batch_attendance', item).then(response => {
                            this.step = 'none'
                            this.dialog = false
                            this.get_invigilator_dashboard_exam()
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                            $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    prepare_import_exam(s_i, b_i, batch_id, batch_name, subject_name, id) {                        
                        this.current_batch_index = b_i
                        this.current_schedule_id = id
                        this.current_batch_name = batch_name
                        this.current_batch_id = batch_id
                        this.current_batch_name = batch_name
                        this.current_subject_name = subject_name
                        this.echelon_index = s_i
                        this.schedule_index = b_i

                        this.step = 'import_exam'
                    },
                    get_exam_file(){
                        this.exam_excel_file = this.$refs.file.files[0];
                    },
                    import_exam(){
                        this.dialog = true
                        var item = new FormData()
                        item.append('exam_excel_file', this.exam_excel_file)
                        item.append('schedule_data', this.exam_schedule[this.echelon_index].schedule_data[this.schedule_index].id)
                        Vue.axios.post('/user/exam/import_exam', item).then(response => {
                            this.dialog = false
                            if (response.data.unfound.length) {
                                alert('Imported. But these reg no are not found in this exam: ' + response.data.unfound.join(', '))
                            } else {  
                                alert('Import Succesfully. Now check the marks.')
                                this.exam_excel_file = ''                              
                                this.prepare_exam_entry()
                            }
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                            $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            alert(error.response.data.error)
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    get_invigilator_minutes_summary(){
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_invigilator_minutes_summary/' + this.date_from + '/' + this.date_to).then(response => {
                            this.invigilator_minutes_summary = response.data.attendance_list
                            var item = this.invigilator_minutes_summary
                            this.dialog = false
                            var total = 0
                            for (var i = 0; i < item.length; i++) {
                                total += item[i].invigilator_minute
                            }
                            this.total_minutes = total
                        });
                    },
                    change_check_all(){
                        var value = this.check_all
                        for (var i = 0; i < this.batch_student_list.length; i++) {
                            this.batch_student_list[i].attendance = value
                        }
                    },
                    get_user_message() {
                        Vue.axios.get('/user/schedule/get_user_message').then(response => {
                            this.user_messages = response.data.user_message
                            if(this.user_messages.length){this.user_message_dialog = true}
                        });
                    },
                    read_user_message(id){
                        this.dialog = true
                        Vue.axios.post('/user/schedule/read_user_message/' + id).then(response => {
                            this.dialog = false
                            var index = this.user_messages.findIndex(x => x.id == id)
                            this.user_messages.splice(index, 1)
                            if (this.user_messages.length==0) {this.user_message_dialog = false}                            
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                            $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    }
            }
    } 
</script>

<style>
  .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
    .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
  .v-messages {
    display: none;
  }
  .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
</style>