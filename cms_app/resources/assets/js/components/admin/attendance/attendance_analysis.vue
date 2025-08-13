<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="attendance_analysis">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Attendance Report</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 lecture_card">
                    <v-container fluid>
                        <h4 class="fw-600 dash_card_title">Attendance of {{$moment(dialogItem.date).format('DD MMM YY')}}
                            <v-btn small class="mid-btn m-l-20" @click="send_sms_dialog = true" style="color: #fff; background: #607d8b">Attendance SMS</v-btn>
                        </h4>
                        <hr class="m-t-5 m-b-5">
                        <v-layout class="m-b-10">
                            <v-dialog v-model="date_dialog" full-width lazy ref="date_ref" width="290px">
                                <v-text-field label="Date" :clearable=true readonly slot="activator" v-model="dialogItem.date"></v-text-field>
                                <v-date-picker @change="date_dialog = false" ref="picker" v-model="dialogItem.date"></v-date-picker>
                            </v-dialog>
                            <v-autocomplete @change="filter_student_attendance('get_batch')" class="m-r-15 m-l-10" :clearable=true :disabled="echelon.length==0" :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete> 
                             <v-autocomplete @change="filter_student_attendance" class="m-r-15 m-l-10" :clearable=true :disabled="batch.length==0" :items="batch" label="Batch" v-model="dialogItem.batch_id"></v-autocomplete> 
                        </v-layout>
                        <v-layout>
                             <v-autocomplete @change="filter_student_attendance" class="m-r-15 m-l-10" :clearable=true :items="attendance" label="Attendance" v-model="dialogItem.attendance"></v-autocomplete> 
                             <v-text-field label="Search" @keyup.enter="filter_student_attendance" v-model="dialogItem.search" :clearable=true></v-text-field>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_student_attendance_list">Go</v-btn>
                        </v-layout>
                        
                        <div class="m-b-10" v-for="(batch, st_i) in student_attendance_list">
                            <div class="fs-16 fw-600 m-b-5">{{batch.name}}</div>
                            <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="fs-13" style="max-width: 50px">Reg No</th>
                                    <th class="fs-13">Name</th>
                                    <th class="fs-13">Mobile</th>
                                    <th class="fs-13">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(std, b_i) in batch.students">
                                    <td class="fs-13 fw-600">{{std.reg_no}}</td>
                                    <td :class="{'fs-13 fw-600': true, 'red-color': std.attendances.length && !std.attendances[0].attendance}">
                                        {{std.name}}
                                        <div class="fs-10" v-if="!std.latest_payments">
                                            <span style="color: #e91e63; font-weight: 600">Unpaid</span>
                                        </div>
                                        <div class="fs-10" v-if="std.latest_payments && std.latest_payments.unpaid_status != 'Paid'">
                                            <span style="color: #e91e63; font-weight: 600">{{std.latest_payments.unpaid_status}}</span>
                                            <span style="color: #e91e63; font-weight: 600" v-if="std.latest_payments.unpaid_status == 'Unpaid'">({{std.latest_payments.last_month_of_pay}})</span>
                                        </div>
                                    </td>
                                    <td class="fs-13">{{std.mobile}} <span v-if="std.mobile2">, {{std.mobile2}}</span></td>
                                    <td class="fs-13" v-if="std.attendances.length && std.attendances[0].attendance">Present</td>
                                    <td class="fs-13" v-else>Absent</td>
                                </tr>
                            </tbody>
                            </table>
                            <hr class="m-b-5 m-t-5">
                        </div>  
                    </v-container>
                </div>
            </template>
        </v-container>
        <!-- loader dialog -->
        <v-dialog v-model="dialog" hide-dialog persistent width="300">
            <v-card color="#009688" dark>
                <v-card-text>
                    Please Wait..
                    <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-dialog v-model="sms_dialog" persistent width="1000">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">SMS Report <v-icon class="pos-a" style="right: 15px;" @click="sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">
                    <div class="p-r-15 p-l-15">Current Balance: <span class="fw-600">{{parseFloat(sms_balance).toFixed(2)}}</span> <span class="float-right">SMS Failed: {{sms_failed.length}}/{{sms_report.length}}</span></div>                    
                    <v-container grid-list-md>
                        <table class="table table-bordered fs-12">
                            <thead>
                              <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>SMS</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="report in sms_report">
                                <td>{{report.reg_no}}</td>
                                <td>{{report.name}}</td>
                                <td>{{report.sms}}</td>
                                <td :class="{'text-danger': report.status !='OK'}">{{report.status}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-dialog v-model="send_sms_dialog" persistent width="1000">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">Send SMS <v-icon class="pos-a" style="right: 15px;" @click="send_sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">                 
                    <v-container grid-list-md>
                        <v-autocomplete class="" :items="sms_option" label="Type" v-model="dialogItem.sms_option"></v-autocomplete>
                        <v-textarea rows="2" counter v-model="dialogItem.sms_text" label="SMS"></v-textarea>
                        <v-btn small color="success" @click="send_sms">Send</v-btn>
                    </v-container>
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
                    batch:[],
                    dialogItem: {                        
                        year: new Date().getFullYear()
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },
                    student_attendance_list: [],
                    origianl_student_attendance_list: [],
                    valid: true,
                    date_dialog: false,
                    attendance: [{'value': 1, 'text': 'Present'}, {'value': 0, 'text': 'Absent'}], 
                    sms_option: [{'value': 'all', 'text': 'All'}, {'value': 'all_present', 'text': 'All Present'}, {'value': 'all_absent', 'text': 'All Absent'}, {'value': 'all_unpaid', 'text': 'All Unpaid'}, {'value': 'present_and_unpaid', 'text': 'Present & Unpaid'}, {'value': 'absent_and_unpaid', 'text': 'Absent & Unpaid'}],
                    send_sms_dialog: false,
                    sms_dialog: false,
                    sms_failed: [],
                    sms_report: [],
                    sms_balance: ''
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
                this.get_student_attendance_list()
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
                get_student_attendance_list() {
                    if (!this.dialogItem.date) {this.dialogItem.date = this.$moment().format('YYYY-MM-DD')}
                    this.dialog = true
                    this.axios.get('/admin/attendance/get_student_attendance_list/' + this.dialogItem.date).then(response => {
                        this.dialog = false
                        var data = response.data.batches
                        this.batch = data.map(x => { return {'value': x.id, 'text': x.name}})
                        for (var i = 0; i < data.length; i++) {
                            for (var j = 0; j < data[i].students.length; j++) {
                                if (data[i].students[j].latest_payments) {
                                    data[i].students[j].latest_payments.unpaid = true
                                    data[i].students[j].latest_payments.unpaid_status = 'Unpaid'
                                    data[i].students[j].latest_payments.last_month_of_pay = this.$moment(data[i].students[j].latest_payments.month).format('MMM YY')
                                    var now = this.$moment()
                                    var payment_month = this.$moment(data[i].students[j].latest_payments.month)
                                    if(now.diff(payment_month, 'months') < 1 && data[i].students[j].latest_payments.amount == data[i].students[j].latest_payments.paid) {
                                        data[i].students[j].latest_payments.unpaid = false
                                        data[i].students[j].latest_payments.unpaid_status = 'Paid'
                                    }
                                    if(now.diff(payment_month, 'months') < 1 && data[i].students[j].latest_payments.amount != data[i].students[j].latest_payments.paid) {
                                        data[i].students[j].latest_payments.unpaid = true
                                        data[i].students[j].latest_payments.unpaid_status = 'Due'
                                    }
                                }
                            }
                        }
                        this.origianl_student_attendance_list = data;
                        this.filter_student_attendance()
                    });
                },
                filter_student_attendance(get_batch){
                    var data = this.origianl_student_attendance_list.map(x => { return Object.assign({}, x) }); 
                    if (this.dialogItem.echelon_id) {
                        data = data.filter(xx => xx.echelon_id == this.dialogItem.echelon_id)
                        if (get_batch == 'get_batch') {
                            this.dialogItem.batch_id = ''
                            this.batch = data.map(x => { return {'value': x.id, 'text': x.name}})
                        }

                    }
                    if (this.dialogItem.batch_id) {
                        data = data.filter(xx => xx.id == this.dialogItem.batch_id)
                    }

                    data = data.sort(function(a,b){return a.echelon_id - b.echelon_id})
                    this.student_attendance_list = data
                    this.search_filter()
                },
                search_filter(){
                    var data = this.student_attendance_list
                    for (var i = 0; i < data.length; i++) {
                        if (this.dialogItem.search) {
                            data[i].students = data[i].students.filter(y => y.reg_no.toString().indexOf(this.dialogItem.search) > -1 || y.name.toLowerCase().indexOf(this.dialogItem.search.toLowerCase()) > -1)
                        }
                        if (this.dialogItem.attendance || this.dialogItem.attendance == 0) {
                            data[i].students = data[i].students.filter(z => z.attendances && z.attendances[0].attendance == this.dialogItem.attendance)
                        }
                    }
                    data = data.filter(x => x.students.length > 0)
                    this.student_attendance_list = data
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
                        this.student_attendance_list[this.current_echelon_index].students.splice(this.current_student_index, 1)
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
                },
                send_sms(){
                    if (!this.dialogItem.sms_text) {return false}
                    if (this.dialogItem.sms_text.length>159) {return alert('Sms length must be in 160 character')}
                    this.dialog = true
                    this.axios.post('/admin/attendance/send_sms', this.dialogItem).then(response => {
                        this.dialog = false
                        if (response.data.sms == 'sms') {
                            this.sms_report = response.data.sms_report
                            this.sms_balance = response.data.balance
                            this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                            this.send_sms_dialog = false
                            this.sms_dialog = true   
                        }
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {
                            $('.user_nav').removeClass('successful')
                        }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
            }
    } 
</script>


<style>
  .attendance_analysis .v-messages{
    min-height: 0px;
  }
  .attendance_analysis .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .attendance_analysis .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
  .attendance_analysis .random_attendance .v-input--checkbox{
      margin-top: 14px;
  }
  .attendance_analysis .random_attendance .v-input--checkbox label{
      margin-top: 4px;
      font-size: 15px!important;
  }
  .attendance_analysis .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
  .attendance_analysis .red-color {
    color: #ff5722;
  }
</style>