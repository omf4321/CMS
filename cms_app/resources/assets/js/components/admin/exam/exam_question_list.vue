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
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Question List</h3>
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
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel m-r-25" style="top: 10px" @click="question_list"> Go </v-btn>
                        </v-layout>                       
                        <!-- @add_item_dialog -->

                        <v-dialog v-model="edit_dialog" persistent max-width="500px">
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, get_branch()">New</v-btn>
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
                                                <v-select v-model="dialogItem.echelon_id":items="echelon":rules="[v => !!v || 'Class is required']" label="Class" @change="get_subject"required ></v-select>
                                                <v-autocomplete v-model="dialogItem.subject_id" :items="subject":rules="[v => !!v || 'Subject is required']" label="Subject" required></v-autocomplete>
                                                <v-text-field v-model="dialogItem.full_mark" label="Full Mark"></v-text-field>
                                                <v-text-field v-model="dialogItem.full_time" label="Full Time"></v-text-field>
                                                <v-text-field v-model="dialogItem.chapter" label="Chapter"></v-text-field>
                                                <v-text-field v-model="dialogItem.topic" label="Topic"></v-text-field>
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
                            <td :class="{'text-xs-left':true}">{{ props.item.id }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.echelons.name }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.subjects.name | first_letter_word}}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.chapter }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.topic }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.full_mark }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.full_time }}</td>
                            <td :class="{'text-xs-left':true}">{{ props.item.created_at | moment("D MMM YY") }}</td>
                            <td :class="{'justify-center': true, 'px-0': true, 'layout': true}">
                                <v-icon small class="mr-2" @click="editItem(props.item)"> edit </v-icon>
                                <v-icon v-if="!props.item.active" small class="mr-2" @click="assign_dialog = true"> send </v-icon>
                                <!-- <v-icon small class="mr-2" @click="prepare_teacher_singature(props.item)"> post_add </v-icon> -->
                                <router-link :to="{ name: 'exam_question_setup', params: { id: props.item.id }}">
                                    <v-icon small class="mr-2 pos-rel fs-18" style="top: 14px"> post_add </v-icon>
                                </router-link>
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
        <v-dialog v-model="assign_dialog" persistent max-width="600px">
            <v-card>
                <v-icon class="float-right p-15" @click="assign_dialog = false">close</v-icon>
                <v-card-title>
                    <!-- @add_item_title -->
                    <div class="row">
                        <div class="col-md-4 fs-16 p-15 p-t-20">Assign To Exam</div>
                        <div class="col-md-6">
                            <v-dialog full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="exam_search_date"></v-text-field>
                                <v-date-picker ref="picker" v-model="exam_search_date"></v-date-picker>
                            </v-dialog>
                        </div>
                        <div class="col-md-2">
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="dialog = true, exam_list()"> Go </v-btn>
                        </div>
                    </div>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <!-- @add_item_field -->
                        <template>
                            <div v-if="exam_list_data.length==0"> No exam found!</div>
                            <div class="row" v-for="list in exam_list_data">
                                <div class="col-xs-2"><v-checkbox @change="" v-model="list.active" class="m-t-0 p-t-0"></v-checkbox></div>
                                <div class="col-xs-3">{{list.date | moment("D MMM YY") }}</div>
                                <div class="col-xs-4">{{list.batches.name}}</div>
                                <div class="col-xs-3">{{list.schedules.subjects.name | first_letter_word}}</div>
                            </div>
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
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Class', value: 'echelons.name'}, {text: 'Subject', value: 'subjects.name'}, {text: 'Chapter', value: 'chapter'}, {text: 'Topic', value: 'topic'},  {text: 'F.Mark', value: 'full_mark'}, {text: 'Time', value: 'full_time'}, {text: 'Created At', value: 'created_at'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data 
                    rows: [],
                    branch: [],
                    echelon: [],
                    teacher: [],
                    subject: [],
                    original_subject: [],
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
                        batches: {},
                    },
                    assign_exams: [],
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
                    exam_search_date: '',
                    assign_dialog: false,
                    exam_list_data: [],
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'setting_subject');
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
                        this.question_list();
                        this.admin_echelon_list()
                        this.admin_subject_list();
                    }
                    this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                },
                question_list() {
                    this.data_load = true;
                    this.axios.get('/admin/exam/get_exam_question_list/' + this.search_date).then(response => {
                        var item = response.data.question_list;
                        for (var i = 0; i < item.length; i++) {
                            for (var j = 0; j < item[i].assign_exams.length; j++) {
                                this.assign_exams.push(item[i].assign_exams[j].id)
                            }
                        }
                        this.rows = item                       
                        this.data_load = false
                        this.exam_list()
                    });
                },
                exam_list() {
                    this.axios.get('/admin/exam/get_question_exam_list/' + this.exam_search_date).then(response => {
                        var item = response.data.exam_list;
                        item = item.map(x => {
                            var a = Object.assign({}, x)
                            a.active = false
                            return a
                        })
                        for (var i = 0; i < item.length; i++) {
                            if (this.assign_exams.includes(item[i].id)) {
                                item[i].active = true
                            }
                        }
                        this.exam_list_data = item
                        this.dialog = false
                    });
                },
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                }, 
                admin_subject_list(){
                    this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                        this.original_subject = response.data.subject_list_by_branch;
                        this.subject = response.data.subject_list_by_branch;
                        this.subject = this.subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    });
                }, 
                get_subject(){
                    var echelon_id = this.dialogItem.echelon_id
                    var subject = Object.assign([], this.original_subject);
                    let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
                    this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
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
                        this.originial_updated_exam_list = item.map(x => {return {id: x.id, reg_no: x.reg_no, name: x.name, batch_id: x.batch_id, sub_mark: x.exams[0].sub_mark, ob_mark : x.exams[0].ob_mark}})
                        this.count_un_entry = this.originial_updated_exam_list.filter(x => !x.ob_mark || !x.sub_mark)                        
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
                    this.assign_dialog = true
                },
                save_teacher_singature(singnature_type) {
                    this.dialog = true
                    this.axios.post('/admin/exam/save_teacher_singature/' + this.editedId + '/' + singnature_type).then(response => {
                        this.exam_list()
                        this.dialog = false
                        this.assign_dialog = false
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
                    this.dialogItem.subject_id = this.rows[this.editedIndex].subjects.id; 
                    this.dialogItem.echelon_id = this.rows[this.editedIndex].echelons.id;             
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
            }
    } 
</script>

<style>  
  .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
   .active_color{
     background: #ffe2e2;
    }
</style>