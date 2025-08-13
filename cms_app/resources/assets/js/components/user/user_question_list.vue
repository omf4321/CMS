
<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Question Lists</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-toolbar flat style="background: none">
                        <v-layout>                        
                            <v-dialog full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="search_date"></v-text-field>
                                <v-date-picker ref="picker" v-model="search_date"></v-date-picker>
                            </v-dialog>
                            <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="get_question_list"> Go </v-btn>
                        </v-layout>
                    </v-toolbar>
                    <hr class="m-t-5 m-b-5">
                    <div class="text-danger p-l-8 p-r-8 fs-13" v-if="upcomming_exam_class">Create new question for class:  <span class="fs-17 text-success">{{upcomming_exam_class}}</span></div>
                    <hr class="m-t-5 m-b-5">
                    <v-dialog v-model="dialog" persistent max-width="500px">
                        <!-- @add_item_button -->
                        <v-btn small slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1">New Question</v-btn>
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
                                            <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'required']" label="Class" @change="get_schedule_list" :disabled="editedIndex>-1"></v-select>
                                            <h6 class="fw-600 fs-13" v-if="exam_list.length">Choose Exam for this question</h6>
                                            <hr v-if="exam_list.length" class="m-b-5 m-t-5" style="border-color: #444">
                                            <v-checkbox v-for="(list, i) in exam_list" v-model="list.value" :label="list.text" :key="i"></v-checkbox>
                                            <!-- @add_item_submit -->
                                            <div class="m-t-10">
                                                <v-btn :disabled="!valid || loading" :loading="loading" @click="save">
                                                    submit
                                                </v-btn>
                                                <v-btn @click="close">close</v-btn>
                                            </div>
                                        </v-form>
                                    </template>
                                </v-container>
                            </v-card-text>
                        </v-card>
                    </v-dialog>
                    <hr class="m-t-5 m-b-20">
                    <!-- @list_table @list_header @list_column -->
                    <div class="row fs-12">
                        <div class="col-xs-4 p-r-8 fw-600">Details</div>
                        <div class="col-xs-4 p-l-8 p-r-8 fw-600">Topic</div>
                        <div class="col-xs-4 p-l-8 fw-600 text-center">Action</div>
                    </div>
                    <hr class="m-t-10 m-b-10" style="border-color: #222">
                    <div class="row fs-12" v-for="question in question_list">
                        <div class="col-xs-4 p-r-8">
                            <div class="fw-600 fs-13">{{question.subjects.echelons.name}} - {{question.subjects.name | first_letter_word}}</div>
                            <div>
                                <span v-for="schedule in question.schedules" class="m-r-5">{{schedule.batches.name}}</span>
                            </div>
                            <div>Ex Date: {{question.schedules[0].date | moment("D MMM")}}</div>
                        </div>
                        <div class="col-xs-4 p-l-8 p-r-8">
                            <div v-if="question.chapter_text">Ch- <span v-for="chapter in question.chapters">{{chapter.name}}</span></div>
                            <div style="white-space:nowrap;overflow:hidden">{{question.topic}}</div>
                        </div>
                        <div class="col-xs-4 p-l-8 text-center">
                            <router-link :to="{ name: 'question_setup', params: { id: question.id }}">
                                <v-btn flat color="primary" class="tiny-btn pos-rel" style="top: 0px; margin: 0px; padding: 5px"> <v-icon class="fs-22">add_task</v-icon> </v-btn>
                            </router-link>
                            <v-btn flat color="info" class="tiny-btn pos-rel" style="top: 0px; margin: 0px; padding: 5px" @click="editItem(question)"> <v-icon class="fs-20">edit</v-icon> </v-btn>
                            <v-btn v-if="question.status != 'printable'" flat color="error" class="tiny-btn pos-rel" style="top: 0px; margin: 0px; padding: 5px" @click="question_delete(question)"> <v-icon class="fs-20">delete</v-icon> </v-btn>
                            <div>
                                <v-chip small color="success" class="m-t-5 fc-white" v-if="question.status =='finish'">Finished</v-chip>
                                <v-chip small color="success" class="m-t-5 fc-white" v-if="question.status =='printable'">Printed</v-chip>
                                <v-chip small color="warning" class="m-t-5 fs-11 fc-white" v-if="!question.status">Pending</v-chip>
                            </div>
                            
                        </div>
                        <div class="col-xs-12" >
                            <hr style="float:none; clear:both; border-color: #999" class="m-t-12 m-b-12">
                        </div>
                    </div>
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
                    question_list: [],
                    search: '',
                    pagination: {
                        'sortBy': 'date',
                        'descending': false,
                        'page': 1,
                        'rowsPerPage': 25
                    },
                    data_load: false,
                    search_date: '',
                    dialogItem: {subjects: {echelons:{}}},
                    original_exam_list: [],
                    exam_list: [],
                    echelon: [],
                    branch: [],
                    rules: {
                        required: value => !!value || 'Required.'
                    },
                    dialog: false,
                    valid: true,
                    dialog_loading: false,
                    editedIndex: -1,
                    loading: false,
                    upcomming_exam_class: ''

                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                if (window_user_role == 'teacher' && window_authorise != 'authorised') {return next('/authentication')}
                let p = window_admin_permissions.findIndex(x => x.name=='question_check');
                let r = window_user_role.findIndex(x => x == 'teacher')
                if (p > -1 || r > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
            },
            // @load_method
            created() {
                this.admin_branch_list();
            },

            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                }
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Question' : 'Edit Question'
                }
            },

            methods: {
                admin_branch_list(){
                    this.branch = window_branch;
                    if (this.branch.length==1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list();
                        this.get_question_list()
                    }
                    this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                },
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                get_question_list() {
                    this.dialog_loading = true;
                    this.search_date = this.search_date ? this.search_date : this.$moment().format('YYYY-MM-DD')
                    Vue.axios.get('/user/question/question_makable_list_by_date/' + this.search_date).then(response => {
                        this.question_list = response.data.question_list;
                        this.upcomming_exam_class = response.data.exam_alert.map(x => {return x.subjects.echelons.name })
                        if (this.upcomming_exam_class.length) {
                            this.upcomming_exam_class = this.upcomming_exam_class.join(', ')
                        } else {
                            this.upcomming_exam_class = ''
                        }
                        this.pagination.descending = false
                        this.dialog_loading = false
                    });
                },  
                get_schedule_list() {
                    this.dialog_loading = true;
                    Vue.axios.get('/user/question/exam_schedule_by_class_date/' + this.dialogItem.echelon_id).then(response => {
                        this.original_exam_list = response.data.schedule_list
                        this.exam_list = [];
                        for (var i = 0; i < response.data.schedule_list.length; i++) {
                            this.exam_list.push({
                                id: response.data.schedule_list[i].id,
                                text: this.$moment(response.data.schedule_list[i].date).format('DD MMM') + ' -' + response.data.schedule_list[i].batches.name + ' - ' + response.data.schedule_list[i].subjects.name,
                                value: false
                            })
                        }
                        if (this.editedIndex> -1) {
                            for (var i = 0; i < this.question_list[this.editedIndex].schedules.length; i++) {
                                var index = this.exam_list.findIndex(x => x.id == this.question_list[this.editedIndex].schedules[i].id)
                                this.exam_list[index].value = true
                            }
                        }
                        this.dialog_loading = false
                    });
                },
                editItem (item) {
                    this.editedIndex = this.question_list.findIndex(x => x.id==item.id);
                    this.editedId = this.question_list[this.editedIndex].id;
                    this.dialogItem.branch_id = 1
                    this.dialogItem.subject_id = this.question_list[this.editedIndex].subject_id
                    this.dialogItem.echelon_id = this.question_list[this.editedIndex].subjects.echelon_id
                    this.get_schedule_list()
                    setTimeout(function () { this.dialog = true }.bind(this), 300)
                },
                save() {
                    if (!this.$refs.form.validate() || !this.exam_list) {return false}
                    var schedule = []
                    var exam_list = this.exam_list.filter(x => x.value == true)
                    for (var i = 0; i < exam_list.length; i++) {
                        schedule.push(exam_list[i].id)
                        var index = this.original_exam_list.findIndex(x => x.id == exam_list[i].id)
                        if (this.dialogItem.subject_id && this.dialogItem.subject_id != this.original_exam_list[index].subject_id) {
                            return alert('Select exams do not have same subject')
                        } else {
                            this.dialogItem.subject_id = this.original_exam_list[index].subject_id
                        }
                    }
                    this.dialogItem.schedule_ids = schedule
                    this.dialog_loading = true
                    if (this.editedIndex == -1) {
                        Vue.axios.post('/user/question/save_question_list', this.dialogItem).then(response => {
                            this.question_list.push(response.data.question_list)
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
                        Vue.axios.post('/user/question/edit_question_list/' + this.editedId, this.dialogItem).then(response => {
                            this.question_list[this.editedIndex] = response.data.question_list
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
                question_delete(item){
                    var con = confirm("Are you sure?")
                    if (!con) {return false}
                    Vue.axios.post('/user/question/question_delete/' + item.id).then(response => {
                        var index = this.question_list.findIndex(x => x.id == item.id)
                        this.question_list.splice(index, 1)
                        $('.user_nav').addClass('successful')
                        setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                    });
                },
                // @add_item_method_dialog
                close() {
                    this.dialog = false
                    this.$refs.form.reset()
                    this.exam_list = []
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
</style>