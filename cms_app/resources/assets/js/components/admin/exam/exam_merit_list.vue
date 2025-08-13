

<template>
    <div class="exam_merit_list">
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
                                    <v-autocomplete :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete>
                                </v-flex>                                
                                <v-flex lg2 xs12 class="m-r-10">
                                    <v-combobox @change="change_batch" :items="batch" :clearable="true" label="Batch" v-model="dialogItem.batches" ref="combobox" multiple small-chips hide-selected></v-combobox>
                                </v-flex>
                                <v-flex lg2 xs12 class="m-r-10">
                                    <v-dialog v-model="date_from_dialog" full-width lazy ref="date_ref" width="290px">
                                        <v-text-field :rules="[v => !!v || 'required']" label="Date From" readonly slot="activator" v-model="dialogItem.date_from">
                                        </v-text-field>
                                        <v-date-picker @change="date_from_dialog = false" ref="picker" v-model="dialogItem.date_from">
                                        </v-date-picker>
                                    </v-dialog>
                                </v-flex>
                                <v-flex lg2 xs12 class="m-r-10">
                                    <v-dialog v-model="date_to_dialog" full-width lazy ref="date_ref1" width="290px">
                                        <v-text-field :rules="[v => !!v || 'required']" label="Date To" readonly slot="activator" v-model="dialogItem.date_to">
                                        </v-text-field>
                                        <v-date-picker ref="picker1" @change="date_to_dialog = false" v-model="dialogItem.date_to">
                                        </v-date-picker>
                                    </v-dialog>
                                </v-flex>
                                <v-flex lg2 class="m-r-10">
                                    <v-autocomplete :clearable="true" @change="change_exam_type" v-model="dialogItem.exam_type" :items="exam_type" label="Exam Type"></v-autocomplete>
                                </v-flex>
                                <v-flex lg2 class="m-r-10">
                                    <v-autocomplete :clearable="true" v-model="dialogItem.ranking_system" :items="ranking_system" label="Ranking System"></v-autocomplete>
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
            <div class="card merit_list_card m-t-10 no-opacity" v-if="student_mark_list.length">
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
                                Merit List of {{current_batch_name ? current_batch_name : 'Class ' + current_echelon_name}}
                                <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 10%; top: 2px;"> 
                            </div>
                            <div class="text-center fs-16">{{dialogItem.date_from | moment("D MMMM")}} to {{dialogItem.date_to | moment("D MMMM")}}</div>
                            <div class="text-center fs-16" v-if="dialogItem.exam_type">{{current_exam_type}}</div>
                        </div>
                        <!-- Merit list photo -->
                        <div class="photo m-t-20">
                            <div class="row row-centered">
                                <div class="col-lg-3 col-centered single_photo" v-for="photo in merit_photo_list">
                                    <div class="rank fw-600 fs-17" style="padding: 5px 15px;">{{photo.rank | ordinal_number}} <span class="fs-12">({{photo.total}})</span></div>
                                    <div class="img" v-if="photo.photo"><img :src="'/storage/avatar/' + photo.batch_name + '/' + photo.photo" style="width: 100px; height: 120px;"></div>
                                    <div class="img" v-if="!photo.photo" style="width: 100px; height: 120px; border: 1px solid; margin: auto"></div>
                                    <div class="reg_no fw-600" style="padding: 5px 15px 0px;">{{photo.name.substring(0,18)}}</div>
                                    <div class="reg_no fw-600 fs-13" style="padding: 0px 15px;">{{photo.reg_no}}</div>
                                </div>
                            </div>
                        </div>
                        <!-- list section -->
                        <table class="table table-bordered fs-12 m-t-10">
                            <thead>
                              <tr>
                                <th class="text-center">Reg No</th>
                                <th>Student Name</th>
                                <th class="text-center" v-for="(subject, s_i) in student_mark_list[0].subjects">{{subject.subject_name | first_letter_word}}</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">GPA</th>
                                <th class="text-center">Rank</th>
                              </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(list, i) in student_mark_list">
                                    <td class="text-center">{{list.reg_no}}</td>
                                    <td>{{list.name}}</td>
                                    <td class="text-center" v-for="subject in list.subjects">{{subject.total}}</td>
                                    <td class="text-center">{{list.total}}</td>
                                    <td class="text-center">{{list.gpa_format}}</td>
                                    <td class="text-center" v-if="list.rank">{{list.rank | ordinal_number}}</td>
                                    <td class="text-center" v-else>{{list.rank_status}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- 
                        <div class="fs-11 m-t-15 pos-rel merit_list_div">
                            <div class="row merit_list_header">
                                <div class="col-lg-1 fw-600 column" style="padding: 5px 15px 5px!important">Reg</div>
                                <div class="col-lg-3 fw-600 column" style="padding: 5px 15px 5px!important">Name</div>
                                <div class="subject_column fw-600 text-center" v-if="subject_size>=4" v-for="(subject, s_i) in merit_list_item.subjects">{{subject.subject_name | first_letter_word}}
                                </div>
                                <div class="subject_column no-border" v-if="subject_size<4">
                                    <div class="row sub_name">
                                        <div class="per_subject_column" v-for="(subject, s_i) in merit_list_item.subjects">
                                            <div class="text-center no-border p-t-5 p-b-5 fw-600">{{subject.subject_name | first_letter_word}}</div>
                                            <div :class="{'row':true, 'last_sub_name': s_i == merit_list_item.subjects.length-1}">
                                                <div class="col-lg-6 text-center no-border border-right border-top p-t-5 p-b-5 fw-600 p-r-2 p-l-2">Sub</div>
                                                <div class="col-lg-6 text-center no-border border-top sub_name_ob p-t-5 p-b-5 fw-600 p-r-2 p-l-2">Ob</div>
                                            </div>                                        
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-1 text-center fw-600 column" style="padding: 5px 15px 5px!important">Total</div>
                                <div class="col-lg-1 text-center border-right fw-600 column" style="padding: 5px 15px 5px!important">Rank</div>
                            </div>
                            <div class="row merit_list" v-for="(list, std_i) in student_mark_list">
                                <div :class="{'col-lg-1': true, 'border-top': std_i==0, 'p-t-5': true, 'p-b-5':true}">{{list.reg_no}}</div>
                                <div :class="{'col-lg-3': true, 'border-top': std_i==0, 'p-t-5': true, 'p-b-5':true}">{{list.student_name.substring(0,25)}}</div>
                                <div v-if="subject_size>=4" class="subject_column text-center p-t-5 p-b-5" v-for="subject in list.subjects">{{subject.mark ? subject.mark : 0}}</div>
                                <div class="subject_column no-border" v-if="subject_size<4">
                                    <div class="row">
                                        <div class="per_subject_column" v-for="subject in list.subjects">
                                            <div class="row">
                                                <div class="col-lg-6 text-center no-border border-right p-t-5 p-b-5">{{subject.sub_mark ? subject.sub_mark : 0}}</div>
                                                <div class="col-lg-6 text-center no-border p-t-5 p-b-5">{{subject.ob_mark ? subject.ob_mark : 0}}</div>
                                                <div v-if="subject_size>=4" class="col-lg-12 text-center no-border p-t-5 p-b-5">{{subject.mark ? subject.mark : 0}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div :class="{'col-lg-1': true, 'text-center': true, 'border-top': std_i==0, 'p-t-5': true, 'p-b-5':true}">{{list.total}}</div>
                                <div :class="{'col-lg-1': true, 'text-center': true, 'border-right': true, 'border-top': std_i==0, 'p-t-5': true, 'p-b-5':true, 'p-r-2': true, 'p-l-2':true}">{{list.rank_status ? list.rank_status : list.rank | ordinal_number}}</div>
                            </div>
                        </div>   -->
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
                        name: '',
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
                    student_mark_list: [],
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
                    date_from_dialog: false,
                    date_to_dialog: false,
                    combine_subject_rule: []
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'manage_exam');
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
                    var strings = value.split(' ')
                    var a = []
                    for (var i = 0; i < strings.length; i++) {
                        var b = strings[i].substring(0, 3)
                        a.push(b)
                    }
                    return a.join(' ')
                    
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
                    this.student_mark_list = []
                    this.axios.post('/admin/exam/get_mark_list', this.dialogItem).then(response => {
                        this.dialog = false
                        // return false
                        this.student_mark_list = response.data.mark_list
                        this.merit_photo_list = response.data.showcase_student
                        this.student_mark_list = this.student_mark_list.sort(function(a,b){
                            return a.reg_no - b.reg_no
                        })
                        // this.subject_size = this.merit_list_item.subjects ? this.merit_list_item.subjects.length: 0
                        var subject_size = this.subject_size
                        var item = this.merit_list_item
                        setTimeout(function() {
                            
                            $('.no-opacity').removeClass('no-opacity')
                            this.dialog = false
                        }.bind(this), 200)
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
  .exam_merit_list .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
  .exam_merit_list .merit_list div:not(.row) {
    border: 1px solid;
    border-right: 0px;
    border-top: 0px;
  }
  .exam_merit_list .merit_list_header div:not(.row) {
    border: 1px solid;
    border-right: 0px;
  }
  
  .exam_merit_list .no-border {
    border: 0px!important;
  }
  .exam_merit_list .border-right {
    border-right: 1px solid!important;
  }
  .exam_merit_list .border-top {
    border-top: 1px solid!important;
  }
  .exam_merit_list .row .last_sub_name .sub_name_ob {
    border-right: 1px solid!important;
  }
  .exam_merit_list .no-opacity {
    opacity: 0
  }
</style>