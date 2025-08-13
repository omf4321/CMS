<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
   <div>
      <v-container fluid>
         <!-- @page_headline -->
         <h3 class="headline m-t-0">Update Topic</h3>
         <p>{{schedule_data.echelons.name}} - {{schedule_data.batches ? schedule_data.batches.name : ''}} {{schedule_data.date_from}} to {{schedule_data.date_to}}</p>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
               <template>
                  <v-form ref="form" v-model="valid" lazy-validation>
                    <v-container>
                      <v-layout>
                        <v-flex lg6>
                          <v-select
                             v-model="course_id"
                             :items="course"
                             :rules="[v => !!v || 'Course is required']"
                             label="Course"
                          ></v-select>
                        </v-flex>
                        <v-flex lg2>
                          <v-btn color="success" :disabled="!valid || loading" :loading="loading" class="" @click="update_schedule()">Update</v-btn>
                        </v-flex>
                      </v-layout>
                      <hr class="m-t-10 m-b-10" style="border-top: 2px solid #607D8B">
                      <div v-for="(sub, s_i) in selected_subject">
                        <v-layout class="pos-rel">
                          <v-flex lg3 class="mr-3">
                            <v-select
                               v-model="sub.subject_id"
                               :items="subject"
                               :rules="[v => !!v || 'Subject is required']"
                               @change="get_chapter(s_i)"
                               required
                            ></v-select>
                          </v-flex>
                          <v-flex lg2 class="mr-3">
                            <v-checkbox v-model="sub.include_chapter" label="Include Chapters"></v-checkbox>
                          </v-flex>
                          <v-flex lg3 v-if="sub.include_chapter" class="mr-3 chapter_break">
                            <v-select
                               v-model="sub.break_chapter_id"
                               :items="chapter[s_i]"
                               label="Break Chapter"
                               :clearable = true
                               required
                            ></v-select>
                          </v-flex>
                          <v-flex lg1 v-if="sub.include_chapter" class="chapter_break">
                            <v-text-field
                               v-model="sub.break_chapter_complete"
                            ></v-text-field>
                          </v-flex>
                          <v-btn style="right: 5px; top: 0px; position: absolute" outline small fab color="indigo" @click="delete_subject(s_i)"><v-icon>close</v-icon></v-btn>
                        </v-layout>
                        <div v-if="sub.include_chapter">
                          <v-list class="m-l-30 chapter_list">
                            <v-layout row wrap>
                                <div v-for="(chap, c_i) in sub.chapters">
                                    <v-list-item style="width: 250px;">
                                        <v-list-item-action>
                                            <v-checkbox v-model="chap.custom"></v-checkbox>
                                        </v-list-item-action>
                                        <v-list-item-content >
                                            <v-select
                                               v-if="!chap.custom"
                                               v-model="chap.chapter_id"
                                               :items="chapter[s_i]"
                                               :rules="[v => !!v || 'Chapter is required']"
                                               required
                                            ></v-select>
                                            <v-text-field
                                               v-if="chap.custom"
                                               v-model="chap.custom_text"
                                               :rules="[v => !!v || 'Text is required']"
                                               label="Custom Text"
                                            ></v-text-field>
                                        </v-list-item-content>
                                        <v-btn outline small fab color="indigo" @click="delete_chapter(s_i, c_i)">
                                            <v-icon>close</v-icon>
                                        </v-btn>
                                    </v-list-item>
                                </div>
                            </v-layout>
                          </v-list>
                          <v-chip class="m-l-30 cur-p" small @click="add_chapter(s_i)">+ Add Chapter</v-chip>
                        </div>
                        <hr class="m-t-5 m-b-10" style="border-top: 2px solid #607D8B">
                      </div>
                      <v-btn color="primary" @click="add_subject">+ Add Subject</v-btn>
                      <v-layout>
                        <v-flex lg12 v-if="exist">
                          <div class="red--text">Fail to Generate!! A schedule already exist within your selected dates and criteria. <router-link :to="{name:'schedule_list'}">view schedule</router-link></div>
                        </v-flex>
                        <v-flex lg12 v-if="created">
                          <div class="green--text subheading">A schedule is created. <router-link :to="{name:'schedule_list'}">view schedule</router-link></div>
                        </v-flex>
                      </v-layout>
                    </v-container>
                  </v-form>
                </template>
            </div>
         </template>
      </v-container>
   </div>
</template>
<script>
   export default {
     data() {
             return {
               original_subject: [],
               subject: [],
               selected_subject: [],
               chapter: [],
               original_chapter: [],
               course: [],
               branch: [],
               // @add_item_field_data =====start
               dialog: false,
               id: '',
               name: '',
               branch_id: '',
               echelon_id: '',
               course_id: '',
               date_from: '', 
               date_to: '',
               vacation_range: [],
               editedItem: '',     
               rules: {
                 required: value => !!value || 'Required.'
               },
               
               // @accesories_data
               valid: true,
               success_alert: false,
               error_alert: false,
               loading: false,
               data_load: false,
               created: false,
               exist: false,
               schedule_data: '',
               schedule_content: []

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='schedule');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
   
     watch: {
       dialog (val) {
         val || this.close()
       }
     },
   
     methods:{
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.branch_id = this.branch[0].id
               this.admin_course_list()
               this.schedule_list_by_id();
           }
       },
       // @add_item_method
       schedule_list_by_id(){
         this.axios.get('/admin/schedule/schedule_list_by_id/' + this.$route.params.id).then(response => {
             this.schedule_data = response.data.schedule_list_by_id
             this.schedule_content = response.data.schedule_list_by_id.schedule_contents
             this.echelon_id = response.data.schedule_list_by_id.echelon_id
             this.chapter_list()
         }); 
       },
       admin_course_list(){
         this.axios.get('/admin/request/course_list_by_branch/'+this.branch_id).then(response => {
             this.original_course = response.data.course_list_by_branch;
             this.course = response.data.course_list_by_branch;
             this.course = this.course.map(arr => { return { value: arr['id'], text: arr['name'] } })
         }); 
       },
       // @list_method
       admin_subject_list(){
         this.axios.get('/admin/request/subject_list_by_branch/'+this.branch_id).then(response => {
             this.original_subject = response.data.subject_list_by_branch;
             this.get_subject()
         });
       },
       chapter_list(){
         this.axios.get('/admin/schedule/chapter_list_by_class/'+this.echelon_id).then(response => {
             this.original_chapter = response.data.chapter_list_by_class;
             var x = this.chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
             this.chapter.push(x)
             this.admin_subject_list()
         }); 
       },
       get_subject(){
          var echelon_id = this.echelon_id
          if (this.echelon_id == 8) {echelon_id = 7}
          var subject = Object.assign([], this.original_subject);
          let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
          this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
          if (this.schedule_content.length>0) {
            this.course_id = this.schedule_content[0].course_id
            var subject_id = null
            for (var i = 0; i < this.schedule_content.length; i++) {
              if (this.schedule_content[i].subject_id != subject_id){
                var x = {'subject_id': this.schedule_content[i].subject_id, 'include_chapter': this.schedule_content[i].include_chapter, 'break_chapter_id': this.schedule_content[i].break_chapter_id, 'break_chapter_complete': this.schedule_content[i].break_chapter_complete, chapters: []}
                this.selected_subject.push(x)
                subject_id = this.schedule_content[i].subject_id
                this.get_chapter(this.selected_subject.length - 1)
              }
              var x = {'chapter_id': this.schedule_content[i].chapter_id, 'custom': this.schedule_content[i].custom_text ? true : false, 'custom_text': this.schedule_content[i].custom_text}
              this.selected_subject[this.selected_subject.length-1].chapters.push(x)
            }
          }
          else{
            var x = {'subject_id': '', 'include_chapter': true, 'break_chapter_id': '', 'break_chapter_complete': '', chapters: [{'chapter_id': '', 'custom': false, 'custom_text': ''}]}
            this.selected_subject.push(x)       
          }
       },
       get_chapter(i){
          var chapter = Object.assign([], this.original_chapter);
          let filter_chapter = chapter.filter(x=>{return x.subject_id == this.selected_subject[i].subject_id})
          filter_chapter = filter_chapter.sort((a, b) => {
            return parseFloat(a.chapter_no) - parseFloat(b.chapter_no)
          })
          var x = filter_chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
          if (this.chapter.length > i) {this.chapter[i] = x}
          else {this.chapter.push(x)}
       },
       update_schedule(){
            if (this.$refs.form.validate()) {
              var item = {'subject_data': this.selected_subject, 'course_id': this.course_id, 'schedule_id': this.schedule_data.id}
              this.loading = true
              this.axios.post('/admin/schedule/update_schedule', item).then(response => {
                  $('.user_nav').addClass('successful')
                  this.loading = false
                  setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
              }, error => {
                  this.loading = false
                  $('.user_nav').addClass('denied')
                  setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
              });
            }
       },
       add_chapter(i){
          var x = {'chapter_id': '', 'custom': false, 'custom_text': ''}
          this.selected_subject[i].chapters.push(x)
       },
       delete_chapter(i, j){
          this.selected_subject[i].chapters.splice(j, 1)
       },
       add_subject(){
          var x = {'subject_id': '', 'include_chapter': true, chapters: [{'chapter_id': '', 'custom': false, 'custom_text': '', 'break_chapter_id': '', 'break_chapter_complete': ''}]}   
          this.selected_subject.push(x)
       },
       delete_subject(i){
          this.selected_subject.splice(i, 1)
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
    height: 30px;
    width: 30px;
  }
  .chapter_list .v-btn--floating.v-btn--small{
    height: 22px;
    width: 22px;
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
  .v-chip .v-chip__content{
    cursor: pointer;
  }
  .v-list__tile__action{
    min-width: 36px;
  }
    .v-list__tile__title{
    font-size: 14px;
  }
  .v-input--selection-controls.v-input--checkbox .v-label {
    font-size: 13px;
    top: 3px;
  }
  .chapter_list .v-select__selection--comma, .chapter_list input, .chapter_list label{
    font-size: 12px!important;
  }
  .chapter_break .v-select__selection--comma, .chapter_break input, .chapter_break label{
    font-size: 12px!important;
  }
</style>