
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
                            <v-dialog full-width lazy ref="date_ref" width="290px" class="m-r-10">
                                <v-text-field label="Date From" :clearable=true readonly slot="activator" v-model="search_date"></v-text-field>
                                <v-date-picker ref="picker" v-model="search_date"></v-date-picker>
                            </v-dialog>
                            <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-btn small color="primary" class="ml-2 tiny-btn pos-rel" style="top: 10px" @click="question_list"> Go </v-btn>
                            <v-spacer></v-spacer>
                            <v-dialog v-model="dialog" persistent max-width="600px">
                            <!-- @add_item_button -->
                                <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, dialogItem.branch_id = 1">New Question</v-btn>
                                <v-card>
                                    <v-card-title>
                                        <!-- @add_item_title -->
                                        <span class="headline">Add/Edit Questions</span>
                                    </v-card-title>
                                    <v-card-text>
                                        <v-container grid-list-md>
                                            <!-- @add_item_field -->
                                            <template>
                                                <v-form ref="form" v-model="valid" lazy-validation>
                                                    <v-layout>
                                                        <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'required']" label="Branch" required @change="get_item"></v-select>
                                                    </v-layout>
                                                    <v-layout>
                                                        <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'required']" label="Class" @change="get_subject" :disabled="editedIndex>-1"></v-select>
                                                    </v-layout>
                                                    <v-layout>
                                                        <v-autocomplete v-model="dialogItem.subject_id" :items="subject" :rules="[v => !!v || 'required']" label="Subject" @change="get_schedule_list" :disabled="editedIndex>-1"></v-autocomplete>
                                                    </v-layout>
                                                    <v-layout>
                                                        <v-combobox class="exam_lists" :clearable="true" :items="exam_list" hide-selected label="Exams" multiple ref="combobox" small-chips v-model="dialogItem.exam_lists" :rules="[v => !!v || 'required']"></v-combobox>
                                                    </v-layout>
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
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.subjects.echelons.name}}</td>
                            <td class="text-xs-left">
                                <span v-for="(schedule, s_i) in props.item.schedules"><span class="m-r-5">{{schedule.batches.name}}</span></span>
                            </td>
                            <td class="text-xs-left">{{ props.item.subjects ? props.item.subjects.name : '' }}</td>
                            <td class="text-xs-left solaiman fs-14">
                                <span class="m-r-5" v-for="(chap, key) in props.item.chapters">{{chap.name}}
                                    <span v-if="key + 1 < props.item.chapters.length">,</span>
                                </span>
                            </td>
                            <td class="text-xs-left">{{ props.item.topic }}</td>
                            <td class="text-xs-left">{{ props.item.schedules[0].date | moment("D MMM YY")}}</td>
                            <td class="layout px-0">
                                <v-btn small color="info" class="mr-2 tiny-btn pos-rel" style="top: 8px" @click="editItem(props.item)">
                                    Edit
                                </v-btn>
                                <router-link :to="{ name: 'question_setup', params: { id: props.item.id }}">
                                    <v-btn small color="primary" class="mr-2 tiny-btn pos-rel" style="top: 8px">
                                        Make
                                    </v-btn>
                                </router-link>
                                <router-link v-if="props.item.status == 'finish'" :to="{ name: 'question_view', params: { id: props.item.id }}">
                                    <v-btn small color="primary" class="mr-2 tiny-btn pos-rel" style="top: 8px">
                                        Print Setup
                                    </v-btn>
                                </router-link>
                                <router-link v-if="props.item.status == 'printable'" :to="{ name: 'question_print', params: { id: props.item.id }}">
                                    <v-btn small color="success" class="mr-2 tiny-btn pos-rel" style="top: 8px">
                                        Print
                                    </v-btn>
                                </router-link>
                                <v-btn small color="error" class="mr-2 tiny-btn pos-rel" style="top: 8px" @click="question_delete(props.item)">
                                    Del
                                </v-btn>
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
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Class', value: 'subjects.echelons.name'}, {text: 'Batch', value: 'schedules.batches.name'}, {text: 'Subject', value: 'subjects.name'}, {text: 'Chapter', value: 'chapters.name'}, {text: 'Topic', value: 'topic'}, {text: 'Exam Date', value: 'created_at'}, {text: 'Actions', sortable: false, align: 'left'}],
                    // @list_column_data
                    rows: [],
                    search: '',
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
                    dialogItem: {subjects: {echelons:{}}},
                    original_exam_list: [],
                    exam_list: [],
                    echelon: [],
                    branch: [],
                    loading: false,
                    editedIndex: -1,
                    dialog_loading: false,
                    subject: [],
                    original_subject: []


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
                    return this.editedIndex === -1 ? 'New Class' : 'Edit Class'
                }
            },

            watch: {
                dialog(val) {
                    val || this.close()
                }
            },

            methods: {
                admin_branch_list(){
                    this.branch = window_branch;
                    if (this.branch.length==1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list();
                        this.question_list()
                        setTimeout(function () { this.admin_subject_list() }.bind(this), 2000)
                    }
                    this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
                },
                admin_echelon_list() {                    
                    this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                },
                admin_subject_list(){
                    Vue.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                        this.original_subject = response.data.subject_list_by_branch;
                    });
                },
                get_subject(){
                   var echelon_id = this.dialogItem.echelon_id
                   if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
                   var subject = Object.assign([], this.original_subject);
                   let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
                   this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })   
                },
                question_list() {
                    this.data_load = true;
                    this.search_date = this.search_date ? this.search_date : this.$moment().format('YYYY-MM-DD')
                    Vue.axios.get('/admin/question/question_makable_list_by_date/' + this.search_date).then(response => {
                        this.rows = response.data.question_list;
                        this.pagination.descending = false
                        this.data_load = false
                    });
                },
                get_schedule_list() {
                    this.dialog_loading = true;
                    Vue.axios.get('/admin/question/exam_schedule_by_class_date/' + this.dialogItem.subject_id).then(response => {
                        this.original_exam_list = response.data.schedule_list
                        this.exam_list = [];
                        for (var i = 0; i < response.data.schedule_list.length; i++) {
                            var topic = ''
                            if (response.data.schedule_list[i].chapter_text) {
                                topic = '-' + response.data.schedule_list[i].chapter_text
                            }
                            if (response.data.schedule_list[i].topic) {
                                topic = topic + '-' + response.data.schedule_list[i].topic
                            }
                            this.exam_list.push({
                                value: response.data.schedule_list[i].id,
                                text: this.$moment(response.data.schedule_list[i].date).format('DD MMM') + ' -' + response.data.schedule_list[i].batches.name + ' - ' + response.data.schedule_list[i].subjects.name + topic
                            })
                            topic = ''
                        }
                        this.dialog_loading = false
                    });
                },
                question_delete(item){
                    var con = confirm("Are you sure?")
                    if (!con) {return false}
                    Vue.axios.post('/admin/question/question_makable_delete/' + item.id).then(response => {
                        var index = this.rows.findIndex(x => x.id == item.id)
                        this.rows.splice(index, 1)
                        $('.user_nav').addClass('successful')
                        setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)   
                    });
                },
                get_item(){
                   if (this.branch.length>1) {
                       this.admin_echelon_list(this.dialogItem.branch_id);
                   }
                },
                editItem (item) {
                    this.editedIndex = this.rows.findIndex(x => x.id==item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;
                    this.dialogItem.branch_id = 1
                    this.dialogItem.subject_id = this.rows[this.editedIndex].subject_id
                    this.dialogItem.echelon_id = this.rows[this.editedIndex].subjects.echelon_id
                    this.get_schedule_list()
                    this.dialogItem.exam_lists = []
                    for (var i = 0; i < this.rows[this.editedIndex].schedules.length; i++) {
                        this.dialogItem.exam_lists.push({
                            value: this.rows[this.editedIndex].schedules[i].id,
                            text: this.$moment(this.rows[this.editedIndex].schedules[i].date).format('DD MMM') + ' -' + this.rows[this.editedIndex].schedules[i].batches.name + ' - ' + this.rows[this.editedIndex].subjects.name
                        })
                    }
                    this.dialog = true
                },
                save() {
                    if (!this.$refs.form.validate() || !this.dialogItem.exam_lists) {return false}
                    var schedule = []
                    for (var i = 0; i < this.dialogItem.exam_lists.length; i++) {
                        schedule.push(this.dialogItem.exam_lists[i].value)
                        var index = this.original_exam_list.findIndex(x => x.id == this.dialogItem.exam_lists[i].value)
                        if (this.dialogItem.subject_id && this.dialogItem.subject_id != this.original_exam_list[index].subject_id) {
                            return alert('Select exams do not have same subject')
                        } else {
                            this.dialogItem.subject_id = this.original_exam_list[index].subject_id
                        }
                    }
                    this.dialogItem.schedule_ids = schedule
                    this.dialog_loading = true
                    if (this.editedIndex == -1) {
                        Vue.axios.post('/admin/question/save_question_list', this.dialogItem).then(response => {
                            this.rows.push(response.data.question_list)
                            $('.user_nav').addClass('successful')
                            setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            this.dialog_loading = false
                            this.dialog = false
                        }, error => {
                            $('.user_nav').addClass('denied')
                            setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                        });
                    } else {
                        Vue.axios.post('/admin/question/edit_question_list/' + this.editedId, this.dialogItem).then(response => {
                            this.rows[this.editedIndex] = response.data.question_list
                            $('.user_nav').addClass('successful')
                            setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            this.dialog_loading = false
                            this.dialog = false
                        }, error => {
                            $('.user_nav').addClass('denied')
                            setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                        });
                    }
                },
                close () {
                    // this.dialog = false
                    this.dialog = false
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
</style>