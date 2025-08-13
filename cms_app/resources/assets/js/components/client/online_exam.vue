<template>
    <div class="client_home">
        <v-container fluid>
            <div class="card p-l-15 p-r-15 p-t-15 p-b-10 m-b-15" v-if="step == 'begin'">
                <h4 class="fw-600 fs-17 dash_card_title text-center">{{question_list.exam_types.bangla_text}} </h4>
                <div class="text-center fs-14 m-b-5"> {{schedule.subjects.bangla_text}} </div>
                <div class="text-center fs-13 m-b-5 solaiman"> অতিথির স্মৃতি </div>
                <div class="text-center fs-12 m-b-5"> Full Marks- {{question_list.full_marks}}, Time - {{question_list.full_time}} Min </div>
                <hr class="m-t-5 m-b-15" style="border-bottom: 1px solid">
                <h4 class="fw-600 fs-16 text-center solaiman">মানবন্টন</h4>

                <div class="text-center fs-12 m-b-5 solaiman" v-for="type in question_list.exam_question_types"> 
                    <span class="text-center fs-12 m-b-5 solaiman" v-if="type.question_type == 'cq'">সৃজনশীল - {{type.answerable*10}} </span>
                    <span class="text-center fs-12 m-b-5 solaiman" v-if="type.question_type == 'mcq'"> বহুনির্বাচনী - {{type.answerable}} </span>
                </div>
                <div v-if="current_status !='submit'" class="text-center fs-12 m-b-5 solaiman" style="color: #f44336"> বহুনির্বাচনী পরীক্ষা থাকলে তা প্রথমে শুরু করতে হবে </div>
                <div v-if="question_type == 'mcq' && current_status != 'mcq_finish' && current_status != 'finish'" class="text-center p-b-25"><v-btn outline color="primary" class="m-t-10 m-b-0" @click="start_exam('mcq_start')">Start MCQ</v-btn></div>
                <div v-if="question_type != 'mcq' && current_status != 'finish' && current_status != 'submit'" class="text-center p-b-25"><v-btn outline color="primary" class="m-t-10 m-b-0" @click="start_exam('cq_start')">Start {{question_type}}</v-btn></div>
                <div class="text-center" v-if="current_status =='submit'">
                    <v-btn class="fs-20 fw-600" flat color="success">You Already Attend for this Exam</v-btn>
                    <router-link to="/" style="display:block">
                        <v-btn outline color="primary">Go Back</v-btn>
                    </router-link>
                </div>
                <div v-if="current_status !='submit'" class="solaiman fs-12">
                    <h4 class="fw-600 fs-16 text-center">Rules</h4>
                    <hr class="m-t-5 m-b-15" style="border-bottom: 1px solid">
                    <div class="text-justify m-b-5">১. পরীক্ষা শুরু করার পর একসাথে পরীক্ষা শেষ করতে হবে। ভিন্ন ভিন্ন সময়ে পরীক্ষায় অংশগ্রহণের সুযোগ নেই।</div>
                    <div class="text-justify m-b-5">২. কোন কারণে ইন্টারনেট সংযোগ বিচ্ছিন্ন হলে বহুর্নির্বাচনী পরীক্ষা চালানো যাবে। ।</div>
                </div>
            </div>
            <!-- MCQ -->
            <div class="card p-l-15 p-r-15 p-t-15 p-b-10 m-b-15" v-if="step == 'mcq'">
                <h4 class="fw-600 fs-17 dash_card_title text-center solaiman">বহুনির্বাচনী প্রশ্ন (২০) </h4>
                <div class="text-center m-b-15"><span style="border: 1px solid; padding: 5px 15px">{{timer.minute_zero}}{{timer.minutes}} : {{timer.second_zero}}{{timer.seconds}}</span></div>
                <div class="fw-600 fs-12 p-t-10 p-b-10">Question No: {{question_no + 1}}</div>
                <div class="solaiman fs-12 question" v-html="questions[question_no].question"></div>
                <div>
                    <div class="fw-600 fs-12">Answer</div>
                    <label class="radio-inline"> <input :disabled="!questions[question_no].skip" v-model="questions[question_no].given_answer" type="radio" value="1">1 </label>
                    <label class="radio-inline"> <input :disabled="!questions[question_no].skip" v-model="questions[question_no].given_answer" type="radio" value="2">2 </label>
                    <label class="radio-inline"> <input :disabled="!questions[question_no].skip" v-model="questions[question_no].given_answer" type="radio" value="3">3 </label>
                    <label class="radio-inline"> <input :disabled="!questions[question_no].skip" v-model="questions[question_no].given_answer" type="radio" value="4">4 </label>
                    <!-- <v-btn flat class="tiny-btn" @click="questions[question_no].given_answer = 0">clear</v-btn> -->
                </div>
                <div class="text-center m-t-10">
                    <v-btn outline small color="success" @click="next_question('next')" :disabled="!questions[question_no].given_answer" v-if="answer_remain && current_status != 'mcq_finish'">Next</v-btn>
                    <v-btn outline small color="info" @click="next_question('skip')" v-if="answer_remain && questions[question_no].skip">Skip</v-btn>
                    <v-btn outline small color="success" @click="get_mcq_result" v-if="!answer_remain || current_status == 'mcq_finish'">Finish</v-btn>
                </div>
                <div class="text-center p-t-15 p-b-25">
                    <div>Go To Question</div>
                    <v-btn v-for="(qb, key) in questions" :class="{'info': qb.given_answer > 0 || key == question_no, 'error': qb.given_answer == 0 && key != question_no, 'tiny-btn': true, 'current_question': key == question_no}" :key="key" @click="go_to_question(key)">{{key + 1}}</v-btn>
                </div>
            </div>
            <!-- mcq result -->
            <div class="card p-l-15 p-r-15 p-t-15 p-b-10 m-b-15" v-if="step == 'mcq_result'">
                <div class="fw-600 fs-12 p-t-10 p-b-10">Correct Answer: {{correct_answer}}</div>
                <v-btn outline small color="success" @click="step = 'mcq_review'">Review</v-btn>
                <v-btn outline small color="success" @click="step = 'begin', question_type = 'cq'">Go Back</v-btn>
            </div>
            <!-- mcq_review -->
            <div class="card p-l-15 p-r-15 p-t-15 p-b-10 m-b-15" v-if="step == 'mcq_review'">
                <div v-for="(question, key) in questions">
                    <div class="fw-600 fs-12 p-t-10 p-b-10">Question No: {{key + 1}}</div>
                    <div class="solaiman fs-12 question" v-html="question.question"></div>
                    <span class="fs-13 fw-600">Answer: {{question.answer}}</span>
                    <span :class="{'m-l-10': true, 'fs-13': true, 'fc-red': question.answer != question.given_answer, 'fc-green': question.answer == question.given_answer, 'fw-600': true}">Your Answer: {{question.given_answer}}</span>
                    <hr>
                </div>
                <div class="text-center p-b-15">
                    <v-btn outline small color="success" @click="step = 'begin', question_type = 'cq'">Go Back</v-btn>
                </div>
            </div>

            <div class="card p-l-15 p-r-15 p-t-15 p-b-10 m-b-15" v-if="step == 'cq'">
                <div class="text-center m-b-15"><span style="border: 1px solid; padding: 5px 15px">{{timer.minute_zero}}{{timer.minutes}} : {{timer.second_zero}}{{timer.seconds}}</span></div>
                <div v-if="current_status != 'finish'">
                    <h4 class="fw-600 fs-17 dash_card_title text-center solaiman">সৃজনশীল প্রশ্ন (২০) </h4>
                    <div class="pos-rel" v-for="(question, key) in questions" :key="key">
                        <span class="pos-a solaiman fs-12">{{key + 1}}. </span>
                        <div class="solaiman fs-12 question p-l-15 p-r-15" v-html="question.question"></div>
                    </div>
                </div>
                <div v-if="current_status == 'finish'" class="fw-600 fc-red fs-14 text-center">Exam is Finished. Please Upload the script photos in 10 miniutes</div>
                <div class="fw-600 fs-17 m-t-15 text-center p-t-15 p-b-15">Upload Script</div>
                <template>
                  <div class="text-center p-b-15">
                    <vue-simple-upload :options="options" @progress-update="progressUpdate" @file-size-error="fileSizeError " ref="fileUploadComp"> </vue-simple-upload>
                    <div class="upload-file-info-section p-t-15" v-show="fileInfoList.length > 0">
                      <div class="file-info-item" v-for="(fileInfo, index) in fileInfoList" :key="index">
                        <div class="progress-info">
                            <div class="text-center">
                                <div class="file-progress" v-if="fileInfo.type != 'success'">{{ fileInfo.progress }}</div>
                                <img class="m-b-5" src="" :id="fileInfo.id" style="width: 120px">
                                <input v-model="fileInfo.page_number" class="form-control text-center m-auto" type="number" name="page_no" v-if="fileInfo.type === 'success'" placeholder="Page Number" style="width: 120px">
                                <div class="m-t-5">{{ fileInfo.fileName }}</div>
                                <div class="operate">
                                    <v-btn class="success tiny-btn" v-if="fileInfo.type === 'waiting'" @click="startUpload(fileInfo.id)">Start Upload</v-btn>
                                    <!-- <span class="fa fa-arrow-up fl-l" v-if="fileInfo.type === 'waiting'" @click="startUpload(fileInfo.id)"></span> -->
                                    <span style="color: green; font-size: 12" v-if="fileInfo.type === 'success'">Uploaded</span>
                                    <span class="tiny-btn" color="error" v-if="fileInfo.type === 'fail' || fileInfo.type === 'abort'">Fail</span>
                                    <v-icon color="error" v-if="fileInfo.type === 'uploading'" @click="abortUpload(fileInfo.id)">cancel</v-icon>
                                    <v-icon class="fs-18" color="error" v-if="fileInfo.type === 'success'" @click="">delete</v-icon>
                                </div>
                            </div>
                          <!-- <div class="file-name">{{ fileInfo.fileName }}</div> -->
                        </div>
                        <div
                          class="progress-bg col-xs-2"
                          :style="{ width: fileInfo.type === 'uploading' ? fileInfo.progress : '100%' }"
                        ></div>
                      <hr style="float: none; clear: both">
                      </div>
                    </div>
                    <div class="text-center m-t-15" v-if="fileInfoList.length">
                        <v-btn color="success" @click="submit_script">Submit</v-btn>
                    </div>
                  </div>
                </template>
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
    var watch_time
    import {VueSimpleUpload} from 'vue-simple-file-upload'
    export default {
        components: { VueSimpleUpload },
        data() {
                return {
                    payment_alert: true,
                    schedule: {},
                    now: this.$moment(new Date()).format('HH:mm:ss'),
                    timer: {minutes: 0, seconds: 0, minute_zero: 0, second_zero: 0},
                    questions: [{question: '', answer: 0, given_answer: 0, skip: true}],
                    question_list: '',
                    step: 'begin',
                    question_no: 0,
                    explore_question: 0,
                    answer_remain: true,
                    dialog: false,
                    has_mcq: false,
                    question_type: '',
                    question_limit: 0,
                    correct_answer: 0,
                    options: {
                        className: 'v-btn v-btn--small theme--light info',
                        btnContent: 'Add Files + ',
                        url: '/client/upload_script',
                        accept: 'image/jpeg, image/png',
                        formData: {'_token': $('meta[name="csrf-token"]').attr('content'), 'schedule_id': this.$route.params.schedule_id},
                        multiple: true,
                        autoStart: true,
                        size: 7000000
                    },
                    imageUrl: '',
                    fileInfoList: [],
                    current_status: '',
                    student: {}
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
                this.prepare_exam()
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
                },
                digit_to_bangla: function(value) {
                    var numbers = [0, 1, 2, 3, '4', '5', '6', '7', '8', '9'];
                    var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
                    for (var i = 0; i < value.length; ++i) {
                        var index = numbers.findIndex(x => x == value[i])
                        value = value.replace(numbers[index], bangla_num[index])
                    }
                    return value;
                },
            },
            methods: {
                prepare_exam(){
                    this.dialog = true
                    Vue.axios.get('/client/get_question_type/' + this.$route.params.schedule_id).then(response => {
                        this.dialog = false
                        this.schedule = response.data.question.schedules[0]
                        this.student = response.data.student
                        this.question_list = response.data.question
                        
                        // current status
                        this.current_status = this.student.online_exams.length ? this.student.online_exams[0].status : ''
                        
                        if (this.current_status == 'submit' || this.schedule.online_exam_status == 'submit') {
                            this.current_status = 'submit'
                            this.$cookies.remove('timer')
                            this.$cookies.remove('cookie_mcq_question')
                            this.$cookies.remove('current_status')
                            return false
                        }
                        this.$cookies.remove('timer')
                        if (this.$cookies.get('current_status')) {
                            this.current_status = this.$cookies.get('current_status')
                            if (this.current_status == 'finish') {
                                this.step = 'cq'
                                this.get_time()
                                return false
                            }
                            console.log(this.current_status)
                        }
                        


                        var item = response.data.question.exam_question_types
                        for (var i = 0; i < item.length; i++) {
                            if (item[i].question_type != 'mcq') {
                                this.question_type = item[i].question_type
                            }
                            if (item[i].question_type == 'mcq' && this.current_status != 'mcq_finish' && this.current_status != 'cq_start') {
                                this.question_type = 'mcq'
                                break
                            }
                        }

                        if (this.current_status == 'mcq_start') {
                            this.current_status = 'mcq_start'
                            this.start_exam()
                        }
                    });
                },
                get_question() {
                    // this.dialog = true
                    var index = this.question_list.exam_question_types.findIndex(x => x.question_type == this.question_type)
                    var item = this.question_list.exam_question_types[index].questions;
                    item = item.map(arr => { return { id: arr['id'], question: arr['question_text'], answer: arr['mcq_answer'], given_answer: '', skip: true } })
                    this.questions = item
                    var cookie_question = this.$cookies.get('cookie_mcq_question')
                    if (cookie_question) {
                        var x = JSON.parse(cookie_question)
                        if (x.length) {
                            var y = false
                            var count = 0
                            for (var i = 0; i < this.questions.length; i++) {
                                this.questions[i].answer = x[i].answer
                                this.questions[i].given_answer = x[i].given_answer
                                this.questions[i].skip = x[i].skip
                                if (x[i].given_answer) {
                                    count = count + 1
                                }
                                if (x[i].skip == true && y == false) {
                                    y = true
                                    this.question_no = i
                                }
                            }
                            if (count == this.questions.length) {
                                this.current_status = 'mcq_finish'
                            }
                            this.next_question('next', true)
                        }
                    }
                    this.$cookies.set('current_status', this.current_status)
                    // this.dialog = false;
                },                
                get_time(){
                    this.timer.minutes = 0
                    this.timer.seconds = 0
                    var interval_time = 5000
                    // this.$cookies.remove('timer') 
                    if (!this.$cookies.get('timer') && this.schedule.online_exam_time) {
                        var now = this.$moment(new Date(), 'HH:mm:ss'); //todays date
                        var end = this.$moment(this.schedule.online_exam_time, 'HH:mm:ss'); // another date
                        var duration = this.$moment.duration(now.diff(end))
                        var st = Math.floor(duration.asMinutes());
                        if (!this.current_status) {
                            if (st < 10) {st = 0} else {st = st - 10}
                        }
                        this.timer.minutes = st
                    } else {
                        var cookie_time = this.$cookies.get('timer') 
                        if (cookie_time) {
                            // var x = JSON.parse(cookie_time)
                            this.timer.seconds = parseInt(cookie_time.seconds)
                            this.timer.minutes = parseInt(cookie_time.minutes)
                            interval_time = 2000
                        }
                    }

                    if (this.current_status == 'finish') {
                        this.timer.minutes = this.question_list.full_time
                        this.timer.seconds = 0
                    }

                    setTimeout(function() {
                        watch_time = setInterval(function() {
                            this.timer.minute_zero = this.timer.minutes < 9 ? 0 : ''
                            this.timer.second_zero = this.timer.seconds < 9 ? 0 : ''
                            this.timer.seconds = this.timer.seconds + 1;
                            if (this.timer.seconds == 60) {
                                this.timer.minutes = this.timer.minutes + 1;
                                this.timer.second_zero = 0
                                this.timer.seconds = 0
                            }
                            if (this.timer.seconds%5 == 0) {
                                // this.$cookies.remove("timer");
                                var t = {minutes: parseInt(this.timer.minutes), seconds: parseInt(this.timer.seconds)}
                                var x = JSON.stringify(t)
                                this.$cookies.set('timer', x)
                            }
                            var counter = 0
                            if (counter == 0 && this.timer.minutes >= this.question_list.full_time) {
                                this.current_status = 'finish'
                                 this.$cookies.set('current_status', 'finish')
                                 counter = 1
                            }
                            if (this.timer.minutes >= this.question_list.full_time + 30) {
                                this.$cookies.remove('cookie_mcq_question')
                                clearInterval(watch_time)
                                this.current_status = 'submit'
                                this.$cookies.remove('timer')
                                this.$cookies.remove('cookie_mcq_question')
                                this.submit_script()
                            }
                        }.bind(this), 1000)
                    }.bind(this), interval_time)
                },
                start_exam(type){
                    this.current_status = type
                    this.step = this.question_type
                    this.get_time()
                    this.get_question();
                },
                next_question(option, on_load){
                    if (option == 'skip') {
                        this.questions[this.question_no].given_answer = 0
                    } 

                    if (option == 'next' && this.questions[this.question_no].given_answer == 0) {
                        return false;
                    }

                    if (option == 'next') {
                        this.questions[this.question_no].skip = false
                    }
                    
                    if (this.questions.length == this.explore_question + 1) {
                        this.answer_remain = false
                        for (var i = 0; i < this.questions.length; i++) {
                            if (this.questions[i].given_answer == 0){
                                this.answer_remain = true
                                if (this.question_no != i) {
                                    this.question_no = i
                                    break
                                }
                            }
                        }
                    } else {
                        this.explore_question = this.question_no + 1
                        this.question_no = this.question_no + 1
                    }
                    // set cookie
                    if (!on_load) {                        
                        var cookie_item =  this.questions.map(arr => { return { id: arr['id'], answer: arr['answer'], given_answer: arr['given_answer'], skip: arr['skip'] } })
                        var x = JSON.stringify(cookie_item)
                        this.$cookies.set('cookie_mcq_question', x);                        
                    }
                },
                go_to_question(index){
                    if (this.explore_question < index) {
                        return false
                    }
                    this.question_no = index
                },
                get_mcq_result(){
                    for (var i = 0; i < this.questions.length; i++) {
                        if (this.questions[i].answer == this.questions[i].given_answer) {
                            this.correct_answer += 1;
                        }
                    }
                    this.step = 'mcq_result'

                    clearInterval(watch_time)
                    var t = {minutes: parseInt(this.timer.minutes), seconds: parseInt(this.timer.seconds)}
                    var x = JSON.stringify(t)
                    this.$cookies.set('timer', x)

                    var item = {}
                    item.schedule_id = this.schedule.id
                    item.questions = this.questions
                    Vue.axios.post('/client/finish_mcq_exam', item).then(response => {                        
                        this.$cookies.set('current_status', 'mcq_finish')                       
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                submit_script(){
                    clearInterval(watch_time)
                    this.$cookies.remove('timer')
                    this.$cookies.remove('cookie_mcq_question')

                    var item = {}
                    item.fileinfolist = this.fileInfoList.length ? this.fileInfoList : []
                    item.status = 'submit'
                    item.schedule_id = this.schedule.id
                    Vue.axios.post('/client/submit_script', item).then(response => {
                        this.current_status = 'submit'    
                        this.$cookies.remove('current_status')
                        this.$router.push({ name: 'student_home' })            
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.loading = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                fileSizeError(fileNames) {
                  alert('You can not upload more than 7MB file')
                },

                progressUpdate(fileInfoList) {
                  
                  // this.fileInfoList = fileInfoList
                  for (var i = 0; i < fileInfoList.length; i++) {
                    var index = this.fileInfoList.findIndex(x => x.id == fileInfoList[i].id)
                    if (index == -1) {
                        this.fileInfoList.push(fileInfoList[i])
                        this.read_file(fileInfoList[i].fileInfo, fileInfoList[i].id)
                    }
                    if (index > -1 && fileInfoList[i].type == 'fail') {
                        this.fileInfoList.splice(index, 1)
                    }
                  }
                },

                abortUpload(id) {
                  this.$refs.fileUploadComp.abort(id)
                },

                startUpload(id) {
                    this.$refs.fileUploadComp.startUpload(id)
                },
                read_file(file, id){
                    var reader = new FileReader();
                    reader.onload = function() {
                        var dataURL = reader.result;
                        var output = document.getElementById(id)
                        output.src = dataURL;
                    }
                    reader.readAsDataURL(file)
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
  .question p {
    margin-bottom: 5px;
  }
  .current_question {
    border: 1px solid #000!important;
  }
  .fc-red {
    color: #F44336;
  }
  .fc-green {
    color: #4CAF50;
  }
</style>