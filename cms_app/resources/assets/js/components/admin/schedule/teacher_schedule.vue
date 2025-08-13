<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="teacher_schedule">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Edit Teacher Schedule</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" xs6 md3>
                                        <v-autocomplete :items="teacher" :clearable=true label="Teacher" v-model="dialogItem.teacher_id"> </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-select v-model="dialogItem.echelon_id" :clearable="true" :items="echelon" label="Class" @change="get_batch()" required></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-autocomplete :clearable="true" :items="batch" label="Batch" v-model="dialogItem.batch_id"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md3>
                                        <v-autocomplete :clearable=true :items="subject" id="edit_subject" label="Subject" v-model="dialogItem.subject_id"> </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs6 md3>
                                        <v-autocomplete :items="protect_combine" :clearable=true label="Protect Combine" v-model="dialogItem.protect_combine"> </v-autocomplete>
                                    </v-flex>
                                </v-layout>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" xs4 md3>
                                        <v-select :clearable="true" :items="schedule_type" label="Schedule Type" required v-model="dialogItem.schedule_type"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs6 md3>
                                        <v-autocomplete @change="change_job_type" :items="job_type" :clearable=true label="Teacher Type" v-model="dialogItem.job_type"> </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-dialog full-width lazy ref="date_ref_1" width="290px" class="m-r-10">
                                            <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="dialogItem.date_from"></v-text-field>
                                            <v-date-picker ref="picker_1" v-model="dialogItem.date_from"></v-date-picker>
                                        </v-dialog>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-dialog full-width lazy ref="date_ref_2" width="290px" class="m-r-10">
                                            <v-text-field label="Date To" :clearable=true readonly slot="activator" v-model="dialogItem.date_to"></v-text-field>
                                            <v-date-picker ref="picker_2" v-model="dialogItem.date_to"></v-date-picker>
                                        </v-dialog>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="get_teacher_schedule_list"> Go </v-btn>
                                    </v-flex>
                                </v-layout>
                                <hr>
                                <div class="row" v-for="(schedule, e_i) in teacher_schedule_list">
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        {{schedule.batches.echelons.name}}-{{schedule.batches.name}}
                                    </div>
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        {{schedule.subjects.name}}
                                    </div>
                                    <div class="col-md-1 col-xs-3 p-l-5 p-r-5">
                                        {{schedule.schedule_type}}
                                    </div>
                                    <div class="col-md-2 p-l-5 p-r-5">
                                        <v-autocomplete v-if="schedule.schedule_type=='class'" :items="teacher" :clearable=true label="Teacher" v-model="schedule.teacher_id"> </v-autocomplete>
                                        <v-autocomplete v-if="dialogItem.job_type == 'invigilator' && schedule.exam_lists && schedule.exam_lists.invigilator_id" :items="teacher" :clearable=true label="Inviliator" v-model="schedule.exam_lists.invigilator_id"> </v-autocomplete>
                                        <v-autocomplete v-if="dialogItem.job_type == 'scripter' && schedule.exam_lists && schedule.exam_lists.scripter_id" :items="teacher" :clearable=true label="Scripter" v-model="schedule.exam_lists.scripter_id"> </v-autocomplete>
                                    </div>
                                    <div class="col-md-1 col-xs-3 p-l-5 p-r-5">
                                        <v-text-field v-if="dialogItem.job_type == 'invigilator' && schedule.exam_lists && schedule.exam_lists.invigilator_id" class="tiny-text-input" v-model="schedule.invigilator_minute" label="Invigilator Minutes"></v-text-field>
                                        <v-text-field v-if="dialogItem.job_type == 'scripter' && schedule.exam_lists && schedule.exam_lists.scripter_id" class="tiny-text-input" v-model="schedule.script_quantity" label="Script Quantity"></v-text-field>
                                    </div>
                                    <div v-if="dialogItem.job_type == 'invigilator' && schedule.exam_lists" class="col-md-1">
                                        {{schedule.exam_lists.start_time}}
                                    </div>
                                    <div class="col-md-1" style="display: inline-flex">
                                        <span v-if="schedule.teacher_attendance_status == 'done'">Y</span>
                                        <v-checkbox v-model="schedule.teacher_done" label="Done"></v-checkbox>
                                    </div>
                                    <div class="col-md-2 col-xs-1 p-l-5 p-r-5">
                                        <div>{{schedule.date | moment("ddd, D MMM")}}</div>
                                    </div>
                                </div>
                                <div class="fw-600 m-t-10 m-b-10">Total: {{final_total}}</div>
                                <div>
                                    <v-btn small color="success" @click="save_teacher_schedule_list">Submit</v-btn>
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
                // @list_header_data
                headers: [ {text: 'Class', value: 'echelons.name'} , {text: 'Batch', value: 'batches.name'} , {text: 'Schedule', value: 'schedule_type'} , {text: 'Subject', value: 'subjects.name'} , {text: 'Day', value: 'day'} , {text: 'Class No', value: 'class_no'} , {text: 'Actions', value: 'name', sortable: false, align: 'center'}
                ], // @list_column_data
                rows: [],
                branch: [],
                echelon: [],
                original_batch: [],
                batch: [],
                subject: [],
                schedule_type: [ {'value': 'class', 'text': 'Class'} , {'value': 'exam', 'text': 'Exam'} , {'value': 'close', 'text': 'Close'} ],
                job_type: [ {'value': 'class', 'text': 'Class'} , {'value': 'invigilator', 'text': 'Invigilator'} , {'value': 'scripter', 'text': 'Scripter'} ],
                protect_combine: [ {'value': 1, 'text': 'Yes'} , {'value': 0, 'text': 'No'} ],
                search: '',
                search_filter: '',
                search_filter1: '', // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                pagination: {
                    page: 1, rowsPerPage: -1, // -1 for All",
                    sortBy: 'created_at'
                },
                dialogItem: {id: '', name: '', branch_id: '', echelon_id: '', batches: [], date_from: '', date_to: '', vacation_range: [], protect_combine: 1 },
                editedItem: '',
                rules: {
                    required: value=> !!value || 'Required.'
                }
                , // @accesories_data
                valid: true,
                success_alert: false,
                error_alert: false,
                loading: false,
                data_load: false,
                created: false,
                exist: false,
                teacher_schedule_list: [],
                exam_record: [],
                chapter: [],
                original_chapter: [],
                teacher: [],
                original_teacher: [],
                final_total: 0
            } //end return
        }
        , // @router_permission
        beforeRouteEnter (to, from, next) {
            let p=window_admin_permissions.findIndex(x=> x.name=='manage_teacher');
            if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
                next()
            }
            else {
                next('/')
            }
        }
        , // @load_method
        created() {
            this.admin_branch_list();
        },
        methods: {
            // @add_item_method
            admin_branch_list() {
                this.branch=window_branch;
                if (this.branch.length==1) {
                    this.dialogItem.branch_id=this.branch[0].id 
                    this.admin_echelon_list(this.dialogItem.branch_id);
                    this.teacher_list()
                    setTimeout(function () {  }.bind(this), 2000)
                }
                this.branch=this.branch.map(arr=> {return {value: arr['id'], text: arr['name'] } } )
            },
            admin_echelon_list() {
                this.echelon=window_echelons.map(arr=> {return {value: arr['id'], text: arr['name'] } } ) 
                this.admin_subject_list()
            },
            admin_batch_list() {
                this.axios.get('/admin/request/batch_list_by_branch/'+this.dialogItem.branch_id).then(response=> {
                    this.original_batch=response.data.batch_list_by_branch;
                    this.batch=response.data.batch_list_by_branch;
                    this.batch=this.batch.map(arr=> {return {value: arr['id'], text: arr['name'] } } )
                    this.admin_subject_list()
                });
            },
            get_batch() {
                var batch=Object.assign([], this.original_batch);
                let filter_batch = batch.filter(x=> {return x.echelon_id==this.dialogItem.echelon_id } ) 
                this.batch = filter_batch.map(arr=> {return {value: arr['id'], text: arr['name'] } } ) 
                this.get_subject()
            },
            admin_subject_list(){
                 this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                     this.original_subject = response.data.subject_list_by_branch;
                     this.subject = this.original_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
                });
            },
            get_subject(){
                if (!this.dialogItem.echelon_id) {
                    this.subject = this.original_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
                } else {                    
                    var echelon_id = this.dialogItem.echelon_id
                    if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
                    var subject = Object.assign([], this.original_subject);
                    let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
                    this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })   
                }
            },
            teacher_list(){
              this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                  this.original_teacher = response.data.teacher_list_by_branch;
                  this.teacher = response.data.teacher_list_by_branch;
                  this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
              }); 
            },
            get_teacher_schedule_list() {
                this.dialog = true 
                if (!this.dialogItem.date_from) {
                    this.dialogItem.date_from = this.$moment().clone().startOf('month').format('YYYY-MM-DD')
                    this.dialogItem.date_to = this.$moment().clone().endOf('month').format('YYYY-MM-DD');
                }
                this.axios.post('/admin/schedule/get_teacher_schedule_list', this.dialogItem).then(response=> {
                    this.teacher_schedule_list = response.data.daily_schedule
                    this.final_total = 0
                    for (var i = 0; i < this.teacher_schedule_list.length; i++) {
                        if (this.teacher_schedule_list[i].teacher_schedules && this.teacher_schedule_list[i].teacher_schedules.type == 'teacher' &&  this.dialogItem.job_type == 'class') {
                            this.teacher_schedule_list[i].teacher_done = true
                            this.final_total = this.final_total + 1
                        } else {
                            this.teacher_schedule_list[i].teacher_done = false
                        }

                        this.teacher_schedule_list[i].invigilator_minute = this.teacher_schedule_list[i].exam_lists ? this.teacher_schedule_list[i].exam_lists.full_time : ''
                        this.teacher_schedule_list[i].script_quantity =  this.teacher_schedule_list[i].exam_lists ? this.teacher_schedule_list[i].exam_lists.script_quantity : ''

                        if (this.teacher_schedule_list[i].teacher_schedules && this.teacher_schedule_list[i].teacher_schedules.type == 'invigilator' && this.dialogItem.job_type == 'invigilator') {
                            this.teacher_schedule_list[i].teacher_done = true
                            this.teacher_schedule_list[i].invigilator_minute = this.teacher_schedule_list[i].teacher_schedules.invigilator_minute
                            this.final_total = this.final_total + this.teacher_schedule_list[i].teacher_schedules.invigilator_minute
                        }

                        if (this.teacher_schedule_list[i].teacher_schedules && this.teacher_schedule_list[i].teacher_schedules.type == 'scripter' && this.dialogItem.job_type == 'scripter') {
                            this.teacher_schedule_list[i].teacher_done = true
                            this.teacher_schedule_list[i].script_quantity = this.teacher_schedule_list[i].teacher_schedules.script_quantity
                            this.final_total = this.final_total + this.teacher_schedule_list[i].teacher_schedules.script_quantity
                        }
                    }
                    this.dialog = false
                    $('.user_nav').addClass('successful') 
                    setTimeout(function () {$('.user_nav').removeClass('successful') }.bind(this), 1000)
                }, error=> {
                    this.dialog = false 
                    $('.user_nav').addClass('denied') 
                    setTimeout(function () {$('.user_nav').removeClass('denied') }.bind(this), 1000)
                });
            },
            save_teacher_schedule_list() {
                this.dialog = true 
                var item = {}
                item.teacher_list = this.teacher_schedule_list
                item.job_type = this.dialogItem.job_type
                this.axios.post('/admin/schedule/save_teacher_schedule_list', item).then(response=> {
                    $('.user_nav').addClass('successful')
                    this.dialog = false 
                    setTimeout(function () {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error=> {                    
                    this.dialog = false 
                    $('.user_nav').addClass('denied') 
                    setTimeout(function () {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            },
            change_job_type(){
                if (this.dialogItem.teacher_id && this.dialogItem.date_from && this.dialogItem.date_to) {
                    this.get_teacher_schedule_list()
                }
            },
            add_vaction() {
                var x= {'date_from': '', 'date_to': '', 'vacation_text': ''}
                this.dialogItem.vacation_range.push(x)
            },
            delete_vaction(index, type) {
                this.dialogItem.vacation_range.splice(index, 1)
            }, // @add_item_method_close_dialog
        }
}

</script>


<style>
  .teacher_schedule .v-alert{
      position: fixed;
      z-index: 1;
      right: 10px;
      top: 70px;
      width: 300px;
      height: 50px;
  }
  .teacher_schedule .v-text-field label, .teacher_schedule .v-text-field input {
    font-size: 14px;
  }
  .teacher_schedule #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .teacher_schedule .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .teacher_schedule .v-input{
    font-size: 14px;
  }
  .teacher_schedule .v-btn{
    height: 32px;
  }
  .teacher_schedule .v-input--selection-controls {
    margin-top: 0px;
  }
</style>