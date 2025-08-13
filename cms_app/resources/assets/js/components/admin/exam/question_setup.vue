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
            <h3 class="headline">
                Set Up Question 
                <span class="fs-14">{{exam_data.echelons.name}}</span> 
                <span class="fs-14">{{exam_data.subjects.name}}</span>
                <router-link :to="{ name: 'online_exam'}" v-if="step < 3 && is_admin()">
                    <v-btn flat color="primary" class="mr-2 mid-btn float-right">
                        <v-icon>keyboard_backspace</v-icon>
                        Back to List
                    </v-btn>
                </router-link>
            </h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <h4 v-if="step < 3">Question Detail <span class="float-right fs-14" v-if="step == 2" @click="step = 1">Reset</span></h4>
                                <div v-if="step == 1">
                                    <v-text-field v-model="dialogItem.chapter" label="Chapters" :clearable=true :rules="[v => !!v || 'Chapter is required']"></v-text-field>
                                    <v-text-field v-model="dialogItem.topic" label="Topic" :rules="[v => !!v || 'Topic is required']"></v-text-field>
                                    <div v-for="(question, q_i) in dialogItem.question_data" class="card p-10 m-b-10">
                                        <v-layout>
                                            <v-flex xs6 class="m-r-8">
                                                <v-select v-model="question.question_type" :items="question_type" :rules="[v => !!v || 'required']" label="Question Type*" @change="change_question_type(question.question_type, q_i)"></v-select>
                                            </v-flex>
                                                <v-flex xs2 class="m-r-8">
                                                <v-text-field v-model="question.total" label="Total" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                            </v-flex>
                                            <v-flex xs3 class="m-r-8">
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
                                        <hr class="m-t-5 m-b-5">
                                        <div class="row">
                                            <div class="col-xs-12 button-section">
                                                <router-link :to="{ name: 'exam_question_add', params: { id: question.id }}">
                                                    <v-btn outline small color="primary">Create</v-btn>
                                                </router-link>
                                                <v-btn outline small color="primary" @click="type_index=q_i, step = 5">View</v-btn>
                                                <input style="display: none" v-model="question.serial = q_i + 1">
                                            </div>
                                        </div>
                                    </div>
                                    <v-chip @click="add_question_data" v-if="step == 1">+ Add Another</v-chip>
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
                                                    <th></th>
                                                    <th>#</th>
                                                    <th>Chapter</th>
                                                    <th>Set</th>
                                                    <th>Text</th>
                                                </tr>
                                            </thead>
                                            <tbody class="view_question_no_space">
                                                <tr v-for="(question, q_i) in this.exam_data.exam_question_types[type_index].questions">
                                                    <td class="fs-12"><v-btn class="tiny-btn error">delete</v-btn></td>
                                                    <td class="fs-12">{{question.id}}</td>
                                                    <td class="fs-12"><span class="m-r-5" v-for="(chapter, c_i) in question.chapters">{{chapter.name}}</span></td>
                                                    <td class="fs-12">{{question_sets[question.pivot.set - 1]}}</td>
                                                    <td class="pos-rel p-r-30"><span style="hidden; display: block; font-size: 12px; padding-bottom: 10px; line-height: 1.5" v-html="question.question_text"></span></td>
                                                    <td><span class="text-success">{{question.short_answer}}</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
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
               exam_data: {echelons: {}, subjects: {}},
               chapter: [],
               original_chapter: [],
               question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'custom', text: 'Custom'}],
               chapter_topics: [],
               dialogItem: {
                 id: '',
                 chapters: '',
                 topic: '',
                 question_data: [{'question_type': '', 'total': '', 'answerable': '', 'serial': '', 'relatives': {}, 'relative_chapters': {}}]
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
         this.axios.get('/admin/question/question_makable_list/' + this.$route.params.id).then(response => {
             this.exam_data = response.data.schedule_list;
             if (this.exam_data.exam_question_types.length>0) {
                this.step = 2; 
                var a = this.exam_data.exam_question_types.map(x => {
                    return {
                        id: x.id,
                        question_type: x.question_type,
                        total: x.total,
                        answerable: x.answerable,
                        questions: x.questions,
                        chapters: x.chapters,
                    }
                })
                this.dialogItem.question_data = a
             }
             else {this.step = 1}
             this.dialogItem.id = this.exam_data.id
             this.dialogItem.topic = this.exam_data.topic
             this.dialog = false
         });
       },
       change_question_type(type, index){
          if (type == 'relative') {this.dialogItem.question_data[index].relatives.items.push({'relative_question_type': '', 'total': ''})}
       },
       save(question_type, q_i){
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
            this.dialog = true
            if (this.step<4) {this.question_type_save(item, question_type, q_i)}
            if (this.step==4) {this.generate_question()}
       },
       question_type_save(item, question_type, q_i){
          this.axios.post('/admin/question/question_type_setup', item).then(response => {
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
       // @add_item_method_close_dialog
       clear(){
         this.search = ''
         this.search_filter = ''
         this.search_filter1 = ''
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