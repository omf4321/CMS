<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="add_exam">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Edit Schedule</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class" @change="get_batch()" required></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-autocomplete :clearable="true" :items="batch" label="Batch" v-model="dialogItem.batch_id"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md3>
                                        <v-autocomplete :clearable=true :items="subject" id="edit_subject" label="Subject" v-model="dialogItem.subject_id"> </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs6 md3>
                                        <v-autocomplete :items="subject" :clearable=true label="Compare Subject" v-model="dialogItem.compare_subject"> </v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs6 md3>
                                        <v-autocomplete :items="teacher" :clearable=true label="Teacher" v-model="dialogItem.teacher_id"> </v-autocomplete>
                                    </v-flex>
                                </v-layout>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" xs4 md3>
                                        <v-select :clearable="true" :items="schedule_type" label="Schedule Type" required v-model="dialogItem.schedule_type"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md3>
                                        <v-select :clearable="true" :items="schedule_type" label="Compare Type" required v-model="dialogItem.compare_schedule_type"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-dialog v-model="date_from_dialog" full-width lazy ref="date_ref_1" width="290px" class="m-r-10">
                                            <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="dialogItem.date_from"></v-text-field>
                                            <v-date-picker @change = "date_from_dialog = false" ref="picker_1" v-model="dialogItem.date_from"></v-date-picker>
                                        </v-dialog>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-dialog v-model="date_to_dialog" full-width lazy ref="date_ref_2" width="290px" class="m-r-10">
                                            <v-text-field label="Date To" :clearable=true readonly slot="activator" v-model="dialogItem.date_to"></v-text-field>
                                            <v-date-picker @change="date_to_dialog = false" ref="picker_2" v-model="dialogItem.date_to"></v-date-picker>
                                        </v-dialog>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0" xs4 md2>
                                        <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="get_schedule_exam_list"> Go </v-btn>
                                    </v-flex>
                                </v-layout>
                                <hr>
                                <div class="row" v-for="(exam, e_i) in exam_schedule_list">
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        <v-autocomplete class="tiny-text-input" :clearable=true v-model="exam.subject_id" :items="subject" label="Subject"></v-autocomplete>
                                    </div>
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        <v-autocomplete class="tiny-text-input" :clearable=true v-model="exam.teacher_id" :items="teacher" label="Teacher"></v-autocomplete>
                                    </div>
                                    <div class="col-md-1 col-xs-3 p-l-5 p-r-5">
                                        <v-select class="tiny-text-input" v-model="exam.schedule_type" :items="schedule_type" label="Type"></v-select>
                                    </div>
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        <v-text-field class="tiny-chip" :clearable="true" v-model="exam.chapter_text">
                                        </v-text-field>
                                    </div>
                                    <div class="col-md-2 col-xs-3 p-l-5 p-r-5">
                                        <v-text-field class="tiny-text-input" :clearable=true hide-details label="Topic" single-line v-model="exam.topic"></v-text-field>
                                    </div>
                                    <div class="col-md-1">
                                        <v-text-field class="tiny-text-input" label="Period" single-line v-model="exam.period"></v-text-field>
                                    </div>
                                    <div class="col-md-2 col-xs-1 p-l-5 p-r-5">
                                        <div :class="{'fs-13 fw-600': true, 'red--text': !exam.subject_id || !exam.teacher_id || (!exam.topic && !exam.chapter_text), 'green--text': exam.subject_id == dialogItem.subject_id}">{{exam.date | moment("ddd, D MMM")}}</div>
                                    </div>
                                </div>
                                <v-layout>
                                    <v-autocomplete :clearable=true v-model="dialogItem.apply_batch" :items="batch" label="Apply To" class="m-r-10"></v-autocomplete> 
                                    <v-autocomplete :clearable=true v-model="dialogItem.apply_by" :items="apply_by" label="Apply By"></v-autocomplete> 
                                </v-layout>
                                <div>
                                    <v-btn small color="success" @click="save_schedule_exam_list">Submit</v-btn>
                                </div> 
                                <hr>                              
                                <div class="row" v-if="exam_schedule_list.length">
                                    <div class="col-md-2">                        
                                        <v-dialog full-width lazy ref="date_ref_3" width="290px" class="m-r-10">
                                            <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="dialogItem.search_date_from"></v-text-field>
                                            <v-date-picker ref="picker_3" v-model="dialogItem.search_date_from"></v-date-picker>
                                        </v-dialog>
                                    </div>
                                    <div class="col-md-2">
                                        <v-dialog full-width lazy ref="date_ref_4" width="290px" class="m-r-10">
                                            <v-text-field label="Date To" :clearable=true readonly slot="activator" v-model="dialogItem.search_date_to"></v-text-field>
                                            <v-date-picker ref="picker_4" v-model="dialogItem.search_date_to"></v-date-picker>
                                        </v-dialog>
                                    </div>
                                    <div class="col-md-2">
                                        <v-autocomplete :clearable=true v-model="dialogItem.search_batch" :items="batch" label="Batch"></v-autocomplete>
                                    </div>
                                    <div class="col-md-2">
                                        <v-autocomplete :clearable=true v-model="dialogItem.search_subject" :items="subject" label="Subeject"></v-autocomplete>
                                    </div>
                                    <div class="col-md-2">
                                        <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="search_schedule_exam_list"> Go </v-btn>
                                    </div>
                                </div>
                                
                                <div v-if="exam_record.length">
                                    <hr>
                                    <table class="table table-bordered fs-11 m-t-15">
                                        <thead>
                                            <tr>
                                                <th>Subeject</th>
                                                <th>Chapter</th>
                                                <th>Topic</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(list, i) in exam_record">
                                                <td :class="{'exam_color': list.schedule_type == 'exam'}">{{list.subjects.name}}</td>
                                                <td>{{list.chapter_text}}</td>
                                                <td>{{list.topic}}</td>
                                                <td>{{list.date | moment("D MMM YY")}}</td>
                                            </tr>
                                        </tbody>
                                    </table>                                    
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
                schedule_type: [ {'value': 'class', 'text': 'Class'} , {'value': 'exam', 'text': 'Exam'} , {'value': 'close', 'text': 'Close'},  {'value': 'online_class', 'text': 'Online Class'}, {'value': 'online_exam', 'text': 'Online Exam'}],
                apply_by: [ {'value': 'subject', 'text': 'Subject'} , {'value': 'period', 'text': 'Period'} ],
                day: [ {'value': 'sat', 'text': 'Sat'} , {'value': 'sun', 'text': 'Sun'} , {'value': 'mon', 'text': 'Mon'} , {'value': 'tue', 'text': 'Tue'} , {'value': 'wed', 'text': 'Wed'} , {'value': 'thu', 'text': 'Thu'} , {'value': 'fri', 'text': 'Fri'} ], // @list_search_data
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
                dialogItem: {id: '', name: '', branch_id: '', echelon_id: '', batches: [], date_from: '', date_to: '', vacation_range: [], apply_by: 'subject' },
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
                exam_schedule_list: [],
                exam_record: [],
                chapter: [],
                original_chapter: [],
                teacher: [],
                original_teacher: [],
                date_from_dialog: false,
                date_to_dialog: false
            } //end return
        }
        , // @router_permission
        beforeRouteEnter (to, from, next) {
            let p=window_admin_permissions.findIndex(x=> x.name=='edit_schedule');
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
                    this.admin_batch_list()
                    setTimeout(function () { this.teacher_list() }.bind(this), 2000)
                    
                }
                this.branch=this.branch.map(arr=> {return {value: arr['id'], text: arr['name'] } } )
            },
            admin_echelon_list() {
                this.echelon=window_echelons.map(arr=> {return {value: arr['id'], text: arr['name'] } } ) 
            },
            admin_batch_list() {
                this.axios.get('/admin/request/batch_list_by_branch/'+this.dialogItem.branch_id).then(response=> {
                    this.original_batch=response.data.batch_list_by_branch;
                    this.batch=response.data.batch_list_by_branch;
                    this.batch=this.batch.map(arr=> {return {value: arr['id'], text: arr['name'] } } )
                    this.admin_subject_list() 
                    this.get_subject()
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
                });
            },
            get_subject(){
                var echelon_id = this.dialogItem.echelon_id
                if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
                var subject = Object.assign([], this.original_subject);
                let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
                this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })   
            },
            teacher_list(){
              this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                  this.original_teacher = response.data.teacher_list_by_branch;
                  this.teacher = response.data.teacher_list_by_branch;
                  this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
              }); 
            },
            get_schedule_exam_list() {
                this.dialog = true 
                if (!this.dialogItem.date_from) {
                    this.dialogItem.date_from = this.$moment().clone().startOf('month').format('YYYY-MM-DD')
                    this.dialogItem.date_to = this.$moment().clone().endOf('month').format('YYYY-MM-DD');
                }
                this.axios.post('/admin/schedule/get_schedule_exam_list', this.dialogItem).then(response=> {
                    this.exam_schedule_list = response.data.daily_schedule
                    for (var i = 0; i < this.exam_schedule_list.length; i++) {
                        if (this.exam_schedule_list[i].teacher_attendance_status == 'done') {
                            this.exam_schedule_list[i].teacher_done = true
                        } else {
                            this.exam_schedule_list[i].teacher_done = false
                        }
                    }
                    this.dialog = false
                    $('.user_nav').addClass('successful') 
                    setTimeout(function () {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error=> {
                    this.dialog = false 
                    $('.user_nav').addClass('denied') 
                    setTimeout(function () {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            },
            search_schedule_exam_list() {
                this.dialog = true                
                this.axios.post('/admin/schedule/search_schedule_exam_list', this.dialogItem).then(response=> {
                    this.exam_record = response.data.daily_schedule
                    this.dialog = false
                    $('.user_nav').addClass('successful') 
                    setTimeout(function () {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error=> {
                    this.dialog = false 
                    $('.user_nav').addClass('denied') 
                    setTimeout(function () {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            },
            save_schedule_exam_list() {
                this.dialog = true 
                var item = {}
                item.exam_list = this.exam_schedule_list
                item.apply_batch = this.dialogItem.apply_batch
                item.apply_by = this.dialogItem.apply_by
                this.axios.post('/admin/schedule/save_schedule_exam_list', item).then(response=> {
                    $('.user_nav').addClass('successful')
                    this.dialog = false 
                    setTimeout(function () {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error=> {
                    this.dialog = false 
                    $('.user_nav').addClass('denied') 
                    setTimeout(function () {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
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
  .add_exam .v-alert{
      position: fixed;
      z-index: 1;
      right: 10px;
      top: 70px;
      width: 300px;
      height: 50px;
  }
  .add_exam .v-text-field label, .v-text-field input {
    font-size: 14px;
  }
  .add_exam #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .add_exam .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .add_exam .v-input{
    font-size: 14px;
  }
  .add_exam .v-btn{
    height: 32px;
  }
</style>