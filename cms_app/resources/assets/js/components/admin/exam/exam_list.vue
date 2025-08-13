<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="exam_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Undone Exam List</h3>
            <v-divider class="my-3"></v-divider>
            <template v-if="step == 'none'">
                <div>
                    <v-toolbar flat color="grey lighten-2 pb-1">
                        <v-layout>                        
                            <v-dialog full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="search_date"></v-text-field>
                                <v-date-picker ref="picker" v-model="search_date"></v-date-picker>
                            </v-dialog>
                            <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="exam_list"> Go </v-btn>
                        </v-layout>                        
                        <!-- @add_item_dialog -->

                        <v-dialog v-model="edit_dialog" persistent max-width="500px">
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="headline">{{ formTitle }}</span>
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md>
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form ref="form" v-model="valid" lazy-validation>
                                                <v-text-field v-model="dialogItem.sub_full_mark" label="Subjective Full"></v-text-field>
                                                <v-text-field v-model="dialogItem.ob_full_mark" label="Objective Full"></v-text-field>
                                                <v-text-field v-model="dialogItem.start_time" mask="time-with-seconds" label="Start Time" :rules="[rules.required]" required></v-text-field>
                                                <v-text-field v-model="dialogItem.full_time" label="Full Time" :rules="[rules.required]" required></v-text-field>
                                                <v-autocomplete v-model="dialogItem.invigilator_id" :items="teacher" label="Invigilator"></v-autocomplete>                                               
                                                <v-autocomplete v-model="dialogItem.scripter_id" :items="teacher" label="Scripter"></v-autocomplete>
                                                <v-dialog v-if="is_admin()" full-width lazy ref="date_ref1" width="290px" class="m-r-10">
                                                    <v-text-field label="Date Limit" :clearable=true readonly slot="activator" v-model="dialogItem.date_limit"></v-text-field>
                                                    <v-date-picker ref="picker1" v-model="dialogItem.date_limit"></v-date-picker>
                                                </v-dialog>
                                                <!-- @add_item_submit -->
                                                <v-btn :disabled="!valid || loading" :loading="loading" @click="save">
                                                    submit
                                                </v-btn>
                                                <v-btn @click="close">close</v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table v-if="!update" :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td :class="{'active_color': props.item.active}">{{ props.item.schedule_id }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.schedules.echelons.name }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.batches.name }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.date | moment("D MMM") }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.schedules.subjects.name | first_letter_word}}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.sub_full_mark }} + {{ props.item.ob_full_mark }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.active, 'text-danger': props.item.schedules.full_time != props.item.full_time}">{{ props.item.schedules.full_time }} / {{ props.item.full_time }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.start_time }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.invigilators ? props.item.invigilators.name: '' }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.scripters ? props.item.scripters.name : '' }}</td>
                            <td :class="{'text-xs-left':true,'active_color': props.item.active}">{{ props.item.status}}</td>
                            <td :class="{'justify-center': true, 'active_color': props.item.active, 'px-0': true, 'layout': true}" v-if="check_permission('manage_exam')">
                                <v-icon v-if="!props.item.active" small class="mr-2" @click="prepare_exam_entry(props.item)"> send </v-icon>
                                <v-icon small class="mr-2" @click="editItem(props.item)"> edit </v-icon>
                                <v-icon small class="mr-2" @click="prepare_teacher_singature(props.item)"> how_to_reg </v-icon>
                                <!-- @delete_item -->
                                <!-- <v-icon small @click="done_exam_list(props.item)"> done_outline </v-icon> -->
                                <v-icon small v-if="is_admin" @click="exam_list_delete(props.item)"> delete </v-icon>
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
                            <tr v-for="(list, std_i) in originial_updated_exam_list">
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
        <v-dialog v-model="singnature_dialog" persistent max-width="500px">
            <v-card>
                <v-icon class="float-right p-15" @click="singnature_dialog = false">close</v-icon>
                <v-card-title>
                    <!-- @add_item_title -->
                    <span class="headline">Teacher Signature</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <!-- @add_item_field -->
                        <template>
                            <v-layout>                            
                                <v-autocomplete disabled v-model="dialogItem.invigilator_id" :items="teacher" label="Invigilator"></v-autocomplete> 
                                <v-btn outline small class="mid-btn m-l-10 pos-rel" style="top: 10px" @click="save_teacher_singature('invigilator')">Done</v-btn>
                            </v-layout>
                            <v-layout>                                           
                                <v-autocomplete disabled v-model="dialogItem.scripter_id" :items="teacher" label="Scripter"></v-autocomplete>
                                <v-btn outline small class="mid-btn m-l-10 pos-rel" style="top: 10px" @click="save_teacher_singature('scripter')">Done</v-btn>
                            </v-layout>
                        </template>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
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
                    headers: [{text: 'Code', align: 'left', value: 'schedule_id'}, {text: 'Class', value: 'schedules.echelons.name'}, {text: 'Batch', value: 'batches.name'}, {text: 'Date', value: 'date'}, {text: 'Subject', value: 'schedules.subjects.name'}, {text: 'Mark', value: 'sub_full_mark'}, {text: 'Time', value: 'full_time'}, {text: 'Start', value: 'start_time'}, {text: 'Invigilator', value: 'invigilators.name'}, {text: 'Scripter', value: 'scripters.name'}, {text: 'status', value: 'status'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    echelon: [],
                    teacher: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    edit_dialog: false,
                    editedIndex: -1,
                    editedId: null,
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
                    valid: true,
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    data_load: false,
                    update: false,
                    step: 'none',
                    current_batch_id: '',
                    current_batch_name: '',
                    exam_entry: {marks_type: 'both', mark: ''},
                    updated_exam_list: [],
                    originial_updated_exam_list: [],
                    student_exam_list: [],
                    marks_type: [{'value': 'mcq', 'text': 'MCQ'}, {'value': 'cq', 'text': 'CQ'}, {'value': 'both', 'text': 'Both'}],
                    exam_error_message: '',
                    count_un_entry: 0,
                    search_date: '',
                    singnature_dialog: false
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
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
                }
            },

            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                },
                ordinal_number: function(value) {
                    if (value == 1) {return value = value + 'st'}
                    if (value == 2) {return value = value + 'nd'}
                    if (value == 3) {return value = value + 'rd'}
                    if (value > 3) {return value = value + 'th'}
                }
            },

            methods: {                
                // @list_method
                admin_branch_list(){
                    this.branch = window_branch;
                    if (this.branch.length==1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.exam_list();
                        this.teacher_list()
                    }
                    this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                },
                exam_list() {
                    this.data_load = true;
                    this.axios.get('/admin/exam/get_exam_list/' + this.search_date).then(response => {
                        var item = response.data.exam_list;
                        item = item.map(x => {
                            var a = Object.assign({}, x)
                            a.active = new Date(x.date_limit) > new Date() ? false : true
                            return a
                        })
                        this.rows = item                        
                        this.data_load = false
                    });
                },      
                teacher_list(){
                    this.axios.get('/admin/request/teacher_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.teacher = response.data.teacher_list_by_branch;
                        this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    }); 
                },                          
                // @edit_item_method
                exam_list_edit() {
                    this.loading = true;
                    this.axios.post('/admin/exam/exam_list_edit/' + this.editedId, this.dialogItem).then(response => {
                        this.rows[this.editedIndex] = response.data.exam_list
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
                prepare_exam_entry(item){
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.dialog = true
                    this.axios.get('/admin/exam/get_student_exam_entry_list/' + item.schedule_id + '/' + item.batch_id).then(response => {
                        var item = response.data.student_list                         
                        item = item.sort((a, b) => {return parseInt(a.reg_no) - parseInt(b.reg_no) })
                        this.student_exam_list = item
                        this.step = 'entry_mark'
                        var type = this.exam_entry.marks_type
                        this.originial_updated_exam_list = item.map(x => {return {id: x.exams[0].id, student_id: x.id, reg_no: x.reg_no, name: x.name, batch_id: x.batch_id, sub_mark: x.exams[0].sub_mark, ob_mark : x.exams[0].ob_mark}})
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.ob_mark || !x.sub_mark)
                        console.log(this.originial_updated_exam_list)                   
                        this.dialog = false
                    });
                },
                // @admin_add_edit
                save() {
                    if (this.editedIndex > -1) {
                        this.exam_list_edit()
                    }
                },
                get_branch() {
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].value
                    }
                },
                // @delete_item_method
                done_exam_list(item) {
                    var con = confirm("Want to Done?");
                    if (con) {
                        const index = this.rows.findIndex(x => x.id == item.id);
                        this.axios.post('/admin/exam/done_exam_list/' + item.id).then(response => {
                            this.exam_list()
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
                    }
                },
                exam_list_delete(item) {
                    var con = confirm("Want to Delete?");
                    if (con) {
                        const index = this.rows.findIndex(x => x.id == item.id);
                        this.axios.post('/admin/exam/exam_list_delete/' + item.id).then(response => {
                            this.rows.splice(index, 1)
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
                    }
                },
                prepare_teacher_singature(item){
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;                    
                    this.singnature_dialog = true
                },
                save_teacher_singature(singnature_type) {
                    this.dialog = true
                    this.axios.post('/admin/exam/save_teacher_singature/' + this.editedId + '/' + singnature_type).then(response => {
                        this.exam_list()
                        this.dialog = false
                        this.singnature_dialog = false
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
                // @edit_item_method
                // open dialog
                editItem(item) {
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;                    
                    this.edit_dialog = true
                },
                // @add_item_method_dialog
                close() {
                    this.edit_dialog = false
                    this.$refs.form.reset()
                },
                // @add_item_method_close_dialog
                clear() {
                    this.$refs.form.reset()
                },
                press_digit(n){
                    this.exam_entry.mark = this.exam_entry.mark ? this.exam_entry.mark + '' + (n) : (n)
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
                    object.student_id = this.student_exam_list[index].student_id
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
                save_single(id){
                    this.dialog = true
                    this.updated_exam_list = this.originial_updated_exam_list.filter(x => x.id == id)
                    this.save_exam_mark()
                },
                save_all(){
                    this.updated_exam_list = Object.assign([], this.originial_updated_exam_list)
                    this.save_exam_mark()
                },
                save_exam_mark(){
                    var con = confirm("Data will save as showing. Are you sure?")
                    if (!con) {return false}
                    this.dialog = true
                    var item = {}
                    item.mark_type = this.exam_entry.marks_type
                    item.student_mark_list = this.updated_exam_list
                    item.schedule_id = this.dialogItem.schedule_id
                    this.axios.post('/admin/exam/save_exam_mark', item).then(response => {
                        var ids = response.data.ids
                        for (var i = 0; i < ids.length; i++) {
                            var index = this.updated_exam_list.findIndex(x => x.id == ids[i])
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
                        $('.user_nav').removeClass('denied') }.bind(this), 3000) });
                },
            }
    } 
</script>

<style>  
  .exam_list .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
  .exam_list .active_color{
     background: #ffe2e2;
    }
</style>