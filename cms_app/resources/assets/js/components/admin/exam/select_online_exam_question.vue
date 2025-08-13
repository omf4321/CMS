<template>
    <div class="question_edit">
        <v-container fluid class="p-t-10">
            <!-- @page_headline -->
            <div>
                <div class="row">
                    <div class="col-lg-12">
                        <h3 class="fs-20 inline-block">Select Question</h3>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step==1">
                        <v-select v-model="search_by" :items="search_by_item" label="Search By" :clearable=true @change="question_list"></v-select>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step<3">
                        <v-text-field v-model="search_text" label="Search"></v-text-field>
                    </div>
                    <div class="col-lg-2 col-xs-6 pos-rel">
                        <v-chip style="display:inline-table; top: 7px; height: 35px; width: 70px; margin-left: 10px; text-align: center" class="" @click="">Go</v-chip>
                    </div>
                    <div v-if="step > 1" class="col-lg-2 col-xs-6">
                        <span class="float-right m-t-23 cur-p" @click="go_back">Go Back ⇾</span>
                    </div>
                </div>
            </div>
            <v-divider></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-alert :value="success_alert" transition="scale-transition" color="rgb(43, 123, 0)" icon="check_circle" outline>
                        Updated Successfully
                    </v-alert>
                    <!-- @accesories_alert -->
                    <v-alert :value="error_alert" transition="scale-transition" color="error" icon="warning" outline>
                        An error has occured
                    </v-alert>
                    <template>
                        <v-container class="p-0">
                            <div class="row">
                                <div class="col-md-5" v-if="step==1">
                                    <div class="step_1_div card p-15 cur-p m-b-10" v-for="(search_item, s_i) in itemsSorted">
                                        <div class="step_1" @click="goto_step2(s_i)">
                                            <div class="inline-block" style="min-width:50px">#{{s_i + 1}}</div>
                                            <div class="inline-block fs-16 fw-700">{{search_item.name}}</div>
                                            <div class="inline-block float-right m-l-15 fs-13" v-for="(sb_id, sb_key) in subject_ids" v-if="search_item.id == sb_id">
                                                <div class="text-right">Question Range: {{ question_range[sb_key].min }}-{{ question_range[sb_key].max }}</div>
                                                <div class="text-right success--text">Selected: {{ selected_by_subject[sb_key] }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="inline-block" style="min-width:50px"></div>
                                            <div class="inline-block">{{search_item.questions.length}} Questions</div>
                                        </div>
                                    </div>  
                                </div>           
                                <div class="step_2_div col-md-5" v-if="step==2">
                                    <!-- step2 when not combine -->
                                    <div class="step_2">
                                        <h4 class="fs-16 fw-700">{{question_list_data[search_by_index].name}} ({{question_list_data[search_by_index].questions.length}} Questions)</h4> 
                                        <hr class="m-t-5 m-b-10">
                                        <div style="max-height: 1200px; overflow-y: auto; overflow-x: hidden; padding-right: 15px">
                                            <div class="card solaiman p-15 m-b-10 pos-rel" v-for="(question, q_i) in itemsSorted">
                                                <span class="pos-a cur-p" style="color:#4caf50; right: 10px; top:7px; z-index:1" @click="add_remove_question('add', question, q_i)">+ Add</span>
                                                <div class="mr-2 pos-rel qus-list">
                                                    <p class="m-b-3 question_p" v-html="question.question_text"></p>
                                                    <p v-if="question.language == 'bangla'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_bangla[op_key] + option.option_text"></p>
                                                    <p v-if="question.language == 'english'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_english[op_key] + option.option_text"></p>
                                                </div>
                                                <!-- question div end -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1" style="border-right: 1px solid; min-height: 600px; max-height: 1000px"></div>
                                <div class="col-md-1"></div>
                                <div class="step_2_div col-md-5">
                                    <!-- step2 when not combine -->
                                    <div class="step_2">
                                        <h4 class="fs-16 fw-700">({{selected_questions.length}} Questions Selected)</h4>
                                        <hr class="m-t-5 m-b-10">
                                        <div style="max-height: 1200px; overflow-y: auto; overflow-x: hidden; padding-right: 15px">
                                            <div class="card solaiman p-15 m-b-10 pos-rel" v-for="(question, q_i) in selected_questions">
                                                <span class="pos-a cur-p" style="color:#f44336; right: 10px; top:7px; z-index:1" @click="add_remove_question('remove', question, q_i)">remove</span>
                                                <div class="mr-2 pos-rel qus-list">
                                                    <p class="m-b-3 question_p" v-html="question.question_text"></p>
                                                    <p v-if="question.language == 'bangla'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_bangla[op_key] + option.option_text"></p>
                                                    <p v-if="question.language == 'english'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_english[op_key] + option.option_text"></p>
                                                </div>
                                                <div>
                                                    <span class="m-r-10 fs-12">Question No: {{question.question_no}}</span>
                                                    <span class="fs-12">Subject: {{question.subject_name}}</span>
                                                </div>
                                                <!-- question div end -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </v-container>
                    </template>
                </div>
            </template>
        </v-container>
    </div>
</template>

<script>
    // import JQuery from 'jquery'
    // let $ = JQuery
    import htmlEditor from './html-editor'
    export default {
        components: {
            'htmlEditor': htmlEditor
        },
        data() {
            return {                
                subject: [],                
                question: [],
                question_tag: [],                
                question_list_data: [],
                selected_questions: [],              
                search_by: '',
                search_by_item: [{value: 'tag', text: 'Tags'}, {value: 'subject', text: 'Subjects'}],                
                // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                dialogItem: {
                    id: '',
                    subject_id: '',
                    question_mark: '',
                    question_status: '',
                    topics: '',
                    question_text: '',
                    question_tag: '',
                    question_length: "0",
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
                step: 1,
                search_by_index: '',
                question_index: '',
                search_text: '',
                search_id: '',
                option_char_bangla: ["ক. ","খ. ","গ. ","ঘ. ","ঙ. "],
                option_char_english: ["a. ","b. ","c. ","d. ","e. "],
                subject_ids: [],
                question_range: [],
                selected_by_subject: [],
                question_no_by_subject: []
            } //end return
        },
        // @router_permission
        beforeRouteEnter(to, from, next) {
            let p = window_admin_permissions.findIndex(x => x.name == 'question_edit');
            if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                next()
            } else {
                next('/')
            }
        },
        computed: {
            // search for step 1
            itemsSorted: function() {
                if (this.step == 1) {
                    var items = this.question_list_data
                    items = items.filter(item => item.name.toLowerCase().indexOf(this.search_text.toLowerCase()) > -1)
                }
                if (this.step == 2) {
                    var items = this.question_list_data[this.search_by_index].questions
                    items = items.filter(item => item.question_text.toLowerCase().indexOf(this.search_text.toLowerCase()) > -1)   
                }
                return items;
            },
        },
        filters: {
            strip_tags: function(value) {
                if (!value) return ''
                return value.replace(/<\/?[^>]+(>|$)/g, "")
            }
        },
        // @load_method
        created() {
            this.get_online_exam_subject()
            this.get_question_tag()
        },

        methods: {
            get_online_exam_subject() {
              this.data_load = true;
                  this.axios.get('/admin/exam/get_online_exam_subject').then(response => {
                      this.subject = response.data.subjects.map(x => {return {value: x.id, text: x.name}});
                      this.data_load = false
                  });
              },
              get_question_tag() {
                  this.data_load = true;
                  this.axios.get('/admin/exam/get_question_tag').then(response => {
                      this.question_tag = response.data.tags.map(x => {return {value: x.id, text: x.name}});
                      this.data_load = false
                  });
              },
            question_list() {
                this.axios.get('/admin/exam/get_selection_online_exam_question/' + this.search_by + '/' + this.$route.params.online_exam_id).then(response => {
                    this.question_list_data = response.data.search_by_list;  
                    this.subject_ids = response.data.subjects;
                    for (var i = 0; i < this.subject_ids.length; i++) {
                        this.selected_by_subject.push(0)
                    }
                    for (var i = 0; i < response.data.question_range.length; i++) {
                        this.question_no_by_subject.push(parseInt(response.data.question_range[i].split('-')[0]) - 1)
                        this.question_range.push(
                            {min: parseInt(response.data.question_range[i].split('-')[0]), 
                            max: parseInt(response.data.question_range[i].split('-')[1])
                        })
                    }                    
                });
            },
            add_remove_question(option, question, question_index){
                if (option == 'add') {
                    question.subject_name = this.question_list_data[this.search_by_index].name
                    question.subject_index = this.search_by_index
                    this.question_no_by_subject[this.search_by_index] = this.question_no_by_subject[this.search_by_index] + 1
                    question.question_no = this.question_no_by_subject[this.search_by_index]
                    this.selected_questions.push(question)
                    this.question_list_data[this.search_by_index].questions.splice(question_index, 1)
                    this.selected_by_subject[this.search_by_index] = this.selected_by_subject[this.search_by_index] + 1
                }
                if (option == 'remove') {
                    this.selected_questions.splice(question_index, 1)
                    this.question_list_data[question.subject_index].questions.push(question)
                    this.selected_by_subject[question.subject_index] = this.selected_by_subject[question.subject_index] - 1
                    this.question_no_by_subject[question.subject_index] = this.question_no_by_subject[question.subject_index] - 1
                    for (var i = 0; i < this.selected_questions.length; i++) {
                        if (this.selected_questions[i].subject_index == question.subject_index) {
                            this.selected_questions[i].question_no = this.question_range[question.subject_index].min + i
                        }
                    }
                }
            },                
            save() {
                this.refine_editor_text()
                if (!this.question_editor) {
                    return alert('Editor is empty')
                }
                if (this.dialogItem.question_text.length == 0) {
                    return alert('No Question Found')
                }
                if ( this.dialogItem.question_text.length>1) {
                    return alert('You cannot save more than 1 question')
                }
                if (this.$refs.form.validate()) {
                    this.loading = true
                    this.axios.post('/admin/question/exam_question_edit/' + this.dialogItem.id, this.dialogItem).then(response => {
                        // $('.user_nav').addClass('successful')
                        this.loading = false
                        this.question_list()
                        $('.user_nav').attr('style', 'background-color: #4caf50!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    }, error => {
                        // $('.user_nav').addClass('denied')
                        this.loading = false
                        $('.user_nav').attr('style', 'background-color: #f44336!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    });
                }
            },
            goto_step2(search_by_index) {
                this.search_by_index = search_by_index                    
                $('.step_2_div').hide()
                this.search_text = ""
                this.step = 2
                $('.step_2_div').fadeIn('slow')
            },
            
            go_back() {
                this.search_text = ""
                if (this.step == 2) {
                    this.step = 1
                }
                if (this.step == 3) {
                    this.goto_step2(this.search_by_index)
                }
                var step = this.step
                $('.step_' + step + '_div').hide()
                $('.step_' + step + '_div').fadeIn('slow')
            },                
        } //end method
    } 
</script>