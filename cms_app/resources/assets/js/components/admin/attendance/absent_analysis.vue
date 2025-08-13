<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="absent_analysis">
        <v-container fluid >
            <!-- @page_headline -->
            <h3 class="headline">Absent Analysis</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 lecture_card">
                    <v-container fluid>
                        <h4 class="fw-600 dash_card_title">Absent for Last {{dialogItem.days}} Days</h4>
                        <hr class="m-t-5 m-b-5">
                        <v-layout style="width: 40%" class="m-b-10">
                            <v-autocomplete @change="filter_student_absent" class="m-r-15" :clearable=true :disabled="echelon.length==0" :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete> 
                            <v-text-field class="m-r-15" type="number" hide-details label="Count of Days" single-line v-model="dialogItem.days"></v-text-field>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_student_absent_list">Go</v-btn>
                        </v-layout>
                        
                        <div class="m-b-10" v-for="(student, st_i) in student_absent_list">
                            <div class="fs-16 fw-600 m-b-5">{{student.echelon_name}}</div>
                            <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="fs-13">Reg No</th>
                                    <th class="fs-13">Name</th>
                                    <th class="fs-13">Batch</th>
                                    <th class="fs-13">Mobile</th>
                                    <th class="fs-13">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(std, b_i) in student.students">
                                    <td class="fs-13 fw-600">{{std.reg_no}}</td>
                                    <td class="fs-13">{{std.name}}</td>
                                    <td class="fs-13">{{std.batch_name}}</td>
                                    <td class="fs-13">{{std.mobile}} <span v-if="std.mobile2">, {{std_mobile2}}</span></td>
                                    <td class="fs-13">
                                        <v-btn class="tiny-btn m-0" color="primary" @click="get_dialog_item('communicate', std.student_id, st_i, b_i)">Communicate</v-btn>
                                        <v-btn class="tiny-btn m-0" color="error" @click="get_dialog_item('drop', std.student_id, st_i, b_i)">Drop</v-btn>
                                    </td>
                                </tr>
                            </tbody>
                            </table>
                            <hr class="m-b-5 m-t-5">
                        </div>  
                    </v-container>
                </div>
            </template>
        </v-container>
        <!-- dialog -->
        <v-dialog max-width="600px" persistent v-model="edit_dialog">
            <v-card>
                <!-- @add_item_field -->
                <template>
                    <v-container grid-list-md>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <h4>{{dialog_title}}<v-icon @click="edit_dialog=false" class="float-right fs-20">close</v-icon></h4>
                            <hr class="m-t-5 m-b-5">
                            <v-select v-if="dialog_item=='communicate'" :clearable="true" :items="attendance_status" hide-selected label="Absent Status" v-model="dialogItem.attendance_status" :rules="[v => !!v || 'required']">
                            </v-select>
                            <v-dialog v-model="assume_date_dialog" v-if="dialog_item=='communicate'" full-width lazy ref="date_ref" width="290px">
                                <v-text-field label="Assume Absent Date" :clearable=true readonly slot="activator" v-model="dialogItem.assume_absent_date" :rules="[v => !!v || 'required']"></v-text-field>
                                <v-date-picker @change="assume_date_dialog=false" ref="picker" v-model="dialogItem.assume_absent_date"></v-date-picker>
                            </v-dialog>
                            <v-select v-if="dialog_item=='drop'" :clearable="true" :items="month" hide-selected label="Last Month of Payment" v-model="dialogItem.month" :rules="[v => !!v || 'required']">
                            </v-select>
                            <v-text-field v-if="dialog_item=='drop'" class="m-r-15" type="number" hide-details label="Year" single-line v-model="dialogItem.year" :rules="[v => !!v || 'required']"></v-text-field>
                            <v-select v-if="dialog_item=='drop'" :clearable="true" :items="drop_reason" hide-selected label="Drop Reason" v-model="dialogItem.drop_reason" :rules="[v => !!v || 'required']">
                            </v-select>
                            <!-- @add_item_submit -->
                            <v-btn :disabled="!valid" small color="success m-t-10" @click="save">submit</v-btn>
                        </v-form>
                    </v-container>
                </template>
            </v-card>
        </v-dialog>
        <!-- loader dialog -->
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
                    branch: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    echelon: [],
                    dialogItem: {                        
                        year: new Date().getFullYear(),
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },
                    student_absent_list: [],
                    origianl_student_absent_list: [],
                    attendance_status: [{value: 'exam', text: 'Exam'}, {value: 'travel', text: 'Go for Travel'}, {value: 'sickness', text: 'Sickness'}],
                    month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
                    edit_dialog: false,
                    dialog_item: '',
                    dialog_title: '',
                    drop_reason: [{value: 'not_understand', text: 'Not Understand'}, {value: 'move_out', text: 'Move Out'}, {value: 'keep_teacher', text: 'Keep Teacher'}, {value: 'syllabus_problem', text: 'Syllabus Problem'}, {value: 'other', text: 'Other'}],
                    current_echelon_index: '',
                    current_student_index: '',
                    valid: true,
                    assume_date_dialog: false                
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'attendance');
                if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.admin_branch_list();
                this.get_student_absent_list()
            },

            methods: {
                // @add_item_method
                admin_branch_list() {
                    this.branch = window_branch;
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list()
                    }
                    this.branch = this.branch.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                // @list_method                
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                get_student_absent_list() {
                    if (!this.dialogItem.days) {this.dialogItem.days = 3}
                    this.dialog = true
                    this.axios.get('/admin/attendance/get_student_absent_list/' + this.dialogItem.days).then(response => {
                        this.origianl_student_absent_list = response.data.students;
                        this.dialog = false
                        this.filter_student_absent()
                    });
                },
                filter_student_absent(){
                    var data = this.origianl_student_absent_list
                    var item = []
                    for (var i = 0; i < data.length; i++) {
                        var echelon_object = {echelon_id:'', echelon_name: '', students: [] }
                        var echelon_index
                        if (i == 0) {echelon_index = -1 } 
                        else {
                            echelon_index = item.findIndex(e => e.echelon_id == data[i].batches.echelon_id)
                        }
                        var student_object = {}
                        student_object.student_id = data[i].id
                        student_object.name = data[i].name
                        student_object.reg_no = data[i].reg_no
                        student_object.batch_name = data[i].batches.name
                        student_object.mobile = data[i].mobile
                        student_object.mobile2 = data[i].mobile2
                        student_object.echelon_id = data[i].batches.echelon_id
                        if (echelon_index == -1) {
                            item.push(echelon_object)
                            item[item.length - 1].echelon_name = data[i].batches.echelons.name
                            item[item.length - 1].echelon_id = data[i].batches.echelon_id
                            item[item.length - 1].students.push(student_object)
                        }
                        if (echelon_index > -1) {
                            item[echelon_index].students.push(student_object)
                        }
                    }
                    if (this.dialogItem.echelon_id) {
                        item = item.map(x => {
                            return {
                                echelon_id: x.echelon_id,
                                echelon_name: x.echelon_name,
                                students: x.students.filter(y=> y.echelon_id == this.dialogItem.echelon_id)
                            }
                        }).filter(xx => xx.students.length > 0)
                    }
                    item = item.sort(function(a,b){return a.echelon_id - b.echelon_id})
                    this.student_absent_list = item
                },
                get_dialog_item(item, id, st_i, b_i){
                    this.dialog_item = item
                    this.dialogItem.student_id = id
                    this.current_echelon_index = st_i
                    this.current_student_index = b_i
                    this.dialogItem.attendance_status = ''
                    this.dialogItem.assume_absent_date = ''
                    this.dialogItem.drop_reason = ''
                    this.edit_dialog = true
                    if (this.dialog_title=='drop') {this.dialog_title = 'Drop Detail'}
                    else {this.dialog_title = 'Communication Detail'}
                },
                save(){
                    if (!this.$refs.form.validate()) {return false}
                    this.dialog = true
                    this.axios.post('/admin/attendance/save_attendance_status', this.dialogItem).then(response => {
                        this.dialog = false
                        this.edit_dialog = false 
                        this.student_absent_list[this.current_echelon_index].students.splice(this.current_student_index, 1)
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {
                        $('.user_nav').removeClass('successful')
                        }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                        $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
                    });
                }
            }
    } 
</script>


<style>
  .absent_analysis .v-messages{
    min-height: 0px;
  }
  .absent_analysis .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .absent_analysis .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
  .absent_analysis .random_attendance .v-input--checkbox{
      margin-top: 14px;
  }
  .absent_analysis .random_attendance .v-input--checkbox label{
      margin-top: 4px;
      font-size: 15px!important;
  }
  .absent_analysis .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
</style>