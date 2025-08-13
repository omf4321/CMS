<template>
    <div class="user_home">
        <v-container fluid>
            <h3 class="fs-20 m-t-10 dash_title">Script Checker's Dashboard <v-icon class="m-l-10 cur-p pos-rel" style="top: 3px" @click="get_scripter_dashboard_exam">refresh</v-icon></h3> 
            <div>
                <router-link :to="{ name: 'user_invigilator_home'}" v-if="has_role('invigilator')">
                    <v-btn flat color="primary" class="mr-2 mid-btn pos-rel" style="top: 8px"> Invigilator </v-btn>
                </router-link>
            </div>           
            <v-divider class="my-3"></v-divider>
            <v-alert :value="schedule_alert" transition="scale-transition" :color="alert_type" icon="info" outline> New discontented schedule has arrived. Please resolve them.
                <router-link :to="{name: 'user_schedule'}">
                    <v-btn flat color="info" class="tiny-btn m-t-0 m-b-0 p-0">Let's Check</v-btn>
                </router-link>
            </v-alert>
            <!-- take script -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="step == 'none'">
                <h5 class="fw-600 dash_card_title">Take Script</h5>
                <hr class="m-t-5 m-b-5">                 
                <!-- <v-layout> 
                    <v-autocomplete class="m-r-15" :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete>
                    <v-autocomplete :items="batch" :clearable="true" label="Batch" v-model="dialogItem.batch_id"></v-autocomplete>
                </v-layout> -->
                <v-text-field v-model="dialogItem.schedule_id" label="Exam Code" type="number"></v-text-field>
                <div class="text-center m-t-15"><v-btn small color="success" @click="take_script">Take</v-btn></div>
            </div>
            <!-- todays schedule -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="exam_schedule.length>0 && step=='none'">
                <h5 class="fw-600 dash_card_title">Taken Scripts</h5>
                <hr class="m-t-5 m-b-5">
                <div v-for="(schedule, s_i) in exam_schedule">
                    <p class="dash_card_subtitle">{{schedule.batches.echelons.name}}</p>
                    <div class="row">
                        <div class="col-xs-5 p-r-5">{{schedule.batches.name}} - {{schedule.schedule_id}}</div>
                        <div :class="{'col-xs-2':true, 'p-l-5':true, 'p-r-5':true, 'lecture_color': schedule.lecture_sheet}">{{schedule.schedules.subjects.name | first_letter_word}}</div>
                        <div class="col-xs-5 p-l-5">
                            <v-btn outline :color="schedule.status == 'scripter_taken' ? 'primary' : 'success'" class="tiny-btn m-t-0 m-b-0 p-5" @click="prepare_exam_entry(s_i, schedule.batch_id, schedule.batches.name, schedule.date, schedule.schedules.subjects.name, schedule.schedule_id)">EN</v-btn>
                            <v-btn v-if="schedule.schedules.questions && schedule.schedules.questions.exam_question_types" outline color="warning" class="tiny-btn m-t-0 m-b-0 p-5" @click="view_question(s_i)">QS</v-btn>
                            <v-btn outline v-if="schedule.status != 'scripter_taken'" color="primary" class="tiny-btn m-t-0 m-b-0 p-5" @click="prepare_submit(schedule.id)">SB</v-btn>
                        </div>
                    </div>
                    <hr class="m-t-10 m-b-10">
                </div>
            </div>

            <!-- view question -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" v-if="step=='view_question'">
                <div class="fw-600 fs-13">Question No- {{question_index + 1}}</div>
                <hr class="m-t-5 m-b-5">
                <div class="fw-400 solaiman fs-12" v-html="question[question_index].question_text"></div>
                <div class="fw-600 fs-13">Answer</div>
                <hr class="m-t-5 m-b-5">
                <div class="fw-400 solaiman fs-12" v-html="question[question_index].detail_answer"></div>
                <v-footer class="pa-3" dark color="teal" fixed height="40">
                    <div class="text-left">
                        <v-btn flat @click="step = 'none'">Go Back</v-btn>
                    </div>
                    <v-spacer></v-spacer>
                    <div class="text-right">
                        <v-btn flat @click="prev_question">Prev</v-btn>
                        <v-btn flat @click="next_question" class="m-r-5">Next</v-btn>
                    </div>
                </v-footer>
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
                                <th>Ob Mark</th>
                                <th>Sub Mark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(list, std_i) in originial_updated_exam_list">
                                <td @click="get_student_mark(list.reg_no)">{{list.reg_no}}</td>
                                <td @click="get_student_mark(list.reg_no)">{{list.name.substring(0,25)}}</td>
                                <td @click="get_student_mark(list.reg_no)">{{list.ob_mark}}</td>
                                <td @click="get_student_mark(list.reg_no)">{{list.mark}}</td>
                            </tr>
                        </tbody>
                    </table>            
                </v-container>
            </div>
            <!-- script detail -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-20 m-b-15 step_edit" v-if="step == 'none'">
                <h5 class="fw-600 dash_card_title">Script Detail <span class="m-r-10" v-if="script_summary.length">({{total_quantity}})</span></h5>
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
                        <v-btn outline color="primary" class="tiny-btn" @click="get_script_summary">GO</v-btn>
                    </div>
                </div>
                <hr class="m-t-5 m-b-5" style="float: none; clear: both">
                <table class="table table-bordered fs-12 m-t-10">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="summary in script_summary">
                            <td>{{summary.schedules.date | moment("D MMM YY")}}</td>
                            <td>{{summary.batches.name}} - {{summary.schedules.subjects.name | first_letter_word}}</td>
                            <td>{{summary.script_quantity}}</td>
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
        <v-dialog v-model="auth_dialog" hide-dialog persistent width="300">
            <v-card>
                <v-icon class="cur-p p-15 float-right" @click="auth_dialog = false">close</v-icon>
                <v-card-text class="text-center">
                    <v-text-field v-model="authorise_code" label="Authorise Code" style="width: 100px; display: block; margin: auto" type="number"></v-text-field>
                    <v-btn small outline color="success" @click="submit_script">Submit</v-btn>
                </v-card-text>
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
                    exam_schedule: [],                    
                    dialog: false,
                    schedule_alert: false,
                    alert_type: '',                    
                    step: 'none',
                    current_batch_id: '',
                    current_batch_name: '',
                    current_batch_index: '',
                    current_schedule_index: '',
                    teacher_id: window_teacher_id,
                    user_message_dialog: false,
                    user_messages: [],                    
                    marks_type: [{'value': 'mcq', 'text': 'MCQ'}, {'value': 'cq', 'text': 'CQ'}],
                    exam_entry: {marks_type: 'cq', mark: ''},
                    student_exam_list: [],
                    updated_exam_list: [],
                    originial_updated_exam_list: [],
                    exam_error_message: '',   
                    count_un_entry: [],
                    echelon: [],
                    branch: [],
                    original_batch: [],
                    batch: [],
                    dialogItem: {},
                    authorise_code: '',
                    auth_dialog: false,
                    exam_id: '',
                    date_from: '',
                    date_to: '',
                    script_summary: [],
                    total_quantity: 0,
                    current_subject_name: '',
                    question: [],
                    question_index: 0,
                    date_from_dialog: false,
                    date_to_dialog: false 
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) { 
                if (window_authorise != 'authorised') {return next('/authentication')}               
                let role = window_user_role.findIndex(x => x == 'script_checker');
                if (role > -1) {
                    next()
                } else {
                    role = window_user_role.findIndex(x => x == 'teacher');
                    if (role>-1) {next('/')}
                    else {
                       next('/user_invigilator_home') 
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
                this.admin_branch_list()
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
                    admin_branch_list(){
                        this.branch = window_branch;
                        if (this.branch.length==1) {
                            this.dialogItem.branch_id = this.branch[0].id
                            this.admin_echelon_list()
                            this.admin_batch_list();
                            this.get_scripter_dashboard_exam()
                        }
                        this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                    },
                    admin_echelon_list() {                    
                        this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    },
                    admin_batch_list(){
                        Vue.axios.get('/admin/request/batch_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                            this.original_batch = response.data.batch_list_by_branch;
                        }); 
                    },
                    get_batch(){
                        var batch = Object.assign([], this.original_batch);
                        let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                        this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                        var index = this.echelon.findIndex(x => x.value == this.dialogItem.echelon_id)
                        this.current_echelon_name = this.echelon[index].text
                    },                    
                    get_scripter_dashboard_exam() {
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_scripter_dashboard_exam').then(response => {
                            this.exam_schedule = response.data.exam_list;
                            this.get_user_message()
                            this.save_exam_mark_from_cookies()
                            this.dialog = false
                        });
                    },
                    view_question(index){
                        if (!this.exam_schedule[index].schedules.questions.exam_question_types) {return false}
                        if (!this.exam_schedule[index].schedules.questions.exam_question_types[0].questions) {return false}
                        this.question = this.exam_schedule[index].schedules.questions.exam_question_types[0].questions
                        this.question_index = 0
                        console.log(this.question)
                        this.step = 'view_question'
                    },
                    next_question(){
                        if (this.question.length <= this.question_index + 1) {
                            return false
                        }
                        this.question_index = this.question_index + 1
                        this.step =  '';
                        setTimeout(function() {
                            this.step = 'view_question'
                        }.bind(this), 200)
                    },
                    prev_question(){
                        if (this.question_index == 0) {
                            return false
                        }
                        this.question_index = this.question_index - 1
                    },
                    prepare_exam_entry(s_i, batch_id, batch_name, date, subject_name, schedule_id){
                        this.current_batch_name = batch_name
                        this.step = 'entry_exam'
                        this.current_batch_id = batch_id
                        this.current_batch_name = batch_name
                        this.current_subject_name = subject_name
                        this.current_schedule_index = s_i
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_student_exam_entry_list/' + schedule_id + '/' + batch_id + '/' + date).then(response => {
                            var item = response.data.student_list                         
                            item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                            this.student_exam_list = item
                            var type = this.exam_entry.marks_type
                            var schedule_id = this.exam_schedule[this.current_schedule_index].schedule_id
                            this.originial_updated_exam_list = item.map(x => {return {id: x.id, reg_no: x.reg_no, name: x.name, batch_id: x.batch_id, schedule_id: schedule_id, mark: type == 'mcq' ? x.exams[0].ob_mark : x.exams[0].sub_mark, ob_mark: x.exams[0].ob_mark}})
                            this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.mark && x.ob_mark)
                            this.dialog = false
                            this.step = 'entry_exam'
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
                        var ob_full_mark = this.exam_schedule[this.current_schedule_index].ob_full_mark
                        var sub_full_mark = this.exam_schedule[this.current_schedule_index].sub_full_mark
                        if (this.exam_entry.marks_type == 'mcq' && parseInt(data[1])>ob_full_mark) {return alert('Mark greather than full marks')}
                        if (this.exam_entry.marks_type == 'cq' && parseInt(data[1])>sub_full_mark) {return alert('Mark greather than full marks')}
                        var index = this.student_exam_list.findIndex(x => x.reg_no == data[0])
                        if (index==-1) {
                            this.exam_error_message = "No Student Found"
                            return false
                        }
                        // add list
                        var index2 = this.updated_exam_list.length ? this.updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1 
                        var index3 = this.originial_updated_exam_list.length ? this.originial_updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1
                        var object = {id: '', reg_no: '', name: '', batch_id: '', mark: '', schedule_id: '', ob_mark: ''}                     
                        object.id = this.student_exam_list[index].id
                        object.reg_no = this.student_exam_list[index].reg_no
                        object.name = this.student_exam_list[index].name
                        object.batch_id = this.student_exam_list[index].batch_id
                        object.schedule_id = this.exam_schedule[this.current_schedule_index].schedule_id
                        object.mark = data[1]
                        if (index2>-1) {                            
                            this.updated_exam_list[index2] = object
                        }
                        else {
                            this.updated_exam_list.push(object)
                        }
                        if (index3>-1) {
                            this.originial_updated_exam_list[index3].mark = object.mark
                        }else{
                            this.originial_updated_exam_list.push(object)
                        }
                        this.originial_updated_exam_list = this.originial_updated_exam_list.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                        // count unentry
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.mark && x.ob_mark)

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
                    save_exam_mark(){
                        var item = {}
                        item.mark_type = this.exam_entry.marks_type ? this.exam_entry.marks_type : 'mcq'
                        item.student_mark_list = this.updated_exam_list
                        item.entry_completed = this.count_un_entry.length ? 'false' : 'true'
                        Vue.axios.post('/user/exam/save_exam_mark', item).then(response => {
                            var ids = response.data.ids
                            for (var i = 0; i < ids.length; i++) {
                                var index = this.updated_exam_list.findIndex(x => x.id == ids[i])
                                if (index>-1) {this.updated_exam_list.splice(index,1)}
                            }
                            if (this.count_un_entry==0) {this.get_scripter_dashboard_exam()}
                            var x = JSON.stringify(this.updated_exam_list)
                            this.$cookies.set('unsaved_exam_mark', x);
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
                    take_script() {
                        if (!this.dialogItem.schedule_id) {return false}                       
                        this.dialog = true
                        Vue.axios.post('/user/exam/take_script', this.dialogItem).then(response => {
                            this.get_scripter_dashboard_exam();
                            $('.user_nav').addClass('successful')                            
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    prepare_submit(id){
                        this.exam_id = id
                        this.auth_dialog = true
                    },
                    submit_script(){
                        this.dialog = true
                        Vue.axios.post('/user/exam/submit_script/' + this.exam_id + '/' + this.authorise_code).then(response => {
                            this.get_scripter_dashboard_exam();
                            this.dialog = false
                            this.auth_dialog = false
                            $('.user_nav').addClass('successful')                            
                            setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                        }, error => {
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    get_script_summary(){
                        if (!this.date_from) {return alert('Please choose date from')}
                        this.dialog = true
                        Vue.axios.get('/user/exam/get_script_summary/' + this.date_from + '/' + this.date_to).then(response => {
                            this.script_summary = response.data.attendance_list
                            var item = this.script_summary
                            this.dialog = false
                            var total = 0
                            for (var i = 0; i < item.length; i++) {
                                total += item[i].script_quantity
                            }
                            this.total_quantity = total
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
                            $('.user_nav').addClass('denied')
                            this.dialog = false;
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                        });
                    },
                    check_invigilator_completence(s_i, batch_id, status) {                        
                        for (var i = 0; i < this.exam_schedule[s_i].exam_invigilators.length; i++) {
                            if (this.exam_schedule[s_i].exam_invigilators[i].batch_id == batch_id && this.exam_schedule[s_i].exam_invigilators[i].invigilator_id) {
                                if (status && this.exam_schedule[s_i].exam_invigilators[i].invigilator_id == window_teacher_id) {
                                    return true
                                }
                                if(this.exam_schedule[s_i].exam_invigilators[i].invigilator_id != window_teacher_id) {
                                    return true
                                }
                            }                            
                        }                                             
                        return false
                    },
                    check_exam_status(s_i, batch_id){
                        for (var i = 0; i < this.exam_schedule[s_i].exam_invigilators.length; i++) {
                            if (this.exam_schedule[s_i].exam_invigilators[i].batch_id == batch_id && this.exam_schedule[s_i].exam_invigilators[i].invigilator_id == window_teacher_id && this.exam_schedule[s_i].exam_invigilators[i].status != 'setup') {
                                return true
                            }                            
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
                    get_discontented_schedule_alert() {
                        this.dialog = true
                        Vue.axios.get('/user/schedule/get_discontented_schedule_alert').then(response => {
                            var dis_contented_schedule = response.data.daily_schedule;
                            if (dis_contented_schedule) {
                                this.alert_type = "warning"
                                this.schedule_alert = true
                            }
                            var date = this.$moment(new Date()).add(7, 'days').format('YYYY-MM-DD');
                            if (dis_contented_schedule && dis_contented_schedule.date <= date) {
                                this.alert_type = 'error'
                            }
                            this.dialog = false
                        });
                    },
                    
                    get_batch_attendance(batch_id, batch_name, s_i, b_i) {
                        this.current_batch_id = batch_id
                        this.current_batch_name = batch_name
                        this.current_batch_index = b_i
                        this.current_schedule_index = s_i
                        this.dialog = true
                        Vue.axios.get('/user/schedule/get_batch_attendance/' + batch_id).then(response => {
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