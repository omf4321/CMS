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
        <v-container fluid="">
            <!-- @page_headline -->
            <h3 class="headline m-t-0">
                Schedule Planner
            </h3>
            <v-divider class="my-3">
            </v-divider>
            <template>
                <div>
                    <v-toolbar color="grey lighten-2 pb-1" flat="">
                        <v-text-field append-icon="search" hide-details="" label="Search" single-line="" v-model="search">
                        </v-text-field>
                        <v-text-field append-icon="search" class="ml-5" hide-details="" label="Search" single-line="" v-model="search_filter">
                        </v-text-field>
                        <v-icon @click="clear" class="ml-5">backspace</v-icon>
                        <!-- @add_item_dialog -->
                    </v-toolbar>
                    <template>
                        <v-form lazy-validation="" ref="form" v-model="valid">
                            <v-container>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-autocomplete :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" @change="get_item" label="Branch" required="" v-model="dialogItem.branch_id">
                                        </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-select :items="echelon" :rules="[v => !!v || 'Class is required']" @change="dialogItem.course_id = '', get_subject()" label="Class" required="" v-model="dialogItem.echelon_id">
                                        </v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-autocomplete :rules="[v => !!v || 'Course is required']" :items="course" label="Course" v-model="dialogItem.course_id">
                                        </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-autocomplete :items="subject" :rules="[v => !!v || 'Subject is required']" @change="get_chapter_and_planner" label="Subject" v-model="dialogItem.subject_id">
                                        </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md3="" xs12="">
                                        <template>
                                            <v-autocomplete :clearable="true" :items="chapter" @change="change_class_no" label="Chapter" v-model="dialogItem.chapter_id">
                                            </v-autocomplete>
                                        </template>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md1="" xs12="">
                                        <v-icon @click="prev_chapter" class="ml-1" medium="">keyboard_arrow_left</v-icon>
                                        <v-icon @click="next_chapter" class="ml-1" medium="">keyboard_arrow_right</v-icon>
                                    </v-flex>
                                </v-layout>
                                <v-layout class="planner-topic">
                                    <v-flex class="pb-0 pt-0" md6="" xs12="">
                                        <v-textarea v-if="!swich" :auto-grow="true" :clearable="true" :rules="[v => !!v || 'Topic is required']" id="edit_topic" label="Topic" required="" rows="1" v-model="dialogItem.topic">
                                        </v-textarea>
                                        <v-text-field v-if="swich" :auto-grow="true" :clearable="true" :rules="[v => !!v || 'Topic is required']" id="edit_topic" label="Topic" @keyup.enter="save()" v-model="dialogItem.topic">
                                        </v-text-field>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-text-field :rules="[v => !!v || 'Class No is required']" @keyup.enter="swich ? editedIndex : editedIndex=-1, save()" id="edit_class_no" label="Class No" type="number" v-model="dialogItem.class_no">
                                        </v-text-field>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-btn :disabled="!valid || loading" :loading="loading" @click="editedIndex=-1, save()" class="mb-2" color="primary" v-if="!planner_before && !swich">
                                            + Add
                                        </v-btn>
                                        <v-btn :disabled="!valid || loading" :loading="loading" @click="planner_add_before" class="mb-2" color="info" v-if="planner_before">
                                            Topic Add Before
                                        </v-btn>
                                        <v-btn :disabled="!valid || loading" :loading="loading" @click="save()" class="mb-2" color="cyan" v-if="swich">
                                            <v-icon class="mr-1" small="">edit</v-icon>
                                            Edit
                                        </v-btn>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="">
                                        <v-switch :label="swich?'Serial Edit':'Add'" @change="planner_list" class="m-t-10" v-if="!planner_before" v-model="swich">
                                        </v-switch>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </v-form>
                    </template>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="itemsSorted" :loading="data_load" :pagination.sync="pagination" :search="search" class="elevation-1" hide-actions="">
                        <v-progress-linear color="blue" indeterminate="" slot="progress">
                        </v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">
                                {{ props.item.subjects.echelons.name }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.courses.name }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.subjects.name }}
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog @open="editItem(props.item, 'chapter')" @save="save" lazy="" v-if="props.item.chapter_id">
                                    {{ props.item.chapters.chapter_no }} - {{ props.item.chapters.name }}
                                    <template v-slot:input="">
                                        <v-autocomplete :disabled="swich" :items="chapter" label="Edit Chapter" required="" v-model="editedItem.chapter_id">
                                        </v-autocomplete>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.topic" @open="editItem(props.item, 'topic')" @save="save" lazy="">
                                    {{ props.item.topic }}
                                    <template v-slot:input="">
                                        <v-text-field :disabled="swich" :clearable="true" label="Edit Topic" single-line="" v-model="editedItem.topic">
                                        </v-text-field>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.class_no" @open="editItem(props.item, 'class_no')" @save="save" lazy="">
                                    {{ props.item.class_no }}
                                    <template v-slot:input="">
                                        <v-text-field label="Edit Class No" single-line="" type="number" v-model="editedItem.class_no">
                                        </v-text-field>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="justify-center layout px-0">
                                <v-icon @click="pick_add_before_data(props.item)" class="mr-2" small="">
                                    play_for_work
                                </v-icon>
                                <v-icon @click="pick_planner_data(props.item)" class="mr-2" small="">
                                    call_made
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon @click="planner_delete(props.item)" small="">
                                    delete
                                </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
        </v-container>
    </div>
</template>
<script>
   export default {
     data() {
             return {
               // @list_header_data
               headers: [
                 { text: 'Class', value: 'subjects.echelons.name' },
                 { text: 'Course', value: 'courses.name' },
                 { text: 'Subject', value: 'subjects.name' },
                 { text: 'Chapter', value: 'chapters.name' },
                 { text: 'Topic', value: 'topic' },
                 { text: 'Class No', value: 'class_no' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               original_rows: [],
               branch: [],
               echelon: [],
               course: [],
               original_course: [],
               subject: [],
               original_subject: [],
               chapter: [],
               original_chapter: [],
               // @list_search_data
               search: '',
               search_filter: '',
               search_filter1: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               pagination: {
                  descending: false,
                  page: 1,
                  rowsPerPage: -1, // -1 for All",
                  sortBy: 'row'
               },
               dialogItem: {
                 id: '',
                 topic: '',
                 branch_id: '',
                 echelon_id: '',
                 course_id: '',
                 subject_id: '',
                 chapter_id: '',
                 subjects: {'id':'', 'name':'', branches: {'id':'', 'name':''}, echelons: {'id':'', 'name':''}},
                 chapters: {'id':'', 'name':'', 'chapter_no': ''},
                 courses: {'id':'', 'name':''},
                 created_at: '',
                 topics: []
               }, 
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
               planner_before: false,
               swich: false

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
     
     computed: {
       itemsSorted: function() {
          var items = this.rows
          .filter(item => 
            item.topic.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.subjects.branches.name.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.subjects.echelons.name.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.subjects.name.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.courses.name.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.chapters && item.chapters.name.toLowerCase().indexOf(this.search_filter.toLowerCase()) > -1 ||
            item.class_no.toString().indexOf(this.search_filter) > -1)
           return items
        },
     },
   
     methods:{
       // @add_item_method
       admin_branch_list(){
             this.branch = window_branch;
             if (this.branch.length==1) {
                 this.dialogItem.branch_id = this.branch[0].id
                 this.admin_echelon_list(this.dialogItem.branch_id);
                 this.admin_course_list()
                 this.admin_subject_list()
             }
             this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
       },
       admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
       },
       // @add_item_method
       get_item(){
          if (this.branch.length>1) {
              this.admin_echelon_list(this.dialogItem.branch_id);
          }
       },
       admin_course_list(){
         this.axios.get('/admin/request/course_list_by_branch/'+this.dialogItem.branch_id).then(response => {
             this.original_course = response.data.course_list_by_branch;
             this.course = response.data.course_list_by_branch;
             this.course = this.course.map(arr => { return { value: arr['id'], text: arr['name'] } })
         }); 
       },
       // @list_method
       admin_subject_list(){
         this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
             this.original_subject = response.data.subject_list_by_branch;
             this.subject = response.data.subject_list_by_branch;
             this.subject = this.subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
         });
       },
       planner_list(){
         this.data_load = true;
         this.axios.get('/admin/schedule/planner_list/' + this.dialogItem.subject_id).then(response => {
             this.rows = response.data.planner_list;
             this.original_rows = response.data.planner_list;
             this.data_load = false;
             if (this.swich) {this.swich_on()}
         }); 
       },
       chapter_list(){
         this.axios.get('/admin/request/chapter_list_by_subject/' + this.dialogItem.subject_id).then(response => {
             this.original_chapter = response.data.chapter_list_by_subject;
             this.chapter = response.data.chapter_list_by_subject;
             this.chapter = this.chapter.sort((a, b) => {
                return parseFloat(a.chapter_no) - parseFloat(b.chapter_no)
             })
             this.chapter = this.chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + ' - ' + arr['name'] } })
         }); 
       },
       get_subject(){
          var echelon_id = this.dialogItem.echelon_id
          if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
          var subject = Object.assign([], this.original_subject);
          let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
          this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
       },
       get_chapter_and_planner(){
          this.planner_list()
          this.chapter_list()
       },
       // @add_item_method
       planner_add(){
          // textarea text to array
          var texts = []
          var lines = this.dialogItem.topic.split(/\n/);
          for (var i=0; i < lines.length; i++) {
              // only non whitespace character.
              if (/\S/.test(lines[i])) {
                texts.push($.trim(lines[i]));
              }
          }
          this.dialogItem.topics = texts
          if (this.$refs.form.validate() && this.loading==false) {
            this.loading=true;
            this.axios.post('/admin/schedule/planner_add', this.dialogItem).then(response => {
                // push created planner
                var data = response.data.saved_planner
                for (var i = 0; i < data.length; i++) {
                  var new_dialogItem = Object.assign({}, this.dialogItem);
                  new_dialogItem.subjects.id = new_dialogItem.subject_id
                  new_dialogItem.subjects.name = this.subject[this.subject.findIndex(x => x.value == new_dialogItem.subject_id)].text
                  new_dialogItem.subjects.echelons.id = new_dialogItem.echelon_id
                  new_dialogItem.subjects.echelons.name = this.echelon[this.echelon.findIndex(x => x.value==new_dialogItem.echelon_id)].text
                  new_dialogItem.courses.id = new_dialogItem.course_id
                  new_dialogItem.courses.name = this.course[this.course.findIndex(x => x.value==new_dialogItem.course_id)].text
                  new_dialogItem.subjects.branches.id = new_dialogItem.branch_id
                  new_dialogItem.subjects.branches.name = this.branch[this.branch.findIndex(x => x.value==new_dialogItem.branch_id)].text
                  new_dialogItem.chapters.id = new_dialogItem.chapter_id
                  new_dialogItem.chapters.name = this.original_chapter[this.original_chapter.findIndex(x => x.id ==new_dialogItem.chapter_id)].name
                  new_dialogItem.chapters.chapter_no = this.original_chapter[this.original_chapter.findIndex(x => x.id ==new_dialogItem.chapter_id)].chapter_no
                  new_dialogItem.topic = data[i].topic
                  new_dialogItem.class_no = data[i].class_no
                  new_dialogItem.id = data[i].id
                  this.rows.push(new_dialogItem)
                }
                // last created item to top
                this.pagination.descending = true
                $('.user_nav').addClass('successful')
                this.loading=false;
                document.getElementById("edit_topic").select()
                setTimeout(function () { this.loading=false }.bind(this), 2000)
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
            });
          }
       },
       // @edit_item_method
       planner_edit(){
          this.loading=true;
          this.axios.post('/admin/schedule/planner_edit/'+this.editedId, this.editedItem).then(response => {
              $('.user_nav').addClass('successful')
              this.rows[this.editedIndex].subject_id = this.editedItem.subject_id
              this.rows[this.editedIndex].chapter_id = this.editedItem.chapter_id
              this.rows[this.editedIndex].chapters = response.data.planner.chapters
              this.rows[this.editedIndex].topic = this.editedItem.topic
              this.rows[this.editedIndex].class_no = this.editedItem.class_no
              this.loading=false;
              if (this.editedIndex == this.rows.length - 1 && this.swich) {
                 alert("Update completed of selected criteria")
              }
              if (this.editedIndex < this.rows.length - 1 && this.swich) {
                  this.editedIndex = this.editedIndex + 1
                  this.editedId = this.rows[this.editedIndex].id
                  this.dialogItem.topic = this.rows[this.editedIndex].topic
                  this.dialogItem.class_no = this.rows[this.editedIndex].class_no
                  setTimeout(function () { document.getElementById("edit_topic").select() }.bind(this), 500)
              }
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.loading=false;   
          });
       },
       planner_add_before(){
          this.loading=true;
          this.axios.post('/admin/schedule/planner_add_before', this.dialogItem).then(response => {
              $('.user_nav').addClass('successful')
              this.loading=false;
              this.planner_before = false
              this.planner_list()
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.loading=false;   
          });
       },
       pick_planner_data(item){
            this.planner_before = false
            this.dialogItem = Object.assign({}, item);
            this.dialogItem.branch_id = item.subjects.echelons.branch_id
            this.dialogItem.echelon_id = item.subjects.echelons.id
            this.dialogItem.subject_id = parseInt(item.subject_id)
            this.dialogItem.course_id = parseInt(item.course_id)
            this.dialogItem.chapter_id = parseInt(item.chapter_id)
            this.get_subject()
            this.change_class_no()
            setTimeout(function () { document.getElementById("edit_topic").select() }.bind(this), 500)
       },
       pick_add_before_data(item){
            this.dialogItem = Object.assign({}, item);
            this.dialogItem.branch_id = item.subjects.echelons.branch_id
            this.dialogItem.echelon_id = item.subjects.echelons.id
            this.dialogItem.subject_id = parseInt(item.subject_id)
            this.dialogItem.course_id = parseInt(item.course_id)
            this.dialogItem.chapter_id = parseInt(item.chapter_id)
            this.get_subject()
            this.planner_before = true
            setTimeout(function () { document.getElementById("edit_topic").select() }.bind(this), 500)
       },
       // @edit_item_method
       // open dialog
       editItem (item, element) {
            this.editedIndex = this.rows.findIndex(x => x.id==item.id);
            this.editedId = this.rows[this.editedIndex].id;
            this.editedItem = Object.assign({}, item);
            this.dialogItem.echelon_id = item.subjects.echelons.id
            this.editedItem.subject_id = parseInt(item.subject_id)
            this.editedItem.chapter_id = parseInt(item.chapter_id)
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1 && !this.planner_before) {
            if (this.swich) {
                this.editedItem = Object.assign({}, this.dialogItem);
            }
            this.planner_edit()
          } 
          if (this.editedIndex ==-1 && !this.planner_before) {
            this.change_class_no()
            setTimeout(function () { this.planner_add() }.bind(this), 200)
          }
          if (this.planner_before) {this.planner_add_before()}
       },
       get_branch(){
          if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
          }
       },
       // @delete_item_method
       planner_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/schedule/planner_delete/'+item.id).then(response => {
                this.rows.splice(index, 1)
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
         }
       },
       
       swich_on(){
          if (this.swich) {
              var new_rows = Object.assign([], this.rows);
              let filter_rows = new_rows.filter(x=>{return x.subject_id == this.dialogItem.subject_id && x.course_id == this.dialogItem.course_id && x.chapter_id == this.dialogItem.chapter_id})
              this.rows = filter_rows.sort((a, b) => parseInt(a.class_no) - parseInt(b.class_no));
              if (filter_rows.length>0) {
                  this.editedId = filter_rows[0].id
                  this.editedIndex = 0;
                  this.dialogItem.topic = this.rows[0].topic
                  this.dialogItem.class_no = this.rows[0].class_no
              }
              setTimeout(function () { document.getElementById("edit_topic").select() }.bind(this), 500)
          }
          else {
             this.rows = this.original_rows
          }
       },
       change_class_no(){
          var new_rows = Object.assign([], this.rows);
          var filter_planner_rows = []
          if (this.dialogItem.chapter_id) {
              filter_planner_rows = new_rows.filter(x=> {return x.chapter_id == this.dialogItem.chapter_id})
          }
          if (this.dialogItem.subject_id && this.dialogItem.course_id && !this.dialogItem.chapter_id) {
              filter_planner_rows = new_rows.filter(x=> {return x.subject_id == this.dialogItem.subject_id && x.course_id == this.dialogItem.course_id})
          }
          if (filter_planner_rows.length>0) {
            let max = Math.max.apply(Math, filter_planner_rows.map(function(x) { return x.class_no }));
            filter_planner_rows.forEach(character => {
                if (character.class_no > max) {
                  max = character.class_no;
                }
              });
            this.dialogItem.class_no = Number(max) + 1
          }
          else {this.dialogItem.class_no = 1}
       },
       next_chapter(){
          if (this.dialogItem.chapter_id) {
              var index = this.chapter.findIndex(x => x.value == this.dialogItem.chapter_id)
              if (this.chapter.length > 0 && index < this.chapter.length - 1) {
                  this.dialogItem.chapter_id = this.chapter[index+1].value
                  this.change_class_no()
              }
              if (this.chapter.length > 0 && index == this.chapter.length - 1) {
                  this.dialogItem.chapter_id = this.chapter[0].value
                  this.change_class_no()
              }
          }
       },
       prev_chapter(){
          if (this.dialogItem.chapter_id) {
              var index = this.chapter.findIndex(x => x.value == this.dialogItem.chapter_id)
              if (this.chapter.length > 0 && index > 0) {
                  this.dialogItem.chapter_id = this.chapter[index-1].value
                  this.change_class_no()
              }
              if (this.chapter.length > 0 && index == 0) {
                  this.dialogItem.chapter_id = this.chapter[this.chapter.length - 1].value
                  this.change_class_no()
              }
          }
       },
       // @add_item_method_dialog
       close () {
         this.dialog = false
         this.$refs.form.reset()
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
  .v-text-field label, .v-text-field input {
    font-size: 14px;
  }
  .planner-topic textarea{
    max-height: 200px;
    overflow: auto;
    font-size: 14px;
  }
  #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
</style>