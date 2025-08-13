<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
step 3 for relative, 4 for generate question
 -->

<template>
    <div class="question_setup">
        <v-container fluid>
            <!-- @page_headline -->
            <h3>
                <span class="fs-medium fw-600">Set Up Question</span>
                <span class="fs-14">{{exam_data.subjects.echelons.name}}</span> 
                <span class="fs-14">{{exam_data.subjects.name}}</span>
                <router-link :to="{ name: 'question_makable_list'}" v-if="step < 3 && is_admin()">
                    <v-btn flat color="primary" class="mr-2 mid-btn float-right">
                        <v-icon>keyboard_backspace</v-icon>
                        Back to List
                    </v-btn>
                </router-link>
                <router-link :to="{ name: 'user_question_list'}" v-if="step < 3 && has_role('teacher')">
                    <v-btn flat color="primary" class="mr-2 mid-btn p-l-5 m-t-10">
                        <v-icon class="m-r-5">keyboard_backspace</v-icon>
                        Go Back
                    </v-btn>
                </router-link>
            </h3>
            <div style="float: none; clear: both"></div>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <h4 v-if="step < 3">Question Detail ({{exam_data.sets}} Sets) <span class="float-right fs-14" v-if="step == 2" @click="step = 1">Reset</span></h4>
                                <div v-if="step == 1">
                                    <span class="m-b-10">Chapter: {{exam_data.chapter_text}}, Topic: {{exam_data.topic}}</span>
                                    <hr class="m-b-10 m-t-10">
                                    <h5 class="fw-600">Choose Specific Chapter and Topic</h5 class="fw-600">
                                    <v-combobox v-model="dialogItem.chapters" :items="chapter" hide-selected label="Chapters" :clearable=true multiple persistent-hint small-chips :rules="[v => !!v || 'required']"></v-combobox>
                                    <v-text-field v-model="dialogItem.topic" label="Topic"></v-text-field>
                                    <v-text-field v-model="dialogItem.sets" label="Total Sets" :rules="[v => !!v || 'required']" type="number"></v-text-field>
                                    <div v-for="(question, q_i) in dialogItem.question_data" class="card p-10 m-b-10">
                                        <v-layout>
                                            <v-flex xs6 class="m-r-8">
                                                <v-select v-model="question.question_type" :items="question_type" :rules="[v => !!v || 'required']" label="Question Type*" @change="change_question_type(question.question_type, q_i)"></v-select>
                                            </v-flex>
                                                <v-flex xs2 class="m-r-8" v-if="question.question_type != 'relative'">
                                                <v-text-field v-model="question.total" label="Total" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                            </v-flex>
                                            <v-flex xs3 class="m-r-8" v-if="question.question_type != 'relative'">
                                                <v-text-field v-model="question.answerable" label="Answerable" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                            </v-flex>
                                            <v-flex xs1>
                                                <v-btn outline small fab color="indigo" @click="delete_question_data(q_i)"> <v-icon>close</v-icon></v-btn>
                                                <input style="display: none" v-model="question.serial = q_i + 1">
                                            </v-flex>
                                        </v-layout>
                                    </div>
                                    <v-chip @click="add_question_data" v-if="step == 1">+ Add Another</v-chip>
                                    <hr>
                                    <v-layout>
                                        <v-flex class="pb-0 pt-0" xs12 md2>
                                            <v-btn color="primary" :disabled="!valid || loading" :loading="loading" class="mb-2" @click="save()">Next</v-btn>
                                        </v-flex>
                                    </v-layout>
                                </div>
                                <div v-if="step == 2">
                                    <div v-for="(question, q_i) in dialogItem.question_data" class="card p-10 m-b-10 step-2">
                                        <div class="row">
                                            <div class="col-xs-12">
                                                {{question.question_type.toUpperCase()}} - Total: {{question.total}} <span class="m-l-10" style="color: #009688">Selected: {{question.questions.length}}</span>
                                            </div>
                                        </div>
                                        <div v-if="question.relatives.relative_text">
                                            Relative Text: {{question.relatives.relative_text | strip_tags}}... 
                                            <v-btn small fab @click="change_relative_text(q_i, question.relatives.id)">
                                                <v-icon>edit</v-icon>
                                            </v-btn>
                                        </div>
                                        <div class="m-l-15 relative_div row" v-if="question.question_type == 'relative'" v-for="(relative_item, r_i) in question.relatives.items">
                                            <div class="col-xs-6 p-5">
                                                <v-select v-model="relative_item.relative_question_type":items="question.relatives.relative_types" :rules="[v => !!v || 'required']" label="Relative Type" class="fs-12" @change = "change = 1" required></v-select>
                                            </div>
                                            <div class="col-xs-2 p-5">
                                                <v-text-field v-model="relative_item.total" label="Total" type="number" @input = "change = 1" :rules="[v => !!v || 'required']"></v-text-field>
                                            </div>
                                            <div class="col-xs-2 p-r-5 p-l-5">
                                                <v-btn outline small fab color="indigo" @click="delete_relative_data(q_i, r_i)"> <v-icon>close</v-icon> </v-btn>
                                            </div>
                                            <div class="col-xs-2 p-l-5">
                                                <v-btn outline small fab color="indigo" @click="add_relative_data(q_i)"> <v-icon>add</v-icon> </v-btn>
                                            </div>
                                        </div>
                                        <hr class="m-t-5 m-b-5">
                                        <div class="row">
                                            <div class="col-xs-12 button-section">
                                                <v-btn outline small color="primary" v-if="question.question_type == 'relative' && !question.relatives.id" @click="change_relative_text(q_i)">Select Text</v-btn>
                                                <router-link v-if="question.question_type != 'relative'" :to="{ name: 'question_select', params: { id: $route.params.id, type_id: question.id}}">
                                                    <v-btn outline small color="primary">Select</v-btn>
                                                </router-link>
                                                <v-btn outline small color="primary" @click="save(question.question_type, q_i)" v-if="question.question_type == 'relative'">Select</v-btn>
                                                <!-- <v-btn outline small color="primary" @click="go_generate(question.id, q_i)">Generate</v-btn> -->
                                                <!-- <v-btn outline small color="primary">Custom</v-btn> -->
                                                <v-btn outline small color="primary" @click="type_index=q_i, step = 5">View</v-btn>
                                                <input style="display: none" v-model="question.serial = q_i + 1">
                                            </div>
                                        </div>
                                    </div>
                                    <v-chip @click="add_question_data" v-if="step == 1">+ Add Another</v-chip>
                                    <div v-if="is_admin()">
                                        <v-chip @click="generate_full">Generate Full Question</v-chip>
                                    </div>
                                    <div class="m-t-10" v-if="!this.dialogItem.not_finish">
                                        <v-btn color="primary" :disabled="loading" :loading="loading" class="mb-2" @click="finish()">Finish</v-btn>
                                    </div>
                                </div>
                                <div v-if="step == 3">
                                    <h4>
                                        Select Relative Text 
                                        <v-icon class="m-l-15 float-right pos-rel" style="top: 5px" @click="step=2">close</v-icon>
                                        <v-btn small color="success" class="float-right mid-btn" @click="done_step_3">Done</v-btn>
                                    </h4>
                                    <hr>
                                    <v-select v-model="select_relative_chapter" :items="chapter" label="Chapters" @change="relative_text_by_chapter"></v-select>
                                    <v-select v-model="select_relative_text":items="relative_text"label="Relative Text"@change="show_relative_text(select_relative_text)"></v-select>
                                    <div v-html="show_relative" class="text-justify"></div>
                                </div>
                                <div v-if="step == 4">
                                    <h4>Generate Questions</h4>
                                    <hr>
                                    <div class="card m-b-10">
                                        <div class="container">
                                            <h5>How many questions you want to use for each chapter?</h5>
                                            <hr class="m-t-5 m-b-5">
                                            <div v-if="!random_chapter" class="row" v-for="(chapter, c_i) in generate_data.chapters">
                                                <div class="col-xs-5">
                                                    <v-select v-model="chapter.value":items="dialogItem.chapters"label="Chapter"@change="change=1"></v-select>
                                                </div>
                                                <div class="col-xs-3">
                                                    <v-text-field v-model="chapter.question_quantity"label="Quantity"type="number":rules="[v => !!v || 'required']"@input="change=1"></v-text-field>
                                                </div>
                                                <div class="col-xs-2 col-lg-1">
                                                    <v-btn outline small fab color="indigo" @click="delete_chapter_to_generate(c_i)">
                                                        <v-icon>close</v-icon>
                                                    </v-btn>
                                                </div>
                                                <div class="col-xs-2 col-lg-1">
                                                    <v-btn outline small fab color="indigo" @click="add_chapter_to_generate()">
                                                        <v-icon>add</v-icon>
                                                    </v-btn>
                                                </div>
                                            </div>
                                            <label class="checkbox-inline"><input type="checkbox" v-model="random_chapter" value="" @change="set_random_chapter">Choose Chapter's Question Quantity Amount Sets Randomly</label>
                                        </div>
                                    </div>
                                    <div class="card m-b-10" v-if="chapter_topics.length>0">
                                        <div class="container">
                                            <h5>Which topics you want to use?</h5>
                                            <hr class="m-t-5 m-b-5">
                                            <ul class="checkbox_flex p-0 list-inline">
                                                <li @click="" v-for="(topic, t_i) in chapter_topics">
                                                    <label class="checkbox-inline"><input type="checkbox" v-model="topic.val" value="">{{topic.topic_no}}-{{topic.name}}</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="container">
                                            <h5>Do you want any tags?</h5>
                                            <hr class="m-t-5 m-b-5">
                                            <ul class="checkbox_flex p-0 list-inline">
                                                <li @click="">
                                                    <label class="checkbox-inline"><input type="radio" @change="change=1" class="m-r-5 pos-rel" style="top:1px" v-model="generate_data.question_tag" value="board">Only Board</label>
                                                    <label class="checkbox-inline"><input type="radio" @change="change=1" class="m-r-5 pos-rel" style="top:1px" v-model="generate_data.question_tag" value="school">Only School</label>
                                                    <label class="checkbox-inline"><input type="radio" @change="change=1" class="m-r-5 pos-rel" style="top:1px" v-model="generate_data.question_tag" value="board_school">Board & School</label>
                                                    <label class="checkbox-inline"><input type="radio" @change="change=1" class="m-r-5 pos-rel" style="top:1px" v-model="generate_data.question_tag" value="none">None</label>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <hr>
                                    <v-btn small color="success" @click="generate_question">Generate</v-btn>
                                </div>
                                <div v-if="step == 5">
                                    <!-- step 5 is for view questions -->
                                    <h4 class="m-t-0">
                                        View Questions 
                                        <v-icon class="m-l-15" @click="step=2">close</v-icon>
                                    </h4>
                                    <hr>
                                    <div class="table-responsive">
                                        <table class="table solaiman">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Chapter</th>
                                                    <th>Set</th>
                                                    <th>Text</th>
                                                </tr>
                                            </thead>
                                            <tbody class="view_question_no_space">
                                                <tr v-for="(question, q_i) in this.exam_data.exam_question_types[type_index].questions">
                                                    <td class="fs-12">{{question.id}}</td>
                                                    <td class="fs-12"><span class="m-r-5" v-for="(chapter, c_i) in question.chapters">{{chapter.name}}</span></td>
                                                    <td class="fs-12">{{question_sets[question.pivot.set - 1]}}</td>
                                                    <td class="pos-rel p-r-30"><span class="pos-a fs-11" style="right: 5px; top: 10px">({{question.relative_question_type}})</span><span style="hidden; display: block; font-size: 12px; padding-bottom: 10px; line-height: 1.5" v-html="question.question_text"></span></td>
                                                    <td><span class="text-success">{{question.short_answer}}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div v-html="show_relative" class="text-justify"></div>
                                </div>
                            </v-container>
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
    </div>
</template>
<script>
   export default {
     data() {
             return {
               exam_data: {subjects: {echelons: {'name': ''}}},
               chapter: [],
               original_chapter: [],
               question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}, {value: 'partial', text: 'Partial'}],
               chapter_topics: [],
               dialogItem: {
                 id: '',
                 chapters: '',
                 topic: '',
                 batches: {'echelons': {'name': ''}},
                 question_data: [{'question_type': '', 'total': '', 'answerable': '', 'serial': '', 'relatives': {}, 'relative_chapters': {}}],
                 sets: ''
               }, 
               valid: true,
               success_alert: false,
               error_alert: false,
               loading: false,
               step: 0,
               select_relative_text: '', 
               relative_text: [],
               original_relative_text: [],
               show_relative: '',
               select_relative_chapter: '',
               question_index: '',
               relative_index: '',
               dialog: false,
               change: 0,
               generate_data: {question_tag:'none', chapters:[{value: '', question_quantity: 0}]},
               random_chapter: false,
               generate_type_index: '',
               question_sets: ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P'],
               type_index: ''

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        if (window_user_role == 'teacher' && window_authorise != 'authorised') {return next('/authentication')}
        let p = window_admin_permissions.findIndex(x => x.name=='question_check');
        let r = window_user_role ? window_user_role.findIndex(x => x == 'teacher') : -1
        if (p > -1 || r > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.schedule_list();
     },

     filters: {
        strip_tags: function (value) {
          let regex = /(<([^>]+)>)/ig;
          return value.replace(regex, "").substring(0, 50) + '...';
        }
     },
   
     methods:{
       // @add_item_method
       // this.$route.params.id
       schedule_list(){
         this.dialog = true
         Vue.axios.get('/admin/question/question_makable_list/' + this.$route.params.id).then(response => {
             this.exam_data = response.data.schedule_list;
             this.chapter_list()
             if (this.exam_data.exam_question_types && this.exam_data.exam_question_types.length>0) {
                this.step = 2; 
                var a = this.exam_data.exam_question_types.map(x => {
                    return {
                        id: x.id,
                        question_type: x.question_type,
                        total: x.total,
                        answerable: x.answerable,
                        questions: x.questions,
                        chapters: x.chapters,
                        relatives: {
                            id: x.relatives ? x.relatives.id : '',
                            relative_text: x.relatives ? x.relatives.relative_text : '',
                            relative_types: x.relatives ? x.relatives.relative_question_type.split(',').map(y => {
                                return {
                                    value: y,
                                    text: y.toUpperCase()
                                }
                            }) : '',
                            items: x.relative_type_data ? x.relative_type_data.map(y => {
                                return {
                                    relative_question_type: y.relative_question_type,
                                    total: y.total
                                }
                            }) :[{
                                relative_question_type: '',
                                total: ''
                            }]
                        },
                        relative_chapters: {
                            id: x.relative_chapters ? x.relative_chapters.id : ''
                        },
                    }
                })
                this.dialogItem.question_data = a
                for (var i = 0; i < a.length; i++) {
                    if (a[i].questions.length >= a[i].total*this.exam_data.sets) {
                        this.dialogItem.not_finish = false
                    } else {
                        this.dialogItem.not_finish = true
                    }
                 }
             }
             else {this.step = 1}

             this.dialogItem.id = this.exam_data.id
             this.dialogItem.topic = this.exam_data.topic
             this.dialogItem.sets = this.exam_data.sets
             this.dialog = false
                
         });
       },
       chapter_list(){
         Vue.axios.get('/admin/request/chapter_list_by_class/'+ this.exam_data.subjects.echelon_id).then(response => {
             this.original_chapter = response.data.chapter_list_by_class;
             this.get_chapter()
         }); 
       },
       relative_text_by_chapter(chapter_id){
          this.dialog = true
          Vue.axios.post('/admin/question/relative_text_by_chapter/' +  this.select_relative_chapter).then(response => {
              this.original_relative_text = response.data.relative_text_by_chapter;
              this.relative_text = this.original_relative_text.map(arr => { return { value: arr['id'], text: arr['relative_text'].substring(0, 50) } })
              this.select_relative_text = this.dialogItem.question_data[this.question_index].relatives.id
              if (this.select_relative_text) {
                  this.show_relative_text(this.select_relative_text)
              }
              this.dialog = false
          });
       },
       get_chapter(){
          var chapter = Object.assign([], this.original_chapter);
          let filter_chapter = chapter.filter(x=>{return x.subject_id == this.exam_data.subject_id})
          this.chapter = filter_chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
          var chapter_array = []
          this.dialogItem.chapters = this.exam_data.chapters.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
          this.get_chapter_topics();
          for (var i = 0; i < this.dialogItem.question_data.length; i++) {
            this.dialogItem.question_data[i].chapters = this.chapter.filter(x=> this.dialogItem.question_data[i].chapters && this.dialogItem.question_data[i].chapters.includes(x.value))
          }
       },
       get_chapter_topics(){
          for (var i = 0; i < this.dialogItem.chapters.length; i++) {
              var index = this.original_chapter.findIndex(x => x.id == this.dialogItem.chapters[i].value)
              this.chapter_topics = this.original_chapter[index].topics.map(x => {return {id: x.id, topic_no: x.topic_no, name: x.name, val: false} })
          }
       },
       show_relative_text(relative_id){
          var index = this.original_relative_text.findIndex(x => x.id == relative_id)
          if (index > -1) {
            this.show_relative = this.original_relative_text[index].relative_text
          }
       },
       change_question_type(type, index){
          if (type == 'relative') {this.dialogItem.question_data[index].relatives.items.push({'relative_question_type': '', 'total': ''})}
       },
       change_relative_text(q_i, relative_id){
          this.question_index = q_i
          if (relative_id) {
              this.select_relative_chapter = this.dialogItem.question_data[q_i].relative_chapters.id
              this.relative_text_by_chapter()
          }
          this.step = 3
       },
       done_step_3(){
          this.dialog = true
          this.dialogItem.question_data[this.question_index].relatives.id = this.select_relative_text
          this.dialogItem.question_data[this.question_index].relative_chapters.id = this.select_relative_chapter
          var item = {question_data:[]}
          item.question_data.push(this.dialogItem.question_data[this.question_index])
          item.id = this.dialogItem.id
          Vue.axios.post('/admin/question/question_type_setup', item).then(response => {
              $('.user_nav').addClass('successful')
              this.schedule_list()
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
              this.dialog = false
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.dialog = false  
          });
          this.step = 2
       },
       save(question_type, q_i){
            if (!this.$refs.form.validate()) {return false}
            var item
            if (this.step == 2 && question_type == 'relative') {
                if (this.change == 0) {
                  var question_type_id = this.dialogItem.question_data[q_i].id
                  this.$router.push({ name: 'question_select', params: { id: this.$route.params.id, type_id: question_type_id}})
                  return false
                }
                item = {question_data:[]}
                item.question_data.push(this.dialogItem.question_data[q_i])
            } else {
              item = this.dialogItem
            }
            if (item.chapters.length > 0) {
                item.chapter = item.chapters.map(x => { return x.value})
            }
            item.exam_lists = this.exam_data.exam_lists
            this.dialog = true
            if (this.step<4) {this.question_type_save(item, question_type, q_i)}
            if (this.step==4) {this.generate_question()}
       },
       question_type_save(item, question_type, q_i){
          Vue.axios.post('/admin/question/question_type_setup', item).then(response => {
                $('.user_nav').addClass('successful')
                if (this.step == 1) {this.schedule_list()}
                if (question_type == 'relative') {
                  var question_type_id = this.dialogItem.question_data[q_i].id
                  this.$router.push({ name: 'question_select', params: { id: this.$route.params.id, type_id: question_type_id}})
                }
                this.step = 2
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                this.dialog = false
          }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                this.dialog = false 
          });
       },
       go_generate(id, q_i){
          this.generate_type_index = q_i
          this.generate_data.exam_question_type_id = id
          this.generate_data.total_question = this.dialogItem.question_data[q_i].total
          this.random_chapter = false
          this.step = 4;
          this.generate_data.generate_type = 'single';
          this.generate_data.type_data = [{'id': id, 'total': this.generate_data.total_question}]          
       },
       generate_full(){
          var confirmation = confirm("Are you sure to generate full question?")
          if (confirmation) {
              this.generate_data.generate_type = 'full';
              this.generate_data.type_data = this.dialogItem.question_data.map(arr => { return { id: arr['id'], total: arr['total'] } })
              this.random_chapter = true
              this.generate_question()
          }
       },
       generate_question(){
          this.generate_data.sets = this.exam_data.sets
          this.generate_data.random_chapter = this.random_chapter
          this.dialog = true
          if (this.random_chapter) {
            this.generate_data.chapters = this.dialogItem.question_data[this.generate_type_index].chapters
            if (this.generate_data.chapters.length==0) {this.generate_data.chapters = this.dialogItem.chapters}
          }
          Vue.axios.post('/admin/question/generate_question', this.generate_data).then(response => {
                $('.user_nav').addClass('successful')
                this.schedule_list()
                this.step = 2
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                this.dialog = false
          }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                this.dialog = false
                if (error.response.data=='no_data') {return alert('No question found for this criteria')} 
          });
       },
       set_random_chapter(){
          this.change = 1
          if (!this.random_chapter) {
            this.generate_data.chapters = []
            this.generate_data.chapters.push({'value': '', 'question_quantity': 0})
          }
       },
       add_question_data(){
          var x = {'question_type': '', 'total': '', 'answerable': '', 'relatives': {'id': '', 'relative_text': '', 'items': [{'relative_question_type': '', 'total': ''}]}}
          this.dialogItem.question_data.push(x)
       },
       add_relative_data(q_i){
          this.dialogItem.question_data[q_i].relatives.items.push({'relative_question_type': '', 'total': ''})
          this.change = 1;
       },
       delete_question_data(index){
          this.dialogItem.question_data.splice(index, 1)
       },
       delete_relative_data(q_i, r_i){
          this.dialogItem.question_data[q_i].relatives.items.splice(r_i, 1)
          this.change = 1;
       },
       add_chapter_to_generate(c_i){
          this.generate_data.chapters.push({'value': '', 'question_quantity': 0})
          
       },
       delete_chapter_to_generate(c_i){
          this.generate_data.chapters.splice(c_i, 1)
       },
       // @add_item_method_close_dialog
       clear(){
         this.search = ''
         this.search_filter = ''
         this.search_filter1 = ''
       },
       finish() {
            this.dialog = true
            Vue.axios.post('/admin/question/finish_make_question/' + this.dialogItem.id).then(response => {
                $('.user_nav').addClass('successful')
                if (window_user_role) {
                    this.$router.push({ name: 'user_question_list'})
                }
                if (window_admin_role) {
                    this.$router.push({ name: 'question_makable_list'})
                }
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                this.dialog = false
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                this.dialog = false
                if (error.response.data=='no_data') {return alert('No question found for this criteria')} 
            });
       }
     }
   }
</script>


<style>
  .v-btn--floating.v-btn--small{
    height: 22px;
    width: 22px;
  }
  .v-chip .v-chip__content{
    cursor: pointer;
  }
  .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .v-input{
    font-size: 14px;
  }
  .v-btn{
    height: 32px;
  }
  .relative_div label{
    font-size: 12px;
  }
  .relative_div .v-input{
    font-size: 12px;
  }
</style>