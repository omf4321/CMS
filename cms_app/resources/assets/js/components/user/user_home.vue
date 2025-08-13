<template>
    <div class="user_home">
        <v-container fluid>
            <h3 class="fs-20 m-t-10 dash_title">Teacher Dashboard <v-icon class="m-l-10 cur-p pos-rel" style="top: 3px" @click="get_dashboard_contented_schedule">refresh</v-icon></h3>
            <div>
                <router-link :to="{ name: 'user_invigilator_home'}" v-if="has_role('invigilator')">
                    <v-btn flat color="primary" class="mr-2 mid-btn pos-rel" style="top: 8px"> Invigilator </v-btn>
                </router-link>
                <router-link :to="{ name: 'user_script_home'}" v-if="has_role('script_checker')">
                    <v-btn flat color="primary" class="mr-2 mid-btn pos-rel" style="top: 8px"> Script Check </v-btn>
                </router-link>
            </div>
            <v-divider class="my-3"></v-divider>
            <v-alert :value="show_alert" transition="scale-transition" :color="alert_type" icon="info" outline style="position:relative; width: 100%"> {{alert_text}}
                <router-link :to="{'name': alert_url}">
                    <v-btn flat color="info" class="tiny-btn m-t-0 m-b-0 p-0">Let's Check</v-btn>
                </router-link>
            </v-alert>
            <!-- todays schedule -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="today_schedule.length>0 && step=='none'">
                <h5 class="fw-600 dash_card_title">Today's Schedule</h5>
                <hr class="m-t-5 m-b-5">
                <div v-for="(schedule, s_i) in today_schedule">
                    <p class="dash_card_subtitle">{{schedule.echelon_name}}</p>
                    <div class="m-b-8" v-for="(data, b_i) in schedule.schedule_data">
                        <div class="row">
                            <div class="col-xs-4 p-r-5">{{data.batches.name}}</div>
                            <div :class="{'col-xs-3':true, 'p-l-5':true, 'p-r-5':true, 'lecture_color': schedule.lecture_sheet}">{{data.subjects.name | first_letter_word}} - {{data.period | ordinal_number}}</div>
                            <div class="col-xs-5 p-l-5">
                                <!-- att button -->
                                <v-btn outline color="primary" class="tiny-btn m-t-0 m-b-0 p-r-4 p-l-4" @click="get_batch_attendance(data.batch_id, data.batches.name, s_i, b_i)" v-if="(!data.attendance_taken || data.attendance_taken == teacher_id) && data.teacher_attendance_status != 'done' && data.schedule_type == 'class'">Att</v-btn>
                                <!-- add link button -->
                                <v-btn outline v-if="data.schedule_type == 'online_class' && !data.online_class_status && (!data.link_creator_id || data.link_creator_id == teacher_id)" color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4" @click="add_online_link(s_i, b_i)">Link</v-btn>
                                <!-- start button -->
                                <v-btn outline v-if="data.online_class_url && !data.online_class_status" color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4" @click="change_online_class_status(data.id, 'begin')">Start</v-btn>
                                <a :href="data.online_class_url" target="_blank"><v-btn outline v-if="data.online_class_status == 'begin'" color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4">Join</v-btn></a>
                                <v-btn outline v-if="data.online_class_status == 'begin'" color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4" @click="change_online_class_status(data.id, 'finish')">Finish</v-btn>
                                <a :href="data.online_class_url" v-if="data.online_class_url" :id="'url_' + data.id" target="_blank" style="display:none"></a>
                                <!-- done button -->
                                <v-btn outline v-if="(data.attendance_taken || data.online_class_status == 'finish') && data.teacher_attendance_status != 'done' && data.protect == 'false'" color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4" @click="done_teacher_attendance(data.id, data.batches.id, data.teacher_id, s_i)">Done</v-btn>
                                <!-- finish -->
                                <v-btn flat v-if="data.teacher_attendance_status == 'done'" color="success" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4">Finish</v-btn>
                            </div>
                        </div>
                        <hr class="m-t-5 m-b-5">
                    </div>
                </div>
            </div>
            <!-- next schedule -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="next_schedule.length>0 && step=='none'">
                <h5 class="fw-600 dash_card_title">Your Next 7 Days Schedule</h5>
                <hr class="m-t-5 m-b-5">
                <div v-for="(schedule, s_i) in next_schedule">
                    <!-- <p class="dash_card_subtitle">{{schedule.echelon_name}}</p> -->
                    <div class="m-b-8" v-for="(data, b_i) in schedule.schedule_data">
                        <div class="row m-b-15">
                            <div class="col-xs-3 p-r-5">
                                <div class="fs-12">{{schedule.echelon_name}}</div>
                                <div class="fs-12">{{data.batches.name}}</div>
                            </div>
                            <div :class="{'col-xs-6': true,  'p-l-5':true, 'p-r-5':true, 'lecture_color': data.lecture_sheet}">
                                <div class="fs-13">{{data.subjects.name | first_letter_word}} - {{data.period | ordinal_number}}</div>
                                <div>
                                    <span v-if="data.chapter_text && data.topic" class="fs-10">{{(data.chapter_text + data.topic).substring(0,25)}}</span>
                                    <span v-if="data.chapter_text" class="fs-10">{{data.chapter_text.substring(0,25)}}</span>
                                    <span v-if="data.topic" class="fs-10">{{data.topic.substring(0,25)}}</span>
                                </div>
                            </div>
                            <div class="col-xs-3 p-l-5">
                                <div class="fs-12">{{data.date | moment("D MMM, dd")}}</div>
                                <div :class="{'tiny-btn fs-13':true, 'pos-rel': true, 'lecture_color': data.lecture_sheet, 'cur-p': true, 'fw-600': data.lecture_sheet}" @click="confirm_lecture_sheet(data.id, s_i, b_i)" style="top:0px; color: #1976d2">LS</div>
                            </div>
                            <div class="col-xs-2 p-l-5 fs-13"></div>
                        </div>
                    </div>
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
        </v-container>
        <v-dialog max-width="600px" persistent v-model="check_schedule_dialog">
            <v-card>
                <v-container>
                    <v-card-title class="p-t-5 p-b-15 p-l-0">
                        <v-spacer></v-spacer>
                        <v-icon @click="check_schedule_dialog = false">close</v-icon>
                    </v-card-title>
                    <h4 class="text-center">You have schedule tommorrow!!!</h4>
                    <h5 class="text-center">Are You Confirming Us?</h5>
                    <div class="text-center">
                        <v-btn color="info" medium @click="save_teacher_attendance('confirmed')">Yes I Am Confirming?</v-btn>
                    </div>
                    <div class="text-center">
                        <v-btn class="fs-xs" small color="#888" style="text-transform: none" flat @click="save_teacher_attendance('not_confirmed')">It'll be better If I get another reminder</v-btn>
                    </div>
                </v-container>
            </v-card>
        </v-dialog>
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
        <v-dialog max-width="600px" persistent v-model="add_link_dialog">
            <v-card>
                <v-container>
                    <!-- <v-text-field v-model="online_class_url" label="Online Class Link"></v-text-field> -->
                    <input type="text" class="form-control" id="myText" name="" v-model="online_class_url" style="width: 100%; display: block; border: 1px solid">
                    <v-btn flat small color="primary" @click="paste_url" style="min-width: auto;">paste</v-btn>
                    <v-btn class="float-right" small color="success" @click="save_online_class_url">Save</v-btn>
                    <v-btn class="float-right" small color="error" @click="add_link_dialog = false, online_class_url = ''">Close</v-btn>
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
                    contented_schedule: [],
                    today_schedule: [],
                    next_schedule: [],
                    tommorrow_schedule: [],
                    dialog: false,
                    show_alert: false,
                    alert_type: '',
                    check_schedule_dialog: false,
                    schedule_done_item: '',
                    done_status: 'none',
                    check_schedule: [],
                    check_all: false,
                    batch_student_list: [],
                    step: 'none',
                    current_batch_id: '',
                    current_batch_name: '',
                    current_batch_index: '',
                    current_schedule_index: '',
                    current_attendance_list_id: '',
                    teacher_id: window_teacher_id,
                    user_message_dialog: false,
                    user_messages: [],
                    alert_text: '',
                    alert_url: '',
                    add_link_dialog: false,
                    online_class_url: ''
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) { 
                if (window_authorise != 'authorised') {return next('/authentication')}               
                let role = window_user_role.findIndex(x => x == 'teacher');
                if (role > -1) {
                    next()
                } else {
                    role = window_user_role.findIndex(x => x == 'invigilator');
                    if (role>-1) {next('/user_invigilator_home')}
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
                this.get_dashboard_contented_schedule()
                Echo.channel('chat')
                .listen('.MessageSent', (e) => {
                    for (var j = 0; j < e.schedule.length; j++) {
                        for (var i = 0; i < this.today_schedule.length; i++) {
                            var index = this.today_schedule[i].schedule_data.findIndex(x => x.id == e.schedule[j].id)
                            if (index > -1) {
                                this.today_schedule[i].schedule_data[index].teacher_attendance_status = e.schedule[j].teacher_attendance_status
                                this.today_schedule[i].schedule_data[index].online_class_url = e.schedule[j].online_class_url
                                this.today_schedule[i].schedule_data[index].online_class_status = e.schedule[j].online_class_status
                                this.today_schedule[i].schedule_data[index].online_exam_status = e.schedule[j].online_exam_status
                                this.today_schedule[i].schedule_data[index].link_creator_id = e.schedule[j].link_creator_id
                            }
                        }
                    }
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
                    if (value > 3) {return value = value + 'th'}
                }
            },
            methods: {
                logout() {
                        this.$refs.logout_form.submit()
                    },
                    get_dashboard_contented_schedule() {
                        this.dialog = true
                        Vue.axios.get('/user/schedule/get_dashboard_contented_schedule').then(response => {
                            this.contented_schedule = response.data.daily_schedule;
                            this.organize_schedule(response.data.daily_schedule)
                            this.dialog = false                            
                            this.get_user_message()
                        });
                    },
                    organize_schedule(items) {
                        var data = []
                        for (var i = 0; i < items.length; i++) {
                            var object = {echelon_name: '', echelon_id: '', date: '', schedule_type: '', status: '' , schedule_data: [] }
                            if (i == 0) {
                                object.echelon_name = items[i].batches.echelons.name
                                object.echelon_id = items[i].batches.echelon_id
                                object.date = items[i].date
                                object.schedule_type = items[i].schedule_type
                                object.schedule_data.push(items[i])
                                data.push(object)
                            } else {
                                var index = data.findIndex(x => x.echelon_id == items[i].batches.echelon_id && x.date == items[i].date)
                                if (index > -1) {
                                    data[index].schedule_data.push(items[i])
                                } else {
                                    object.echelon_name = items[i].batches.echelons.name
                                    object.echelon_id = items[i].batches.echelon_id
                                    object.date = items[i].date
                                    object.schedule_type = items[i].schedule_type
                                    object.schedule_data.push(items[i])
                                    data.push(object)
                                }
                            }
                        }
                        this.contented_schedule = data
                        var date = this.$moment(new Date()).format('YYYY-MM-DD');
                        this.today_schedule = this.contented_schedule.filter(x => x.date == date)
                        this.today_schedule = this.today_schedule.sort((a, b) => {return parseInt(a.echelon_id) - parseInt(b.echelon_id) })

                        var date_7 = this.$moment(new Date()).add(7, 'days').format('YYYY-MM-DD');
                        this.next_schedule = this.contented_schedule.filter(x => x.date > date && x.date <= date_7 && x.schedule_type == 'class')

                        var date_1 = this.$moment(new Date()).add(1, 'days').format('YYYY-MM-DD');
                        this.tomorrow_schedule = this.contented_schedule.filter(x => x.date == date_1 && x.schedule_type == 'class' && x.status != 'confirmed')
                        
                        this.remaining_undone_schedule()
                        console.log(this.today_schedule)
                    },
                    done_teacher_attendance(schedule_id, batch_id, teacher_id, index){
                        var item = {}
                        item.schedule_id = schedule_id;
                        item.batch_id = batch_id;
                        item.teacher_id = teacher_id;
                        item.next_schedule
                        this.schedule_done_item = item
                        this.schedule_done_item.index = index
                        if (this.alert_type == 'error') {
                            return alert("Please resolve the alert first.")
                        }
                        if (this.today_schedule.length - this.remaining_undone_schedule()<=1 && this.tomorrow_schedule.length) {
                            this.check_schedule_dialog = true                            
                            return false
                        }
                        var confirmation = confirm('Are you sure?')
                        if (!confirmation) {
                            return false
                        }
                        this.save_teacher_attendance()
                    },                    
                    save_teacher_attendance(confirm_status) {
                        if (confirm_status == 'confirmed') {
                            this.schedule_done_item.status = confirm_status
                        }
                        this.dialog = true
                        Vue.axios.post('/user/schedule/save_teacher_attendance', this.schedule_done_item).then(response => {
                            this.today_schedule[this.schedule_done_item.index].status = 'done'
                            $('.user_nav').addClass('successful')
                            this.dialog = false;
                            this.check_schedule_dialog = false                            
                            this.get_dashboard_contented_schedule()
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
                    confirm_lecture_sheet(id, s_i, b_i){
                        var con = confirm("Are you sure to active lecture sheet for this date?")
                        if (!con) {return false}
                        this.dialog = true
                        Vue.axios.post('/user/schedule/confirm_lecture_sheet/' + id).then(response => {
                            this.dialog = false;
                            if (this.next_schedule[s_i].schedule_data[b_i].lecture_sheet) {
                                this.next_schedule[s_i].schedule_data[b_i].lecture_sheet = null
                            } else {
                                this.next_schedule[s_i].schedule_data[b_i].lecture_sheet = 1
                            }
                            
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            if (error.response.status == 419) {
                                alert('Session Expired. This page is going to reload')
                                location.reload();
                            }
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    },
                    check_teacher_attendance(schedule_id, batch_id) {
                        for (var i = 0; i < this.today_schedule.length; i++) {
                            for (var j = 0; j < this.today_schedule[i].teacher_batches.length; j++) {
                                if (this.today_schedule[i].teacher_batches[j].id == batch_id && this.today_schedule[i].id ==schedule_id) {
                                    return true
                                }
                            }
                        }
                        return false
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
                        this.get_discontented_schedule_alert()
                    },                    
                    get_discontented_schedule_alert() {
                        this.dialog = true
                        Vue.axios.get('/user/schedule/get_user_alert').then(response => {
                            var date = this.$moment(new Date()).add(7, 'days').format('YYYY-MM-DD');
                            // exam alert
                            var exam_alert = response.data.exam_alert
                            if (exam_alert.length) {
                                this.alert_type = 'warning'
                                this.show_alert = true
                                this.alert_url = 'user_question_list'
                                var echelons = []
                                for (var i = 0; i < exam_alert.length; i++) {
                                    echelons.push(exam_alert[i].subjects.echelons.name)
                                }
                                this.alert_text = 'Question not created or finished for upcomming exam of class ' + echelons.join(', ')  +' . Please resolve it.'
                            }
                            if (exam_alert.filter(x => x.date <= date).length) {
                                this.alert_type = 'error'
                            }
                            // schedule alert
                            var dis_contented_schedule = response.data.schedule_alert;
                            if (dis_contented_schedule) {
                                this.alert_type = "warning"
                                this.show_alert = true
                                this.alert_text = 'New discontented schedule has arrived. Please resolve them.'
                                this.alert_url = 'user_schedule'
                            }
                            if (dis_contented_schedule && dis_contented_schedule.date <= date) {
                                this.alert_type = 'error'
                            }
                            this.dialog = false
                        });
                    },
                    remaining_undone_schedule(){
                        var x = this.tomorrow_schedule.filter(x => x.status != 'done')
                        return x.length
                    },
                    get_batch_attendance(batch_id, batch_name, s_i, b_i) {
                        this.current_batch_id = batch_id
                        this.current_batch_name = batch_name
                        this.current_batch_index = b_i
                        this.current_schedule_index = s_i
                        this.dialog = true
                        Vue.axios.post('/user/schedule/get_batch_attendance/' + batch_id).then(response => {
                            this.batch_student_list = response.data.student_list
                            this.current_attendance_list_id = response.data.attendance_list_id
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
                        item.attendance_list_id = this.current_attendance_list_id
                        item.student_attendance = this.batch_student_list
                        Vue.axios.post('/user/schedule/save_batch_attendance', item).then(response => {
                            this.step = 'none'
                            this.dialog = false
                            this.get_dashboard_contented_schedule()
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
                    add_online_link(s_i, b_i){
                        this.current_schedule_index = s_i
                        this.current_batch_index = b_i
                        this.online_class_url = this.today_schedule[s_i].schedule_data[b_i].online_class_url
                        this.add_link_dialog = true
                    },
                    save_online_class_url(){
                        var item = {}
                        item.schedule_id = this.today_schedule[this.current_schedule_index].schedule_data[this.current_batch_index].id
                        item.online_class_url = document.getElementById("myText").value
                        Vue.axios.post('/user/schedule/save_online_class_url', item).then(response => {       
                            this.add_link_dialog = false   
                            this.online_class_url = ''    
                            // this.get_dashboard_contented_schedule()
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    },
                    change_online_class_status(id, status){
                        var con = confirm('Are you sure?')
                        if (!con) {return false}
                        var item = {}
                        item.schedule_id = id
                        item.status = status
                        Vue.axios.post('/user/schedule/change_online_class_status', item).then(response => { 
                            if (status == 'begin') {
                                document.getElementById('url_' + id).click()
                            }
                            // this.get_dashboard_contented_schedule()      
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                            alert(error.response.data.error)
                        });
                    },
                    paste_url(){
                        var text
                        paste()
                        this.online_class_url = text
                        async function paste() {
                          // const text = await navigator.clipboard.readText();
                            text = await navigator.clipboard.readText();
                            document.getElementById("myText").value = text
                        }
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
</style>