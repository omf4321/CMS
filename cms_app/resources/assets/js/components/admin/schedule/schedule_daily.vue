<!-- =======Operation========
    @page_headline
    @list (title, table)
    @add_item (button, title, field, submit)
    @edit_item
    @delete_item
    @accesories
     -->
<template>
    <div class="daily_schedule">
        <v-container fluid >
            <!-- @page_headline -->
            <h3 class="m-t-0 fs-medium">
                Manage Daily Schedule
                <span style="float:right; font-size: 15px">
                    Total Class: {{count_schedule}}
                </span>
            </h3>
            <v-divider class="my-3">
            </v-divider>
            <template>
                <div>
                    <v-toolbar color="schedule_toolbar grey lighten-2 p-b-15 p-t-10" flat>
                        <div class="col-xs-12 col-md-3 p-l-5 p-r-5">
                            <v-btn class="tiny-btn" style="height: 28px!important" outline color="info" @click="daily_schedule('today')">Today</v-btn>
                            <v-btn class="tiny-btn" style="height: 28px!important" outline color="info" @click="daily_schedule('next_day')">Next Day</v-btn> 
                        </div>
                        <v-dialog full-width class="col-xs-6 col-md-2 pos-rel p-r-5" style="top: 10px" lazy ref="date_ref" width="290px">
                            <v-text-field :clearable=true label="From" readonly slot="activator" :value="search_from_date"></v-text-field>
                            <v-date-picker ref="picker" v-model="search_from_date"></v-date-picker>
                        </v-dialog>
                        <v-dialog class="col-xs-6 col-md-2 pos-rel p-l-5" style="top: 10px" full-width lazy ref="date_ref_1" width="290px">
                            <v-text-field :clearable=true label="To" readonly slot="activator" :value="search_to_date"></v-text-field>
                            <v-date-picker ref="picker_1" v-model="search_to_date"></v-date-picker>
                        </v-dialog>
                        <v-text-field :clearable="true" class="col-xs-10 col-md-3 p-r-5" hide-details label="Search" single-line v-model="search"> </v-text-field>
                        <v-text-field :clearable="true" class="col-xs-5 col-md-3 p-r-5 hidden-xs" hide-details label="Search" single-line v-model="search_1"> </v-text-field>
                        <div class="col-xs-2 col-md-2 mb-2">
                            <v-btn small outline class="tiny-btn pos-rel m-l-5 p-l-5" style="top: 0px" @click="daily_schedule">Go</v-btn>
                        </div>
                        <!-- <v-icon @click="clear" class="col-xs-2 col-md-2 p-l-5" style="top: 5px;">backspace</v-icon> -->
                        <v-spacer></v-spacer>
                        <div class="col-md-4 p-l-5 p-r-5">
                            <v-btn small class="mb-2" color="error" @click="delete_dialog=true">Delete</v-btn>
                            <v-btn small @click="editedIndex=-1, add_dialog = true" class="mb-2" color="primary" dark> Add New </v-btn>
                        </div>
                        <!-- add dialog -->
                        <v-dialog max-width="600px" persistent v-model="add_dialog">
                            <!-- @add_item_button -->
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="fs-medium">Add/Edit Schedule</span>
                                    <v-spacer></v-spacer>
                                    <v-btn @click.native="close" icon style="right: 5px; top: 5px; position: absolute!important"> <v-icon> close </v-icon></v-btn>
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md class="p-t-0">
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form lazy-validation ref="add_form" v-model="valid">
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-autocomplete :disabled="editedIndex>-1" :clearable="true" :items="echelon" hide-selected @change="get_batch" label="Class" v-model="dialogItem.echelon_id">
                                                        </v-autocomplete>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-autocomplete :disabled="editedIndex>-1" :clearable="true" :items="batch" hide-selected label="Batch" :rules="[v => !!v || 'required']" @change="get_advance_option_batch" v-model="dialogItem.batch_id"> </v-autocomplete>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-text-field :rules="[v => !!v || 'required']" label="Period" required v-model="dialogItem.period"> </v-text-field>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-autocomplete :items="subject" :rules="[v => !!v || 'required']" id="edit_subject" label="Subject" v-model="dialogItem.subject_id"> </v-autocomplete>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-autocomplete :items="teacher" :rules="[v => !!v || 'required']" label="Teacher" v-model="dialogItem.teacher_id"> </v-autocomplete>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <template>
                                                            <v-dialog full-width lazy ref="date_ref" width="290px">
                                                                <v-text-field :rules="[v => !!v || 'required']" label="Date" readonly slot="activator" v-model="dialogItem.date"> </v-text-field>
                                                                <v-date-picker ref="picker" v-model="dialogItem.date">
                                                                </v-date-picker>
                                                            </v-dialog>
                                                        </template>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-select :clearable="true" :items="schedule_type" :rules="[v => !!v || 'required']" label="Schedule Type" required v-model="dialogItem.schedule_type">
                                                        </v-select>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4"> 
                                                        <v-text-field id="edit_time" mask="time-with-seconds" label="Time" v-model="dialogItem.time">
                                                        </v-text-field>
                                                    </div>
                                                    <div class="col-xs-6 col-md-4">
                                                        <v-text-field label="Chapter" v-model="dialogItem.chapter_text">
                                                        </v-text-field>
                                                    </div>
                                                    <div class="pb-0 pt-0 col-xs-12 col-md-8">
                                                        <v-text-field label="Topic" v-model="dialogItem.topic">
                                                        </v-text-field>
                                                    </div>
                                                    <div class="pb-0 pt-0 col-xs-12 col-md-4">
                                                        <v-checkbox v-model="dialogItem.lecture_sheet" label="Lecture Sheet"></v-checkbox>
                                                    </div>
                                                </div>
                                                <!-- @add_item_submit -->
                                                <v-expansion-panel style="position: relative; box-shadow: none; margin-left: -15px;" v-model="advance_option">
                                                    <v-expansion-panel-content>
                                                        <template v-slot:header class="p-l-15">
                                                            <div>Advance Save Option</div>
                                                        </template>
                                                            <div class="p-15">
                                                                <div class="fs-13 p-b-15">Select batch for same change</div>
                                                                <v-combobox :clearable="true" :items="advance_option_batch" hide-selected label="Batch" multiple ref="combobox" small-chips v-model="dialogItem.batches">
                                                        </v-combobox>
                                                            </div>
                                                    </v-expansion-panel-content>
                                                </v-expansion-panel>
                                                <v-btn small color="success" :disabled="!valid || loading" :loading="loading" @click="save()">Save</v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                        <!-- @add_item_dialog -->
                        <!-- delete dialog -->
                        <v-dialog max-width="600px" persistent v-model="delete_dialog">
                            <!-- @add_item_button -->
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="fs-medium">Delete Schedule By Date and Batch</span>
                                    <v-spacer></v-spacer>
                                    <v-btn @click.native="close" icon style="right: 5px; top: 5px; position: absolute!important"> <v-icon> close </v-icon></v-btn>
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md class="p-t-0">
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form lazy-validation ref="delete_form" v-model="valid">
                                                <div class="row">
                                                    <div class="col-xs-6 col-md-6">
                                                        <v-autocomplete :clearable="true" :items="echelon" hide-selected @change="get_batch" label="Class" v-model="dialogItem.echelon_id">
                                                        </v-autocomplete>
                                                    </div>
                                                    <div class="col-xs-6 col-md-6">
                                                        <v-combobox :clearable="true" :items="batch" hide-selected label="Batch" :rules="[v => !!v || 'required']" @change="get_advance_option_batch" small-chips v-model="dialogItem.delete_batch" multiple> </v-combobox>
                                                    </div>
                                                    <div class="col-xs-6 col-md-6">
                                                        <template>
                                                            <v-dialog full-width lazy ref="date_delete_ref" width="290px">
                                                                <v-text-field :rules="[v => !!v || 'required']" label="Date From" readonly slot="activator" v-model="dialogItem.delete_from"> </v-text-field>
                                                                <v-date-picker ref="picker" v-model="dialogItem.delete_from">
                                                                </v-date-picker>
                                                            </v-dialog>
                                                        </template>
                                                    </div>
                                                    <div class="col-xs-6 col-md-6">
                                                        <template>
                                                            <v-dialog full-width lazy ref="date_delete_ref1" width="290px">
                                                                <v-text-field :rules="[v => !!v || 'required']" label="Date To" readonly slot="activator" v-model="dialogItem.delete_to"> </v-text-field>
                                                                <v-date-picker ref="picker" v-model="dialogItem.delete_to">
                                                                </v-date-picker>
                                                            </v-dialog>
                                                        </template>
                                                    </div>
                                                    
                                                </div>
                                                <v-btn small color="success" :disabled="!valid || loading" :loading="loading" @click="delete_by_date()">Delete</v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :search="search" :headers="headers" :items="itemsSorted" :loading="data_load" :pagination.sync="pagination" class="elevation-1" hide-actions>
                        <v-progress-linear color="blue" indeterminate slot="progress">
                        </v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.batches.echelons.name }} </td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.batches.name }} </td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.schedule_type }} </td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}">{{ props.item.subjects ? props.item.subjects.name : '' }}</td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.teachers ? props.item.teachers.name : '' }}</td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.chapter_text }} </td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.topic }}</td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.period | ordinal_number }} - {{props.item.time}}</td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}"> {{ props.item.date | moment("D MMM YY, ddd") }} </td>
                            <td :class="{'text-xs-left':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}">
                                <v-checkbox :input-value="props.item.lecture_sheet" @change="lecture_sheet_update(props.item.id)"> </v-checkbox>
                            </td>
                            <td :class="{'text-xs-left justify-center layout':true, 'green lighten-4': props.item.schedule_type =='exam', 'red lighten-4': !props.item.chapter_text && !props.item.topic}">
                                <v-icon @click="pick_edit_data(props.item)" class="mr-2" small> edit </v-icon>
                                <!-- @delete_item -->
                                <v-icon @click="daily_schedule_delete(props.item)" class="mr-2" small> delete </v-icon>
                                <v-icon @click="get_exchangable(props.item)" class="mr-2" small> find_replace </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
            <v-dialog max-width="800px" persistent v-model="dialog">
                <v-card>
                    <v-card-title>
                        <!-- @add_item_title -->
                        <v-btn @click.native="dialog = false" icon>
                            <v-icon>
                                close
                            </v-icon>
                        </v-btn>
                        <span class="headline">
                            Exchange Class
                        </span>
                        <v-spacer>
                        </v-spacer>
                        <v-btn :disabled="loading" :loading="loading" @click="exchange_class" color="primary">
                            Save
                        </v-btn>
                    </v-card-title>
                    <v-card-text>
                        <v-container grid-list-md>
                            <!-- @add_item_field -->
                            <template>
                                <v-form lazy-validation ref="form" v-model="valid">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <v-autocomplete :items="subject" @change="get_exchangable_list_from" label="From Subject" required v-model="exchange_from_subject_id">
                                            </v-autocomplete>
                                            <v-list subheader two-line v-bind:key="e_list.id" v-for="(e_list, key) in exchangable_list_from">
                                                <v-list-item @click>
                                                    <v-list-item-action>
                                                        <v-checkbox @change="get_exchange_from(e_list, key)" v-model="e_list.value">
                                                        </v-checkbox>
                                                    </v-list-item-action>
                                                    <v-list-item-content @click>
                                                        <v-list-item-title>
                                                            {{e_list.date | moment("D MMM YY, ddd")}}
                                                        </v-list-item-title>
                                                        <v-list-item-sub-title>
                                                            {{e_list.schedule_type}}
                                                        </v-list-item-sub-title>
                                                        <span class="exchange_index" v-if="e_list.index">
                                                            {{e_list.index}}
                                                        </span>
                                                    </v-list-item-content>
                                                </v-list-item>
                                            </v-list>
                                        </div>
                                        <div class="col-md-2">
                                        </div>
                                        <div class="col-md-5">
                                            <v-autocomplete :items="subject" @change="get_exchangable_list_to" label="To Subject" required v-model="exchange_to_subject_id">
                                            </v-autocomplete>
                                            <v-list subheader two-line v-bind:key="e_list.id" v-for="(e_list, key) in exchangable_list_to">
                                                <v-list-item>
                                                    <v-list-item-action>
                                                        <v-checkbox @change="get_exchange_to(e_list,key)" v-model="e_list.value">
                                                        </v-checkbox>
                                                    </v-list-item-action>
                                                    <v-list-item-content>
                                                        <v-list-item-title>
                                                            {{e_list.date | moment("D MMM YY, ddd")}}
                                                        </v-list-item-title>
                                                        <v-list-item-sub-title>
                                                            {{e_list.schedule_type}}
                                                        </v-list-item-sub-title>
                                                        <span class="exchange_index" v-if="e_list.index">
                                                            {{e_list.index}}
                                                        </span>
                                                    </v-list-item-content>
                                                </v-list-item>
                                            </v-list>
                                        </div>
                                    </div>
                                    <!-- @add_item_submit -->
                                </v-form>
                            </template>
                        </v-container>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-container>
    </div>
</template>
<script>
    export default {
      data() {
              return {
                // @list_header_data
                headers: [
                  { text: 'Class', value: 'batches.echelons.name' },
                  { text: 'Batch', value: 'batches.name' },
                  { text: 'Schedule', value: 'schedule_type' },
                  { text: 'Subject', value: 'subjects.name' },
                  { text: 'Teacher', value: 'teachers.name' },
                  { text: 'Chapter', value: 'chapter_text' },
                  { text: 'Topic', value: 'topic' },
                  { text: 'Time', value: 'time' },
                  { text: 'Date & Day', value: 'date' },
                  { text: 'Sheet', value: 'lecture_sheet'},
                  { text: 'Actions', value: 'name', sortable: false, align: 'center' }
                ],
                // @list_column_data
                rows: [],
                original_rows: [],
                branch: [],
                echelon: [],
                original_batch: [],
                batch: [],
                advance_option_batch: [],
                original_subject: [],
                subject: [],
                chapter: [],
                original_chapter: [],
                teacher: [],
                original_teacher: [],
                day: [{'value': 1, 'text': 'Sun'}, {'value': 2, 'text': 'Mon'}, {'value': 3, 'text': 'Tue'}, {'value': 4, 'text': 'Wed'}, {'value': 5, 'text': 'Thu'}, {'value': 6, 'text': 'Fri'}, {'value': 7, 'text': 'Sat'}],
                schedule_type: [{'value': 'class', 'text': 'Class'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'close', 'text': 'Close'}, {'value': 'online_class', 'text': 'Online Class'}, {'value': 'online_exam', 'text': 'Online Exam'}],
                // @list_search_data
                search: '',
                search_1: '',
                search_batch: '',
                search_from_date: '',
                search_to_date: '',
                // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                pagination: {
                   page: 1,
                   rowsPerPage: -1, // -1 for All",
                   sortBy: 'date'
                },
                dialogItem: {
                  id: '',
                  name: '',
                  branch_id: '',
                  echelon_id: '',
                  subject_id: '',
                  chapter_id: '',
                  subjects: {'id':'', 'name':''},
                  echelons: {'id':'', 'name':''},
                  batches: [],
                  chapters: [],
                  teachers: {'id':'', 'name':''},
                  created_at: '',
                  day: '',
                  date_list: [],
                  date_from: '',
                  date_to: ''
    
                }, 
                editedItem: '',     
                rules: {
                  required: value => !!value || 'Required.'
                },
                
                // @accesories_data
                valid: true,
                success_alert: false,
                error_alert: false,
                loading: false,
                data_load: false,
                advance_option: null,
                inline_edit: false,
                exchangeItem: {'from_subject_id': ''},
                exchangable_list_from: [],
                exchangable_list_to: [],
                exchange_from_subject_id: '',
                exchange_to_subject_id: '',
                exchange_from:[],
                exchange_to:[],
                count_schedule: 0,
                add_dialog: false,
                delete_dialog: false
             } //end return
      },
      // @router_permission
      beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='edit_schedule');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
      },
      // @load_method
      created(){
        this.admin_branch_list();
      },
      
        computed: {
            itemsSorted: function() {
               // item.schedule_id.toString().indexOf(this.search_batch) > -1
                var rows = this.rows
                if (this.search_1) {
                   var items = rows.filter(item =>
                     (item.batches && item.batches.name.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1) ||
                     (item.subjects && item.subjects.name.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1) ||
                     (item.teachers && item.teachers.name.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1) ||
                     this.$moment(item.date).format('D MMM YY, ddd').toLowerCase().indexOf(this.search_1.toLowerCase()) > -1 ||
                     item.time.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1 ||
                     item.schedule_type.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1)
                } else {
                    var items = rows
                }
                this.count_schedule = items.length;
                return items
            },
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
    
        methods:{
        // @add_item_method
        admin_branch_list(){
              this.branch = window_branch;
              if (this.branch.length==1) {
                  this.dialogItem.branch_id = this.branch[0].id
                  this.admin_echelon_list();
                  this.admin_batch_list();
                  this.daily_schedule()
                  setTimeout(function () { this.admin_subject_list() }.bind(this), 2000)
                  setTimeout(function () { this.teacher_list() }.bind(this), 3000)
              }
              this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
        },
        admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
       },
        admin_batch_list(){
          this.axios.get('/admin/request/batch_list_by_branch/'+this.dialogItem.branch_id).then(response => {
              this.original_batch = response.data.batch_list_by_branch;
          }); 
        },
        // @add_item_method
        get_item(){
           if (this.branch.length>1) {
               this.admin_echelon_list(this.dialogItem.branch_id);
           }
        },
        // @list_method
        admin_subject_list(){
            this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                this.original_subject = response.data.subject_list_by_branch;
            });
        },
        teacher_list(){
          this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
              this.original_teacher = response.data.teacher_list_by_branch;
              this.teacher = response.data.teacher_list_by_branch;
              this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
          }); 
        },
        daily_schedule(option){
            var date = this.$moment(new Date()).format('YYYY-MM-DD');
            if (option == 'today' || !this.search_from_date) {
                this.search_from_date = date
                this.search_to_date = date
            }
            if (option == 'next_day') {
                var next_day = this.search_from_date ? this.$moment(this.search_from_date).format('YYYY-MM-DD') : date 
                this.search_from_date = this.$moment(next_day).add(1, 'days').format('YYYY-MM-DD');
                this.search_to_date = this.$moment(next_day).add(1, 'days').format('YYYY-MM-DD');
            }
            this.data_load = true;
            this.axios.get('/admin/schedule/daily_schedule/' + this.search_from_date + '/' + this.search_to_date).then(response => {
                this.original_rows = response.data.daily_schedule;
                this.rows = response.data.daily_schedule;
                this.data_load = false;
            }); 
        },
        get_subject(){
           var echelon_id = this.dialogItem.echelon_id
           if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
           var subject = Object.assign([], this.original_subject);
           let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
           this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })   
        },
        get_batch(){
           var batch = Object.assign([], this.original_batch);
           let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
           this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
           this.get_subject()
        },
        get_advance_option_batch(){
           var batch = Object.assign([], this.batch);
           batch.splice(batch.findIndex(x => x.value == this.dialogItem.batch_id), 1)
           this.advance_option_batch = batch
        },
        // @add_item_method
        daily_schedule_add(){
           if (this.$refs.add_form.validate() && this.loading==false) {
             this.loading=true;
             this.axios.post('/admin/schedule/daily_schedule_add', this.dialogItem).then(response => {
                 var array = response.data.daily_schedule
                 for (var i = 0; i < array.length; i++) {
                    this.rows.push(array[i])
                 }
                 this.close()
                 this.pagination.descending = true
                 $('.user_nav').addClass('successful')
                 this.loading = false;
                 this.add_dialog = false
                 setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
             }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
                this.loading=false; 
             });
           }
        },
        // @edit_item_method
        daily_schedule_edit(){
            if (this.dialogItem.batches.length) {
                var index = this.batch.findIndex(x => x.value == this.dialogItem.batch_id)
                this.dialogItem.selected_batches = this.dialogItem.batches.map(x => {return {value: x.value, text: x.text}})
                this.dialogItem.selected_batches.push({value: this.batch[index].value, text: this.batch[index].text})
            }
            this.loading=true;
            this.axios.post('/admin/schedule/daily_schedule_edit/' + this.editedId, this.dialogItem).then(response => {
               $('.user_nav').addClass('successful')
               if (response.data.item_no>1) {this.daily_schedule()}
               else {
               }
               var item = response.data.daily_schedule
               for (var i = 0; i < item.length; i++) {
                   var index = this.rows.findIndex(x=> x.id == item[i].id)
                   this.rows[index].teacher_id = item[i].teacher_id
                   this.rows[index].subject_id = item[i].subject_id
                   this.rows[index].chapter_text = item[i].chapter_text
                   this.rows[index].topic = item[i].topic
                   this.rows[index].date = item[i].date
                   this.rows[index].subjects = item[i].subjects
                   this.rows[index].teachers = item[i].teachers
                   this.rows[index].batches = item[i].batches
                   this.rows[index].period = item[i].period 
                   this.rows[index].schedule_type = item[i].schedule_type 
                   this.success_alert = true
               }
               this.advance_option = null
               this.loading = false;
               this.add_dialog = false
               setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
           }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false;   
           });
        },
        lecture_sheet_update(id){
            this.editedIndex = this.rows.findIndex(x => x.id == id);
            this.axios.post('/admin/schedule/lecture_sheet_update/' + id).then(response => {
               $('.user_nav').addClass('successful')
               this.rows[this.editedIndex].lecture_sheet = this.rows[this.editedIndex].lecture_sheet == 1 ? 0 : 1 
               setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
        },
        pick_edit_data(item, index){
             this.editedIndex = this.rows.findIndex(x=>x.id == item.id)
             this.editedId = this.rows[this.editedIndex].id;
             this.dialogItem = Object.assign({}, item);
             this.dialogItem.branch_id = item.batches.echelons.branch_id
             this.dialogItem.echelon_id = item.batches.echelons.id
             this.dialogItem.batches = []
             this.get_batch()
             this.dialogItem.subject_id = item.subject_id
             this.dialogItem.teacher_id = item.teacher_id
             this.add_dialog = true
             this.get_advance_option_batch()
             this.inline_edit = false
        },
        // @admin_add_edit
        save(){
            if (this.editedIndex > -1) {
                this.daily_schedule_edit()
            } else {
                this.daily_schedule_add()
            }
        },
        get_branch(){
           if (this.branch.length==1) {
               this.dialogItem.branch_id=this.branch[0].value
           }
        },
        change_chapter(){
           this.editedItem.chapters.name = this.chapter[this.chapter.findIndex(x=>x.value == this.editedItem.chapter_id)].text
        },
        change_subject(){
           this.editedItem.subjects.name = this.subject[this.subject.findIndex(x=>x.value == this.editedItem.subject_id)].text
        },
        // @delete_item_method
        daily_schedule_delete(item){
          var con = confirm("Want to delete?");
          if (con) {
             const index = this.rows.findIndex(x => x.id==item.id);
             this.axios.post('/admin/schedule/daily_schedule_delete/'+item.id).then(response => {
                 this.rows.splice(index, 1)
                 $('.user_nav').addClass('successful')
                 setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
             }, error => {
                 $('.user_nav').addClass('denied')
                 setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
             });
          }
        },
        delete_by_date(){
            this.dialogItem.delete_batch = this.dialogItem.delete_batch.map(x => {return x.value})
            this.axios.post('/admin/schedule/delete_by_date', this.dialogItem).then(response => {
                this.delete_dialog = false
                this.$refs.delete_form.reset()
                this.daily_schedule()
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
        },
        change_date_list(){
            this.dialogItem.date_list = []
            if (this.advance_option && this.dialogItem.day && this.editedIndex == -1) {
                var date_from = this.$moment(this.dialogItem.date_from)
                var date_to = this.$moment(this.dialogItem.date_to)
                var a = new Date(date_from)
                a = this.$moment(a).day(parseInt(this.dialogItem.day) - 1);
                if (a._d<date_from) {a.add(7, 'days')}
                // console.log(date_diff)
                while (a._d <= date_to) {
                  this.dialogItem.date_list.push({
                    date: a.format('YYYY-MM-DD'),
                    date_text: a._d.getDate() + ' ' + this.month[a._d.getMonth()] + ' ' + a._d.getFullYear().toString().substr(-2),
                    type: 'Class',
                    chapter: '',
                    topic: ''
                  })
                  a = a.add(7, 'days');
                }
            }
            if (this.advance_option && this.editedIndex > -1) {
                var date_from = this.$moment(this.dialogItem.date_from)
                var date_to = this.$moment(this.dialogItem.date_to)
                // var a = new Date(date_from)
                var date_list = this.rows.filter(x => this.$moment(x.date) >= date_from && this.$moment(x.date)<= date_to && x.subject_id == this.dialogItem.subject_id)
                date_list = date_list.sort(function(a,b){
                    return new Date(a.date) - new Date(b.date)
                })
                for (var i = 0; i < date_list.length; i++) {
                    var date = new Date(date_list[i].date)
                    this.dialogItem.date_list.push({
                        id: date_list[i].id,
                        date: date_list[i].date,
                        date_text: date.getDate() + ' ' + this.month[date.getMonth()] + ' ' + date.getFullYear().toString().substr(-2),
                        type: date_list[i].schedule_type,
                        chapter: date_list[i].chapters.map(x=> {return x.chapter_no}).join(','),
                        topic: date_list[i].topic
                    })
                }
            }
            this.$forceUpdate();
        },
        add_date_list(d_i, date_text){
            var item = { date: null, date_text: ' ' + date_text.substring(2,6).trim() + ' ' + date_text.substring(6,11).trim(), type: 'Class',  chapter: null, topic: null }
            this.dialogItem.date_list.splice(d_i + 1, 0, item)
            this.$forceUpdate();
        },
        delete_date_list(d_i){
            this.dialogItem.date_list.splice(d_i, 1)
            this.$forceUpdate();
        },
        // @edit_item_method
        advance_option_change(){
           this.$forceUpdate();
        },
        // open dialog
        editItem (item, element) {
          this.inline_edit = true
          if (this.advance_option) {return alert("Turn off the serial edit!")}
          this.editedIndex = this.rows.findIndex(x => x.id==item.id);
          this.editedId = this.rows[this.editedIndex].id;
          this.editedItem = Object.assign({}, item);
          this.editedItem.schedule_type = item.schedule_type.toLowerCase()
          this.editedItem.period = item.batches.map(x => {return x.pivot.period}).join(',')
          this.dialogItem.echelon_id = item.echelon_id
          this.editedItem.subject_id = item.subject_id
          this.editedItem.chapter_id = item.chapter_id
          this.editedItem.chapters = this.editedItem.chapters || {}
          this.editedItem.teacher_id = item.teacher_id
          this.editedItem.topic = item.topic
          this.editedItem.date = new Date(item.date).toISOString().substr(0, 10)
          this.editedItem.inline_edit = element == 'chapter' ? 'chapter' : true
        },
        change_period(){
           var new_rows = Object.assign([], this.rows);          
           let filter_topic_rows = new_rows.filter(x=> {return x.day == this.dialogItem.day && x.echelons.id == this.dialogItem.echelon_id && x.batches.id == this.dialogItem.batch_id})
           if (filter_topic_rows.length>0) {
             let max = Math.max.apply(Math, filter_topic_rows.map(function(x) { return x.period }));
             filter_topic_rows.forEach(character => {
                 if (character.period > max) {
                   max = character.period;
                 }
               });
             this.dialogItem.period = Number(max) + 1
           }
           else {this.dialogItem.period = 1}
        },
        
        get_exchangable(item){
            this.dialogItem.echelon_id = item.batches.echelon_id
            this.dialogItem.batch_id = item.batch_id
            this.get_subject()
            this.exchange_from_subject_id = item.subject_id
            this.exchange_to_subject_id = ''
            this.exchangable_list_from = []
            this.exchangable_list_to = []
            this.exchange_from = []
            this.exchange_to = []
            this.get_exchangable_list_from()
            this.dialog = true
        },
        get_exchangable_list_from(){
            var new_rows = Object.assign([], this.original_rows);          
            let filter_rows = new_rows.filter(x=> {return x.subject_id == this.exchange_from_subject_id && x.batches.echelons.id == this.dialogItem.echelon_id && x.batch_id == this.dialogItem.batch_id && this.$moment(x.date).isSameOrAfter(new Date(), 'day')})
            this.exchangable_list_from = filter_rows.map((arr, index) => { return { id: arr['id'], date: arr['date'], schedule_type: arr['schedule_type'], index: '', value: false } })
            this.exchange_from = []
        },
        get_exchangable_list_to(){
            var new_rows = Object.assign([], this.original_rows);          
            let filter_rows = new_rows.filter(x=> {return x.subject_id == this.exchange_to_subject_id && x.batches.echelons.id == this.dialogItem.echelon_id && x.batch_id == this.dialogItem.batch_id && this.$moment(x.date).isSameOrAfter(new Date(), 'day')})
            this.exchangable_list_to = filter_rows.map((arr, index) => { return { id: arr['id'], date: arr['date'], schedule_type: arr['schedule_type'], index: '', value: false } })
            this.exchange_to = []
        },
        get_exchange_from(list,key){
            if (list.value) {
              var x = {'id': list.id, 'date': list.date}
              this.exchange_from.push(x)
              var index = this.exchange_from.length - 1
              this.exchangable_list_from[key].index = index + 1
            }
            else {
              var index = this.exchange_from.findIndex(x=>x.id==list.id)
              this.exchange_from.splice(index, 1)
              this.exchangable_list_from[key].index = ""
              this.exchangable_list_from.forEach(function(part, ind, theArray) {
                if (theArray[ind].index && ind>key) {
                    theArray[ind].index--;
                }
              });
            }
        },
        get_exchange_to(list,key){
            if (list.value) {
              var x = {'id': list.id, 'date': list.date}
              this.exchange_to.push(x)
              var index = this.exchange_to.length - 1
              this.exchangable_list_to[key].index = index + 1
            }
            else {
              var index = this.exchange_to.findIndex(x=>x.id==list.id)
              this.exchange_to.splice(index, 1)
              this.exchangable_list_to[key].index = ""
              this.exchangable_list_to.forEach(function(part, ind, theArray) {
                if (theArray[ind].index && ind>key) {
                    theArray[ind].index--;
                }
              });
            }
        },
        exchange_class(){
            if (this.exchange_from.length==0 || this.exchange_to.length==0) {return alert('Choose exchange data!')}
            this.loading = true
            var exchange_data = {}
            exchange_data.from_subject_id = this.exchange_from_subject_id
            exchange_data.to_subject_id = this.exchange_to_subject_id
            exchange_data.exchange_from = this.exchange_from
            exchange_data.exchange_to = this.exchange_to
            this.axios.post('/admin/schedule/exchange_class', exchange_data).then(response => {
                 this.loading = false
                 this.dialog = false
                 this.daily_schedule()
                 $('.user_nav').addClass('successful')
                 setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                 this.loading = false
                 $('.user_nav').addClass('denied')
                 setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
        },
        // @add_item_method_dialog
        close () {
          // this.dialog = false
            this.add_dialog = false
            this.delete_dialog = false
            setTimeout(function () { 
                this.advance_option = null; this.dialogItem.date_list = []; this.$refs.add_form.reset();
                this.$refs.delete_form.reset();
            }.bind(this), 200)
          
        },
        // @add_item_method_close_dialog
        clear(){
          this.search = ''
        }
      }
    }

</script>

<style>


.daily_schedule .v-text-field label,
.v-text-field input {
    font-size: 14px;
}

.daily_schedule #edit_period {
    color: #FF5722!important;
    font-weight: 500;
    font-size: 30px;
}

.daily_schedule .v-text-field {
    padding-top: 2px;
    margin-top: 2px;
}

.daily_schedule .v-input {
    font-size: 14px;
}

.daily_schedule .v-input--checkbox label {
    font-size: 14px;
}

.daily_schedule .v-btn {
    height: 32px;
}

.daily_schedule .chapter_next_prev {
    position: absolute;
    top: -15px;
    right: 23px;
}
.daily_schedule .v-datatable .v-input--checkbox .v-input--selection-controls__input i {
    font-size: 20px;
    position: relative;
    top: 10px;
}
.daily_schedule .v-datatable .v-input--checkbox .v-input--selection-controls__ripple {
    top: -2px;
    z-index: 99;
}
.daily_schedule .v-toolbar__content .v-input.v-text-field.v-select.v-autocomplete {
    top: 10px;
}
.daily_schedule .v-dialog .v-list--two-line .v-list__tile {
    height: 50px;
}
.daily_schedule .exchange_index{
    position: absolute;
    top: 5px;
    right: 0px;
    background-color: #00BCD4;
    padding: 2px 9px;
    border-radius: 50%;
    font-size: 14px;
    color: #fff;
}
.daily_schedule .v-expansion-panel__header{
    padding-left: 15px
}
.daily_schedule .v-expansion-panel__header__icon{
    position: absolute;
    left: 160px;
}

@media only screen and (max-width: 768px) {
    .daily_schedule .schedule_toolbar .v-toolbar__content {
        height: auto!important;
        display: block;
    }
    .daily_schedule .v-dialog__activator button{
        height: 24px;
        min-width: 0px;
        padding: 0px 15px;
        font-size: 10px;
        margin: 15px 0px 0px 15px;
    }
}


</style>