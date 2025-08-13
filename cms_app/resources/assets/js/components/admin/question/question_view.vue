<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
step 3 for relative, 4 for generate question
 -->

<template>
    <div class="question_setup">
        <v-container fluid class="p-b-100">
            <!-- @page_headline -->
            <h3 class="headline">Questions Print Setup <span class="fs-14">{{exam_data.subjects.echelons.name}}</span> <span class="fs-14">{{exam_data.subjects.name}}</span></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container v-if="step == 1">
                            <div>
                                <!-- step 5 is for view questions -->
                                <div class="m-b-10">Sets:
                                    <v-chip :class="{'active': active_set == index-1}" v-for="index in exam_data.sets" :key="index" @click="change_set(index)">{{question_sets[index-1]}}</v-chip>
                                </div>
                                <div v-for="(type, t_i) in dialogItem.question_data">
                                    <h3 class="m-t-0">#{{type.id}}{{type.question_type.toUpperCase()}}</h3>
                                    <div class="table-responsive">
                                        <table class="table solaiman">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Chapter</th>
                                                    <th>Set</th>
                                                    <th>Text</th>
                                                </tr>
                                            </thead>
                                            <tbody class="view_question_no_space">
                                                <tr v-for="(question, q_i) in type.questions">
                                                    <td class="fs-12">{{question.id}}</td>
                                                    <td class="fs-12"><span class="m-r-5" v-for="(chapter, c_i) in question.chapters">{{chapter.name}}</span></td>
                                                    <td class="fs-12">{{question_sets[question.pivot.set - 1]}}</td>
                                                    <td><span style="hidden; display: block; font-size: 12px; padding-bottom: 10px; line-height: 1.5" v-html="question.question_text"></span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </v-container>
                        <v-form ref="form" v-model="valid" lazy-validation v-if="step == 2">
                            <h4>Header Section</h4>
                            <hr class="m-t-5 m-b-5">
                            <div class="row">
                                <div class="col-md-2">
                                    <v-select :rules="[v => !!v || 'required']" v-model="dialogItem.exam_type" :items="exam_type" label="Exam Type"></v-select>
                                </div>
                                <div class="col-md-2">
                                    <v-text-field :rules="[v => !!v || 'required']" v-model="dialogItem.full_marks" label="Full Marks" class="m-r-15"></v-text-field>
                                </div>
                                <div class="col-md-2">
                                    <v-text-field :rules="[v => !!v || 'required']" v-model="dialogItem.full_time" label="Time" class="m-r-15"></v-text-field>
                                </div>              
                            </div>  
                            <div class="row">
                                <div class="col-md-2">
                                    <v-select :rules="[v => !!v || 'required']" v-model="dialogItem.layout" :items="layout" label="Layout"></v-select>
                                </div>
                                <div class="col-md-2">
                                    <v-select :rules="[v => !!v || 'required']" v-model="dialogItem.language" :items="language" label="Language"></v-select>
                                </div>
                                <div class="col-md-2">                
                                    <v-text-field v-model="dialogItem.chapter_text" label="Chapter Text" class="m-r-15"></v-text-field>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">                
                                    <v-text-field v-model="dialogItem.answer_instruction" label="Answer Instruction" class="m-r-15"></v-text-field>
                                </div>
                            </div>
                            <h4>Question Section</h4>
                            <hr class="m-t-5 m-b-5">
                            <div class="row m-t-10" v-for="(type, t_i) in dialogItem.question_data">
                                <div class="col-lg-10 col-xs-12">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <h3 class="m-r-15 fs-14"><span class="fw-600 m-r-5">{{t_i + 1}}.</span>{{type.question_type.toUpperCase()}}</h3>
                                        </div>
                                        <div class="col-md-3 p-r-5 p-l-5">
                                            <v-text-field v-model="type.title" label="Title" class="m-r-15 tiny-text-input"></v-text-field>
                                        </div>
                                        <div class="col-md-5 p-r-5 p-l-5">
                                            <v-text-field class="tiny-text-input" v-model="type.answer_instruction" label="Answer Instruction"></v-text-field>
                                        </div>
                                        <div class="col-md-2 p-r-5 p-l-5">
                                            <label style="top: 10px" v-if="type.question_type=='mcq'" class="checkbox-inline m-l-15 pos-rel">
                                            <input type="checkbox" v-model="seperation">PS</label>
                                        </div>
                                    </div>
                                    <div class="row" v-for="(relative_type, r_i) in type.relative_type_data">
                                        <div class="col-md-2 p-r-5 p-l-5"></div>
                                        <div class="col-md-3 p-r-5 p-l-5">
                                            <div>{{relative_type.relative_question_type}}</div>
                                        </div>
                                        <div class="col-md-5 p-r-5 p-l-5">
                                            <v-text-field v-model="relative_type.answer_instruction" label="Answer Instruction" class="m-r-15"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </v-form>
                        <hr>
                        <v-btn :disabled="step == 1 || !valid" color="success" small @click="save">Save</v-btn>
                    </template>
                </div>
            </template>
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
                exam_data: {
                    subjects: {echelons: {}}
                },
                chapter: [],
                original_chapter: [],
                question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}],
                layout: [{value: 'question', text: 'Question'}, {value: 'lecture', text: 'Lecture'}],
                language: [{value: 'bangla', text: 'Bangla'}, {value: 'english', text: 'English'}],
                chapter_topics: [],
                dialogItem: {
                    id: '',
                    chapters: '',
                    topic: '',
                    question_data: [{'question_type': '', 'total': '', 'answerable': '', 'relatives': {}, 'relative_chapters': {} }],
                    full_time: '',
                    full_marks: '',
                    layout: 'question',
                    language: 'bangla'
                },
                valid: true,
                step: 2,
                question_index: '',
                dialog: false,
                change: 0,
                question_sets: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'],
                type_index: '',
                active_set: 0,
                seperation: false,
                exam_type: [],
                question_instruction: [],
                rules: {
                    required: value => !!value || 'Required.'
                },
            } //end return
        },
        // @router_permission
        beforeRouteEnter(to, from, next) {
            let p = window_admin_permissions.findIndex(x => x.name == 'question');
            if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin' || window_question_add) {
                next()
            } else {
                next('/')
            }
        },
        // @load_method
        created() {
            this.schedule_list();
        },

        computed: {
            TypeSort: function() {
                var items = this.exam_data.exam_question_types
                if (this.showing_question_type == 'relative') {
                    items = items.filter(item => item.relative_question_type && item.relative_question_type.toLowerCase().indexOf(this.relative_question_type_filter.toLowerCase()) > -1)
                }
                return items
            },
        },

        filters: {
            strip_tags: function(value) {
                let regex = /(<([^>]+)>)/ig;
                return value.replace(regex, "").substring(0, 50) + '...';
            }
        },

        methods: {
            // @add_item_method
            // this.$route.params.id
            schedule_list() {
                this.dialog = true
                Vue.axios.get('/admin/question/question_makable_list_with_intruction/' + this.$route.params.id).then(response => {
                    this.question_instruction = response.data.question_instruction
                    this.exam_data = response.data.schedule_list;
                    this.question_type = this.exam_data.exam_question_types.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['question_type'].toUpperCase()
                        }
                    })
                    this.exam_type_list()
                    if (this.exam_data.exam_question_types.length > 0) {
                        this.step = 2;
                        var a = this.exam_data.exam_question_types.map(x => {
                            return {
                                id: x.id,
                                question_type: x.question_type,
                                total: x.total,
                                answerable: x.answerable,
                                // questions: x.questions.filter(y => y.pivot.set == 1),
                                chapters: x.chapters,
                                title: x.title,
                                answer_instruction: x.answer_instruction,
                                seperation: x.seperation,
                                relative_type_data: x.relative_type_data
                            }
                        })
                        for (var i = 0; i < a.length; i++) {                            
                            if (!a[i].title && a[i].question_type == 'cq' ) {
                                a[i].title = response.data.cq_title
                            }
                            if (!a[i].answer_instruction && a[i].question_type == 'cq' ) {
                                a[i].answer_instruction = response.data.cq_answer_instruction
                            }
                            if (!a[i].title && a[i].question_type == 'mcq' ) {
                                a[i].title = response.data.mcq_title
                            }
                            if (!a[i].answer_instruction && a[i].question_type != 'mcq' && a[i].question_type != 'cq') {
                                if (a[i].question_type=='relative') {
                                    for (var j = 0; j < a[i].relative_type_data.length; j++) {
                                        var ind = this.question_instruction.findIndex(x => x.sub_type == a[i].relative_type_data[j].question_type)
                                        if (ind>-1) {a[i].relative_type_data[j].answer_instruction = this.question_instruction[ind].answer_instruction}
                                    }
                                } else {
                                    var ind = this.question_instruction.findIndex(x => x.question_type == a[i].question_type)
                                        if (ind>-1) {a[i].answer_instruction = this.question_instruction[ind].answer_instruction}
                                }
                            }
                            if (!a[i].title && a[i].question_type != 'mcq' && a[i].question_type != 'cq') {
                                if (a[i].question_type=='relative') {
                                    for (var j = 0; j < a[i].relative_type_data.length; j++) {
                                        var ind = this.question_instruction.findIndex(x => x.sub_type == a[i].relative_type_data[j].question_type)
                                        if (ind>-1) {a[i].title = this.question_instruction[ind].title}
                                    }
                                } else {
                                    var ind = this.question_instruction.findIndex(x => x.question_type == a[i].question_type)
                                        if (ind>-1) {a[i].title = this.question_instruction[ind].title}
                                }
                            }
                        }
                        this.dialogItem.question_data = a
                    }

                    this.dialogItem.id = this.exam_data.id
                    this.dialogItem.topic = this.exam_data.topic                    
                    this.dialogItem.exam_type = this.exam_data.exam_type_id
                    this.dialogItem.language = this.exam_data.language
                    var chapters = []
                    for (var i = 0; i < this.exam_data.chapters.length; i++) {
                        chapters.push(this.exam_data.chapters[i].name)
                    }
                    this.dialogItem.chapter_text = this.exam_data.chapter_text ? this.exam_data.chapter_text : chapters.join(', ')
                    this.dialog = false
                    // calculate time for cq 2min/mark, mcq 1min/mark
                    var full_marks = 0
                    var full_time = 0
                    var item = this.exam_data.exam_question_types
                    if (!this.exam_data.full_marks || !this.exam_data.full_time) {                        
                        for (var i = 0; i < item.length; i++) {
                            for (var j = 0; j < item[i].questions.length; j++) {
                                if (item[i].question_type == 'mcq') {full_time = full_time + (1*item[i].questions[j].question_mark)}
                                else {full_time = full_time + (2*item[i].questions[j].question_mark)}
                                full_marks = full_marks + item[i].questions[j].question_mark
                            }        
                        }
                    }
                    this.dialogItem.full_marks = this.exam_data.full_marks ? this.exam_data.full_marks : full_marks/this.exam_data.sets
                    this.dialogItem.full_time = this.exam_data.full_time ? this.exam_data.full_time : full_time/this.exam_data.sets

                });
            },
            exam_type_list() {
                Vue.axios.get('/admin/request/exam_type_list').then(response => {
                    this.exam_type = response.data.exam_type_list
                    this.exam_type = this.exam_type.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                });
            },
            save() {
                if (this.$refs.form.validate()) {
                    this.dialog = true
                    Vue.axios.post('/admin/question/question_print', this.dialogItem).then(response => {
                        this.dialog = false
                        this.$router.push({
                            name: 'question_print',
                            params: {
                                id: this.$route.params.id
                            }
                        })
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
                        this.dialog = false
                    });                    
                }
            },
            change_set(index) {
                this.active_set = index - 1
                for (var i = 0; i < this.dialogItem.question_data.length; i++) {
                    this.dialogItem.question_data[i].questions = this.exam_data.exam_question_types[i].questions.filter(x => x.pivot.set == index)
                }
            },
            add_chapter_to_generate(c_i) {
                this.generate_data.chapters.push({
                    'value': '',
                    'question_quantity': 0
                })
            },
            delete_chapter_to_generate(c_i) {
                this.generate_data.chapters.splice(c_i, 1)
            },
            // @add_item_method_close_dialog
            clear() {
                this.search = ''
                this.search_filter = ''
                this.search_filter1 = ''
            }
        }
    } 
</script>


<style>
  .v-btn--floating.v-btn--small{
    height: 22px;
    width: 22px;
  }
  .v-chip .v-chip__content{
    cursor: pointer;
  }
  .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .v-input{
    font-size: 14px;
  }
  .v-btn{
    height: 32px;
  }
  .relative_div label{
    font-size: 12px;
  }
  .relative_div .v-input{
    font-size: 12px;
  }
</style>