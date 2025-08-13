
<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Question Makable List</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-toolbar flat color="white">
                        <v-layout>  
                            <v-select class="m-r-15" v-model="search_branch" @change="get_branch_item" :disabled="branch.length==1" :items="branch" label="Branch"></v-select>                      
                            <v-dialog v-model="date_dialog" full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="search_date"></v-text-field>
                                <v-date-picker @change="date_dialog = false" ref="picker" v-model="search_date"></v-date-picker>
                            </v-dialog>
                            <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="get_online_exam"> Go </v-btn>
                            <v-spacer></v-spacer>
                            <v-dialog v-model="dialog" persistent max-width="600px">
                            <!-- @add_item_button -->
                                <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, dialogItem.branch_id = 1" :disabled="!dialogItem.branch_id">+ Create</v-btn>
                                <v-card>
                                    <v-card-title>
                                        <!-- @add_item_title -->
                                        <span class="headline">{{formTitle}}</span>
                                    </v-card-title>
                                    <v-card-text>
                                        <v-container grid-list-md>
                                            <!-- @add_item_field -->
                                            <template>
                                                <v-form ref="form" v-model="valid" lazy-validation>
                                                    <h5 class="fw-600">Exam Information</h5>
                                                    <hr class="m-t-5 m-b-10">
                                                    <v-layout>
                                                        <v-select class="m-r-10" v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'required']" label="Branch" required></v-select>
                                                        <v-text-field :rules="[v => !!v || 'required']" class="m-r-10" v-model="dialogItem.name" label="Exam Name"></v-text-field>
                                                        <v-dialog v-model="exam_date_dialog" full-width lazy ref="date_ref1" width="290px">
                                                            <v-text-field label="Exam Date" :clearable=true readonly slot="activator" v-model="dialogItem.date" :rules="[v => !!v || 'required']"></v-text-field>
                                                            <v-date-picker @change="exam_date_dialog = false" ref="picker1" v-model="dialogItem.date"></v-date-picker>
                                                        </v-dialog>
                                                    </v-layout>
                                                    <v-layout>                       
                                                        <v-text-field class="m-r-10" v-model="dialogItem.full_mark" label="Full Mark" :rules="[v => !!v || 'required']"></v-text-field>
                                                        <v-text-field class="m-r-10" v-model="dialogItem.negative_mark" label="Negative Mark" hint="Per Question"></v-text-field>
                                                        <v-text-field v-model="dialogItem.start_time" label="Start From" hint="H:m:s (17:00:00)" mask="time-with-seconds" :rules="[v => !!v || 'required']"></v-text-field>
                                                    </v-layout>
                                                    <v-layout>                       
                                                        <v-text-field class="m-r-10" v-model="dialogItem.full_time" label="Time in Minutes" hint="Left blank for no limit"></v-text-field>
                                                        <v-select class="m-r-10" v-model="dialogItem.sit_exam_at" :items="sit_exam_at" label="Sit exam at" :rules="[v => !!v || 'required']"></v-select>
                                                    </v-layout>
                                                    <h5 class="fw-600">Subject <v-btn flat icon color="info tiny-btn" @click="add_remove_subject('add')"><v-icon dark>add</v-icon></v-btn></h5>
                                                    <hr class="m-t-0 m-b-15">
                                                    <v-layout class="nonpadding-checkbox" v-for="(sub, sb_key) in dialogItem.subjects" :key="sb_key">    
                                                        <v-autocomplete class="m-r-15" v-model="sub.subject" :items="subject" label="Subject" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                        <v-text-field :rules="[v => !!v || 'required']" class="m-r-10" v-model="sub.question_range" label="Question Range" hint="example: 1-10"></v-text-field>
                                                        <v-btn flat icon color="info tiny-btn" @click="add_remove_subject('remove', sb_key)"><v-icon dark>close</v-icon></v-btn>
                                                    </v-layout>
                                                    <h5 class="fw-600">Rules</h5>
                                                    <hr class="m-t-5 m-b-10">
                                                    <div class="nonpadding-checkbox">
                                                        <v-checkbox v-model="dialogItem.change_answer" label="Student can change answer"></v-checkbox>
                                                        <v-checkbox v-model="dialogItem.answer_later" label="Student can answer later"></v-checkbox>
                                                        <v-checkbox v-model="dialogItem.left_answer" label="Student can left doubtful answer"></v-checkbox>   
                                                        <v-checkbox v-model="dialogItem.random_question" label="Show question randomly to different student"></v-checkbox>
                                                        <v-checkbox v-model="dialogItem.random_option" label="Show options randomly to different student"></v-checkbox>   
                                                    </div>   
                                                    <h5 class="fw-600">Passmark</h5>
                                                    <hr class="m-t-5 m-b-10">
                                                    <v-layout class="nonpadding-checkbox m-t-10">
                                                        <div v-if="!dialogItem.pass_mark_option" style="width:150px; display:flex; margin-right: 15px">
                                                            <v-text-field class="m-r-10" v-model="dialogItem.pass_mark" label="Pass Mark" :rules="[v => !!v || 'required']"></v-text-field>%
                                                        </div>
                                                        <v-checkbox v-model="dialogItem.pass_mark_option" label="Choose pass mark after exam"></v-checkbox>
                                                    </v-layout>
                                                    <h5 class="fw-600">Result</h5>
                                                    <hr class="m-t-5 m-b-10">
                                                    <v-layout>
                                                        <div class="nonpadding-checkbox">
                                                            <v-select class="m-r-10" v-model="dialogItem.show_result_option" :items="show_result_option" label="Show Result Option" :rules="[v => !!v || 'required']"></v-select>
                                                            <v-checkbox v-model="dialogItem.show_pdf" label="Show downloadable pdf"></v-checkbox>   
                                                        </div>   
                                                    </v-layout>
                                                    <!-- @add_item_submit -->
                                                    <hr>
                                                    <v-btn color="success" :disabled="!valid || loading" :loading="loading" @click="save">
                                                        submit
                                                    </v-btn>
                                                    <v-btn color="error" @click="close">close</v-btn>
                                                </v-form>
                                            </template>
                                        </v-container>
                                    </v-card-text>
                                </v-card>
                            </v-dialog>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.branch_name}}</td>
                            <td class="text-xs-left">{{ props.item.date | moment("D MMM YY")}}</td>
                            <td class="text-xs-left">{{ props.item.full_mark}}</td>
                            <td class="text-xs-left">{{ props.item.start_at | moment("h:i A")}}</td>
                            <td class="text-xs-left">{{ props.item.full_time}}</td>
                            <td class="text-xs-left">{{ props.item.total_attendance}}</td>
                            <td class="text-xs-left">{{ props.item.selected_question}}</td>
                            <td class="layout px-0">
                                <v-icon small class="mr-2 fs-20" @click="editItem(props.item)"> edit </v-icon>
                                <router-link :to="{ name: 'select_online_exam_question', params: { online_exam_id: props.item.id }}">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon v-on="on" style="top:10px" small class="pos-rel mr-2 fs-25" @click="editItem(props.item)"> post_add </v-icon>
                                        </template>
                                        <span>Add Question</span>
                                    </v-tooltip>
                                </router-link>
                                <router-link v-if="props.item.status == 'finish'" :to="{ name: 'question_view', params: { id: props.item.id }}">
                                    <v-btn small color="primary" class="mr-2 tiny-btn pos-rel" style="top: 8px">
                                        View Question
                                    </v-btn>
                                </router-link>
                                <v-icon small class="mr-2 fs-20" @click="delete_online_exam(props.item)"> delete </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
        </v-container>
        <v-dialog v-model="dialog_loading" hide-dialog persistent width="300">
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
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Name', value: 'name'}, {text: 'Branch', value: 'branch_name'}, {text: 'Exam Date', value: 'date'}, {text: 'Full Mark', value: 'full_mark'}, {text: 'Start At', value: 'start_at'}, {text: 'Full Time', value: 'full_time'}, {text: 'Attendance', value: 'total_attendance'}, {text: 'Selected', value: 'selected_question'}, {text: 'Actions', sortable: false, align: 'left'}],
                    // @list_column_data
                    rows: [],
                    search: '',
                    search_branch: 'all_branch',
                    pagination: {
                        'sortBy': 'date',
                        'descending': false,
                        'page': 1,
                        'rowsPerPage': 25
                    },
                    data_load: false,
                    search_date: '',
                    dialog: false,
                    valid: true,
                    rules: {
                      required: value => !!value || 'Required.'
                    },
                    dialogItem: {branch_id: 'all_branch', subjects: [{subject: '', question_range: ''}]},
                    original_exam_list: [],
                    exam_list: [],
                    echelon: [],
                    branch: [],
                    subject: [],
                    loading: false,
                    editedIndex: -1,
                    dialog_loading: false,
                    original_subject: [],
                    sit_exam_at: [{value: 'anytime', text: 'Anytime of the day'}, {value: 'start_time', text: 'Start time of the exam'}],
                    show_result_option: [{value: 'immediately', text: 'Show result immediately'}, {value: 'on_allow', text: 'Show result when I want'}],
                    date_dialog: false,
                    exam_date_dialog: false

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'question');
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
                    return this.editedIndex === -1 ? 'New Online Exam' : 'Edit Online Exam'
                }
            },

            watch: {
                dialog(val) {
                    val || this.close()
                }
            },

            methods: {
                admin_branch_list() {
                    this.branch = window_branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    this.branch.unshift({value: 'all_branch', text: 'All Branch'})
                    this.get_online_exam()
                    this.get_online_exam_subject()
                },
                get_online_exam() {
                    this.data_load = true;
                    this.search_date = this.search_date ? this.search_date : this.$moment().format('YYYY-MM-DD')
                    this.axios.get('/admin/exam/get_online_exam/' + this.search_branch + '/' + this.search_date).then(response => {
                        this.rows = response.data.online_exam_list;
                        console.log(this.rows)
                        this.pagination.descending = false
                        this.data_load = false
                    });
                },
                get_online_exam_subject() {
                  this.data_load = true;
                      this.axios.get('/admin/exam/get_online_exam_subject').then(response => {
                          this.subject = response.data.subjects.map(x => {return {value: x.id, text: x.name}});
                      });
                },
                delete_online_exam(item){
                    var con = confirm("Are you sure?")
                    if (!con) {return false}
                    this.axios.post('/admin/exam/delete_online_exam/' + item.id).then(response => {
                        var index = this.rows.findIndex(x => x.id == item.id)
                        this.rows.splice(index, 1)
                        $('.user_nav').addClass('successful')
                        setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                    });
                },
                get_branch_item(){
                    this.get_online_exam()                    
                },
                add_remove_subject(option, index){
                    if (option == 'add') {this.dialogItem.subjects.push({subject: '', question_range: ''})}
                    if (option == 'remove') {this.dialogItem.subjects.splice(index, 1)}
                },
                editItem (item) {
                    this.editedIndex = this.rows.findIndex(x => x.id==item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;
                    // set branch
                    this.dialogItem.branch_id = this.branch[this.branch.findIndex(x => x.text == this.rows[this.editedIndex].branch_name)].value
                    // exam rules
                    this.dialogItem.change_answer = this.rows[this.editedIndex].exam_rule.includes('change_answer') ? true : false
                    this.dialogItem.answer_later = this.rows[this.editedIndex].exam_rule.includes('answer_later') ? true : false
                    this.dialog = true
                    this.dialogItem.left_answer = this.rows[this.editedIndex].exam_rule.includes('left_answer') ? true : false
                    this.dialog = true
                    this.dialogItem.random_question = this.rows[this.editedIndex].exam_rule.includes('random_question') ? true : false
                    this.dialogItem.random_option = this.rows[this.editedIndex].exam_rule.includes('random_option') ? true : false
                    // passmark
                    this.dialog = true

                },
                save() {
                    if (!this.$refs.form.validate()) {return false}
                    this.dialog_loading = true
                    if (this.editedIndex == -1) {
                        this.axios.post('/admin/exam/add_online_exam', this.dialogItem).then(response => {
                            this.rows.push(response.data.online_exam_list)
                            $('.user_nav').addClass('successful')
                            setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            this.dialog_loading = false
                            this.dialog = false
                        }, error => {
                            this.dialog_loading = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                        });
                    } else {
                        this.axios.post('/admin/exam/edit_online_exam/' + this.editedId, this.dialogItem).then(response => {
                            this.rows[this.editedIndex] = response.data.online_exam_list
                            $('.user_nav').addClass('successful')
                            setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            this.dialog_loading = false
                            this.dialog = false
                        }, error => {
                            this.dialog_loading = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                        });
                    }
                },
                close () {
                    // this.dialog = false
                    this.dialog = false
                    this.dialogItem.subjects = [{'subject': '', 'question_range': ''}]
                    setTimeout(function () { this.$refs.form.reset() }.bind(this), 200)
                },
            }
    } 
</script>


<style>
  .v-alert{
      position: absolute;
      z-index: 1;
      right: 10px;
      top: 10px;
      width: 300px;
      height: 50px;
  }
  .v-menu__content .v-list__tile__title {
    font-size: 11px;
  }
  .v-menu__content .v-list__tile {
    height: 32px;
  }
  .nonpadding-checkbox .v-input--selection-controls {
    margin-top: 0px;
    padding-top: 0px;
  }
  .nonpadding-checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot {
    margin-bottom: 0px;
  }
  .nonpadding-checkbox .v-input--selection-controls.v-input .v-label {
    top: 3px;
  }
  .nonpadding-checkbox .v-text-field {
    padding-top: 0px;
    margin-top: 0px;
  }
</style>