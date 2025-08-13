<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="attendance_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Attendance <v-icon class="m-l-10 cur-p float-right pos-rel" style="top: 3px" @click="delete_idb">refresh</v-icon></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- attendance list -->
                    <div class="card" v-if="step=='attendance'">
                        <v-container>
                            <div class="admin_home">
                                <h4 class="fw-600 dash_card_title">Attendance ({{current_date | moment("D MMM YY")}}) 
                                    <v-icon class="m-l-10 cur-p pos-rel" style="top: 3px" @click="attendance_list">refresh</v-icon>
                                    <v-btn small color="primary" class="float-right" @click="get_random_attendance">Random Attendance</v-btn>
                                </h4>
                                <hr class="m-t-5 m-b-5" style="float: none; clear: both">
                                <!-- search option -->
                                <v-layout style="width: 80%" class="m-b-15">
                                    <v-autocomplete class="m-r-15" :clearable=true :disabled="echelon.length==0" :items="echelon" label="Class" v-model="dialogItem.echelon_id" @change="filter_attendance"></v-autocomplete>
                                    <v-text-field class="m-r-15" hide-details label="Time" single-line v-model="dialogItem.time" :clearable=true></v-text-field>
                                    <v-text-field class="m-r-15" hide-details label="Reg No" single-line v-model="dialogItem.find_reg_no" :clearable=true></v-text-field>
                                    <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="filter_attendance">Go</v-btn>
                                </v-layout>
                            </div>
                            <!-- attendance of single student -->
                            <table class="table table-bordered pos-rel" v-if="single_student_attendance" style="width: 80%">
                                <tbody>
                                    <tr>
                                        <td>{{single_student_attendance.id}}</td>
                                        <td>{{single_student_attendance.reg_no}}</td>
                                        <td>{{single_student_attendance.name}}</td>
                                        <td>{{single_student_attendance.todays_attendance && single_student_attendance.todays_attendance[0].attendance ? 'Present' : 'Absent'}}</td>
                                        <td class="text-center"><v-icon class="fs-20" @click="single_student_attendance=''">close</v-icon></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-for="(schedule, s_i) in schedule_of_batch">
                                <div class="fs-16 fw-600" style="border-bottom: 1px solid #8d8d8d;">{{schedule.echelons.name}}</div>
                                <div>
                                    <div class="col-md-2 m-r-8 card m-t-8 m-b-8 p-t-8 p-b-8" v-for="(batch, b_i) in schedule.batch"> 
                                        <div class="row">
                                            <div class="fs-13 fw-600 col-md-6" style="color: #444"><div>{{batch.name}}</div></div>
                                            <div class="col-md-6 text-right">
                                                <v-icon color="primary" class="fs-17 cur-p m-r-10" @click="get_batch_attendance(batch.id, batch.name, s_i, b_i)">edit</v-icon>
                                                <v-icon v-if="batch.attendance_lists.length>0" color="error" class="fs-17 cur-p" @click="delete_batch_attendance(batch.id, s_i, b_i)">delete</v-icon>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="m-b-10" style="float: none; clear: both"></div>
                            </div>
                        </v-container>
                    </div>                    
                    <!-- attednace of a batch student -->
                    <div class="card m-t-10" v-if="step=='batch_attendance'">
                        <v-container>
                            <h4 class="m-b-20 fw-600">Attendance of {{current_batch_name}} <span class="float-right cur-p" @click="step='attendance'"><v-icon>close</v-icon></span></h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Reg No</th>
                                        <th>Name</th>
                                        <th class="pos-rel">Attendance <span class="pos-a" style="top: 5px; left: 100px"><v-checkbox @change="change_check_all" v-model="check_all"></v-checkbox></span><span class="fs-13 pos-a" style="top: 8px; left: 130px">Check All</span></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(student, st_i) in batch_student_list">
                                        <td>{{student.student_id}}</td>
                                        <td>{{student.reg_no}}</td>
                                        <td>{{student.name}}</td>
                                        <td><v-checkbox v-model="student.attendance"></v-checkbox></td>
                                    </tr>
                                </tbody>
                            </table>  
                            <v-btn class="float-right" color="success" @click="save_batch_attendance">Submit</v-btn>
                        </v-container>
                    </div>
                    <!-- random attendance -->
                    <div class="card admin_home random_attendance m-t-10" v-if="step=='random_attendance'">
                        <v-container>
                            <h4 class="m-b-20 fw-600">Random Attendance <span class="float-right cur-p pos-rel" style="top: -3px" @click="close_random_attendance"><v-icon>close</v-icon></span><span class="m-r-20 float-right pos-rel" style="top: -3px"><v-icon class="m-r-5 cur-p" :disabled="new_random_attendance.length==0" @click="save_random_attendance">save</v-icon>{{new_random_attendance.length}}</span></h4>
                            <v-layout>
                                <v-text-field id="rag_no" v-on:keyup.enter="find_student" hide-details label="Reg No" single-line v-model="dialogItem.reg_no" :clearable=true type="number"></v-text-field>
                                <v-btn small outline class="tiny-btn pos-rel m-l-10" style="top: 12px" @click="find_student">Go</v-btn>
                                <v-checkbox class="m-l-25" v-model="random_attendance_type" :label="random_attendance_type ? 'Present' : 'Absent'"></v-checkbox>
                            </v-layout>
                            <div class="m-t-5" style="color:#ff5722" v-if="find_error">{{find_error}}</div>
                            <div class="m-t-10">
                                <div :class="{'col-md-2':true, 'card':true, 'light_green_background': student.attendance, 'light_red_background': student.payment_status == 'unpaid', 'light_yellow_background': student.payment_status == 'due', 'light_blue_background': student.payment_status == 'late_pay'}" style="margin: 8px; padding: 8px 15px; position: relative" v-for="(student, st_i) in random_attendance"> 
                                    <v-icon class="fs-20 cur-p pos-a" style="right: 10px" @click="delete_random_attendance(st_i)">close</v-icon>
                                    <div class="fs-14 fw-600">{{student.reg_no}} <span v-if="student.payment_status == 'due'" class="m-l-5">({{student.payment_status}} - {{student.payment_amount}})</span></div>
                                    <div class="fs-13">{{student.name}}</div>
                                    <div class="fs-12">{{student.batches.name}}</div>
                                </div>
                            </div>
                            <div class="m-b-10" style="float: none; clear: both"></div>
                        </v-container>
                    </div>
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
                    branch: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        name: '',
                        class_no: '',
                        branch_id: '',
                        description: '',
                        created_at: ''
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },
                    schedule_of_batch: [],
                    original_schedule_of_batch: [],
                    echelon: [],
                    current_date: '',
                    batch_student_list: [],
                    step: 'attendance',
                    current_batch_id: '',
                    current_batch_index: '',
                    current_batch_name: '',
                    current_schedule_index: '',
                    check_all: false,
                    all_student_list: [],
                    random_attendance: [],
                    find_error: '',
                    new_random_attendance: [],
                    random_attendance_type: true,
                    single_student_attendance: ''

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                if (window_latest_unpaid.overdue == 'true') {
                    next('/billing_invoice')
                }
                let p = window_admin_permissions.findIndex(x => x.name == 'edit_attendance');
                if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.admin_branch_list();
            },

            methods: {
                // @add_item_method
                admin_branch_list() {
                    this.branch = window_branch
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list()
                        this.attendance_list();
                    }
                    this.branch = this.branch.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                // @list_method
                attendance_list() {
                    this.dialog = true
                    // this.check_idb()
                    // var date = this.$moment(new Date()).format('YYYY-MM-DD');
                    // this.current_date = date
                    this.attendance_list_from_database()
                    // this.save_random_from_cookies()
                },
                attendance_list_from_database(){
                    var date = this.$moment(new Date()).format('YYYY-MM-DD');
                    this.axios.get('/admin/attendance/get_attendance_schedule/' + date).then(response => {
                        this.current_date = date
                        this.original_schedule_of_batch = response.data.schedule_of_batch;
                        this.dialog = false
                        this.filter_attendance()
                        // this.initialize_idb('true')
                        this.save_random_from_cookies()
                    });
                },
                save_random_from_cookies(){
                    var unsaved_random_attendance = this.$cookies.get('unsaved_random_attendance')
                    if (unsaved_random_attendance) {
                        var x = JSON.parse(unsaved_random_attendance)
                        if (x.length) {
                            this.new_random_attendance = x
                            this.save_random_attendance()
                        }
                    }
                },
                filter_attendance(){
                    if (this.dialogItem.find_reg_no) {
                        this.arrange_student_list()
                        var index = this.all_student_list.findIndex(x => x.reg_no == this.dialogItem.find_reg_no)
                        if (index==-1) {return false}
                        this.single_student_attendance = this.all_student_list[index]                     
                        return false
                    }
                    else {this.single_student_attendance = ''}
                    var item = []
                    var echelon_index = -1
                    for (var i = 0; i < this.original_schedule_of_batch.length; i++) {
                        if (i>0) {
                            echelon_index = item.findIndex(x => x.echelons.id == this.original_schedule_of_batch[i].echelons.id)
                        }
                        var object = {}
                        object.echelons = this.original_schedule_of_batch[i].echelons
                        object.batch = []
                        if (echelon_index==-1) {
                            item.push(object)
                            item[item.length-1].batch.push(this.original_schedule_of_batch[i])
                        }
                        else {
                            item[echelon_index].batch.push(this.original_schedule_of_batch[i]) 
                        }
                    }
                    item = item.sort(function(a,b){return a.echelons.id - b.echelons.id})
                    if (this.dialogItem.echelon_id) {
                        item = item.filter(x => x.echelons.id == this.dialogItem.echelon_id)
                    }
                    if (this.dialogItem.time) {
                        var time = this.dialogItem.time.split('-')
                        var from_time = this.$moment(time[0], "HH:mm").format("HH:mm");
                        if (time.length > 1) {
                            var to_time = this.$moment(time[1], "HH:mm").format("HH:mm");
                        } else {
                            var to_time = this.$moment('23:59', "HH:mm").format("HH:mm");
                        }
                        item = item.map(x => {
                            return {
                                echelons: x.echelons,
                                batch: x.batch.filter(y => y.time >= from_time && y.time <= to_time)
                            }
                        }).filter(yy => yy.batch.length > 0)
                    }
                    this.schedule_of_batch = item
                    console.log(item)
                },
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                get_batch_attendance(batch_id, batch_name, s_i, b_i) {
                    this.current_batch_id = batch_id
                    this.current_batch_name = batch_name
                    this.current_batch_index = b_i
                    this.current_schedule_index = s_i
                    this.check_all = false
                    this.dialog = true
                    this.axios.post('/admin/attendance/get_batch_attendance/' + batch_id).then(response => {
                        this.batch_student_list = response.data.student_list
                        var item = this.batch_student_list.map(x => {return {
                            attendance_id: x.todays_attendance[0].id,
                            student_id: x.id,
                            reg_no: x.reg_no,
                            name: x.name,
                            attendance: x.todays_attendance[0].attendance,
                            batch_id: x.batch_id,
                            attendance_list_id: x.todays_attendance[0].attendance_list_id
                        }})
                        item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                        this.batch_student_list = item
                        this.dialog = false
                        this.step = 'batch_attendance'
                    }, error => {
                        if (error.response.status == 419) {
                            alert('Session Expired. This page is going to reload')
                            location.reload();
                        }
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                save_batch_attendance(){
                    this.dialog = true
                    var item = {}
                    item.batch_id = this.current_batch_id
                    item.student_attendance = this.batch_student_list
                    this.axios.post('/admin/attendance/save_batch_attendance', item).then(response => {
                        this.step = 'attendance'
                        this.dialog = false
                        this.schedule_of_batch[this.current_schedule_index].batch[this.current_batch_index].attendance_lists = []
                        this.schedule_of_batch[this.current_schedule_index].batch[this.current_batch_index].attendance_lists.push('has_data')
                        this.initialize_idb()
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
                delete_batch_attendance(batch_id, s_i, b_i){
                    this.current_batch_id = batch_id
                    this.current_batch_index = b_i
                    this.current_schedule_index = s_i
                    var confirmation = confirm("Are you sure?")
                    if (!confirm) {return false}
                    this.dialog = true
                    this.axios.post('/admin/attendance/delete_batch_attendance/' + batch_id).then(response => {
                        this.step = 'attendance'
                        this.dialog = false
                        this.schedule_of_batch[this.current_schedule_index].batch[this.current_batch_index].attendance_lists=[]
                        this.initialize_idb()
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {
                        $('.user_nav').removeClass('successful')
                        }.bind(this), 3000)
                    }, error => {
                        alert(error.response.data)
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                        $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
                    });
                },
                change_check_all(){
                    var value = this.check_all
                    for (var i = 0; i < this.batch_student_list.length; i++) {
                        this.batch_student_list[i].attendance = value
                    }
                },
                get_random_attendance(){
                    this.step = 'random_attendance'
                    this.random_attendance = []
                    this.new_random_attendance = []
                    this.arrange_student_list()
                },
                arrange_student_list(){
                    var item = []
                    for (var i = 0; i < this.original_schedule_of_batch.length; i++) {
                            if(this.original_schedule_of_batch[i].students){
                                for (var j = 0; j < this.original_schedule_of_batch[i].students.length; j++) {
                                    item.push(this.original_schedule_of_batch[i].students[j])
                                }
                            }
                    }
                    this.all_student_list = item
                },
                find_student(){
                    this.find_error = ''
                    var index = this.all_student_list.findIndex(x => x.reg_no == this.dialogItem.reg_no)
                    if (index==-1) {
                        this.find_error = "No student found"
                    }
                    else {
                        var object = {}
                        object.student_id = this.all_student_list[index].id
                        object.reg_no = this.all_student_list[index].reg_no
                        object.payment_status = this.all_student_list[index].payment_status
                        object.payment_amount = this.all_student_list[index].payment_amount
                        object.next_payment_date = this.all_student_list[index].next_payment_date
                        object.name = this.all_student_list[index].name
                        object.batches = this.all_student_list[index].batches
                        object.batch_id = this.all_student_list[index].batches.id
                        object.attendance = this.random_attendance_type
                        this.random_attendance.unshift(object)
                        this.new_random_attendance.push(object)
                        if (this.new_random_attendance.length>4) {
                            this.save_random_attendance()
                        }
                        var x = JSON.stringify(this.new_random_attendance)
                        this.$cookies.set('unsaved_random_attendance', x);
                    }
                    this.dialogItem.reg_no = ''
                },
                delete_random_attendance(st_i){
                    if (!this.new_random_attendance.length) {
                        this.new_random_attendance.push(this.random_attendance[st_i])
                        this.new_random_attendance[this.new_random_attendance.length-1].attendance = !this.new_random_attendance[this.new_random_attendance.length-1].attendance                        
                        this.save_random_attendance()
                    }
                    else {
                        this.new_random_attendance.splice(st_i, 1)
                        var x = JSON.stringify(this.new_random_attendance)
                        this.$cookies.set('unsaved_random_attendance', x);
                    }
                    this.random_attendance.splice(st_i, 1)                    
                },
                save_random_attendance(){
                    if (!this.new_random_attendance.length) {return false}
                    var item = {}
                    item.random_attendance = this.new_random_attendance
                    this.axios.post('/admin/attendance/save_random_attendance', item).then(response => {
                        var ids = response.data.ids
                        for (var i = 0; i < ids.length; i++) {
                            var index = this.new_random_attendance.findIndex(x => x.student_id == ids[i])
                            if (index>-1) {this.new_random_attendance.splice(index,1)}
                        }
                        var x = JSON.stringify(this.new_random_attendance)
                        this.$cookies.set('unsaved_random_attendance', x);
                        this.initialize_idb()
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {
                        $('.user_nav').removeClass('successful')
                        }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                        $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
                    });
                },
                close_random_attendance(){
                    if (this.new_random_attendance.length>0) {this.save_random_attendance()}
                    this.step = 'attendance'
                },
                delete_idb(){
                    if (!window_idb) {return false}
                    var date = new Date();
                    var c_date = new Date().getDate()
                    window.indexedDB.deleteDatabase("attendanceDatabase_" + c_date);
                },
                check_idb(){
                    if (!window_idb) {this.attendance_list_from_database(); return false}
                    var data = []
                    var data_status = false
                    var idb;
                    var date = new Date();
                    var c_date = date.getDate()
                    // window.indexedDB.deleteDatabase("studentDatabase");
                    var idb_request = window.indexedDB.open("attendanceDatabase_" + c_date, 1);
                    idb_request.onsuccess = function(event) {
                        idb = idb_request.result;
                        // read all
                        var objectStore = idb.transaction("attendance").objectStore("attendance");
                        var x = objectStore.getAll();
                        x.onsuccess = function() {
                            data_status = true
                            data = x.result
                        };
                    };
                    var i = 0
                    var interval = setInterval(function() {
                        if (data_status) {
                            this.original_schedule_of_batch = data; 
                            this.filter_attendance()
                            clearInterval(interval); 
                            this.dialog = false
                        }
                        if (i == 10) {
                            clearInterval(interval); 
                            this.attendance_list_from_database()
                        }
                        i++
                    }.bind(this), 500)
                },
                initialize_idb(on_load){
                    if (!window_idb) {return false}
                    // const studentDate = this.rows
                    var data = this.original_schedule_of_batch
                    var data_status = false
                    var idb;
                    var date = new Date();
                    var c_date = new Date().getDate()
                    var p_date = date.setDate(date.getDate() - 1)
                    p_date = new Date(p_date).getDate()
                    window.indexedDB.deleteDatabase("attendanceDatabase_" + p_date);
                    window.indexedDB.deleteDatabase("attendanceDatabase_" + c_date);
                    var idb_request = window.indexedDB.open("attendanceDatabase_" + c_date, 1);
                    idb_request.onsuccess = function(event) {
                        idb = idb_request.result;
                    };
                    idb_request.onerror = function(event) {
                       alert("Unable to retrieve daa from database!");
                    };
                    idb_request.onupgradeneeded = function(event) {
                        idb = event.target.result;
                        var objectStore = idb.createObjectStore("attendance", {keyPath: "id"});
                        for (var i = 0; i < data.length; i++) {
                            objectStore.add(data[i]);
                            if (i == data.length - 1 && on_load == 'true') {alert('Store')}
                        }
                    }
                }
            }
    } 
</script>


<style>
  .attendance_list .v-messages{
    min-height: 0px;
  }
  .attendance_list .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .attendance_list .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
  .attendance_list .random_attendance .v-input--checkbox{
      margin-top: 14px;
  }
  .attendance_list .random_attendance .v-input--checkbox label{
      margin-top: 4px;
      font-size: 15px!important;
  }
  .attendance_list .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
  .attendance_list .light_red_background {
        background: #ff6b60!important;
  }
  .attendance_list .light_yellow_background {
        background: #ff9800!important;
  }
  .attendance_list .light_blue_background {
        background: #818dd4!important;
  }
</style>