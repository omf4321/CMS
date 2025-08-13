<template>
    <div class="client_home">
        <v-container fluid>
        <!-- todays schedule -->
            <v-alert :value="payment_alert" transition="scale-transition" color="error" icon="info" outline>You didn't pay for {{new Date() | moment("MMMM YYYY")}}. Please pay within 3 days.
                <!-- <router-link :to="{name: 'user_schedule'}">
                    <v-btn color="info" class="tiny-btn m-t-0 m-b-0 fs-12">Pay</v-btn>
                </router-link> -->
            </v-alert>
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title">Today's Schedule 
                    <span class="m-l-20">{{clock}}</span>
                   <!--  <span class="cur-p fw-500 fs-13 m-l-10" @click="get_schedule">
                    <v-icon class="fs-14 m-r-2 cur-p pos-rel" style="top: 0px">refresh</v-icon>refresh</span>  -->
                </h4>
                <hr class="m-t-5 m-b-15" style="border-bottom: 1px solid">
                <div>
                    <div class="m-b-10" v-for="(sch, s_i) in today_schedule">
                        <div class="row">
                            <div class="col-xs-4 p-r-5">
                                <div class="fw-600">{{sch.subjects.name | first_letter_word}}</div>
                                <div class="fs-10">{{sch.teachers ? sch.teachers.name : ''}}</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-12">{{sch.schedule_type | underscore_capital_first}}</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}" v-if="!sch.online_class_status">
                                <div class="fs-12" v-if="sch.period">{{sch.period | ordinal_number}} Period</div>
                                <div class="fs-12">{{sch.time}}</div>
                            </div>
                            <div class="col-xs-4 p-l-5" v-if="sch.online_class_status == 'begin' && !sch.online_exam_status">
                                <a :href="sch.online_class_url" target="_blank" id="online_url" style="display:none"></a>
                                <v-btn outline color="primary" class="tiny-btn m-t-0 m-b-0 p-r-4 p-l-4" @click="change_online_class(s_i, 'join')">Join</v-btn>
                                <div class="fs-12" style="color: #f44336">{{time_message}}</div>
                            </div>
                            <div class="col-xs-4 p-l-5" v-if="sch.schedule_type == 'online_exam' && sch.online_exam_status == 'exam_start'">
                                <router-link :to="{ name: 'online_exam', params: { schedule_id: sch.id }}">
                                    <v-btn outline color="primary" class="tiny-btn m-t-0 m-b-0 p-r-4 p-l-4">Start Exam</v-btn>
                                </router-link>
                            </div>
                            <div class="col-xs-4 p-l-5" v-if="sch.online_class_status == 'finish' || sch.online_exam_status == 'submit'">
                                <v-btn flat color="primary" class="tiny-btn m-t-0 m-b-0 p-r-4 p-l-4">Finish</v-btn>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                    <div class="m-b-10" v-if="!today_schedule.length">
                        No Schedule Today
                    </div>
                </div>
            </div>
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title">Next Schedule <span class="cur-p fw-500 fs-13 m-l-10"</h4>
                <hr class="m-t-5 m-b-15" style="border-bottom: 1px solid">
                <div>
                    <div class="m-b-10" v-for="sch in next_schedule">
                        <div class="row">
                            <div class="col-xs-4 p-r-5">
                                <div class="fs-12 fw-600">{{sch.date | moment("D MMM YY")}}</div>
                                <div class="fs-12">{{sch.date | moment("ddd")}}</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-12">{{sch.subjects.name | first_letter_word}}</div>
                                <div class="fs-10">{{sch.teachers ? sch.teachers.name : ''}}</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-12" v-if="sch.period">{{sch.period | ordinal_number}} - {{sch.time}}</div>
                                <div class="fs-12">{{sch.schedule_type | underscore_capital_first}}</div>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                    <div class="m-b-10" v-if="!next_schedule.length">
                        No Next Schedule
                    </div>
                    <!-- <div class="cur-p fs-12" style="color: #2196f3" v-if="next_schedule.length">View full schedule</div> -->
                </div>
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
                    payment_alert: false,
                    schedule: [],
                    today_schedule: [],
                    next_schedule: [],
                    now: this.$moment(new Date()).format('HH:mm:ss'),
                    dialog: false,
                    time_message: '',
                    clock: ''
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) { 
                if (window_authorise != 'authorised') {return next('/authentication')}  
                else { return next()}
            },
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            created() {
                this.get_schedule()
                this.startTime()
                Echo.channel('chat')
                .listen('.MessageSent', (e) => {
                    for (var i = 0; i < e.schedule.length; i++) {
                        var index = this.schedule.findIndex(x => x.id == e.schedule[i].id)
                        if (index> -1) {
                            this.schedule[index].online_class_url = e.schedule[i].online_class_url
                            this.schedule[index].online_class_status = e.schedule[i].online_class_status
                            this.schedule[index].online_exam_status = e.schedule[i].online_exam_status
                            var today = this.$moment().format('YYYY-MM-DD')
                            this.schedule[index].time = e.schedule[i].time
                            this.$forceUpdate()
                        }
                    }
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
                },
                underscore_capital_first: function(value) {
                    value = value.replace(/_/g, " ");
                    return value = value.charAt(0).toUpperCase() + value.slice(1);
                }
            },
            methods: {
                get_schedule() {
                    this.dialog = true
                    Vue.axios.get('/client/get_schedule').then(response => {
                        this.dialog = false;
                        this.schedule = response.data.daily_schedule;
                        var date = this.$moment(new Date()).format('YYYY-MM-DD');
                        var date_7 = this.$moment(new Date()).add(7, 'days').format('YYYY-MM-DD');
                        this.today_schedule = this.schedule.filter(x => x.date == date)
                        this.next_schedule = this.schedule.filter(x => x.date > date && x.date <= date_7)
                        if (response.data.payment == 'unpaid') {this.payment_alert = true}
                    });
                },
                change_online_class(index, status){
                    this.time_message = ''
                    var now = this.$moment()
                    var time = this.$moment(now.format('YYYY-MM-DD') + ' ' + this.today_schedule[index].time).subtract(5, 'minutes')
                    var now = this.$moment()
                    if (status == 'join' && time < now) {                       
                        document.getElementById("online_url").click();
                        var date = this.$moment(now.format('MM-DD'));
                        var cookie = this.$cookies.get('attendance_' + date);
                        if (!cookie) {
                            this.save_attendance()
                            this.$cookies.set('attendance_' + date, 1, '4h');
                        }
                    } else {
                        this.time_message = 'Start at ' + this.$moment(now.format('YYYY-MM-DD') + ' ' + this.today_schedule[index].time).subtract(5, 'minutes').format('HH:mm:ss')
                    }
                },
                save_attendance(student_id){
                    Vue.axios.get('/client/save_attendance').then(response => {});
                },
                startTime() {
                    var today = new Date();
                    var h = today.getHours();
                    var m = today.getMinutes();
                    var s = today.getSeconds();
                    m = this.checkTime(m);
                    s = this.checkTime(s);
                    this.clock = h + ":" + m + ":" + s;
                    var t = setTimeout(this.startTime, 500);
                },
                checkTime(i) {
                  if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
                  return i;
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