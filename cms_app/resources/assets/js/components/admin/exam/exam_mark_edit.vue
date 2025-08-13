<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="exam_mark_edit">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Marks Edit</h3>
            <v-divider class="my-3"></v-divider>
            <template v-if="step == 'none'">
                <div>
                    <!-- mark entry card -->
                    <div class="card setup_exam m-t-10 m-b-15">
                        <v-container>
                            <div class="row">
                                <div class="col-xs-10 col-md-4 p-r-5">                        
                                    <v-text-field class="mark-input" v-model="exam_entry.mark" label="Edit Mark (ExamCode Reg Mark)" :clearable=true type="text" @keyup.enter="go_for_exam_edit"></v-text-field>
                                </div>
                                <div class="col-xs-2 col-md-1 p-l-5">                        
                                    <v-icon @click="go_for_exam_edit" class="pos-rel" style="top: 20px">send</v-icon>                                  
                                </div>
                                <div class="col-xs-12 col-md-6">
                                    <v-radio-group v-model="exam_entry.marks_type" row>
                                        <v-radio class="pos-rel m-l-10" value="both" label="Both" style="top: 0px"></v-radio>
                                        <v-radio class="pos-rel m-l-10" value="cq" label="CQ" style="top: 0px"></v-radio>    
                                        <v-radio class="pos-rel m-l-10" value="mcq" label="MCQ" style="top: 0px"></v-radio>   
                                    </v-radio-group>
                                </div>
                            </div>
                            <div class="text-danger">{{exam_error_message}}</div> 
                            <table class="table table-bordered fs-11 m-b-0" v-if="updated_exam_list.length && show_edit_list" style="max-width:400px">
                                <tbody>
                                    <tr v-for="(list, std_i) in updated_exam_list">
                                        <td>{{list.schedule_id}}</td>
                                        <td>{{list.reg_no}}</td>
                                        <td>{{list.sub_mark}}</td>
                                        <td>{{list.ob_mark}}</td>
                                    </tr>
                                </tbody>
                            </table> 
                            <v-btn outline v-if="updated_exam_list.length" small color="success" class="pos-rel tiny-btn" @click="edit_exam_mark" style="top: 10px;">Save</v-btn>
                        </v-container>
                    </div>
                    <v-toolbar flat color="grey lighten-2 pb-1">
                        <v-layout>                        
                            <v-dialog v-model="date_from_dialog" full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="search_date_from"></v-text-field>
                                <v-date-picker @change="date_from_dialog = false" ref="picker" v-model="search_date_from"></v-date-picker>
                            </v-dialog>
                            <v-dialog v-model="date_to_dialog" full-width lazy ref="date_ref_1" width="290px" class="m-r-10">
                                <v-text-field label="Date To" :clearable=true readonly slot="activator" v-model="search_date_to"></v-text-field>
                                <v-date-picker @change="date_to_dialog = false" ref="picker" v-model="search_date_to"></v-date-picker>
                            </v-dialog>
                            <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="exam_list"> Go </v-btn>
                        </v-layout>  
                    </v-toolbar>

                    <!-- @list_table @list_header @list_column -->
                    <v-data-table v-if="!update" :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td :class="{'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.id }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.batches.echelons.name }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.batches.name }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.date | moment("D MMM YY") }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.subjects.name }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.exam_lists ? props.item.exam_lists.sub_full_mark : '' }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.exam_lists ? props.item.exam_lists.ob_full_mark : '' }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.exam_lists && props.item.exam_lists.invigilators ? props.item.exam_lists.invigilators.name: '' }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.exam_lists && props.item.exam_lists.scripters ? props.item.exam_lists.scripters.name : '' }}</td>
                            <td :class="{'text-xs-left': true, 'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">{{ props.item.exam_lists ? props.item.exam_lists.status : 'scheduled'}}</td>
                            <td :class="{'green lighten-4': props.item.exam_lists && props.item.exam_lists.status == 'finish'}">
                                <v-icon small class="mr-2" @click="prepare_exam_entry(props.item)"> send </v-icon>
                                <v-icon small class="mr-2" @click="editItem(props.item)"> edit </v-icon>
                                <v-icon v-if="props.item.exam_lists && !props.item.exam_lists.sms && props.item.exam_lists.status == 'finish'" small class="mr-2" @click="show_send_sms_dialog(props.item)"> textsms </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>    
            <!-- mark entry card -->
            <div class="card setup_exam m-t-10" v-if="step=='entry_mark'">
                <v-container>
                    <h4 class="m-b-20 fw-600 fs-15">Marks Entry of {{dialogItem.batches.name}} ({{student_exam_list.length}}) <span class="float-right cur-p pos-rel" style="top: -3px;" @click="close_mark_entry_card"><span class="m-r-15 pos-rel" style="top:-3px;">{{count_un_entry.length}}</span><v-icon>close</v-icon></span></h4>
                    <v-layout>
                        <v-select v-model="exam_entry.marks_type" @change="change_mark_type" :items="marks_type" label="Marks Type" class="m-r-15"></v-select>
                        <v-text-field v-model="exam_entry.base_reg_no" label="Base Reg No" type="number"></v-text-field>
                        <v-checkbox class="pos-rel m-l-10" v-model="exam_entry.serial" label="SL" style="top: 20px"></v-checkbox>
                    </v-layout>
                    <v-layout>                        
                        <v-text-field class="mark-input" v-model="exam_entry.mark" label="Mark" :clearable=true type="text" @keyup.enter="go_for_exam_entry"></v-text-field>
                        <v-btn  outline color="primary" class="tiny-btn m-l-10 m-t-5 m-b-0 pos-rel visible-xs" @click="backspace" style="top: 8px; height: 36px; padding: 0px 15px!important"><v-icon>backspace</v-icon></v-btn>
                    </v-layout>
                    <div class="text-danger">{{exam_error_message}}</div>
                    <div class="row m-t-15 visible-xs">
                        <div v-for="n in 9" class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="press_digit(n)" style="min-width: 0px; height: auto; padding: 2px 26px">{{n}}</v-btn>
                        </div>
                    </div>
                    <div class="row visible-xs">
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="space" style="min-width: 0px; height: auto; padding: 9px 21px"><v-icon>space_bar</v-icon></v-btn>
                        </div>
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="primary" class="block" @click="press_digit(0)" style="min-width: 0px; height: auto; padding: 2px 26px">0</v-btn>
                        </div>
                        <div class="text-center m-t-0 p-l-0 p-r-0 col-xs-4 calculator-btn">
                            <v-btn outline color="success" class="block" @click="go_for_exam_entry" style="min-width: 0px; height: auto; padding: 9px 21px"><v-icon>done_outline</v-icon></v-btn>
                        </div>
                    </div>
                    <v-layout>  
                        <v-text-field class="m-r-10" v-model="search_student" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <v-btn small color="success" @click="save_all">Finish Entry</v-btn>
                    </v-layout> 
                    <table class="table table-bordered fs-11 m-t-15">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>Sub Mark</th>
                                <th>Ob Mark</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(list, std_i) in sortStudent">
                                <td class="cur-p" @click="get_student_mark(list.reg_no)">{{list.reg_no}}</td>
                                <td class="cur-p" @click="get_student_mark(list.reg_no)">{{list.name.substring(0,25)}}</td>
                                <td><input type="text" v-model="list.sub_mark"></td>
                                <td><input type="text" v-model="list.ob_mark"></td>
                                <td><v-icon class="fs-16 cur-p" @click="save_single(list.id)">save</v-icon></td>
                            </tr>
                        </tbody>
                    </table>  
                    <v-btn small @click="save_all" color="success" class="float-right">Submit</v-btn>          
                </v-container>
            </div>        
        </v-container>
        <v-dialog v-model="dialog" hide-dialog persistent width="300">
            <v-card color="#009688" dark>
                <v-card-text>
                    Please Wait..
                    <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                </v-card-text>
            </v-card>
        </v-dialog>
        <v-dialog v-model="edit_dialog" persistent max-width="500px">
            <v-card>
                <v-card-title>
                    <!-- @add_item_title -->
                    <span class="headline">Edit Exam</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <!-- @add_item_field -->
                        <template>
                            <v-form ref="form" v-model="valid" lazy-validation>
                                <v-text-field v-model="dialogItem.sub_full_mark" label="Subjective Full"></v-text-field>
                                <v-text-field v-model="dialogItem.ob_full_mark" label="Objective Full"></v-text-field>
                                <v-text-field v-model="dialogItem.start_time" mask="time-with-seconds" label="Start Time" :rules="[v => !!v || 'required']"></v-text-field>
                                <v-text-field v-model="dialogItem.full_time" label="Full Time" :rules="[v => !!v || 'required']"></v-text-field>
                                <v-autocomplete :clearable="true" v-model="dialogItem.invigilator_id" :items="teacher" label="Invigilator"></v-autocomplete>                                               
                                <v-autocomplete :clearable="true" v-model="dialogItem.scripter_id" :items="teacher" label="Scripter"></v-autocomplete>
                                <v-autocomplete :clearable="true" :rules="[v => !!v || 'required']" v-model="dialogItem.exam_type_id" :items="exam_type" label="Exam Type"></v-autocomplete>
                                <!-- <v-dialog v-if="is_admin()" full-width lazy ref="date_ref1" width="290px" class="m-r-10">
                                    <v-text-field label="Date Limit" :clearable=true readonly slot="activator" v-model="dialogItem.date_limit"></v-text-field>
                                    <v-date-picker ref="picker1" v-model="dialogItem.date_limit"></v-date-picker>
                                </v-dialog> -->
                                <!-- @add_item_submit -->
                                <v-btn :disabled="!valid || loading" @click="exam_list_edit">
                                    submit
                                </v-btn>
                                <v-btn @click="close">close</v-btn>
                            </v-form>
                        </template>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- destination number -->
        <v-dialog v-model="destination_number_dialog" hide-dialog persistent width="300">
            <v-card>
                <v-icon class="cur-p p-15 float-right" @click="destination_number_dialog = false">close</v-icon>
                <v-card-text class="text-center">
                    <v-autocomplete class="p-t-0 m-t-0 m-l-15" :items="destination_number_data" v-model="destination_number"></v-autocomplete>
                    <v-btn small outline color="success" @click="send_exam_sms">Send Sms</v-btn>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- sms dialog -->
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
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    // @list_header_data
                    headers: [
                        {text: 'Code', align: 'left', value: 'id'}, 
                        {text: 'Class', value: 'batches.echelons.name'}, 
                        {text: 'Batch', value: 'batches.name'}, 
                        {text: 'Date', value: 'date'}, 
                        {text: 'Subject', value: 'subjects.name'}, 
                        {text: 'Sub Full', value: 'exam_lists.sub_full_mark'}, 
                        {text: 'Ob Full', value: 'exam_lists.ob_full_mark'}, 
                        {text: 'Invigilator', value: 'exam_lists.invigilators.name'}, 
                        {text: 'Scripter', value: 'exam_lists.scripters.name'}, 
                        {text: 'Status', value: 'exam_lists.status'}, 
                        {text: 'Actions', value: 'name', sortable: false, align: 'left'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    dialogItem: {
                        name: '',
                        branch_id: '',
                        echelon_id: '',
                        teacher_id: '',
                        bangla_text: '',
                        created_at: '',
                        date: '',
                        batches: {}
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },

                    // @accesories_data
                    update: false,
                    data_load: false,
                    step: 'none',
                    exam_entry: {marks_type: 'both', mark: ''},
                    updated_exam_list: [],
                    originial_updated_exam_list: [],
                    student_exam_list: [],
                    marks_type: [{'value': 'mcq', 'text': 'MCQ'}, {'value': 'cq', 'text': 'CQ'}, {'value': 'both', 'text': 'Both'}],
                    exam_error_message: '',
                    search_date_from: '',
                    search_date_to: '',
                    show_edit_list: true,
                    search_student: '',
                    sms_dialog: false,
                    sms_balance: '',
                    sms_failed: [],
                    sms_report: [],
                    valid: true,
                    edit_dialog: false,
                    teacher: [],
                    loading: false,
                    count_un_entry: 0,
                    exam_type: [],
                    entry_exam_list: [],
                    date_from_dialog: false,
                    date_to_dialog: false,
                    destination_number_data: [{'value': 'guardian', 'text': 'SMS to Guardian'}, {'value': 'student', 'text': 'SMS to Student'}],
                    destination_number: 'guardian',
                    destination_number_dialog: false,
                    current_item: 0
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                if (window_latest_unpaid.overdue == 'true') {
                    next('/billing_invoice')
                }
                let p = window_admin_permissions.findIndex(x => x.name == 'view_exam' || x.name == 'manage_exam');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.admin_branch_list();
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Subject' : 'Edit Subject'
                },
                sortStudent() {
                    var items = this.originial_updated_exam_list
                    .filter(item => item.reg_no.toString().indexOf(this.search_student) > -1 || item.name.toLowerCase().indexOf(this.search_student.toLowerCase()) > -1)
                    return items;
                }
            },

            methods: {                
                // @list_method
                admin_branch_list(){
                    this.branch = window_branch;
                    if (this.branch.length==1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.exam_list();
                        setTimeout(function() { this.teacher_list() }.bind(this), 2000)
                        setTimeout(function() { this.exam_type_list() }.bind(this), 3000)
                    }
                    this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                },
                exam_list() {
                    if (!this.search_date_from) {
                        var date_from = this.$moment().subtract(8, 'days').format('YYYY-MM-DD');
                    } else {
                        var date_from = this.search_date_from
                    }
                    if (!this.search_date_to) {
                        var date_to = this.$moment().format('YYYY-MM-DD');
                    } else {
                        date_to = this.search_date_to
                    }
                    this.data_load = true;
                    this.axios.get('/admin/exam/get_exam_edit_list/' + date_from + '/' + date_to).then(response => {
                        this.rows = response.data.exam_list;
                        this.data_load = false
                    });
                },
                teacher_list(){
                    this.axios.get('/admin/request/teacher_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.teacher = response.data.teacher_list_by_branch;
                        this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    }); 
                },
                exam_type_list(){
                    this.axios.get('/admin/request/exam_type_list').then(response => {
                        this.exam_type = response.data.exam_type_list
                        this.exam_type = this.exam_type.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    });
                },                      
                prepare_exam_entry(item){
                    if (!item.exam_lists) {return alert('Please setup the exam first')}
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.dialog = true
                    this.axios.get('/admin/exam/get_all_student_exam_entry_list/' + item.exam_lists.id).then(response => {
                        var item = response.data.student_list                         
                        item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                        this.student_exam_list = item
                        this.entry_exam_list = response.data.exam_list
                        this.step = 'entry_mark'
                        var type = this.exam_entry.marks_type
                        this.originial_updated_exam_list = item.map(x => {return {id: x.exams[0].id, student_id: x.id, reg_no: x.reg_no, name: x.name, batch_id: x.batch_id, sub_mark: x.exams[0].sub_mark, ob_mark : x.exams[0].ob_mark}})                        
                        this.dialog = false
                    });
                }, 
                go_for_exam_edit(){
                    this.exam_error_message = ''
                    if (!this.exam_entry.mark) {return false}
                    var data = this.exam_entry.mark.replace(/\s\s+/g, ' ')                        
                    data = this.exam_entry.mark.split(' ')
                    if (this.exam_entry.marks_type == 'both' && data.length!=4) {return alert('Entry in wrong way')}
                    if (this.exam_entry.marks_type != 'both' && data.length!=3) {return alert('Entry in wrong way')}                    
                    // add list
                    var index2 = this.updated_exam_list.length ? this.updated_exam_list.findIndex(x => x.reg_no == data[1]) : -1 
                    var s_mark = this.exam_entry.marks_type == 'both' ? data[2] : data[2]
                    var o_mark = this.exam_entry.marks_type == 'both' ? data[3] : data[2]
                    if (this.exam_entry.marks_type == 'cq') {
                        s_mark = data[2]
                        o_mark = ''
                    }
                    if (this.exam_entry.marks_type == 'mcq') {
                        s_mark = ''
                        o_mark = data[2]
                    }

                    var object = {                        
                        schedule_id: data[0], reg_no: data[1], sub_mark: s_mark, ob_mark: o_mark
                    }                     

                    if (index2>-1) {                            
                        this.updated_exam_list[index2] = object
                    }
                    else {
                        this.updated_exam_list.push(object)
                    }  
                    this.show_edit_list = false
                    setTimeout(function() {this.show_edit_list = true }.bind(this), 200)

                    // save database
                    if (this.updated_exam_list.length==5) {
                        this.edit_exam_mark()
                    }
                },  
                go_for_exam_entry(){
                    this.exam_error_message = ''
                    if (!this.exam_entry.mark) {return false}
                    var data = this.exam_entry.mark.replace(/\s\s+/g, ' ')                        
                    data = this.exam_entry.mark.split(' ')
                    if (this.exam_entry.marks_type == 'both' && data.length!=3) {return alert('Entry in wrong way')}
                    if (this.exam_entry.marks_type != 'both' && data.length!=2) {return alert('Entry in wrong way')}
                    if (this.exam_entry.marks_type == 'mcq' && parseInt(data[1])>this.dialogItem.ob_full_mark) {return alert('Mark greather than full marks')}
                    if (this.exam_entry.marks_type == 'cq' && parseInt(data[1])>this.dialogItem.sub_full_mark) {return alert('Mark greather than full marks')}
                    if (this.exam_entry.marks_type == 'both' && (parseInt(data[1])>this.dialogItem.sub_full_mark || parseInt(data[2])>this.dialogItem.ob_full_mark)) {return alert('Mark greather than full marks')}
                    var index = this.student_exam_list.findIndex(x => x.reg_no == data[0])
                    if (index==-1) {
                        this.exam_error_message = "No Student Found"
                        return false
                    }
                    // add list
                    var index2 = this.updated_exam_list.length ? this.updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1 
                    var index3 = this.originial_updated_exam_list.length ? this.originial_updated_exam_list.findIndex(x => x.reg_no == data[0]) : -1
                    var object = {id: '', student_id: '', reg_no: '', name: '', batch_id: '', sub_mark: '', ob_mark: ''}                     
                    object.id = this.student_exam_list[index].exams[0].id
                    object.student_id = this.student_exam_list[index].id
                    object.reg_no = this.student_exam_list[index].reg_no
                    object.name = this.student_exam_list[index].name
                    object.batch_id = this.student_exam_list[index].batch_id
                    object.sub_mark = this.exam_entry.marks_type == 'cq' ? data[1] : this.originial_updated_exam_list[index3].sub_mark
                    object.ob_mark = this.exam_entry.marks_type == 'mcq' ? data[1] : this.originial_updated_exam_list[index3].ob_mark
                    if (this.exam_entry.marks_type == 'both') {
                        object.sub_mark = data[1]
                        object.ob_mark = data[2]
                    }
                    if (index2>-1) {                            
                        this.updated_exam_list[index2] = object
                    }
                    else {
                        this.updated_exam_list.push(object)
                    }
                    if (index3>-1) {
                        this.originial_updated_exam_list[index3] = object
                    }else{
                        this.originial_updated_exam_list.push(object)
                    }
                    this.originial_updated_exam_list = this.originial_updated_exam_list.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                    // go to next reg no
                    
                    this.exam_entry.mark = ''
                    if (this.exam_entry.serial && this.student_exam_list.length > index + 1) {
                        this.exam_entry.mark = this.student_exam_list[index + 1].reg_no + ' '
                    }
                    if (!this.exam_entry.serial && this.exam_entry.base_reg_no) {
                        this.exam_entry.mark = this.exam_entry.base_reg_no
                    }
                    // count unentry
                    if (this.exam_entry.marks_type == 'both') {
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.ob_mark || !x.sub_mark)
                    }
                    else {
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => this.exam_entry.marks_type == 'cq' ? !x.sub_mark : !x.ob_mark)
                    }
                    // save database

                    if (this.updated_exam_list.length==5) {
                        this.save_exam_mark()
                    }
                },              
                close_mark_entry_card(){
                    this.step='none'
                    if (this.updated_exam_list.length>0) {
                        this.save_exam_mark()
                    }
                    this.originial_updated_exam_list = []
                },
                save_single(id){
                    this.dialog = true
                    this.updated_exam_list = this.originial_updated_exam_list.filter(x => x.id == id)
                    this.save_exam_mark()
                },
                save_all(){
                    this.updated_exam_list = Object.assign([], this.originial_updated_exam_list)
                    var reg_no_error = []
                    for (var i = 0; i < this.updated_exam_list.length; i++) {
                        if (this.updated_exam_list[i].sub_mark > this.entry_exam_list.sub_full_mark || this.updated_exam_list[i].ob_mark > this.entry_exam_list.ob_full_mark) {
                            reg_no_error.push(this.updated_exam_list[i].reg_no)
                        }
                    }
                    if (reg_no_error.length) {
                        this.updated_exam_list = []
                        return alert('Has greater mark than full marks. RegNo: ' + reg_no_error.join(', '))
                    }
                    this.dialog = true
                    this.save_exam_mark('finish')
                },
                edit_exam_mark(){
                    if (!this.updated_exam_list.length) {return false}
                    var item = {}
                    item.mark_type = this.exam_entry.marks_type
                    item.student_mark_list = this.updated_exam_list
                    this.dialog = true
                    this.axios.post('/admin/exam/edit_exam_mark', item).then(response => {
                        var not_found = response.data.not_found
                        this.exam_error_message = not_found.length ? not_found.join(',') + ' : student or exam code not found; ': ''
                        var error_reg = response.data.error_reg
                        this.exam_error_message = error_reg.length ? this.exam_error_message + error_reg.join(',') + ' : enter greater marks than full marks': this.exam_error_message
                        var ids = response.data.ids
                        for (var i = 0; i < ids.length; i++) {
                            var index = this.updated_exam_list.findIndex(x => x.schedule_id == ids[i])
                            if (index>-1) {this.updated_exam_list.splice(index,1)}
                        }
                        this.dialog = false
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                        $('.user_nav').removeClass('denied') }.bind(this), 3000) });
                },
                show_send_sms_dialog(item){
                    this.current_item = item
                    this.destination_number_dialog = true
                },
                send_exam_sms(){
                    // var con = confirm("Are you sure to send exam sms to student?")
                    // if (!con) {return false}                    
                    this.dialog = true
                    this.axios.post('/admin/exam/send_exam_sms/' + this.current_item.id + '/' + this.destination_number_dialog).then(response => {
                        var index = this.rows.findIndex(x => x.id == this.current_item.id)
                        this.destination_number_dialog = false
                        if (response.data.sms == 'sms') {
                            this.sms_report = response.data.sms_report
                            this.sms_balance = response.data.balance
                            this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                            this.dialog = false
                            this.sms_dialog = true   
                        }
                        this.rows[index].exam_lists.sms = 1
                        this.dialog = false
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000) });
                },
                finish_entry(){
                    this.dialog = true
                    var id = this.rows[this.editedIndex].exam_lists.id
                    this.axios.post('/admin/exam/finish_mark_entry/' + id).then(response => {
                        this.rows[this.editedIndex].exam_lists.status = 'finish'
                        this.step = 'none'
                        this.dialog = false
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                save_exam_mark(status){
                    var item = {}
                    item.mark_type = this.exam_entry.marks_type
                    item.student_mark_list = this.updated_exam_list
                    item.exam_list_id = this.dialogItem.exam_lists.id
                    if (status) {
                        item.status = status
                    }
                    this.axios.post('/admin/exam/save_exam_mark', item).then(response => {
                        var ids = response.data.ids
                        for (var i = 0; i < ids.length; i++) {
                            var index = this.updated_exam_list.findIndex(x => x.student_id == ids[i])
                            if (index>-1) {this.updated_exam_list.splice(index,1)}
                        }
                        this.dialog = false
                        var x = JSON.stringify(this.updated_exam_list)
                        this.$cookies.set('unsaved_exam_mark', x);
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                        $('.user_nav').removeClass('denied') }.bind(this), 3000) 
                    });
                },
                exam_list_edit() {
                    if(!this.$refs.form.validate()){return false}
                    this.loading = true;
                    this.axios.post('/admin/exam/exam_list_edit/' + this.editedId, this.dialogItem).then(response => {
                        this.rows[this.editedIndex] = response.data.exam_list
                        this.update = true
                        setTimeout(function() { this.update = false }.bind(this), 200)
                        this.close()
                        this.loading = false;
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.loading = false;
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                editItem(item) {
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;  
                    this.dialogItem.sub_full_mark = item.exam_lists ? item.exam_lists.sub_full_mark : ''
                    this.dialogItem.ob_full_mark = item.exam_lists ? item.exam_lists.ob_full_mark: ''
                    this.dialogItem.start_time = item.exam_lists ? item.exam_lists.start_time: ''
                    this.dialogItem.full_time = item.exam_lists ? item.exam_lists.full_time : ''
                    this.dialogItem.invigilator_id = item.exam_lists ? item.exam_lists.invigilator_id : ''
                    this.dialogItem.scripter_id = item.exam_lists ? item.exam_lists.scripter_id : ''  
                    this.dialogItem.exam_type_id = item.exam_lists ? item.exam_lists.exam_type_id : ''                  
                    this.edit_dialog = true
                },
                close() {
                    this.edit_dialog = false
                    this.$refs.form.reset()
                },
                get_student_mark(reg_no){
                    this.exam_entry.mark = reg_no + ' '
                    $('html,body').animate({ scrollTop: 0 }, 'slow');
                    $('.mark-input input').focus()
                },
                backspace(){
                    if (this.exam_entry.mark) {
                        this.exam_entry.mark = this.exam_entry.mark.substring(0, this.exam_entry.mark.length-1)
                    }
                },
                space(){
                    if (this.exam_entry.mark) {
                        this.exam_entry.mark = this.exam_entry.mark + ' '
                    }
                },
                close_mark_entry_card(){
                    this.step='none'
                    if (this.updated_exam_list.length>0) {
                        this.save_exam_mark()
                    }
                    this.originial_updated_exam_list = []
                },
                change_mark_type(){
                    if (this.exam_entry.marks_type == 'both') {
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.ob_mark || !x.sub_mark)
                    }
                    else {
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => this.exam_entry.marks_type == 'cq' ? !x.sub_mark : !x.ob_mark)
                    }
                },
                press_digit(n){
                    this.exam_entry.mark = this.exam_entry.mark ? this.exam_entry.mark + '' + (n) : (n)
                },

            }
    } 
</script>

<style>  
  .exam_mark_edit .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
</style>