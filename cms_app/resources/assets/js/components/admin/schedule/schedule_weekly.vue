<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="schedule_weekly">
        <v-container fluid="">
            <!-- @page_headline -->
            <h3 class="headline m-t-0">
                Manage Weekly Schedule <span @click="weekly_schedule" class="m-l-10"><v-icon>refresh</v-icon></span>
            </h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar color="grey lighten-2 pb-1" flat="">
                        <v-text-field append-icon="search" hide-details="" label="Search" single-line="" v-model="search"></v-text-field>
                        <v-icon @click="clear" class="ml-5">
                            backspace
                        </v-icon>
                        <!-- @add_item_dialog -->
                    </v-toolbar>
                    <template>
                        <v-form lazy-validation="" ref="form" v-model="valid">
                            <v-container>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0">
                                        <v-select :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" @change="get_item" label="Branch" required="" v-model="dialogItem.branch_id"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0">
                                        <v-select :items="echelon" :rules="[v => !!v || 'Class is required']" @change="get_batch" label="Class" required="" v-model="dialogItem.echelon_id"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0">
                                        <v-autocomplete @change="weekly_schedule" :items="batch" label="Batch" v-model="dialogItem.batch_id" :rules="[v => !!v || 'Batch is required']"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0">
                                        <v-autocomplete :items="day" :rules="[v => !!v || 'Day is required']" @change="change_period" label="Day" required="" v-model="dialogItem.day"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0">
                                        <v-icon @click="prev_day" class="ml-1" medium>keyboard_arrow_left</v-icon>
                                        <v-icon @click="next_day" class="ml-1" medium>keyboard_arrow_right</v-icon>
                                    </v-flex>
                                    <v-spacer></v-spacer>
                                    <v-flex class="pb-0 pt-0" md1="" xs12="">
                                        <v-btn class="mid-btn" color="info" small="" @click="duplicate_dialog = true">
                                            <v-icon class="m-r-5 fs-16"> file_copy </v-icon>
                                        </v-btn>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0">
                                        <v-btn class="mid-btn" color="error" small @click="delete_weekly_schedule">
                                            <v-icon class="m-r-5 fs-16"> delete </v-icon>
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-autocomplete :clearable="true" :items="subject" @keyup.enter="editedIndex=-1, save()" id="edit_subject" label="Subject" v-model="dialogItem.subject_id" @change="get_teacher"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-autocomplete :clearable="true" :items="teacher" id="edit_teacher" label="Teacher" v-model="dialogItem.teacher_id"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2 xs12>
                                        <v-select :items="schedule_type" :rules="[v => !!v || 'required']" @keyup.enter="editedIndex=-1, save()" label="Schedule Type" required="" v-model="dialogItem.schedule_type"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md1 xs12>
                                        <v-text-field :rules="[v => !!v || 'required']" @keyup.enter="editedIndex=-1, save()" id="edit_period" label="Period" type="number" v-model="dialogItem.period"></v-text-field>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md1 xs12>
                                        <v-text-field :rules="[v => !!v || 'required']" v-model="dialogItem.time" mask="time-with-seconds" label="Start Time"></v-text-field>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" md2="" xs12="">
                                        <v-btn :disabled="!valid || loading" :loading="loading" @click="editedIndex=-1, save()" class="mb-2" color="primary">
                                            + Add
                                        </v-btn>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </v-form>
                    </template>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :loading="data_load" :pagination.sync="pagination" :search="search" class="elevation-1" hide-actions="">
                        <v-progress-linear color="blue" indeterminate="" slot="progress"></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">
                                {{ props.item.batches.echelons.name }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.batches.name}}
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog @open="editItem(props.item, 'subject')" @save="save" lazy="">
                                    {{ props.item.subjects ? props.item.subjects.name : '' }}
                                    
                                    <template v-slot:input="">
                                        <v-autocomplete :items="subject" label="Subject" required="" v-model="editedItem.subject_id"></v-autocomplete>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog @open="editItem(props.item, 'teacher')" @save="save" lazy="">
                                    {{ props.item.teachers ? props.item.teachers.name : '' }}
                                    
                                    <template v-slot:input="">
                                        <v-autocomplete :items="teacher" label="Teacher" required="" v-model="editedItem.teacher_id"></v-autocomplete>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.schedule_type" @open="editItem(props.item, 'schedule_type')" @save="save" lazy="">
                                    {{ props.item.schedule_type }}
                                    <template v-slot:input="">
                                        <v-autocomplete :items="schedule_type" label="Edit Schedule type" single-line="" v-model="editedItem.schedule_type"></v-autocomplete>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.day" @open="editItem(props.item, 'day')" @save="save" lazy="">
                                    {{ props.item.day }}
                                    
                                    <template v-slot:input="">
                                        <v-autocomplete :items="day" label="Edit Day" required="" v-model="editedItem.day"></v-autocomplete>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.period" @open="editItem(props.item, 'period')" @save="save" lazy="">
                                    {{ props.item.period }}
                                    <template v-slot:input="">
                                        <v-text-field label="Edit Period" single-line="" type="number" v-model="editedItem.period"></v-text-field>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="text-xs-left">
                                <v-edit-dialog :return-value.sync="props.item.time" @open="editItem(props.item, 'time')" @save="save" lazy="">
                                    {{ props.item.time }}
                                    <template v-slot:input="">
                                        <v-text-field label="Edit Time" single-line="" mask="time-with-seconds" v-model="editedItem.time"></v-text-field>
                                    </template>
                                </v-edit-dialog>
                            </td>
                            <td class="justify-center layout px-0">
                                <v-icon @click="pick_weekly_schedule_data(props.item)" class="mr-2" small="">
                                    call_made
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon @click="weekly_schedule_delete(props.item)" small="">
                                    delete
                                </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
        </v-container>
        <v-dialog v-model="duplicate_dialog" hide-dialog persistent max-width="600">
            <v-card class="container">
                <v-card-text class="pos-rel">
                    <v-btn @click.native="duplicate_dialog = false" icon style="right: 0px; top: 5px; position: absolute!important"> <v-icon> close </v-icon></v-btn>
                    <div class="fs-16 fw-600 text-center">Duplicate Weekly Schedule</div>
                    <hr>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="fw-600 fs-13 m-b-10">Duplicate From</div>
                            <v-select :items="echelon" @change="get_duplicate_batch" label="Class" required="" v-model="duplicate.from_echelon_id"></v-select>
                            <v-autocomplete :items="duplicate_batch" label="Batch" v-model="duplicate.from_batch_id"></v-autocomplete>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5">
                            <div class="fw-600 fs-13 m-b-10">Duplicate To</div>
                            <v-autocomplete :items="duplicate_batch" label="Batch" v-model="duplicate.to_batch_id"></v-autocomplete>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">Time of New Schedule</div>
                    <div class="text-center m-t-10">
                        <v-text-field label="Time of 1st Period" single-line="" mask="time-with-seconds" v-model="duplicate.time1" style="max-width: 300px; margin: auto"></v-text-field>
                        <v-text-field label="Time of 2nd Period" single-line="" mask="time-with-seconds" v-model="duplicate.time2" style="max-width: 300px; margin: auto"></v-text-field>
                        <v-text-field label="Time of 3rd Period" single-line="" mask="time-with-seconds" v-model="duplicate.time3" style="max-width: 300px; margin: auto"></v-text-field>
                    </div>
                    <div class="text-center m-t-10"><v-btn :disabled="loading" :loading="loading" color="success" small @click="duplicate_weekly_schedule">Submit</v-btn></div>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
   export default {
     data() {
             return {
               // @list_header_data
               headers: [
                 { text: 'Class', value: 'echelons.batches.name' },
                 { text: 'Batch', value: 'batches.name' },
                 { text: 'Subject', value: 'subjects.name' },
                 { text: 'Teacher', value: 'teachers.name' },
                 { text: 'Schedule Type', value: 'schedule_type' },
                 { text: 'Day', value: 'day' },
                 { text: 'Period', value: 'period' },
                 { text: 'Time', value: 'time' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               branch: [],
               echelon: [],
               batch: [],
               original_batch: [],
               duplicate_batch: [],
               to_batch: [],
               original_subject: [],
               subject: [],
               teacher: [],
               original_teacher: [],
               schedule_type: [{'value': 'class', 'text': 'Class'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'close', 'text': 'Close'}, {'value': 'online_class', 'text': 'Online Class'}, {'value': 'online_exam', 'text': 'Online Exam'}],
               day: [{'value': 'sat', 'text': 'Sat'}, {'value': 'sun', 'text': 'Sun'}, {'value': 'mon', 'text': 'Mon'}, {'value': 'tue', 'text': 'Tue'}, {'value': 'wed', 'text': 'Wed'}, {'value': 'thu', 'text': 'Thu'}, {'value': 'fri', 'text': 'Fri'}],
               // @list_search_data
               search: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               pagination: {
                  page: 1,
                  rowsPerPage: -1, // -1 for All",
                  sortBy: 'id'
               },
               dialogItem: {
                 id: '',
                 name: '',
                 branch_id: '',
                 echelon_id: '',
                 subject_id: '',
                 chapter_id: '',
                 subjects: {'id':'', 'name':''},
                 echelons: {'id':'', 'name':''},
                 weekly_schedules: {'id':'', 'name':''},
                 created_at: ''

               }, 
               editedItem: '',     
               rules: {
                 required: value => !!value || 'Required.'
               },
               
               // @accesories_data
               valid: true,
               loading: false,
               data_load: false,
               duplicate_dialog: false,
               duplicate: {from_echelon_id: '', from_batch_id: '', to_echelon_id: '', to_batch_id: ''}

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='edit_schedule');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
     
     computed: {
     },
   
     watch: {
       dialog (val) {
         val || this.close()
       }
     },
   
     methods:{
       // @add_item_method
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.dialogItem.branch_id = this.branch[0].id
               this.admin_echelon_list(this.dialogItem.branch_id);
               this.admin_batch_list()
               setTimeout(function () { this.admin_subject_list() }.bind(this), 2000)
               setTimeout(function () { this.teacher_list() }.bind(this), 3000)
           }
           this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
       },
       admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
       },
       admin_batch_list(){
            this.axios.get('/admin/request/batch_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                this.original_batch = response.data.batch_list_by_branch;
            }); 
        },
        get_batch(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
           this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
           this.get_subject()
        },
        get_duplicate_batch(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.duplicate.from_echelon_id})
           this.duplicate_batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
        },
        get_to_batch(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.duplicate.to_echelon_id})
           this.to_batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
        },
        get_batch_to_duplicate(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.duplicate.to_echelon_id})
           this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
        },
       // @add_item_method
       get_item(){
            if (this.branch.length>1) {
              this.admin_echelon_list(this.dialogItem.branch_id);
            }
       },
       // @list_method
       admin_subject_list(){
         this.axios.get('/admin/request/subject_list_by_branch/' + this.dialogItem.branch_id).then(response => {
             this.original_subject = response.data.subject_list_by_branch;
             this.subject = response.data.subject_list_by_branch;
             this.subject = this.subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
         });
       },
       weekly_schedule(){
         this.data_load = true;
         this.axios.get('/admin/schedule/weekly_schedule/' + this.dialogItem.batch_id).then(response => {
            this.rows = response.data.weekly_schedule;
            this.data_load = false;
         }); 
       },
       get_subject(){
          var echelon_id = this.dialogItem.echelon_id
          if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
          var subject = Object.assign([], this.original_subject);
          let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
          this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'], teacher_id: arr['teacher_id'] } })          
       },
       teacher_list(){
            this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                this.original_teacher = response.data.teacher_list_by_branch;
                this.teacher = response.data.teacher_list_by_branch;
                this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
            }); 
        },
        get_teacher()
        {
            var index = this.subject.findIndex(x => x.value == this.dialogItem.subject_id);
            this.dialogItem.teacher_id = this.subject[index].teacher_id
        },
       // @add_item_method
        weekly_schedule_add(){
          if (this.$refs.form.validate() && this.loading==false) {
            this.loading=true;
            this.axios.post('/admin/schedule/weekly_schedule_add', this.dialogItem).then(response => {
                this.rows.push(response.data.weekly_schedule)
                this.pagination.descending = true
                $('.user_nav').addClass('successful')
                this.loading=false;
                document.getElementById("edit_subject").select()
                this.dialogItem.class_no = Number(this.dialogItem.class_no) + 1
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
            });
          }
        },
       // @edit_item_method
       weekly_schedule_edit(){
          this.loading=true;
          this.axios.post('/admin/schedule/weekly_schedule_edit/'+this.editedId, this.editedItem).then(response => {
              $('.user_nav').addClass('successful')
              this.rows[this.editedIndex].subject_id = this.editedItem.subject_id
              this.rows[this.editedIndex].subjects.id = this.editedItem.subject_id
              this.rows[this.editedIndex].subjects.name = this.subject[this.subject.findIndex(x => x.value == this.editedItem.subject_id)].text
              this.rows[this.editedIndex].teacher_id = this.editedItem.teacher_id
              this.rows[this.editedIndex].teachers.id = this.editedItem.subject_id
              this.rows[this.editedIndex].teachers.name = this.teacher[this.teacher.findIndex(x => x.value == this.editedItem.teacher_id)].text
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
              this.loading=false;
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.loading=false;   
          });
        },
        duplicate_weekly_schedule(){
            if (!this.duplicate.from_batch_id || !this.duplicate.to_batch_id) {
                return alert('Please select batch');
            }
            this.loading = true;
            this.axios.post('/admin/schedule/duplicate_weekly_schedule', this.duplicate).then(response => {
                $('.user_nav').addClass('successful')
                this.duplicate_dialog = false
                this.loading = false;
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
               alert(error.response.data.error)
            });
        },
        delete_weekly_schedule(){
            this.loading = true;
            this.axios.post('/admin/schedule/delete_weekly_schedule/' + this.dialogItem.batch_id).then(response => {
                $('.user_nav').addClass('successful')
                this.loading = false;
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
            });
        },
        pick_weekly_schedule_data(item){
            this.dialogItem = Object.assign({}, item);
            this.dialogItem.branch_id = item.batches.branch_id
            this.dialogItem.echelon_id = item.batches.echelon_id
            this.dialogItem.weekly_schedule_list_id = item.weekly_schedule_list_id
            this.dialogItem.subject_id = item.subject_id
            this.get_weekly_schedule()
            this.get_subject()
            this.change_period()
            setTimeout(function () { document.getElementById("edit_subject").select() }.bind(this), 1500)
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            this.weekly_schedule_edit()
          } else {
            this.weekly_schedule_add()
          }
       },
       get_branch(){
          if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
          }
       },
       // @delete_item_method
       weekly_schedule_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/schedule/weekly_schedule_delete/'+item.id).then(response => {
                this.rows.splice(index, 1)
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
         }
       },
       // @edit_item_method
       // open dialog
       editItem (item, element) {
         this.editedIndex = this.rows.findIndex(x => x.id==item.id);
         this.editedId = this.rows[this.editedIndex].id;
         this.editedItem = Object.assign({}, item);
            this.editedItem.schedule_type = item.schedule_type.toLowerCase()
            this.editedItem.class_no = item.class_no
            this.editedItem.subject_id = item.subject_id
            this.editedItem.day = item.day.toLowerCase()
            this.editedItem.teacher_id = item.teacher_id
       },
       change_period(){
          var new_rows = Object.assign([], this.rows);          
          let filter_weekly_schedule_rows = new_rows.filter(x=> {return x.day == this.dialogItem.day && x.batches.echelons.id == this.dialogItem.echelon_id})
          if (this.dialogItem.batch_id) {
            filter_weekly_schedule_rows = filter_weekly_schedule_rows.filter(x=> {return x.batch_id == this.dialogItem.batch_id})
          }
          if (filter_weekly_schedule_rows.length>0) {
            let max = Math.max.apply(Math, filter_weekly_schedule_rows.map(function(x) { return x.period }));
            filter_weekly_schedule_rows.forEach(character => {
                if (character.period > max) {
                  max = character.period;
                }
              });
            this.dialogItem.period = Number(max) + 1
          }
          else {this.dialogItem.period = 1}
       },
       next_day(){
          if (this.dialogItem.day) {
              var index = this.day.findIndex(x => x.value == this.dialogItem.day)
              if (this.day.length > 0 && index < this.day.length - 1) {
                  this.dialogItem.day = this.day[index+1].value
                  this.change_period()
              }
              if (this.day.length > 0 && index == this.day.length - 1) {
                  this.dialogItem.day = this.day[0].value
                  this.change_period()
              }
          }
       },
       prev_day(){
          if (this.dialogItem.day) {
              var index = this.day.findIndex(x => x.value == this.dialogItem.day)
              if (this.day.length > 0 && index > 0) {
                  this.dialogItem.day = this.day[index-1].value
                  this.change_period()
              }
              if (this.day.length > 0 && index == 0) {
                  this.dialogItem.day = this.day[this.day.length - 1].value
                  this.change_period()
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
       }
     }
   }
</script>


<style>
  .schedule_weekly .v-text-field label, .schedule_weekly .v-text-field input {
    font-size: 14px;
  }
  .schedule_weekly #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .schedule_weekly .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .schedule_weekly .v-input{
    font-size: 14px;
  }
  .schedule_weekly .v-btn{
    height: 32px;
  }
</style>