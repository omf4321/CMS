<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="individual_attendance">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Individual Attendance Report</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15 lecture_card">
                    <v-container fluid>
                        <div class="row">
                            <div class="col-md-3 m-b-10">
                                <v-dialog class="m-r-10" v-model="date_from_dialog" full-width lazy ref="date_ref_from" width="290px">
                                    <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="dialogItem.date_from"></v-text-field>
                                    <v-date-picker @change="date_from_dialog = false" ref="picker" v-model="dialogItem.date_from"></v-date-picker>
                                </v-dialog>
                            </div>
                            <div class="col-md-3">
                                <v-dialog class="m-r-10" v-model="date_to_dialog" full-width lazy ref="date_ref_to" width="290px">
                                    <v-text-field label="Date To" :clearable=true readonly slot="activator" v-model="dialogItem.date_to"></v-text-field>
                                    <v-date-picker @change="date_to_dialog = false" ref="picker" v-model="dialogItem.date_to"></v-date-picker>
                                </v-dialog>
                            </div>
                            <div class="col-md-3">
                                <v-layout>
                                    <v-text-field @keyup.enter="get_individual_student_attendance" label="Reg No" v-model="dialogItem.reg_no" :clearable=true></v-text-field>
                                    <v-btn :disabled="!dialogItem.reg_no" small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_individual_student_attendance">Go</v-btn> 
                                </v-layout>
                            </div>     
                        </div>                    
                        <hr>
                        <div v-if="found_student">
                            <div class="col-xs-12 fs-xs m-b-5">Name: {{student.name}}</div>
                            <div class="col-xs-12 fs-xs m-b-5">Reg No: {{student.reg_no}}</div>
                            <div class="col-xs-12 fs-xs m-b-5">Mobile: {{student.mobile}} <span v-if="student.mobile2">, {{student.mobile2}}</span></div>
                            <div class="col-xs-12 fs-xs m-b-5">Date of Admin: {{student.date_of_admit | moment("D MMM YY")}}</div>
                        </div>
                        <div v-if="show_no_found" class="text-danger">No Student Found</div>
                        <div style="float:none; clear: both"></div>
                        <hr v-if="found_student">
                        <div class="m-b-10" v-if="student_attendance_list.length">
                            <table class="table table-bordered" style="max-width: 500px">
                            <thead>
                                <tr>
                                    <th class="fs-13 fit" style="max-width: 50px">Date</th>
                                    <th class="fs-13 fit">Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(attendance, b_i) in student_attendance_list">
                                    <td class="fs-13 fw-600 fit">{{attendance.date | moment("D MMM YY")}}</td>
                                    <td class="fs-13 fit"><v-checkbox readonly v-model="attendance.attendance"></v-checkbox></td>
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
    </div>
</template>
<script>
    export default {
        data() {
                return {                    
                    dialog: false,                    
                    dialogItem: {},
                    student_attendance_list: [],
                    date_from_dialog: false,
                    date_to_dialog: false,
                    student: {},
                    found_student: false,
                    show_no_found: false
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'edit_attendance');
                if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                
            },

            methods: {
                get_individual_student_attendance() {
                    if (!this.dialogItem.date_from) {
                        this.dialogItem.date_from = this.$moment().startOf('month').format('YYYY-MM-DD')
                        this.dialogItem.date_to = this.$moment().endOf('month').format('YYYY-MM-DD')
                    }
                    this.dialog = true
                    this.axios.post('/admin/attendance/get_individual_student_attendance', this.dialogItem).then(response => {
                        this.dialog = false
                        this.student = response.data.student
                        if (!this.student) {
                            this.found_student = false
                            this.show_no_found = true
                        } else {
                            this.found_student = true
                            this.show_no_found = false
                        }
                        this.student_attendance_list = response.data.attendances
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        alert(error.response.data.error)
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
            }
    } 
</script>


<style>
  .individual_attendance .v-messages{
    min-height: 0px;
  }
  .individual_attendance .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .individual_attendance .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
  .individual_attendance .random_attendance .v-input--checkbox{
      margin-top: 14px;
  }
  .individual_attendance .random_attendance .v-input--checkbox label{
      margin-top: 4px;
      font-size: 15px!important;
  }
  .individual_attendance .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
  .individual_attendance .red-color {
    color: #ff5722;
  }
  .individual_attendance .table td.fit, 
.table th.fit {
    white-space: nowrap;
    width: 1%;
}
</style>