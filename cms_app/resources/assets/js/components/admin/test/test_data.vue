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
        <v-container fluid class="daily_schedule">
            <template>
                <div>
                    <!-- Schedule Card -->
                    <v-card>
                        <h4 class="p-15 fs-20">Add Schedule</h4>
                        <v-card-text>
                            <v-container grid-list-md class="p-t-0">
                                <!-- @add_item_field -->
                                <template>
                                    <v-form lazy-validation ref="add_form" v-model="valid">
                                        <v-layout>
                                            <v-autocomplete :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id" class="m-r-10"></v-autocomplete>
                                            <template>
                                                <v-dialog class="m-r-10" full-width lazy ref="date_ref" width="290px">
                                                    <v-text-field :rules="[v => !!v || 'required']" label="Date" readonly slot="activator" v-model="dialogItem.date"></v-text-field>
                                                    <v-date-picker ref="picker" v-model="dialogItem.date">
                                                    </v-date-picker>
                                                </v-dialog>
                                            </template>
                                            <v-text-field hide-details label="Chapters" single-line v-model="dialogItem.chapters" class="m-r-10"></v-text-field>
                                            <v-combobox :clearable="true" :items="batch" hide-selected label="Batch" multiple ref="combobox" small-chips v-model="dialogItem.batches" class="m-r-10"></v-combobox>
                                            <v-combobox :clearable="true" :items="subject" hide-selected label="Subject" multiple ref="combobox_subject" small-chips v-model="dialogItem.subjects" class="m-r-10"></v-combobox>
                                        </v-layout>  
                                        <v-layout>
                                            <v-combobox :clearable="true" :items="teacher" hide-selected label="Teacher" multiple ref="combobox_teacher" small-chips v-model="dialogItem.teachers" class="m-r-10"></v-combobox>
                                            <v-autocomplete class="m-r-10" :items="schedule_type" label="Schedule Type" single-line v-model="dialogItem.schedule_type"> </v-autocomplete>
                                            <v-autocomplete class="m-r-10" :items="schedule_status" label="Schedule Status" single-line v-model="dialogItem.schedule_status"> </v-autocomplete>
                                            <v-autocomplete class="m-r-10" :items="record_type" label="Record Type" single-line v-model="dialogItem.record_type"> </v-autocomplete>
                                            <v-autocomplete class="m-r-10" :items="attendance_status" label="Attendance Status" single-line v-model="dialogItem.attendance_status"> </v-autocomplete>
                                        </v-layout>
                                        <v-layout>
                                            <v-autocomplete class="m-r-10" :items="content_type" label="Content Type" single-line v-model="dialogItem.content_type"> </v-autocomplete> 
                                            <label class="checkbox-inline m-l-20 p-t-10 m-r-10"><input type="checkbox" v-model="dialogItem.take_attendance">Take Attendance</label>
                                            <label class="checkbox-inline m-l-20 p-t-10 m-r-10"><input type="checkbox" v-model="dialogItem.teacher_done">Teacher Done</label>
                                            <label class="checkbox-inline m-l-20 p-t-10 m-r-10"><input type="checkbox" v-model="dialogItem.lecture_sheet">Lecture Sheet</label>
                                        </v-layout>                                  
                                        <!-- @add_item_submit -->
                                        <v-btn small color="primary" :disabled="!valid || loading" :loading="loading" @click="save('add_schedule')">Add Schedule</v-btn>
                                    </v-form>
                                </template>
                            </v-container>
                        </v-card-text>
                    </v-card>
                    <!-- student Card -->
                    <v-card>
                        <h4 class="p-15 fs-20">Add Student</h4>
                        <v-card-text>
                            <v-container grid-list-md class="p-t-0">
                                <!-- @add_item_field -->
                                <template>
                                    <!-- $reg_no, $size = 1, $status = 'present', $monthly_fee = true, $course_fee = false, $payment_status='paid', $payment_size= 1, $payment_type = 'unpaid', $payment_today = false -->
                                    <v-form lazy-validation ref="add_form" v-model="valid">
                                        <v-layout>
                                            <v-text-field label="Reg" required v-model="dialogItem.reg_no" class="m-r-10"></v-text-field>
                                            <v-text-field label="Student Size" required v-model="dialogItem.size" class="m-r-10"></v-text-field>
                                            <v-autocomplete :items="status" label="Status" v-model="dialogItem.status" class="m-r-10"></v-autocomplete>
                                            <v-autocomplete :items="fee" label="Fee Type" v-model="dialogItem.fee" class="m-r-10"></v-autocomplete>
                                            <v-text-field label="Payment Size" required v-model="dialogItem.payment_size" class="m-r-10"></v-text-field>
                                            <v-autocomplete :items="payment_status" label="Payment Status" v-model="dialogItem.payment_status" class="m-r-10"></v-autocomplete>
                                            <v-autocomplete :items="payment_type" label="Payment Type" v-model="dialogItem.payment_type" class="m-r-10"></v-autocomplete>
                                            <v-dialog class="m-r-10" full-width lazy ref="payment_date" width="290px">
                                                <v-text-field label="Date" readonly slot="activator" v-model="dialogItem.payment_date"></v-text-field>
                                                <v-date-picker ref="payment_date_picker" v-model="dialogItem.payment_date">
                                                </v-date-picker>
                                            </v-dialog>
                                        </v-layout>   
                                        <v-layout>
                                            <v-text-field label="Mobile" required v-model="dialogItem.mobile" class="m-r-10"></v-text-field>
                                            <v-autocomplete :items="student_add_type" label="Student Add Type" v-model="dialogItem.student_add_type" class="m-r-10"></v-autocomplete>
                                        </v-layout>                                    
                                        <!-- @add_item_submit -->
                                        <v-btn small color="primary" :disabled="!valid || loading" :loading="loading" @click="save('add_student')">Add Student</v-btn>
                                        <v-btn small color="info" @click="save('add_visiting_list')">Add Visiting List</v-btn>
                                    </v-form>
                                </template>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </div>
            </template>
        </v-container>
    </div>
</template>
<script>
    export default {
      data() {
              return {                
                branch: [],
                echelon: [],
                original_batch: [],
                batch: [],
                original_subject: [],
                subject: [],
                chapter: [],
                original_chapter: [],
                teacher: [],
                original_teacher: [],                
                month: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
                // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                pagination: {
                   page: 1,
                   rowsPerPage: -1, // -1 for All",
                   sortBy: 'date'
                },
                dialogItem: {
                    reg_no: 9101,
                    size: 1,
                    status: 'present',
                    fee: 'monthly_fee',
                    payment_status: 'paid',
                    payment_size: 1,
                    payment_type: 'unpaid', 
                    batches: [],
                    subjects: [],
                    teachers: [],
                    schedule_type: 'class',
                    record_type: 'new',
                    attendance_status: '',
                    lecture_sheet: false,
                    take_attendance: false,
                    teacher_done: false,
                    content_type: '',
                    student_add_type: 'new'
                }, 
                editedItem: '',     
                rules: {
                  required: value => !!value || 'Required.'
                },
                
                // @accesories_data
                valid: true,
                loading: false,
                status: [{value:'present', text:'Present'}, {value:'droped', text:'Droped'}, {value:'inactive', text:'Inactive'}],
                payment_status: [{value:'paid', text:'Paid'}, {value:'unpaid', text:'Unpaid'}],
                payment_type: [{value:'unpaid', text:'Unpaid'}, {value:'unpaid_multiple', text:'Unpaid Multiple'}, {value:'advance', text:'Advance'}, {value:'due', text:'Due'}],
                fee: [{value:'monthly_fee', text:'Monthly'}, {value:'course_fee', text:'Coursely'}],
                record_type: [{value:'new', text:'New Schedule'}, {value:'old', text:'Old Schedule'}],
                attendance_status: [{'value': 'completed', 'text': 'Completed'}, {'value': 'sms', 'text': 'SMS'}, {'value': 'both', 'text': 'Both'}],
                schedule_type: [{'value': 'class', 'text': 'Class'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'close', 'text': 'Close'}, {'value': 'online_class', 'text': 'Online Class'}, {'value': 'online_exam', 'text': 'Online Exam'}],
                schedule_status: [{'value': 'confirmed', 'text': 'Confirmed'}],
                content_type: [{'value': 'contented', 'text': 'Contented'}, {'value': 'discontented', 'text': 'Discontented'}],
                student_add_type: [{'value': 'new', 'text': 'New Student'}, {'value': 'old', 'text': 'Old Student'}],
             } //end return
      },
      // @router_permission
      beforeRouteEnter (to, from, next) {
         if (window_admin_role == 'superadmin') {next()} else {next('/')}
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
                this.admin_batch_list()
                this.teacher_list()
            }
            this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
        },
        admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
        },
        admin_batch_list(){
          this.axios.get('/admin/request/batch_list_by_branch/'+this.dialogItem.branch_id).then(response => {
              this.original_batch = response.data.batch_list_by_branch;
          }); 
        },
        // @add_item_method
        get_item(){
           if (this.branch.length>1) {
               this.admin_echelon_list(this.dialogItem.branch_id);
           }
        },
        // @list_method
        admin_subject_list(){
          this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
              this.original_subject = response.data.subject_list_by_branch;
          });
        },
        chapter_list(){
            this.axios.get('/admin/request/chapter_list_by_class/' + this.dialogItem.echelon_id).then(response => {
                this.original_chapter = response.data.chapter_list_by_class;
            }); 
        },
        teacher_list(){
          this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
              this.original_teacher = response.data.teacher_list_by_branch;
              this.teacher = response.data.teacher_list_by_branch;
              this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
          }); 
        },
        schedule_list(){
            this.axios.get('/admin/schedule/daily_schedule_list').then(response => {
                this.original_schedule = response.data.schedule_list
            }); 
        },
        
        get_subject(){
           var echelon_id = this.dialogItem.echelon_id
           if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
           var subject = Object.assign([], this.original_subject);
           let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
           this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })   
        },
        
        get_batch(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
           this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
           this.get_subject()
        },
        
        save(action){
           this.dialogItem.action_type = action
           this.axios.post('/admin/test/save_test_data', this.dialogItem).then(response => {
                 this.loading = false
                 this.dialog = false
                 $('.user_nav').addClass('successful')
                 setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                 this.loading = false
                 $('.user_nav').addClass('denied')
                 setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
        },
        get_branch(){
           if (this.branch.length==1) {
               this.dialogItem.branch_id=this.branch[0].value
           }
        },
        change_chapter(){
           this.editedItem.chapters.name = this.chapter[this.chapter.findIndex(x=>x.value == this.editedItem.chapter_id)].text
        },
        change_subject(){
           this.editedItem.subjects.name = this.subject[this.subject.findIndex(x=>x.value == this.editedItem.subject_id)].text
        },
        close () {
          // this.dialog = false
          this.add_dialog = false
          setTimeout(function () { this.swich = false; this.dialogItem.date_list = []; this.$refs.add_form.reset() }.bind(this), 1000)
          
        },
      }
    }

</script>

<style>


.v-text-field label,
.v-text-field input {
    font-size: 14px;
}

#edit_class_no {
    color: #FF5722!important;
    font-weight: 500;
    font-size: 30px;
}

.v-text-field {
    padding-top: 2px;
    margin-top: 2px;
}

.v-input {
    font-size: 14px;
}

.v-input--checkbox label {
    font-size: 14px;
}

.v-btn {
    height: 32px;
}

.chapter_next_prev {
    position: absolute;
    top: -15px;
    right: 23px;
}
.v-datatable .v-input--checkbox .v-input--selection-controls__input i {
    font-size: 20px;
    position: relative;
    top: 10px;
}
.v-datatable .v-input--checkbox .v-input--selection-controls__ripple {
    top: -2px;
    z-index: 99;
}

.v-dialog .v-list--two-line .v-list__tile {
    height: 50px;
}
.exchange_index{
    position: absolute;
    top: 5px;
    right: 0px;
    background-color: #00BCD4;
    padding: 2px 9px;
    border-radius: 50%;
    font-size: 14px;
    color: #fff;
}

</style>