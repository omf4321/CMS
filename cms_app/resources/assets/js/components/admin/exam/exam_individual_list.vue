

<template>
    <div class="exam_individual_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Merit List</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- mark entry card -->
                    <div class="card setup_exam m-t-10 m-b-15">
                        <v-container>
                            <v-layout>
                                <v-flex lg2 xs10 class="m-r-10">  
                                    <v-text-field label="Reg No" v-model="dialogItem.reg_no"></v-text-field>
                                </v-flex>
                                <v-flex lg2 xs12 class="m-r-10">
                                    <v-dialog v-model="date_from_dialog" full-width lazy ref="date_ref" width="290px">
                                        <v-text-field :rules="[v => !!v || 'required']" label="Date" readonly slot="activator" v-model="dialogItem.date_from">
                                        </v-text-field>
                                        <v-date-picker @change="date_from_dialog = false" ref="picker" v-model="dialogItem.date_from">
                                        </v-date-picker>
                                    </v-dialog>
                                </v-flex>
                                <v-flex lg2 xs12 class="m-r-10">
                                    <v-dialog v-model="date_to_dialog" full-width lazy ref="date_ref1" width="290px">
                                        <v-text-field :rules="[v => !!v || 'required']" label="Date" readonly slot="activator" v-model="dialogItem.date_to">
                                        </v-text-field>
                                        <v-date-picker @change="date_to_dialog = false" ref="picker1" v-model="dialogItem.date_to">
                                        </v-date-picker>
                                    </v-dialog>
                                </v-flex>
                                <v-flex lg2 class="m-r-10">
                                    <v-select :clearable="true" @change="change_exam_type" v-model="dialogItem.exam_type" :items="exam_type" label="Exam Type"></v-select>
                                </v-flex>
                                <v-flex lg2 class="m-r-10">
                                    <v-autocomplete :clearable="true" v-model="dialogItem.ranking_system" :items="ranking_system" label="Result System"></v-autocomplete>
                                </v-flex>
                                <v-flex lg2 class="m-r-10">
                                    <v-autocomplete :clearable="true" v-model="dialogItem.combine_subject_rule_id" :items="combine_subject_rule" label="Combination Rule"></v-autocomplete>
                                </v-flex>
                                <v-flex lg1 lg1>                        
                                    <v-icon @click="get_mark_list" class="pos-rel m-l-10" style="top: 0px">send</v-icon>
                                </v-flex>
                            </v-layout>
                        </v-container>
                    </div>
                </div>
            </template>    
            <!-- mark entry card -->
            <div class="card merit_list_card m-t-10 no-opacity" v-if="show_result">
                <v-container>
                    <div class="float-right">
                        <v-layout>
                            <v-select style="width: 80px; margin-right: 20px" v-model="dialogItem.layout" :items="layout" label="Layout"></v-select>
                            <v-btn small outline @click="print" style="position: relative; top: 10px; min-width: 0px">Print</v-btn>
                        </v-layout>
                    </div>
                    <div id="print_content" style="clear: both; float: none;">
                        <!-- merit list main header -->
                        <div>
                            <div class="fw-600 fs-18 pos-rel text-center m-t-25">
                                Individual Mark List
                                <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 10%; top: 2px;"> 
                            </div>
                            <div class="text-center fs-16">{{dialogItem.date_from | moment("D MMMM")}} to {{dialogItem.date_to | moment("D MMMM")}}</div>
                            <div class="text-center fs-16" v-if="dialogItem.exam_type">{{current_exam_type}}</div>
                            <div class="m-t-25 m-b-30" style="margin-left: 30%;">
                                <!-- <div class="col-md-3"></div> -->
                                <div style="float:left; margin-right: 15px; margin-left: 15px">
                                    <img v-if="student_mark_list.photo" :src="'/storage/avatar/' + student_mark_list.batches.name + '/' + student_mark_list.photo">
                                    <div v-else style="width: 90px; height: 90px; border: 1px solid"></div>
                                </div>
                                <div style="max-width: 400px">
                                    <div style="padding-left: 10px"><span>Name:</span>  {{student_mark_list.name}}</div>
                                    <div style="padding-left: 10px"><span>Reg No:</span>  {{student_mark_list.reg_no}}</div>
                                    <div style="padding-left: 10px"><span>Batch:</span>  {{student_mark_list.batches.name}}</div>
                                    <div style="padding-left: 10px"><span>Institution:</span>  {{student_mark_list.institutions.name}}</div>
                                    <div v-if="dialogItem.ranking_system == 'gpa'" style="padding-left: 10px"><span>GPA:</span>  <span class="fw-600">{{student_mark_list.gpa_format}}</span></div>
                                </div>
                            </div>
                        </div>
                        <!-- list section -->
                        <div class="fs-11 m-t-15 pos-rel p-l-25 p-r-25">
                            <div class="merit_list">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Date</th>
                                            <th>Subject</th>
                                            <th class="text-center">Full Mark</th>
                                            <th class="text-center">Sub / 1st Paper</th>
                                            <th class="text-center">Ob / 2nd Paper</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(list, std_i) in student_mark_list.subjects">
                                            <td class="text-center">{{list.date | moment("D MMM YY")}}</td>
                                            <td>{{list.subject_name}} <span class="fw-600">{{list.optional == 1 ? " (Fourth Subject)" : ""}}</span></td>
                                            <td class="text-center">{{list.full_mark}}</td>
                                            <td class="text-center">{{list.combine_subject_no == 2 ? list.first_sub_mark : list.sub_mark}}</td>
                                            <td class="text-center">{{list.combine_subject_no == 2 ? list.second_sub_mark : list.ob_mark}}</td>
                                            <td class="text-center">{{list.total}}</td>
                                            <td class="text-center">{{list.original_gpa}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>  
                    </div>     
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
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    // @list_header_data
                    headers: [{text: 'Code', align: 'left', value: 'schedule_id'}, {text: 'Class', value: 'schedules.echelons.name'}, {text: 'Batch', value: 'batches.name'}, {text: 'Date', value: 'date'}, {text: 'Subject', value: 'schedules.subjects.name'}, {text: 'Sub Full', value: 'sub_full_mark'}, {text: 'Ob Full', value: 'ob_full_mark'}, {text: 'Invigilator', value: 'invigilators.name'}, {text: 'Scripter', value: 'scripters.name'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    dialogItem: {
                        reg_no: '',
                        branch_id: '',
                        echelon_id: '',
                        teacher_id: '',
                        bangla_text: '',
                        created_at: '',
                        date: '',
                        batches: '',
                        date_from: '',
                        date_to: '',
                        layout: 'portrait'
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },

                    // @accesories_data
                    update: false,
                    data_load: false,
                    step: 'none',
                    student_mark_list: {},
                    merit_photo_list: [],
                    search_date: '',
                    show_edit_list: true,
                    search_student: '',
                    echelon: [],
                    original_batch: [],
                    batch: [],
                    merit_list_item: {subjects:[]},
                    current_echelon_name: '',
                    current_batch_name: '',
                    exam_type: [],
                    current_exam_type: '',
                    subject_size: '',
                    layout: [{value: 'portrait', text: 'Portrait'}, {value: 'landscape', text: 'Landscape'}],
                    ranking_system: [{value: 'total_mark', text: 'Total'}, {value: 'gpa', text: 'GPA'}],
                    combine_subject_rule: [],
                    show_result: false,
                    date_from_dialog: false,
                    date_to_dialog: false
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
                
            },

            filters: {
                ordinal_number: function(value) {
                    if (isNaN(value)) {return value}
                    if (value == 1) {return value = value + 'st'}
                    if (value == 2) {return value = value + 'nd'}
                    if (value == 3) {return value = value + 'rd'}
                    if (value > 3) {return value = value + 'th'}
                },
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                },
            },            

            methods: {                
                // @list_method
                admin_branch_list(){
                    this.branch = window_branch;
                    if (this.branch.length==1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list()
                        this.admin_batch_list();
                        this.exam_type_list()
                        this.get_combine_subject_rule()
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
                exam_type_list(){
                    this.axios.get('/admin/request/exam_type_list').then(response => {
                        this.exam_type = response.data.exam_type_list
                        this.exam_type = this.exam_type.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    });
                },
                get_combine_subject_rule(){
                    this.axios.get('/admin/exam/get_combine_subject_rule/' + this.dialogItem.branch_id).then(response => {
                        this.combine_subject_rule = response.data.combine_subject_rule
                        this.combine_subject_rule = this.combine_subject_rule.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    });
                },
                get_batch(){
                    var batch = Object.assign([], this.original_batch);
                    let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                    this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    var index = this.echelon.findIndex(x => x.value == this.dialogItem.echelon_id)
                    this.current_echelon_name = this.echelon[index].text
                },
                change_batch(){
                    var batch = []
                    for (var i = 0; i < this.dialogItem.batches.length; i++) {
                        var index = this.batch.findIndex(x => x.value == this.dialogItem.batches[i].value)
                        batch.push(this.batch[index].text)
                    }
                    this.current_batch_name = batch.join(', ')
                },
                change_exam_type(){
                    var index = this.exam_type.findIndex(x => x.value == this.dialogItem.exam_type)
                    this.current_exam_type = this.exam_type[index].text
                },
                get_mark_list() {
                    this.dialog = true;
                    // this.dialogItem.date_from = '2021-01-01';
                    // this.dialogItem.date_to = '2021-03-01';
                    this.show_result = false
                    this.axios.post('/admin/exam/get_individual_mark', this.dialogItem).then(response => {
                        this.dialog = false
                        this.show_result = true
                        // return false
                        this.student_mark_list = response.data.mark_list
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                            $('.user_nav').removeClass('denied') }.bind(this), 3000) 
                        });
                },  
                print() {
                    setTimeout(function() {
                        if (this.dialogItem.layout == 'portrait') {
                            $('#print_content').printThis({
                                importCSS: true,
                                loadCSS: ["/css/print_portrait.css"]
                            });
                        } else {
                            $('#print_content').printThis({
                                importCSS: true,
                                loadCSS: ["/css/print.css"]
                            });
                        }
                    }.bind(this), 500)
                },                                    
            }
    } 
</script>

<style>  
  .exam_individual_list .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
  .exam_individual_list .merit_list div:not(.row) {
    border: 1px solid;
    border-right: 0px;
    border-top: 0px;
  }
  .exam_individual_list .merit_list_header div:not(.row) {
    border: 1px solid;
    border-right: 0px;
  }
  
  .exam_individual_list .no-border {
    border: 0px!important;
  }
  .exam_individual_list .border-right {
    border-right: 1px solid!important;
  }
  .exam_individual_list .border-top {
    border-top: 1px solid!important;
  }
  .exam_individual_list .row .last_sub_name .sub_name_ob {
    border-right: 1px solid!important;
  }
  .exam_individual_list .no-opacity {
    opacity: 1
  }
</style>