<template>
    <div class="schedule_manage">
        <v-container fluid class="admin_home">
            <h3 class="fs-22 m-t-10 m-b-25 dash_title">Manage Schedule</h3>
            <v-divider class="my-3"></v-divider>
            <!-- Schedule Card -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 schedule_card" v-if="!force_update">
                <v-container fluid>
                    <div class="fw-600 dash_card_title">
                        <span class="m-r-10 fs-small">Schedule ({{current_schedule_date | moment("D MMM YY")}})</span>
                        <v-btn small outline color="primary" class="tiny-btn pos-rel m-r-5" @click="today_schedule">Today</v-btn>
                        <v-btn small outline color="primary" class="tiny-btn pos-rel m-r-5" @click="next_day_schedule">Next Day</v-btn>
                        <v-btn small outline color="primary" class="tiny-btn pos-rel m-r-20" @click="previous_day_schedule">Prev Day</v-btn>
                        <v-btn small class="mid-btn" @click="get_dashboard_attendance_list" style="color: #fff; background: #607d8b">Attendance SMS</v-btn>
                        <div class="float-right hidden-xs">
                            <span class="exam_color fs-12 m-r-10">Exam</span>
                            <span class="lecture_color fs-12 m-r-10">Lecture Sheet</span>
                        </div>
                    </div>
                    <hr class="m-t-5 m-b-5">
                    <div class="m-b-15 row">
                        <div class="col-md-2 col-xs-4 p-r-5">
                            <v-autocomplete @change="change_schedule_layout" class="m-r-15" :clearable=true :disabled="echelon.length==0" :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete>
                        </div>
                        <div class="col-md-2 col-xs-4 p-r-5 p-l-5">
                            <v-select class="m-r-15" :items="period" label="period" v-model="dialogItem.period" @change="change_schedule_layout"></v-select>
                        </div>
                        <!-- <div class="col-md-2 col-xs-4 p-l-5">
                            <v-select class="m-r-15" :items="show_option" label="Show" v-model="dialogItem.show_option"></v-select>
                        </div> -->
                        <div class="col-md-2 col-xs-4 p-r-5">
                            <v-text-field class="m-r-15" hide-details label="Time" single-line v-model="dialogItem.time" :clearable=true></v-text-field>
                        </div>
                        <div class="col-md-2 col-xs-4 p-r-5 p-l-5">
                            <v-dialog v-model="date_dialog" full-width lazy ref="date_ref" width="290px">
                                <v-text-field label="Date" :clearable=true readonly slot="activator" v-model="dialogItem.date"></v-text-field>
                                <v-date-picker @change="date_dialog = false" ref="picker" v-model="dialogItem.date"></v-date-picker>
                            </v-dialog>
                        </div>
                        <div class="col-md-2 col-xs-4 p-l-5">
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" @click="filter_schedule">Go</v-btn>
                        </div>
                    </div>
                    <!-- body -->
                    <div class="text-right">
                        <v-btn small color="primary" class="pos-rel m-r-10" @click="edit_schedule_dialog = true, editedIndex = -1">New Schedule</v-btn>
                    </div>
                    <div v-for="(schedule, s_i) in dashboard_schedule">
                        <div class="row">
                            <div class="col-md-2 fw-600 fs-small">
                                {{schedule.echelon_name}}
                            </div>
                        </div>
                        <hr class="m-b-10 m-t-5" style="border-color: #444;">
                        <div class="row" v-for="(batch, b_i) in schedule.batch_data">
                            <div class="col-md-2 fw-600 hind m-b-8 m-t-8">
                                {{batch.batches.name}}
                            </div>
                            <div class="col-md-10">
                                <div class="row">
                                    <div :class="{'col-md-3':true, 'col-xs-6':true, 'm-t-8': true, 'm-b-8':true, 'exam_border': subject.schedule_type=='exam', 'lecture_border': subject.lecture_sheet, 'pos-rel': true}" v-for="(subject, s_i) in batch.subject_data">
                                        <div>
                                            <v-icon color="primary" class="fs-20 m-r-5" v-if="subject.attendance_status == 'complete' && subject.teacher_attendance_status !='done'">emoji_people</v-icon>
                                            <v-icon color="primary" class="fs-20 m-l-5" v-if="subject.teacher_attendance_status =='done'">done_all</v-icon>
                                            <span>{{subject.period | ordinal_number}}</span> - 
                                            <span class="fw-500">{{subject.subject_name | first_letter_word}}</span>
                                            <span v-if="subject.schedule_type == 'exam' || subject.schedule_type == 'online_exam'">({{subject.id}})</span>
                                            <v-icon class="fs-15 m-l-5 cur-p" style="right:5px" @click="edit_schedule(subject, s_i)">edit</v-icon>
                                        </div>
                                        <div class="fs-12">
                                            <span :class="{'cur-p': true, 'confirmed_color': subject.status == 'confirmed'}" @click="confirm_teacher(subject.id, subject.teacher_id, s_i, b_i, sb_i)">{{subject.teacher_name}}</span> 
                                        </div>
                                        <div class="" v-if="subject.online_class_url && !subject.online_class_status">
                                            <v-btn class="tiny-btn" outline color="primary" @click="change_online_class_status(subject.id, 'begin')">Start</v-btn>
                                        </div>
                                        <div class="" v-if="subject.online_class_status == 'begin'">
                                            <a :href="subject.online_class_url" target="_blank"><v-btn class="tiny-btn" outline color="primary" style="padding: 3px 5px; font-size: 10px">Join</v-btn></a>

                                            <v-btn v-if="subject.schedule_type == 'online_exam' && subject.online_exam_status != 'exam_start'" class="tiny-btn" outline color="primary" @click="change_online_class_status(subject.id, 'exam_start')" style="padding: 3px 5px; font-size: 10px">Start Exam</v-btn>

                                            <v-btn v-if="(subject.schedule_type == 'online_exam' && subject.online_exam_status == 'exam_start') || subject.schedule_type == 'online_class'" class="tiny-btn" outline color="primary" @click="change_online_class_status(subject.id, 'finish')" style="padding: 3px 5px; font-size: 10px">Finish</v-btn>
                                        </div>
                                        <span class="fs-13 success--text " v-if="subject.online_class_status == 'finish'">Finish</span>
                                        <div class="fs-10">{{subject.online_class_url ? subject.online_class_url.substring(subject.online_class_url.lastIndexOf('/') + 1) : ''}} {{subject.link_creator_name ? '(' + subject.link_creator_name + ')' : ''}}</div>

                                        <!-- <div v-if="dialogItem.show_option=='advance'">Ch: {{subject.chapter_text}}, Topic: {{subject.topic}}</div> -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <hr class="m-b-3 m-t-3" style="border-color: #eee">
                            </div>
                        </div>
                    </div>
                </v-container>
            </div>
            <!-- Lecture Sheet Card -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 lecture_card" v-if="dashboard_lecture_schedule.length">
                <v-container fluid>
                    <h4 class="fw-600 dash_card_title">Lecture Sheet List</h4>
                    <hr class="m-t-5 m-b-5">
                    <div class="row">
                        <div class="col-md-3 m-b-10" v-for="schedule in dashboard_lecture_schedule">
                            <div class="fs-14 fw-600">{{schedule.batches.echelons.name}} - {{schedule.date  | moment("D MMM")}}</div>
                            <div class="fs-12">{{schedule.subjects ? schedule.subjects.name : ''}} - (<span class="m-r-2 m-l-2">{{schedule.batches.name}}</span>)</div>
                            <div class="fs-11">{{schedule.teachers ? schedule.teachers.name : ''}}</div>
                            <!-- <div class="fs-11">Ch: {{schedule.chapter_text}} - {{schedule.topic}}</div> -->
                        </div>
                    </div>
                </v-container>
            </div>
            <!-- Exam List Card -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 lecture_card" v-if="dashboard_exam_schedule.length">
                <v-container fluid>
                    <h4 class="fw-600 dash_card_title">Upcomming Exam List</h4>
                    <hr class="m-t-5 m-b-5">
                    <div class="" v-for="schedule_list in dashboard_exam_schedule">
                        <div class="fs-16 fw-600">{{schedule_list.date | moment("D MMM")}}</div>
                        <hr class="m-t-5 m-b-5">
                        <div class="row">
                            <div class="col-md-3 m-b-10" style="height: 85px; overflow: hidden" v-for="schedule in schedule_list.schedules">
                                <div class="fs-13 fw-600">{{schedule.batches.echelons.name}}</div>
                                <div class="fs-12">{{schedule.subjects ? schedule.subjects.name : ''}} - (<span class="m-r-2 m-l-2">{{schedule.batches.name}}</span>)</div>
                                <div class="fs-11">{{schedule.id}} - {{schedule.teachers ? schedule.teachers.name : ''}}</div>
                                <!-- <div class="fs-11">Ch: {{schedule.chapter_text}} - {{schedule.topic}}</div> -->
                            </div>
                        </div>
                    </div>
                </v-container>
            </div>
        </v-container>
        <!-- edit dialog of schedule -->
        <v-dialog max-width="600px" persistent v-model="edit_schedule_dialog">
            <!-- @add_item_button -->
            <v-card>
                <v-card-title>
                    <!-- @add_item_title -->
                    <span class="fs-medium">Edit Schedule</span>
                    <v-spacer></v-spacer>
                    <v-btn @click.native="close_dialog" icon style="right: 5px; top: 5px; position: absolute!important"> <v-icon> close </v-icon></v-btn>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md class="p-t-0">
                        <!-- @add_item_field -->
                        <template>
                            <v-form lazy-validation ref="edit_form" v-model="valid">
                                <div class="row">
                                    <div class="col-xs-6 col-md-4">
                                        <v-autocomplete :disabled="editedIndex>-1" :items="echelon" hide-selected label="Class" @change="get_batch" v-model="editedItem.echelon_id" :rules="[v => !!v || 'required']">
                                        </v-autocomplete>
                                    </div>
                                    <div class="col-xs-6 col-md-4">
                                        <v-autocomplete :disabled="editedIndex>-1" :items="batch" hide-selected label="Batch" v-model="editedItem.batch_id" :rules="[v => !!v || 'required']"> </v-autocomplete>
                                    </div>
                                    <div class="col-xs-6 col-md-4">
                                        <v-text-field :rules="[v => !!v || 'required']" label="Period" required v-model="editedItem.period"> </v-text-field>
                                    </div>
                                    <div class="col-xs-6 col-md-4">
                                        <v-autocomplete :items="subject" :rules="[v => !!v || 'required']" id="edit_subject" label="Subject" v-model="editedItem.subject_id"> </v-autocomplete>
                                    </div>
                                    <div class="col-xs-6 col-md-4">
                                        <v-autocomplete :items="teacher" :rules="[v => !!v || 'required']" label="Teacher" v-model="editedItem.teacher_id"> </v-autocomplete>
                                    </div>
                                    <div class="col-xs-6 col-md-4">
                                        <v-select :clearable="true" :items="schedule_type" :rules="[v => !!v || 'required']" label="Schedule Type" required v-model="editedItem.schedule_type">
                                        </v-select>
                                    </div>
                                    <div class="col-xs-6 col-md-4" v-if="editedIndex == -1"> 
                                        <v-dialog full-width lazy ref="date_ref1" width="290px">
                                            <v-text-field label="Date" :clearable=true readonly slot="activator" v-model="editedItem.date" :rules="[v => !!v || 'required']"></v-text-field>
                                            <v-date-picker ref="picker" v-model="editedItem.date"></v-date-picker>
                                        </v-dialog>
                                    </div>
                                    <div class="col-xs-6 col-md-4"> 
                                        <v-text-field id="edit_time" mask="time-with-seconds" label="Time" v-model="editedItem.time">
                                        </v-text-field>
                                    </div>
                                    <div class="pb-0 pt-0 col-xs-12 col-md-6">
                                        <v-checkbox v-model="editedItem.lecture_sheet" label="Lecture Sheet"></v-checkbox>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-12 col-md-8" v-if="editedItem.schedule_type == 'online_class' || editedItem.schedule_type == 'online_exam'">
                                            <v-text-field label="Online Class Link" v-model="editedItem.online_class_url">
                                            </v-text-field>
                                        </div>
                                        <!-- <div class="col-xs-12 col-md-4">
                                            <v-checkbox v-model="editedItem.hosting" label="I Am Hosting"></v-checkbox>
                                        </div> -->
                                    </div>
                                    <div class="pb-0 pt-0 col-xs-12 col-md-3" v-if="editedItem.schedule_type == 'online_class' || editedItem.schedule_type == 'online_exam'">
                                        <v-text-field label="Class Status" v-model="editedItem.online_class_status">
                                        </v-text-field>
                                    </div>
                                    <div class="pb-0 pt-0 col-xs-12 col-md-3" v-if="editedItem.schedule_type == 'online_class' || editedItem.schedule_type == 'online_exam'">
                                        <v-text-field label="Exam Status" v-model="editedItem.online_exam_status">
                                        </v-text-field>
                                    </div>
                                    <div class="pb-0 pt-0 col-xs-12 col-md-3" v-if="editedItem.schedule_type == 'online_class' || editedItem.schedule_type == 'online_exam'">
                                        <v-text-field label="Teacher Status" v-model="editedItem.teacher_attendance_status">
                                        </v-text-field>
                                    </div>
                                </div>
                                <hr class="m-b-8 m-t-8">
                                <v-checkbox class="m-b-15" v-model="editedItem.sms" label="SMS to Student to Notify Change"></v-checkbox>
                                <!-- @add_item_submit -->
                                <v-btn small color="success" :disabled="!valid || loading" :loading="loading" @click="edit_dashboard_schedule()">Save</v-btn>
                                <v-btn small color="error" :disabled="!valid || loading" :loading="loading" @click="edit_dashboard_schedule('delete')">Delete</v-btn>
                            </v-form>
                        </template>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- sms dialog -->
        <v-dialog v-model="sms_dialog" persistent width="1000">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">SMS Report <v-icon class="pos-a" style="right: 15px;" @click="sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">
                    <div class="p-r-15 p-l-15">Current Balance: <span class="fw-600">{{ sms_balance }}</span> <span class="float-right">SMS Failed: {{sms_failed.length}}/{{sms_report.length}}</span></div>                    
                    <v-container grid-list-md>
                        <table class="table table-bordered fs-12">
                            <thead>
                              <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>SMS</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="report in sms_report">
                                <td>{{report.reg_no}}</td>
                                <td>{{report.name}}</td>
                                <td>{{report.sms}}</td>
                                <td :class="{'text-danger': report.status !='OK'}">{{report.status == 'OK' ? 'Sent Successfully' : report.status}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- Attedance Dialog -->
        <v-dialog v-model="attendance_dialog" persistent width="800">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">Attendance Report ({{attendance_count}})<v-icon class="pos-a" style="right: 15px;" @click="attendance_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">                   
                    <v-container grid-list-md>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th class="pos-rel">Attendance <span class="pos-a" style="top: 5px; left: 100px"><v-checkbox @change="change_check_all" v-model="check_all"></v-checkbox></span><span class="fs-13 pos-a" style="top: 8px; left: 130px">Check All</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- function get_dashboard_attendance_list -->
                                <tr v-for="(attendance, st_i) in dashboard_attendance">
                                    <td>{{attendance.echelon_name}}</td>
                                    <td>{{attendance.name}}</td>
                                    <td :class="{'confirmed_color': attendance.status}">{{attendance.status ? attendance.status : 'not complete'}} ({{attendance.students}})</td>
                                    <td v-if="attendance.sms==0"><v-checkbox :disabled="!attendance.status" v-model="attendance.attendance"></v-checkbox></td>
                                    <td v-if="attendance.sms==1"><v-icon>done</v-icon></td>
                                </tr>
                            </tbody>
                        </table> 
                        <hr>
                        <!-- <v-btn color="success" small @click="send_attendance_sms">Send SMS</v-btn> -->
                        <v-layout style="width: 400px">
                            <v-btn @click="send_attendance_sms" small color="success">submit</v-btn> 
                            <v-autocomplete class="p-t-0 m-t-0 m-l-15" :items="attendance_sms_type_data" v-model="attendance_sms_type"></v-autocomplete>
                            <v-autocomplete class="p-t-0 m-t-0 m-l-15" :items="destination_number_data" v-model="destination_number"></v-autocomplete>
                        </v-layout>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- loader dialog -->
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
                schedule_alert: false,
                original_dashboard_schedule: [],
                dashboard_schedule: [],
                dialogItem: {
                    days: 3,
                    show_option: 'standard',
                    period: 'all'
                },
                echelon: [],
                branch: [],
                period: [{'value': 'all', 'text': 'All'}, {'value': 1, 'text': '1st'}, {'value': 2, 'text': '2nd'}, {'value': 3, 'text': '3rd'}], 
                show_option: [{'value': 'standard', 'text': 'Standard'}, {'value': 'advance', 'text': 'Advance'}], 
                schedule_type: [{'value': 'class', 'text': 'Class'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'close', 'text': 'Close'}],
                dialog: false,
                valid: true,
                loading: false,
                edit_schedule_dialog: false,
                teacher: [],
                original_batch:[], 
                original_subject: [],
                chapter: [],
                subject: [],
                batch: [],
                editedItem: {echelons:{}, subjects:{}, hosting: false},
                rules: {
                    required: value => !!value || 'Required.'
                },
                tab_active: null,
                exchangable_schedule_list: [],
                exchangable_id: '',
                current_schedule_date: '',
                dashboard_lecture_schedule: [],
                teacher_sign_item: {batch: []},
                dashboard_attendance: [],
                dashboard_exam_schedule: [],
                sms: false,
                sms_report: [],
                sms_balance: '',
                sms_dialog: false,
                sms_failed: [],
                campaign_name: '',
                attendance_dialog: false,
                check_all: false,
                attendance_sms_type_data: [{'value': 'absent', 'text': 'Absent'}, {'value': 'present', 'text': 'Present'}, {'value': 'all', 'text': 'All'}],
                destination_number_data: [{'value': 'guardian', 'text': 'SMS to Guardian'}, {'value': 'student', 'text': 'SMS to Student'}],
                destination_number: 'guardian',
                attendance_sms_type: 'absent',
                force_update: false,
                editedIndex: -1,
                date_dialog: false,
                attendance_count: 0          
            }
        },
        props: ['source'],
        beforeRouteEnter(to, from, next) {
            if (window_latest_unpaid.overdue == 'true') {
                next('/billing_invoice')
            }
            let p = window_admin_permissions.findIndex(x => x.name == 'edit_schedule');
            if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                next()
            } else {
                next('/404')
            }
        },
        created() {
            this.get_dashboard_schedule()
            this.admin_branch_list()  
            Echo.channel('chat')
                .listen('.MessageSent', (e) => {
                this.subject_layout(e.schedule)
                this.$forceUpdate()
            });         
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
                if (value > 3) {return value = value + 'th'} }
        },
        computed: {
            formTitle() {
                return this.editedIndex === -1 ? 'New Batch' : 'Edit Batch'
            }
        },
        methods: {
            logout() {
                    this.$refs.logout_form.submit()
                },
                admin_branch_list() {
                    this.branch = window_branch
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list();
                        this.admin_batch_list()
                        setTimeout(function() { 
                            this.admin_subject_list()
                            this.teacher_list()
                        }.bind(this), 2000)
                    }
                    this.branch = this.branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    this.get_dashboard_exam_schedule()
                    this.get_dashboard_lecture_schedule() 
                },
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                admin_batch_list() {
                    this.axios.get('/admin/request/batch_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.original_batch = response.data.batch_list_by_branch;
                    });
                },
                admin_subject_list() {
                    this.axios.get('/admin/request/subject_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.original_subject = response.data.subject_list_by_branch;
                    });
                },
                teacher_list() {
                    this.axios.get('/admin/request/teacher_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.teacher = response.data.teacher_list_by_branch;
                        this.teacher = this.teacher.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    });
                },
                get_subject() {
                    var echelon_id = this.editedItem.echelon_id
                    if (echelon_id == 8) {echelon_id = 7}
                    var subject = Object.assign([], this.original_subject);
                    let filter_subject = subject.filter(x => {return x.echelon_id == echelon_id })
                    this.subject = filter_subject.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                get_batch() {
                    var batch = Object.assign([], this.original_batch);
                    let filter_batch = batch.filter(x => {return x.echelon_id == this.editedItem.echelon_id })
                    this.batch = filter_batch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    this.get_subject()
                },
                next_day_schedule(){
                    this.dialogItem.date = this.$moment(this.dialogItem.date).add(1, 'days').format('YYYY-MM-DD')
                    this.get_dashboard_schedule()
                },
                today_schedule(){
                    this.dialogItem.date = this.$moment().format('YYYY-MM-DD')
                    this.get_dashboard_schedule()
                },
                previous_day_schedule(){
                    this.dialogItem.date = this.$moment(this.dialogItem.date).subtract(1, 'days').format('YYYY-MM-DD')
                    this.get_dashboard_schedule()
                },
                get_dashboard_schedule() {
                    this.dialog = true
                    var current_date = this.dialogItem.date
                    if (current_date) {
                        var date = this.$moment(current_date).format('YYYY-MM-DD');
                    } else {
                        var date = this.$moment(new Date()).format('YYYY-MM-DD');
                    }
                    this.axios.get('/admin/schedule/get_dashboard_schedule/' + date).then(response => {
                        this.current_schedule_date = date
                        this.original_dashboard_schedule = response.data.daily_schedule;
                        this.dialog = false
                        this.change_schedule_layout()
                    });
                },
                get_dashboard_lecture_schedule() {
                    this.axios.get('/admin/schedule/get_dashboard_lecture_schedule').then(response => {
                        this.dashboard_lecture_schedule = response.data.daily_schedule;                        
                        this.dashboard_lecture_schedule = this.dashboard_lecture_schedule.sort(function(a,b){return new Date(a.date) - new Date(b.date)})
                    });
                },
                get_dashboard_exam_schedule() {
                    this.axios.get('/admin/schedule/get_dashboard_exam_schedule').then(response => {
                        // this.dashboard_exam_schedule = response.data.daily_schedule;
                        var item = response.data.daily_schedule
                        var items = []
                        for (var i = 0; i < item.length; i++) {
                            var object = {date: '', schedules: []}
                            if (i==0) {
                                object.date = item[i].date
                                object.schedules.push(item[i])
                                items.push(object)
                            } else {
                                var index = items.findIndex(x => x.date == item[i].date)
                                if (index>-1) {
                                    items[index].schedules.push(item[i])
                                } else {
                                    object.date = item[i].date
                                    object.schedules.push(item[i])
                                    items.push(object)
                                }
                            }
                        }
                        for (var i = 0; i < items.length; i++) {
                            items[i].schedules.sort(function(a,b){
                                return a.echelon_id - b.echelon_id
                            })
                        }
                        this.dashboard_exam_schedule = items
                    });
                },
                change_schedule_layout() {
                    this.subject_layout()
                },
                subject_layout(changed_schedule) {
                    var schedule = []
                    var item = this.original_dashboard_schedule
                    for (var i = 0; i < item.length; i++) {
                        var schedule_object = {echelon_id: '', id: '', echelon_name: '', batch_data: [] }
                        var batch_object = {batch_id: '', batches: {}, subject_data: []}

                        var subject_object = {}
                        var echelon_index
                        var batch_index

                        if (i == 0) {
                            echelon_index = -1
                            batch_index = -1
                        } else {
                            echelon_index = schedule.findIndex(e => e.echelon_id == item[i].batches.echelon_id)
                            if (echelon_index>-1) {
                                batch_index = schedule[echelon_index].batch_data.findIndex(x => x.batch_id == item[i].batch_id)
                            }
                        }

                        batch_object.batch_id = item[i].batch_id
                        batch_object.batches = item[i].batches

                        subject_object.id = item[i].id
                        subject_object.echelon_id = item[i].batches.echelon_id
                        subject_object.batch_id = item[i].batch_id
                        subject_object.subject_id = item[i].subject_id
                        subject_object.subject_name = item[i].subjects ? item[i].subjects.name : ''
                        subject_object.teacher_id = item[i].teacher_id
                        subject_object.teacher_name = item[i].teachers ? item[i].teachers.name : ''
                        subject_object.teacher_mobile = item[i].teachers ? item[i].teachers.mobile : ''
                        subject_object.schedule_type = item[i].schedule_type
                        subject_object.batches = item[i].batches
                        subject_object.teacher_batches = item[i].teacher_batches
                        subject_object.lecture_sheet = item[i].lecture_sheet
                        subject_object.status = item[i].status
                        subject_object.chapter_text = item[i].chapter_text
                        subject_object.topic = item[i].topic
                        subject_object.period = item[i].period
                        subject_object.time = item[i].time
                        subject_object.status = item[i].status
                        subject_object.attendance_status = item[i].batches.attendance_lists.length ? item[i].batches.attendance_lists[0].status : ''
                        subject_object.teacher_attendance_status = item[i].teacher_attendance_status
                        subject_object.online_class_url = item[i].online_class_url
                        subject_object.online_class_status = item[i].online_class_status
                        subject_object.online_exam_status = item[i].online_exam_status
                        subject_object.link_creator_id = item[i].link_creator_id
                        subject_object.link_creator_name = item[i].link_creator_name

                        if (changed_schedule) {
                            for (var j = 0; j < changed_schedule.length; j++) {
                                if (item[i].id == changed_schedule[j].id) {                                    
                                    subject_object.teacher_attendance_status = changed_schedule[j].teacher_attendance_status
                                    subject_object.online_class_url = changed_schedule[j].online_class_url
                                    subject_object.online_class_status = changed_schedule[j].online_class_status
                                    subject_object.online_exam_status = changed_schedule[j].online_exam_status
                                    subject_object.link_creator_id = changed_schedule[j].link_creator_id
                                    subject_object.link_creator_name = changed_schedule[j].link_creator_name
                                }
                            }
                        }


                        if (echelon_index == -1) {
                            schedule.push(schedule_object)
                            schedule[schedule.length - 1].echelon_id = item[i].batches.echelon_id
                            schedule[schedule.length - 1].echelon_name = item[i].batches.echelons.name
                            schedule[schedule.length - 1].batch_data.push(batch_object)
                            schedule[schedule.length - 1].batch_data[0].subject_data.push(subject_object)
                        }
                        if (echelon_index > -1) {
                            if (batch_index>-1) {
                                schedule[echelon_index].batch_data[batch_index].subject_data.push(subject_object)
                                // sort by period
                                schedule[echelon_index].batch_data[batch_index].subject_data = schedule[echelon_index].batch_data[batch_index].subject_data.sort(function(a,b){return a.period - b.period})
                            } else {
                                schedule[echelon_index].batch_data.push(batch_object)
                                schedule[echelon_index].batch_data[schedule[echelon_index].batch_data.length-1].subject_data.push(subject_object)
                                // sort by period
                                schedule[echelon_index].batch_data[schedule[echelon_index].batch_data.length-1].subject_data = schedule[echelon_index].batch_data[schedule[echelon_index].batch_data.length-1].subject_data.sort(function(a,b){return a.period - b.period})
                            }
                        }
                    }
                    if (this.dialogItem.echelon_id) {
                        schedule = schedule.filter(x => x.echelon_id == this.dialogItem.echelon_id)
                    }
                    if (this.dialogItem.period != 'all') {
                        schedule = schedule.map(x => {
                            return {
                                echelon_id: x.echelon_id,
                                echelon_name: x.echelon_name,
                                batch_data: x.batch_data.map(y => {
                                    return {
                                        batch_id: y.batch_id,
                                        batches: y.batches,
                                        subject_data: y.subject_data.filter(z => z.period == this.dialogItem.period)
                                    }
                                }).filter(xx => xx.subject_data.length > 0)
                            }
                        }).filter(xx => xx.batch_data.length > 0)
                    }
                    if (this.dialogItem.time) {
                        var time = this.dialogItem.time.split('-')
                        var from_time = this.$moment(time[0], "HH:mm").format("HH:mm");
                        if (time.length > 1) {
                            var to_time = this.$moment(time[1], "HH:mm").format("HH:mm");
                        } else {
                            var to_time = this.$moment('23:59', "HH:mm").format("HH:mm");
                        }
                        schedule = schedule.map(x => {
                            return {
                                echelon_id: x.echelon_id,
                                echelon_name: x.echelon_name,
                                batch_data: x.batch_data.map(y => {
                                    return {
                                        batch_id: y.batch_id,
                                        batches: y.batches,
                                        subject_data: y.subject_data.filter(z => z.time >= from_time && z.time <= to_time)
                                    }
                                }).filter(xx => xx.subject_data.length > 0)
                            }
                        }).filter(xx => xx.batch_data.length > 0)
                    }
                    schedule = schedule.sort(function(a,b){return a.echelon_id - b.echelon_id})
                    this.dashboard_schedule = schedule
                    this.$forceUpdate()
                },
                filter_schedule() {
                    if (this.dialogItem.date) {
                        this.get_dashboard_schedule()
                    } else {
                        this.change_schedule_layout()
                    }
                },
                edit_schedule(item, i) {
                    this.editedItem = Object.assign({}, item);
                    this.editedIndex = i
                    this.editedItem.sms = false
                    this.get_subject()
                    this.get_batch()
                    this.edit_schedule_dialog = true
                },
                edit_dashboard_schedule(option){
                    if (this.$refs.edit_form.validate()) {
                        if (this.editedIndex == -1) {this.editedItem.save_type = 'new'}
                        if (option == 'delete') {this.editedItem.save_type = 'delete'}
                        this.dialog = true
                        this.axios.post('/admin/schedule/edit_dashboard_schedule', this.editedItem).then(response => {
                            this.sms = false
                            this.campaign_name = ''
                            this.dialog = false
                            this.edit_schedule_dialog = false
                            if (response.data.sms == 'sms') {
                                this.sms_report = response.data.sms_report
                                this.sms_balance = response.data.balance
                                this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                                this.sms_dialog = true   
                            }
                            // this.get_dashboard_schedule()
                            this.$refs.edit_form.reset()
                            var index = this.original_dashboard_schedule.findIndex(x => x.id == this.editedItem.id)
                            this.original_dashboard_schedule[index] = response.data.daily_schedule
                            this.subject_layout()                            
                            setTimeout(function() { this.force_update = false }.bind(this), 500)
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    }
                },
                change_online_class_status(id, status){
                    var con = confirm('Are you sure?')
                    if (!con) {return false}
                    var item = {}
                    item.schedule_id = id
                    item.status = status
                    this.axios.post('/admin/schedule/change_online_class_status', item).then(response => { 
                        // this.get_dashboard_schedule() 
                        var index = this.original_dashboard_schedule.findIndex(x => x.id == id)
                        this.original_dashboard_schedule[index] = response.data.daily_schedule
                        this.subject_layout()                       
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        alert(error.response.data.error)
                    });
                },
                get_dashboard_attendance_list() {
                    this.dialog = true
                    var date = this.$moment(new Date()).format('YYYY-MM-DD');
                    this.axios.get('/admin/attendance/get_dashboard_attendance_list/' + date).then(response => {
                        this.current_date = date
                        this.attendance_dialog = true
                        this.dashboard_attendance = response.data.dashboard_attendance;
                        this.dialog = false
                        this.dashboard_attendance = this.dashboard_attendance.sort(function(a,b){
                            return a.echelon_id - b.echelon_id
                        })
                        this.filter_attendance()
                    });
                },
                filter_attendance(){
                    var item = this.dashboard_attendance
                    item = item.map(x => {
                        return {
                            id: x.id,
                            echelon_name: x.echelons.name,
                            name: x.name,
                            sms: x.attendance_lists.length ? x.attendance_lists[0].sms_status : 0,
                            status : x.attendance_lists.length ? x.attendance_lists[0].status: '',
                            attendance: 0,
                            students: x.students_count
                        }
                    })
                    this.attendance_count = 0
                    for (var i = 0; i < item.length; i++) {
                        this.attendance_count = this.attendance_count + item[i].students
                    }
                    this.dashboard_attendance = item
                },
                change_check_all(){
                    var value = this.check_all
                    for (var i = 0; i < this.dashboard_attendance.length; i++) {
                        if (this.dashboard_attendance[i].sms == 0 && this.dashboard_attendance[i].status) {
                            this.dashboard_attendance[i].attendance = value
                        }
                    }
                },
                send_attendance_sms(){
                    this.dialog = true
                    var item = {}
                    item.attendance = this.dashboard_attendance.filter(x => x.attendance == 1)
                    item.attendance_sms_type = this.attendance_sms_type
                    item.destination_number = this.destination_number
                    this.axios.post('/admin/attendance/send_attendance_sms', item).then(response => {
                        this.dialog = false
                        this.attendance_dialog = false 
                        if (response.data.sms == 'sms') {
                            this.sms_report = response.data.sms_report
                            this.sms_balance = response.data.balance
                            this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                            this.sms_dialog = true   
                        }
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                hide_menu(){
                    this.$refs['combobox'].blur()
                },
                has_teacher_batch(item, batch_id){
                    var x = item.filter(y => y.id == batch_id)
                    if (x.length) {return true}
                    return false
                },
                confirm_teacher(id, teacher_id, s_i, b_i, sb_i){
                    if (!teacher_id) {return false}                    
                    var confirmation = confirm("Are you sure to confirm teacher?")
                    if (!confirmation) {return false}
                    this.dialog = true
                    this.axios.post('/admin/schedule/confirm_teacher/' + id).then(response => {
                        this.dialog = false
                        this.get_dashboard_schedule()
                        $('.user_nav').addClass('successful')
                        setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                    });
                },
                isToday(){
                    var date1 = new Date()
                    var date2 = new Date(this.current_schedule_date)
                    if (date1.getFullYear() === date2.getFullYear() && date1.getMonth() === date2.getMonth() && date1.getDate() === date2.getDate()) {
                        return true
                    }
                    return false;

                },
                close_dialog(){
                    this.edit_schedule_dialog = false
                    this.$refs.edit_form.reset()
                }
        } //end of method
    }

</script>

<style>
  .schedule_manage .v-input--checkbox.v-input--selection-controls .v-input__slot{
    margin-bottom: 0px!important;
  }
  .schedule_manage .v-input--checkbox .v-messages{
    display: none;
  }
  .schedule_manage .v-input--checkbox.v-input--selection-controls {
    margin-top: 0px;
    padding-top: 0px;
  }
</style>