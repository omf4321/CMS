<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
   <div class="question_check">
      <v-container fluid class="p-t-10">
          <div v-if="step == 1">
            <h3 class="headline m-t-10 fs-22">Check Questions</h3>
            <v-divider class="my-3"></v-divider>
          </div>
         <!-- @page_headline -->
         <div class="step_1_div m-t-20" v-if="step==1">
             <v-layout class="">
                <v-flex xs5 class="mr-3">
                  <v-select
                   v-model="search_echelon"
                   :items="echelon"
                   label="Class"
                   :clearable=true
                   style="padding: 0px;"
                   @change="get_subject"
                  ></v-select>
                </v-flex>
                <v-flex xs7 class="mr-3">
                  <v-select
                   v-model="search_subject"
                   :items="subject"
                   label="Subject"
                   :clearable=true
                   style="padding: 0px;"
                   @change="get_chapter"
                  ></v-select>
                </v-flex>
              </v-layout>
              <v-layout class="">
                <v-flex xs5 class="mr-3">
                  <v-select class="p-0"
                   v-model="search_question_type"
                   :items="question_type"
                   label="Type"
                   :clearable=true
                  ></v-select>
                </v-flex>
                <v-flex xs7 class="mr-3">
                  <v-select class="p-0"
                   v-model="search_question_status"
                   :items="question_status"
                   label="Status"
                   :clearable=true
                  ></v-select>
                </v-flex>
              </v-layout>
              <v-layout>
                <v-flex xs12 class="mr-3">
                  <v-combobox class="p-0"
                    ref="combobox"
                    v-model="search_chapter"
                    :items="chapter"
                    hide-selected
                    label="Chapters"
                    :clearable=true
                    multiple
                    persistent-hint
                    small-chips
                    @change="get_chapter_topic"
                  ></v-combobox>
                </v-flex xs4>
              </v-layout>
              <v-layout>
                <v-flex xs5 class="mr-3">
                  <v-text-field
                   v-model="search_id"
                   label="Search By ID"
                  ></v-text-field>
                </v-flex>
                <v-flex xs7 class="mr-3">
                  <v-text-field
                   v-model="search_tag"
                   label="Tag"
                  ></v-text-field>
                </v-flex>
              </v-layout>
              <v-layout class="text-center pos-rel">
                  <v-btn small :loading="loading" :disabled="search_chapter.length==0" color="primary" style="margin: 20px auto 0px!important;" @click="question_list">Go</v-btn>
                  <v-icon style="position:absolute; top:20px;" v-if="original_question_list_data.length>0" @click="go_to_last_question">history</v-icon>
              </v-layout>
         </div>
         <template>
            <div>
              <!-- @accesories_alert -->
              <v-alert
                :value="success_alert"
                transition="scale-transition"
                color="rgb(43, 123, 0)"
                icon="check_circle"
                outline
              >
              </v-alert>
              <!-- @accesories_alert -->
              <v-alert
                :value="error_alert"
                transition="scale-transition"
                color="error"
                icon="warning"
                outline
              >
              </v-alert>
               <template>
                  <v-form ref="form" v-model="valid" lazy-validation>
                    <div class=" container p-0 p-b-80">
                      <div class="step_2_div m-t-10" v-if="step==2">
                        <div v-for="(question, q_i) in showing_question" :key="q_i">
                          <div v-if="q_i == 0 && question.question_relative_text_id">
                            <p class="fw-600">Relative Text ({{question.chapters[0].name}} {{question.topics.length>0 ? ', ' + question.topics[0].name : ''}})</p>
                            <div class="m-b-5 note-editable relative_editor" contentEditable="true" v-html="question.relatives.relative_text" @input="change = 1"></div>
                            <ul style="list-style: none" class="list-inline" v-if="question.relatives.relative_question_type">
                              <div style="color: #FF5722">Click the question type...</div>
                              <li style="background-color:#607D8B; padding: 3px 15px; border-radius:18px; color: #fff; margin:5px; cursor: pointer" v-for="(type, t_i) in question.relatives.relative_question_type.split(',')" @click="change_relative_question_type(question.question_relative_text_id, type)">{{type.toUpperCase()}}</li>
                            </ul>
                          </div>
                          <div v-if="show_relative_question" class="card mx-auto m-b-10 p-t-15 p-b-15 question_container_card pos-rel">
                            <span class="pos-a" style="left: 50%; transform: translateX(-50%); top: 15px">{{question.question_type.toUpperCase()}} ({{question.id}})</span>
                            <div class="pos-a" style="right: 8px; top: 8px;" v-if="is_admin() || check_permission()">
                              <span @click="delete_question(question.id, q_i, question.question_relative_text_id)" class="fs-20 m-r-7 fa fa-trash"></span>
                            </div>
                            <div class="container">
                              <v-combobox
                                v-model="selected_chapter[q_i]"
                                :items="chapter"
                                hide-selected
                                label="Chapters"
                                :clearable=true
                                multiple
                                persistent-hint
                                small-chips
                                @change="change_chapter(q_i, question.id)"
                              ></v-combobox>
                              <div class="question_div pos-rel">
                                <div class="m-b-5 note-editable question_editor" contentEditable="true" v-html="question.question_text" @input="change = 1"></div>
                                <div class="short_answer_div m-b-15 note-editable" contentEditable="true" v-html="question.short_answer" @input="change = 1" data-placeholder="Short Answer">
                                </div>
                              </div>
                              <hr class="m-t-5 m-b-5">
                              <div class="question_radio">
                                <label class="radio-inline"><input type="radio" v-model="question.question_status" value="examable" @change="change=1">Ex</label>
                                <label class="radio-inline"><input type="radio" v-model="question.question_status" value="lecturable" @change="change=1">Lec</label>
                                <label class="radio-inline"><input type="radio" v-model="question.question_status" value="both" @change="change=1">Both</label>
                                <label class="radio-inline"><input type="radio" v-model="question.question_status" value="wrong" @change="change=1">Wr</label>
                              </div>
                              <hr class="m-t-5 m-b-5">
                              <ul class="checkbox_flex p-0 list-inline" v-if="chapter_topic[q_i].length>0">
                                  <li @click="" v-for="(topic, j) in chapter_topic[q_i]">
                                      <label class="checkbox-inline"><input type="checkbox" v-model="topic.val" @change="change_topic(q_i, question.id)" value="">{{topic.topic_no}}-{{topic.text}}</label>
                                  </li>
                              </ul>
                            </div>
                          </div>
                          <div v-if="question.detail_answer" class="p-b-30">
                              <div class="fw-600 m-b-10">Detail Answer</div>
                              <div class="m-t-5 note-editable answer_editor" contentEditable="true" v-html="question.detail_answer" @input="change=1"></div>
                          </div>
                        </div>
                        <v-icon style="position:fixed; bottom:50px; right:15px; font-size:40px;" @click="refine_text">filter_tilt_shift</v-icon>
                        <v-icon style="position:fixed; bottom:50px; left:15px; font-size:40px; z-index: 3" @click="reset">cancel</v-icon>
                      </div>
                    </div>
                  </v-form>
                </template>
            </div>
         </template>
      </v-container>
      <v-dialog v-model="dialog" hide-dialog persistent width="300">
        <v-card color="#009688" dark>
          <v-card-text>
            Please Wait..
            <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
          </v-card-text>
        </v-card>
      </v-dialog>
      <template>
        <v-footer class="pa-3" dark color="teal" fixed height="40" v-if="step>1">
          <v-btn color="#fff"flat style="min-width: 10px; padding: 0px">
            <v-icon medium @click="go_back">arrow_back</v-icon>
          </v-btn>
          <div style="width: 100%; text-align: center">
            <span>P{{question_page}}</span>
            <v-btn color="#d2d2d2" flat v-if="step==2" @click="prev" style="min-width: 10px; padding: 0px; margin: 0px!important">
              <v-icon large>navigate_before</v-icon>
            </v-btn>
            <span v-if="step==2" style="width: 60px;"><input type="number" v-model="question_input_index" style="width: 25px; text-align: center"> / {{question_list_w_relative.length}}</span>
            <v-btn color="#CDDC39" flat @click="next" v-if="step==2" style="min-width: 10px; padding: 0px; margin: 0px!important">
              <v-icon large>navigate_next</v-icon>
            </v-btn>
          </div>
          <v-spacer></v-spacer>
          <v-btn :disabled="change == 0" color="#fff" flat v-if="step==2" @click="save" style="min-width: 10px; padding: 0px; margin: 0px!important">
              <v-icon>save</v-icon>
          </v-btn>
        </v-footer>
      </template>
   </div>
</template>
<script>
   // import JQuery from 'jquery'
   // let $ = JQuery
   import htmlEditor from './html-editor'
   export default {
     components: {'htmlEditor': htmlEditor},
     data() {
             return {
               echelon: [],
               subject: [],
               original_subject: [],
               chapter: [],
               original_chapter: [],
               chapter_topic: [],
               original_chapter_topic: [],
               question: [],
               question_tag: [],
               combine_question_list: [],
               question_list_data: [],
               original_question_list_data: [],
               question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}],
               question_status: [{value: 'examable', text: 'Examable'}, {value: 'lecturable', text: 'Lecturable'}, {value: 'both', text: 'Both'}, {value: 'wrong', text: 'Wrong'}],
               relative_text: [],
               original_relative_text: [],
               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               short_answer: [],
               short_answer_text: '',
               dialogItem: {
                 id: '',
                 subject_id: '',
                 question_type: '',
                 question_mark: '',
                 board_question_no: '',
                 question_status: '',
                 chapters: [],
                 topics: [],
                 question_text: '',
                 short_answer: '',
                 detail_answer: '',
                 question_tag: '',
                 question_length: "0",
                 relative_text: [],
                 relative_textarea: '',
                 relative_text_id: ''
               },
               editedItem: '',
               rules: {
                 required: value => !!value || 'Required.'
               },

               // @accesories_data
               valid: true,
               valid_1: true,
               success_alert: false,
               error_alert: false,
               loading: false,
               question_editor: '',
               answer_editor: '',
               relative_text_editor: '',
               editor_detail: [],
               question_notation: 'digit',
               question_begin_text: '',
               question_end_text: ')',
               question_child_notation: 'unicode',
               question_child_begin_text: '',
               question_child_end_text: ')',
               step: 1,
               subject_index:'',
               chapter_index: '',
               question_index: "0",
               question_w_relative_index: "0",
               question_input_index: '0',
               question_type_header: [],
               search_1: '',
               search_2: '',
               search_echelon: '',
               search_subject: '',
               search_question_type: '',
               search_question_status: '',
               search_chapter: '',
               search_id: '',
               search_tag: '',
               selected_chapter: [],
               bottomNav: 'recent',
               showing_question: [],
               original_showing_question: [],
               showing_question_type: 'general',
               question_list_w_relative: [],
               change: 0,
               cookie_index: false,
               relative_question_type_filter: '',
               show_relative_question: false,
               question_page: 1,
               question_next_url: '',
               question_prev_url: ''
            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        if (window_user_role == 'teacher' && window_authorise != 'authorised') {return next('/authentication')}
        let p = window_admin_permissions.findIndex(x => x.name=='question_check');
        let r = window_user_role ? window_user_role.findIndex(x => x == 'teacher') : -1
        if (p > -1 || r > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     filters: {
        strip_tags: function (value) {

        }
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
     methods:{
        // @add_item_method
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.dialogItem.branch_id = this.branch[0].id
               this.admin_echelon_list(this.dialogItem.branch_id);
               this.admin_subject_list()
               // this.relative_text_list()
           }
           this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } })
       },
       admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
       },
       admin_subject_list(row_subject_id){
         Vue.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
             this.original_subject = response.data.subject_list_by_branch;
         });
       },
       chapter_list(){
          Vue.axios.get('/admin/request/chapter_list_by_class/'+this.search_echelon).then(response => {
              this.original_chapter = response.data.chapter_list_by_class;
          });
        },
        chapter_topic_list(){
          Vue.axios.get('/admin/request/chapter_topic_by_class/' + this.search_echelon).then(response => {
              this.original_chapter_topic = response.data.chapter_topic_list;
          });
        },
        question_list(){
          this.loading = true
          if (this.step == 2) {this.dialog = true}
          var question_search = {}
          question_search.echelon_id = this.search_echelon
          question_search.subject_id = this.search_subject
          question_search.chapter_id = this.search_chapter
          question_search.question_type = this.search_question_type
          question_search.question_status = this.search_question_status
          question_search.question_tag = this.search_tag
          question_search.id = this.search_id
          Vue.axios.post('/admin/question/question_check_list?page=' + this.question_page, question_search).then(response => {
              this.original_question_list_data = response.data.question_list.data;
              this.question_next_url = response.data.question_list.next_page_url
              this.question_prev_url = response.data.question_list.prev_page_url
              setTimeout(function(){
                this.loading = false
                if (this.original_question_list_data.length==0) {
                  return alert('No Question Found')
                }
                this.goto_step2()
                this.dialog = false
              }.bind(this), 500)
          });
        },
        relative_text_list(){
          Vue.axios.get('/admin/question/relative_text_list').then(response => {
              this.original_relative_text = response.data.relative_text_list;
          });
        },
        get_subject(){
           var echelon_id = this.search_echelon
           if (this.search_echelon == 8) {echelon_id = 7}
           var subject = Object.assign([], this.original_subject);
           let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
           this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
           this.chapter_topic_list()
           this.chapter_list()
           // this.question_list()
        },
        get_chapter(){
           var chapter = Object.assign([], this.original_chapter);
           let filter_chapter = chapter.filter(x=>{return x.subject_id == this.search_subject})
           // filter_chapter.sort(x => x.)
           filter_chapter.sort(function(a, b){
              return parseInt(a.chapter_no) - parseInt(b.chapter_no);
           });
           this.chapter = filter_chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
        },
        get_chapter_topic(){
          // push all chapter topic to per_chapter topic
          if (this.step==1) {this.$refs['combobox'].blur()}
          var per_chapter_topic = []
            for (var i = 0; i < this.selected_chapter.length; i++) {
                per_chapter_topic.push([])
                var chapter_topic = Object.assign([], this.original_chapter_topic);
                for (var j = 0; j < this.selected_chapter[i].length; j++) {
                    let filter_chapter_topic = chapter_topic.filter(x=>{return x.chapter_id == this.selected_chapter[i][j].value})
                    var x = filter_chapter_topic.map(arr => { return { topic_no: arr['topic_no'], value: arr['id'], text: arr['name'], val: 0 } })
                    for (var k = 0; k < x.length; k++) {
                       per_chapter_topic[i].push(x[k])
                    }
                }
          }
          this.chapter_topic = per_chapter_topic
          // change val = 1 which chapter are selected before
          for (var i = 0; i < this.showing_question.length; i++) {
            if (this.showing_question[i].topics.length>0) {
              for (var j = 0; j < this.chapter_topic[i].length; j++) {
                if (this.showing_question[i].topics.findIndex(x => x.id == this.chapter_topic[i][j].value)>-1) {
                  this.chapter_topic[i][j].val = 1
                }
              }
            }
          }
        },
        get_relative_text(){
           var relative_text = Object.assign([], this.original_relative_text);
           if (this.dialogItem.chapters.length>0) {
               let filter_relative_text = relative_text.filter(x=>{return x.chapter_id == this.dialogItem.chapters[0].value})
               this.relative_text = filter_relative_text.map(arr => { return { value: arr['id'], text: arr['relative_text'].substring(0, 30) } })
           }
        },
        image_upload(type){
          var files = $('input[name=insert_image]')[0].files
          if (type=='question') {var elem = $('.question_editor .note-editable')}
          if (type=='answer') {var elem = $('.answer_editor .note-editable')}
          for (var i = 0; i < files.length; i++) {
              elem.find('img').each(function(index){
                  var x = $(this).attr('src').split('/').pop()
                  $(this).attr('id', index)
                  // return console.log(i)
                  if (files[i].name == x) {
                      var reader = new FileReader();
                      reader.onload = function(){
                        var dataURL = reader.result;
                        var output = document.getElementById(index);
                        output.src = dataURL;
                      };
                      reader.readAsDataURL(files[i]);
                  }
              })
          }
          $('input[name=insert_image]').val("")
        },
        save(){
          if (this.step == 2) {
              this.refine_text()
              this.loading = true;
              this.dialog = true;
              var short_answer = []
              var question_text = []
              var detail_answer = []
              var relative_text_edit = $('.relative_editor').html()

              $('.short_answer_div').each(function(){
                short_answer.push($(this).html())
              })
              $('.question_editor').each(function(){
                question_text.push($(this).html())
              })
              $('.answer_editor').each(function(){
                detail_answer.push($(this).html())
              })
              var form_data = {question_data: [], chapter_data: [], topic_data: [], relative_text: relative_text_edit}
              for (var i = 0; i < this.showing_question.length; i++) {
                this.showing_question[i].short_answer = short_answer[i]
                this.showing_question[i].question_text = question_text[i]
                this.showing_question[i].detail_answer = detail_answer[i]
                form_data.question_data.push(this.showing_question[i])
                form_data.chapter_data.push(this.selected_chapter[i])
                form_data.topic_data.push(this.chapter_topic[i].filter(x=>x.val==1))
              }
              setTimeout(function(){
                this.change = 0
                Vue.axios.post('/admin/question/question_check', form_data).then(response => {
                    // $('.user_nav').addClass('successful')
                    this.loading = false
                    this.dialog = false;
                    $('.user_nav').addClass('successful')
                    setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 2000)
                }, error => {
                    // $('.user_nav').addClass('denied')
                    this.loading = false
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 2000)
                });
              }.bind(this), 250)
          }
        },
        delete_question(id, index, relative_id){
          var confirmation = confirm("Do you want to delete?")
          if (!confirmation) {return false}
          this.dialog = true
          Vue.axios.post('/admin/question/question_delete/' + id).then(response => {
              this.showing_question.splice(index, 1)
              if (this.showing_question.length==0) {
                  this.question_list_w_relative.splice(this.question_w_relative_index, 1)
              }
              if (!relative_id && this.question_list_w_relative.length>0 && this.question_w_relative_index < this.question_list_w_relative.length) {this.goto_question()}
              if (!relative_id && this.question_list_w_relative.length>0 && this.question_w_relative_index == this.question_list_w_relative.length) {
                this.question_w_relative_index = parseInt(this.question_w_relative_index) - 1 ;
                this.goto_question()
              }
              if (!relative_id && this.question_list_w_relative.length==0) {this.step = 1}
              $('.user_nav').addClass('successful')
              this.dialog = false
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
          }, error => {
              $('.user_nav').addClass('denied')
              this.loading = false
              this.dialog = false
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)
          });
        },
        delete_question_all(){
          var confirmation = confirm("Do you want to delete?")
          if (!confirmation) {return false}
          var form_data = {}
          form_data.echelon_id = this.search_echelon
          form_data.subject_id = this.search_subject
          form_data.question_type = this.search_question_type
          form_data.question_status = this.search_question_status
          form_data.chapter = this.search_chapter
          form_data.id = this.search_id
          Vue.axios.post('/admin/question/question_delete_all', form_data).then(response => {
              $('.user_nav').addClass('successful')
              this.loading = false
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
          }, error => {
              $('.user_nav').addClass('denied')
              this.loading = false
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)
          });
        },
        goto_step2(){
          var questions = Object.assign([], this.original_question_list_data);
          this.question_list_data = questions
          this.question_list_w_relative = questions
          this.question_index = 0
          this.question_w_relative_index = this.cookie_index ?  parseInt(this.$cookies.get("question_index")) : 0
          this.step = 2
          this.goto_question()
        },
        goto_question(){
            var h = $('.short_answer_p').height()
            $('.short_answer_div').height(h)
            $('.step_2_div').hide()
            this.showing_question = []
            var question = this.question_list_w_relative[this.question_w_relative_index]
            this.dialogItem.chapters = question.chapters.map(x => {return {value: x.id, text: x.name}})
            if (question.question_relative_text_id && !this.search_id && question.question_type=='relative') {
              this.showing_question_type = 'relative'
              this.show_relative_question = false
            }
            else {
              this.showing_question_type = 'general';
              this.show_relative_question = true
            }
            this.showing_question.push(question)
            this.selected_chapter = []
            for (var i = 0; i < this.showing_question.length; i++) {
              var x = this.showing_question[i].chapters.map(x => {return {value: x.id, text: x.name}})
              this.selected_chapter.push(x)
            }
            this.get_chapter_topic()
            this.question_input_index = parseInt(this.question_w_relative_index + 1)
            $('.step_2_div').fadeIn('slow')
            this.$cookies.set('echelon_id', this.search_echelon)
            this.$cookies.set('subject_id', this.search_subject)
            this.$cookies.set('question_index', this.question_w_relative_index)
        },
        reset(){
            this.step = 4
            setTimeout(function () { this.step = 2 }.bind(this), 250)
        },
        go_to_last_question(){
            this.search_id = ""
            this.search_question_status = ""
            this.search_question_type = ""
            this.search_tag = ""
            this.search_chapter = ""
            this.search_echelon = parseInt(this.$cookies.get("echelon_id"));
            this.search_subject = parseInt(this.$cookies.get("subject_id"));
            this.cookie_index = true
            this.goto_step2()
        },
        prev(){
          if (this.step==2) {
            if (this.question_input_index > this.question_list_w_relative.length) {this.question_input_index = this.question_list_w_relative.length + 1}
            if (this.question_input_index <= 0) {this.question_input_index = 1}
            this.question_w_relative_index = this.question_input_index - 1
            if(this.question_w_relative_index == 0) {
              if (!this.question_prev_url) {return alert('No Previous Question')}
              this.question_page = this.question_page - 1
              this.question_list()
            }
            else{
              this.question_w_relative_index = parseInt(this.question_w_relative_index) - 1;
              if (this.change==1) {
                this.save()
              }
              this.goto_question()
            }
          }
        },
        next(){
          if (this.step==2) {
            if (this.question_input_index > this.question_list_w_relative.length) {this.question_input_index = this.question_list_w_relative.length - 1}
            this.question_w_relative_index = this.question_input_index - 1
            if(this.question_w_relative_index == this.question_list_w_relative.length-1) {
              if (!this.question_next_url) {return alert("No more questions")};
              this.question_page = this.question_page + 1
              this.question_list()
            }
            else{
              this.question_w_relative_index = parseInt(this.question_w_relative_index) + 1;
              if (this.change==1) {
                this.save()
              }
              this.goto_question()
            }
          }
        },
        go_back(){
          if (this.step == 2) {
            $('.step_1_div').hide()
            setTimeout(function () { this.step = 1; $('.step_1_div').fadeIn('slow'); this.relative_question_type_filter = '' }.bind(this), 100)
            this.question_page = 1
          }
          if (this.step == 3) {
            $('.step_2_div').hide()
            this.step = 2
            this.goto_question()
            $('.step_2_div').fadeIn('slow')

          }
        },
        change_relative_question_type(id, type){
          this.dialog = true
          $('.card').hide()
          Vue.axios.get('/admin/question/question_check_list_for_relative/' + id + '/' + type).then(response => {
              this.showing_question = response.data.question_list;
              for (var i = 0; i < this.showing_question.length; i++) {
                  var x = this.showing_question[i].chapters.map(x => {return {value: x.id, text: x.name}})
                  this.selected_chapter.push(x)
              }
              this.get_chapter_topic()
              this.show_relative_question = true
              setTimeout(function () { this.dialog = false; $('.card').fadeIn('slow') }.bind(this), 1000)
          }, error => {
              var index = this.showing_question.findIndex(x => x.id == id)
              this.showing_question[index].relatives.relative_question_type = error.data.response
          });
        },
        change_chapter(index, question_id){
          var chapter_ids = []
          for (var i = 0; i < this.selected_chapter[index].length; i++) {
            chapter_ids.push(this.selected_chapter[index][i].value)
          }
          var chapter = Object.assign([], this.original_chapter);
          chapter = chapter.filter(x => chapter_ids.includes(x.id))

          var q_i = this.showing_question.findIndex(x => x.id == question_id)
          this.showing_question[q_i].chapters = chapter
          this.change = 1
          this.get_chapter_topic()
        },
        change_topic(index, question_id){
          var topic_ids = []
          var topics = this.chapter_topic[index].filter(x => x.val == 1)
          for (var i = 0; i < topics.length; i++) {
            topic_ids.push(topics[i].value)
          }
          var topic = Object.assign([], this.original_chapter_topic);
          topic = topic.filter(x => topic_ids.includes(x.id))

          var q_i = this.showing_question.findIndex(x => x.id == question_id)
          this.showing_question[q_i].topics = topic
          this.change = 1
        },
        refine_text(){
          var p = $('.note-editable');
            p.contents()
              .filter(function() { return this.nodeType == 3; }) // Select all textnodes
              .wrap('<p>') // Place them inside paragraph elements
            $('br', p).remove();
            $('.note-editable p:empty').remove();
            $('.note-editable style, .short_answer_div style').remove()
            $('o\\:p').remove()
            // symbolise
            $('.note-editable *, .short_answer_div *').each(function(){
                var a = $(this).css('font-family')
                if (a == 'Symbol' || a == 'Math5') {$(this).addClass('symbol')}
            })
            // remove style attribute
            $('.note-editable *, .short_answer_div *').removeAttr("style")
            // unwrap unblock element
            $('.note-editable *:not(p,div,ul,li,sup,sub,table,tbody,tr,td,img,.symbol,.question,.qus-child)').contents().unwrap();
            // unwrap unblock element of short_answer
            $('.short_answer_div *:not(p,div,sup,sub,img,.symbol)').contents().unwrap();
            // replace element into p element
            $('.note-editable *:not(table,tbody,tr,td,sup,sub,img,.symbol,.question,.qus-child)').wrap('<p></p>').contents().unwrap()

            // unwrap element don't have text
            $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child,sup,sub)').each(function(){
                var a = $(this).clone().children().remove().end().text()
                if (a=='') {$(this).contents().unwrap()}
            })
            // unwrap element don't have text of short_answer
            $('.short_answer_div *:not(img,.symbol)').each(function(){
                var a = $(this).clone().children().remove().end().text()
                if (a=='') {$(this).contents().unwrap()}
            })
            // remove empty element
            $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child)').each(function() {
                var $this = $(this);
                if($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                $this.remove();
            });
            // remove empty element of short answer
            $('.short_answer_div *:not(img,.symbol)').each(function() {
                var $this = $(this);
                if($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                    $this.remove();
            });
            // managing table
            $('.note-editable table').wrap('<div></div>')
            $('.note-editable table col').remove()
            // remove extra space
            $(".question_editor .note-editable").html(function (i, html) {
                return html.replace(/&nbsp;/g, ' ');
            });
            // remove comments
            $('.note-editable *').contents().each(function() {
                if(this.nodeType === Node.COMMENT_NODE) {
                    $(this).remove();
                }
            });
        }
     } //end method
   }
</script>

<style>
  .checkbox_flex .v-list__tile {
    height: 30px;
  }
  .checkbox_flex .v-list__tile__action{
    min-width: 20px;
  }
  .checkbox_flex .v-icon {
    font-size: 18px;
  }
  .checkbox_flex .v-input--selection-controls__input{
    margin-right: 0px;
  }
  .checkbox_flex .v-list__tile__title{
    font-size: 12px;
  }
  .checkbox_flex .v-list__tile{
    padding: 0px 6px;
  }
  .step_2_div .v-text-field {
      padding-top: 5px;
      margin-top: 0px;
  }
</style>
