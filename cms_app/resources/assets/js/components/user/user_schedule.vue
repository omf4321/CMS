<template>
    <div class="user_schedule">
        <v-container fluid>
            <h3 class="fs-20 m-t-10 dash_title">Manage Schedule</h3>
            <v-divider class="my-3"></v-divider>
            <!-- Discontented Schedule Resolve List -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 step_none" v-if="step == 'none' && discontented_schedule.length>0">
                <h5 class="fw-600 dash_card_title">Discontented Schedule</h5>
                <hr class="m-t-5 m-b-5">
                <div v-for="schedule in discontented_schedule">
                    <!-- <p class="dash_card_subtitle"></p> -->
                    <div class="row m-b-15">
                        <div class="col-xs-5 p-r-5">{{schedule.batches.echelons.name}} - {{schedule.batches.name}}</div>
                        <div class="col-xs-4 p-l-5 p-r-5">{{schedule.subjects.name | first_letter_word}}</div>
                        <div class="col-xs-3 p-l-5">
                            <v-btn outline color="primary" class="tiny-btn m-t-0 m-b-0 p-l-4 p-r-4" @click="get_discontented_subject_schedule(schedule.batch_id, schedule.subject_id, schedule.batches.echelon_id, schedule.batches.echelons.name, schedule.subjects.name)">Resolve</v-btn>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Contentented Schedule List -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 step_none" v-if="step == 'none' && contented_schedule.length>0">
                <h5 class="fw-600 dash_card_title">Your Schedule List</h5>
                <div class="row schedule_add">
                    <div class="col-xs-5 p-r-5">
                        <v-text-field v-model="search_schedule" label="Search"></v-text-field>
                    </div>
                    <div class="col-xs-4 p-r-5 p-l-5">
                        <template>
                            <v-dialog full-width lazy ref="date_ref" width="290px">
                                <v-text-field :clearable = true label="Min Date" readonly slot="activator" v-model="search_date"></v-text-field>
                                <v-date-picker ref="picker" v-model="search_date"></v-date-picker>
                            </v-dialog>
                        </template>
                    </div>
                    <div class="col-xs-3 p-l-5">
                        <v-btn outline color="primary" class="tiny-btn" @click="get_contented_schdedule_by_date">GO</v-btn>
                    </div>
                </div>
                <hr class="m-t-5 m-b-5">
                <div v-for="(schedule, s_i) in filterContentedSchedule">
                    <div class="row">
                        <div :class="{'exam_color': schedule.schedule_type=='exam', 'col-xs-4 fs-12': true, 'p-r-5': true}">{{schedule.batches.echelons.name}} - {{schedule.subjects.name | first_letter_word}}</div>
                        <div class="col-xs-4 p-l-5 p-r-5">
                            <span v-if="schedule.chapter_text && schedule.topic" class="fs-12">{{(schedule.chapter_text + schedule.topic).substring(0,25)}}</span>
                            <span v-if="schedule.chapter_text" class="fs-12">{{schedule.chapter_text.substring(0,25)}}</span>
                            <span v-if="schedule.topic" class="fs-12">{{schedule.topic.substring(0,25)}}</span>
                        </div>
                        <div class="col-xs-4 p-l-5 pos-rel fs-12"><span :class="{'exam_color': schedule.schedule_type=='exam'}">{{schedule.date | moment("D MMM, dd")}}</span> <span class="pos-a cur-p edit_icon" style="right: 12px;" @click="get_single_contented_schedule(s_i, schedule.id, schedule.batches.echelons.name, schedule.subjects.name, schedule.date)"><v-icon class="fs-15">edit</v-icon></span>
                        </div>
                    </div>
                    <hr class="m-b-10 m-t-10">
                </div>
            </div>
            <!-- Discontented Schedule List By Subject For Edit -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-20 m-b-15 step_edit" v-if="step == 'editing_discontent' && !force_update">
                <h5 class="fw-600 dash_card_title">{{editing_discontented_schedule}} <span style="top: -5px" class="float-right pos-rel"><v-icon class="m-r-5" @click="clear_all">backspace</v-icon><v-icon @click="step='none'">close</v-icon></span></h5>
                <hr class="m-t-5 m-b-5">
                <div class="row m-b-5 pos-rel">
                    <div class="col-xs-7 p-r-5">
                        <v-text-field :clearable=true v-model="dialogItem.chapter_text" label="Chapter"></v-text-field>
                    </div>
                    <div class="col-xs-3 p-l-5 p-r-5">
                        <v-text-field v-model="dialogItem.count" label="Count"></v-text-field>
                    </div>
                    <div class="col-xs-2 p-l-5">
                        <v-btn flat color="primary" class="tiny-btn m-t-0 m-b-0 p-0 pos-rel" style="top: 20px" @click="add_topic_on_chapter">
                            <v-icon>add</v-icon>
                        </v-btn>
                    </div>
                </div>
                <hr class="m-t-5 m-b-5">
                <div class="row schedule_add" v-for="schedule in discontented_subject_schedule">
                    <div class="col-xs-4 p-r-5">
                        <v-text-field :clearable=true v-model="schedule.chapter_text" label="Chapter"></v-text-field>
                    </div>
                    <div class="col-xs-5 p-l-5 p-r-5">
                        <v-text-field :clearable=true v-model="schedule.topic" label="Topic"></v-text-field>
                    </div>
                    <div class="col-xs-3 p-l-5 p-r-5 pos-rel" style="top: 10px"><span :class="{'exam_color': schedule.schedule_type=='exam'}">{{schedule.date | moment("D MMM")}} <span class="fs-12" v-if="schedule.schedule_type == 'exam'">(exm)</span></span></div>
                </div>
                <hr class="m-t-5 m-b-15">
                <div v-if="batches.length">
                    <h6 class="fw-600 fs-14">Apply same change for following batch</h6>
                    <hr class="m-t-5 m-b-5" style="border: 1px solid #444">
                    <div class="row">
                        <div class="col-xs-4 col-md-2" v-for="batch in batches">
                            <v-checkbox style="color:#444" class="m-t-0" v-model="batch.value" :label="batch.name"></v-checkbox>
                        </div>
                    </div>
                </div>
                <v-btn color="success" class="mid-btn m-t-0 m-b-0 pos-rel" @click="save_discontented_schedule">Submit</v-btn>
            </div>
            <!-- Teacher Class Detail -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-20 m-b-15 step_edit" v-if="step == 'none'">
                <h5 class="fw-600 dash_card_title">Class Summary <span class="m-r-10" v-if="teacher_schedule_summary.length">({{total_teacher_schedule}})</span></h5>
                <div class="schedule_add">
                    <div class="col-xs-5 p-r-5 p-l-5">
                        <template>
                            <v-dialog v-model="date_from_dialog" full-width lazy width="290px">
                                <v-text-field label="Date From" readonly slot="activator" v-model="schedule_date_form"></v-text-field>
                                <v-date-picker @change="date_from_dialog = false" v-model="schedule_date_form"></v-date-picker>
                            </v-dialog>
                        </template>
                    </div>
                    <div class="col-xs-4 p-r-5 p-l-5">
                        <template>
                            <v-dialog v-model="date_to_dialog" full-width lazy width="290px">
                                <v-text-field label="Date To" readonly slot="activator" v-model="schedule_date_to"></v-text-field>
                                <v-date-picker @change="date_to_dialog = false" v-model="schedule_date_to"></v-date-picker>
                            </v-dialog>
                        </template>
                    </div>
                    <div class="col-xs-3 p-l-5">
                        <v-btn outline color="primary" class="tiny-btn" @click="get_teacher_schedule_summary">GO</v-btn>
                    </div>
                </div>
                <hr class="m-t-5 m-b-5" style="float: none; clear: both">
                <table class="table table-bordered fs-12 m-t-10">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="summary in teacher_schedule_summary">
                            <td>{{summary.date | moment("D MMM YY")}}</td>
                            <td>{{summary.echelon_name}} <span class="fs-8">({{summary.batches}})</span></td>
                            <td>{{summary.quantity}}</td>
                        </tr>
                    </tbody>
                </table>
                <div style="float: none; clear: both"></div>
            </div>
            <!-- Each Contented Item Edit -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-20 m-b-15 step_edit" v-if="step == 'editing_content'">
                <h5 class="fw-600 dash_card_title">{{editing_contented_schedule}}<v-icon class="float-right pos-rel" style="top: -5px" @click="step='none'">close</v-icon></h5>
                <hr class="m-t-5 m-b-5">
                <div class="schedule_add">
                    <label :class="{'fs-13': true, 'exam_color': true}" v-if="dialogItem.schedule_type == 'exam'">It's a Exam schedule</label>
                    <v-text-field v-model="dialogItem.topic" label="Topic"></v-text-field>
                    <v-text-field v-model="dialogItem.chapter_text" label="Chapter"></v-text-field>
                    <label v-if="dialogItem.schedule_type == 'class'" class="checkbox-inline m-l-15 fs-14"><input type="checkbox" v-model="dialogItem.lecture_sheet" value="">Lecture Sheet</label>
                </div>
                <hr class="m-t-5 m-b-5">
                <v-btn color="success" class="mid-btn m-t-0 m-b-0 pos-rel float-right" @click="save_contented_schedule">Submit</v-btn>
                <div style="float: none; clear: both"></div>
            </div>
        </v-container>
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
                    success_alert: true,
                    dialogItem: {
                        chapter_id: ''
                    },
                    chapter: [],
                    save_planner: false,
                    discontented_schedule: [],
                    discontented_subject_schedule: [],
                    contented_schedule: [],
                    planner_schedule: [],
                    editing_discontented_schedule: '',
                    editing_contented_schedule: '',
                    step: 'none',
                    dialog: false,
                    schedule_index: -1,
                    discontented_schedule_batch_id: '',
                    discontented_subject_id: '',
                    discontented_echelon_id: '',
                    search_schedule: '',
                    search_date: '',
                    teacher_schedule_summary: [],
                    schedule_date_form: '',
                    schedule_date_to: '',
                    total_teacher_schedule: 0,
                    force_update: false,
                    batches: [],
                    date_from_dialog: false,
                    date_to_dialog: false
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) {
                if (window_authorise != 'authorised') {return next('/authentication')}
                let role = window_user_role.findIndex(x => x == 'teacher');
                if (role > -1) {
                    next()
                } else {
                    next('/404')
                }
            },
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                }
            },
            computed: {
                filterContentedSchedule: function() {
                    // item.schedule_id.toString().indexOf(this.search_schedule) > -1
                    var rows = this.contented_schedule
                    var items = rows.filter(item =>
                        item.batches.echelons.name.toLowerCase().indexOf(this.search_schedule.toLowerCase()) > -1 ||
                        (item.subjects && item.subjects.name.toLowerCase().indexOf(this.search_schedule.toLowerCase()) > -1) ||
                        this.$moment(item.date).format('D MMM YY, ddd').toLowerCase().indexOf(this.search_schedule.toLowerCase()) > -1 ||
                        item.schedule_type.toLowerCase().indexOf(this.search_schedule.toLowerCase()) > -1)
                    return items
                },
            },
            created() {
                this.get_discontented_schdedule()
            },
            methods: {
                logout() {
                    this.$refs.logout_form.submit()
                },
                get_discontented_schdedule() {
                    Vue.axios.get('/user/schedule/get_discontented_schedule').then(response => {
                        this.discontented_schedule = response.data.daily_schedule;
                        this.get_contented_schdedule()
                    });
                },
                get_discontented_subject_schedule(batch_id, subject_id, echelon_id, echelon_name, subject_name) {
                    this.discontented_schedule_batch_id = batch_id
                    this.discontented_subject_id = subject_id
                    this.discontented_echelon_id = echelon_id
                    this.save_planner = false
                    this.dialogItem.subject_id = subject_id
                    this.step = 'editing_discontent'
                    this.dialog = true
                    this.editing_discontented_schedule = echelon_name + ' - ' + subject_name
                    Vue.axios.get('/user/schedule/get_discontented_subject_schedule/' + batch_id + '/' + subject_id).then(response => {
                        var schedule = response.data.daily_schedule;
                        this.batches = this.discontented_schedule.filter(x => x.batches.echelon_id == this.discontented_echelon_id && x.batch_id != this.discontented_schedule_batch_id).map(y => {return { id: y.batch_id, name: y.batches.name, value: false }})
                        var b = []
                        for (var i = 0; i < this.batches.length; i++) {
                            if (b.includes(this.batches[i].id)) {
                                this.batches.splice(i, 1);
                            } else {
                                b.push(this.batches[i].id)
                            }
                        }

                        schedule = schedule.map(x => {
                            return {
                                id: x.id,
                                topic: x.topic,
                                chapter_text: x.chapter_text,
                                date: x.date,
                                subject_id: x.subject_id,
                                schedule_type: x.schedule_type
                            }
                        })
                        this.discontented_subject_schedule = schedule
                        this.dialog = false
                    });
                    this.get_chapter()
                },
                get_single_contented_schedule(index, id, echelon_name, subject_name, date) {
                    this.schedule_index = index
                    this.dialog = true
                    Vue.axios.get('/user/schedule/get_single_contented_schedule/' + id).then(response => {
                        this.dialogItem.id = response.data.daily_schedule.id;
                        this.dialogItem.subject_id = response.data.daily_schedule.subject_id
                        this.step = 'editing_content'
                        this.dialogItem.topic = response.data.daily_schedule.topic
                        this.dialogItem.chapter_text = response.data.daily_schedule.chapter_text
                        this.dialogItem.schedule_type = response.data.daily_schedule.schedule_type
                        this.dialogItem.lecture_sheet = response.data.daily_schedule.lecture_sheet
                        this.editing_contented_schedule = echelon_name + ' - ' + subject_name + ' - ' + this.$moment(date).format("D MMM")
                        this.dialog = false
                        this.get_chapter()
                    });
                },
                get_contented_schdedule() {
                    this.dialog = true
                    Vue.axios.get('/user/schedule/get_contented_schedule').then(response => {
                        this.contented_schedule = response.data.daily_schedule;
                        this.dialog = false
                    });
                },
                get_contented_schdedule_by_date() {
                    if (!this.search_date) {
                        return false
                    }
                    this.dialog = true
                    Vue.axios.get('/user/schedule/get_contented_schedule_by_date/' + this.search_date).then(response => {
                        this.contented_schedule = response.data.daily_schedule;
                        this.dialog = false
                    });
                },
                get_chapter() {
                    Vue.axios.get('/admin/request/chapter_list_by_subject/' + this.dialogItem.subject_id).then(response => {
                        this.chapter = response.data.chapter_list_by_subject;
                        this.chapter = this.chapter.sort(function(a, b) {
                            return parseFloat(a.chapter_no) - parseFloat(b.chapter_no)
                        })
                        this.chapter = this.chapter.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['chapter_no'] + '-' + arr['name'],
                                chapter_no: arr['chapter_no']
                            }
                        })
                    });
                },
                save_discontented_schedule() {
                    this.dialog = true
                    var item = {}
                    item.schedule = this.discontented_subject_schedule;
                    item.save_planner = this.save_planner
                    item.schedule_id = this.discontented_schedule_batch_id
                    item.subject_id = this.discontented_subject_id
                    item.batch = this.batches.filter(x => x.value == true).map(y => {return y.id})
                    Vue.axios.post('/user/schedule/save_discontented_schedule', item).then(response => {
                        $('.user_nav').addClass('successful')
                        this.step = 'none'
                        // this.dialog = false;
                        this.get_discontented_schdedule()
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
                save_contented_schedule() {
                    this.dialog = true
                    Vue.axios.post('/user/schedule/save_contented_schedule', this.dialogItem).then(response => {
                        $('.user_nav').addClass('successful')
                        this.contented_schedule.splice(this.schedule_index, 1)
                        this.contented_schedule.push(response.data.daily_schedule)
                        this.contented_schedule.sort(function(a,b){
                            return new Date(a.date) - new Date(b.date)
                        })
                        this.step = 'none'
                        this.dialog = false;
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
                add_topic_on_chapter() {
                    var count = 0;
                    console.log(this.discontented_subject_schedule)
                    for (var i = 0; i < this.discontented_subject_schedule.length; i++) {
                        if (!this.discontented_subject_schedule[i].topic && this.discontented_subject_schedule[i].schedule_type == 'class') {
                            this.discontented_subject_schedule[i].chapter_text = this.dialogItem.chapter_text
                            count++
                            if (count == this.dialogItem.count) {
                                return false
                            }
                        }
                    }                      
                },
                get_planner_schedule() {
                    Vue.axios.get('/user/schedule/get_planner_schedule/' + this.dialogItem.chapter_id).then(response => {
                        this.planner_schedule = response.data.planner;
                        this.dialogItem.count = ''
                        if (this.planner_schedule.length) {
                            this.dialogItem.count = this.planner_schedule.length
                        }
                        this.$forceUpdate();
                    });
                },
                clear_all() {
                    for (var i = 0; i < this.discontented_subject_schedule.length; i++) {
                        this.discontented_subject_schedule[i].chapters = ''
                        this.discontented_subject_schedule[i].topic = ''
                    }
                },
                hide_menu() {
                    this.$refs['combobox'].blur()
                },
                get_teacher_schedule_summary() {
                    if (!this.schedule_date_form) {return alert('Please choose date from')}
                    this.dialog = true
                    Vue.axios.get('/user/schedule/get_teacher_schedule_summary/' + this.schedule_date_form + '/' + this.schedule_date_to).then(response => {                            
                        var item = response.data.teacher_attendance
                        this.dialog = false
                        var total = 0
                        var summary = []
                        for (var i = 0; i < item.length; i++) {
                            var object = {}
                            object.echelon_id = item[i].batches.echelon_id
                            object.echelon_name = item[i].batches.echelons.name
                            object.date = item[i].schedules.date
                            object.batches = item[i].batches.name
                            object.quantity = 1
                            if (i == 0) {summary.push(object)}
                            else {
                                var index = summary.findIndex(x => x.date == item[i].schedules.date && x.echelon_id == item[i].batches.echelon_id)
                                if (index>-1) {
                                    summary[index].quantity += 1
                                    summary[index].batches = summary[index].batches + ',' + item[i].batches.name
                                } else {
                                    summary.push(object)
                                }
                            }
                        }
                        console.log(summary)
                        summary = summary.sort(function(a, b){
                            return new Date(a.date) - new Date(b.date)
                        })
                        this.teacher_schedule_summary = summary
                        this.total_teacher_schedule = item.length
                    });
                },
                set_force_update(){
                    this.force_update = true
                    setTimeout(function() { this.force_update = false }.bind(this), 200)
                }
            }
    }
</script>

<style>
    #keep .v-navigation-drawer__border {display: none}
</style>