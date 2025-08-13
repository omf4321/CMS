<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="question_select">
        <v-container fluid class="p-t-0">
            <!-- @page_headline -->
            <div class="container p-t-0">
                <h3 class="headline">Select Questions <span class="fs-14">{{dialogItem.subjects.echelons.name}}</span> <span class="fs-14">{{dialogItem.subjects.name}}</span></h3>
                <v-divider class="my-3"></v-divider>
                <v-combobox v-model="dialogItem.selected_chapter" :disabled="chapter.length==1" :items="chapter" hide-selected label="Chapters" :clearable=true multiple persistent-hint small-chips @change="question_list"></v-combobox>
                <v-select v-model="sets_action" :items="sets_distribution" label="Distribution of Sets" class="fs-12" @change="question_list"></v-select>
                <div v-if="sets_action && dialogItem.sets>1" class="question_set">Sets:
                    <v-chip :class="{'active': active_set == index-1}" v-for="index in dialogItem.sets" :key="index" @click="change_set(index)">{{question_sets[index-1]}}</v-chip>
                </div>
                <hr class="m-t-5 m-b-5">
                <div v-if="type_data.question_type == 'relative'" class="text-justify relative-div">
                    <div>{{type_data.relatives.relative_text.substring(0, 200)}}...</div>
                    <v-chip :class="{'m-r-10':true, 'active':relative_type.relative_question_type == relative_question_type}" v-for="(relative_type, r_t_i) in type_data.relative_type_data" :key="r_t_i" @click="change_relative_question_type(relative_type.relative_question_type, r_t_i)">{{relative_type.relative_question_type.toUpperCase()}}</v-chip>
                </div>
            </div>
            <template v-if="mount">
                <div>
                    <template>
                        <div class="container p-t-0">
                            <div>
                                <div v-for="(questions, q_i) in dialogItem.question_data">
                                    <div :id="question.id" v-for="(question, q_i) in questions.question_data" class="card p-10 m-b-10 step-2">
                                        <div class="row">
                                            <div class="col-xs-12 pos-rel">
                                                <v-checkbox class="checkbox_flex" v-model="question.val" :disabled="question_count >= total_question && question.val==false" @change="selection_changed(question.id, question.val, q_i)"></v-checkbox>
                                                <span class="question_id">{{question.id}}</span>
                                                <div class="solaiman fs-14 fw-600"><span v-for="chapter in question.chapters">{{chapter.name}}</span> <span v-if="question.relatives && question.question_type != 'relative'">(Rel: {{question.relatives.id}})</span></div>
                                                <hr class="m-t-5 m-b-5" style="width: 95%">
                                                <div class="solaiman fs-12 m-b-5 text-warning" v-if="question.relatives && question.question_type != 'relative'" v-html="question.relatives.relative_text"></div>
                                                <div class="p-r-30 fs-13 solaiman" v-html="question.question_text"></div>
                                                <div v-if="question.question_tag || question.topics.length>0" class="solaiman">
                                                    <hr style="border-color: #cacaca; width: 95%" class="m-t-5 m-b-5">
                                                    <span v-if="question.question_tag" class="m-r-12 fs-12">Tags: {{question.question_tag}}</span>
                                                    <span v-if="question.topics.length>0" class="m-r-8 fs-12">Topics:</span>
                                                    <span v-if="question.topics.length>0" class="m-r-8 fs-12" v-for="(topic, t_i) in question.topics">{{topic.name}}</span>
                                                </div>
                                                <div v-if="question.short_answer" class="solaiman">
                                                    <hr style="border-color: #cacaca;  width: 95%" class="m-t-5 m-b-5">
                                                    <span style="color:#9e9e9e; font-size: 12px">Short Answer: </span>
                                                    <span style="color:#318434; margin-left: 10px; text-align: justify; font-size: 12px" v-html="question.short_answer"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </v-container>
        <template>
            <v-footer class="pa-3" dark color="teal" fixed height="40">
                <router-link :to="{ name: 'question_setup', params: { id: $route.params.id}}">
                    <v-icon medium @click="">close</v-icon>
                </router-link>
                <div style="width: 100%; text-align: center">
                    <span style="width: 60px;">{{question_count}} / {{total_question}}</span>
                </div>
                <v-spacer></v-spacer>
                <v-btn :disabled="change == 0" color="#fff" flat @click="save" style="min-width: 10px; padding: 0px; margin: 0px!important">
                    <v-icon>save</v-icon>
                </v-btn>
            </v-footer>
        </template>
        <v-dialog v-model="dialog" hide-dialog persistent width="300">
            <v-card color="#009688" dark>
                <v-card-text> Please Wait..
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
                    exam_data: {},
                    chapter: [],
                    original_questions: [],
                    question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}],
                    dialogItem: {
                        id: '',
                        chapters: '',
                        subjects: {echelons: {}},
                        topic: '',
                        selected_chapter: [],
                        question_data: [],
                        selected_questions: []
                    },
                    valid: true,
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    step: 1,
                    select_relative_text: '',
                    relative_text: [],
                    original_relative_text: [],
                    show_relative: '',
                    select_relative_chapter: '',
                    question_index: '',
                    relative_index: '',
                    dialog: false,
                    question_count: 0,
                    type_data: '',
                    relative_question_type: '',
                    question_type: '',
                    total_question: 0,
                    change: 0,
                    mount: false,
                    sets_action: false,
                    sets_distribution: [{
                        value: false,
                        text: 'Suffle Among Sets'
                    }, {
                        value: true,
                        text: 'Select All Sets of Questions'
                    }],
                    question_sets: ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'],
                    active_set: 0,
                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                if (window_user_role == 'teacher' && window_authorise != 'authorised') {return next('/authentication')}
                let p = window_admin_permissions.findIndex(x => x.name=='question_check');
                let r = window_user_role ? window_user_role.findIndex(x => x == 'teacher') : -1
                if (p > -1 || r > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
            },
            // @load_method
            created() {
                this.schedule_list();
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
                    Vue.axios.get('/admin/question/get_select_question_item/' + this.$route.params.id + '/' + this.$route.params.type_id).then(response => {
                        this.dialogItem = response.data.question_list;
                        this.dialogItem.chapters = response.data.question_list.chapters
                        this.type_data = response.data.type;
                        this.sets_action = this.type_data.questions.length > 0 ? true : false
                        this.get_chapter()
                    });
                },
                question_list() {
                    this.dialog = true
                    var data = {}
                    data.chapter = this.dialogItem.selected_chapter
                    data.question_type = this.type_data.question_type
                    data.relative_question_type = this.relative_question_type
                    data.relative_id = this.type_data.relative_id
                    data.exam_question_type_id = this.type_data.id
                    data.subject_id = this.dialogItem.subject_id
                    data.echelon_id = this.dialogItem.echelon_id
                    $('.card').removeClass('selected')
                    Vue.axios.post('/admin/question/select_question_list', data).then(response => {
                        if (response.data.type) {
                            this.type_data.questions = response.data.type.questions
                        }
                        // manage all question data
                        this.original_questions = response.data.question_list;
                        this.dialogItem.question_data = response.data.question_list;
                        this.dialogItem.question_data = this.dialogItem.question_data.map(x => {
                            return {
                                id: x.id,
                                question_type: x.question_type,
                                question_relative_text_id: x.question_relative_text_id,
                                relative_question_type: x.relative_question_type,
                                relatives: x.relatives,
                                question_mark: x.question_mark,
                                question_tag: x.question_tag,
                                question_text: x.question_text,
                                short_answer: x.short_answer,
                                chapters: x.chapters,
                                topics: x.topics,
                                val: false
                            }
                        })
                        var item = this.dialogItem.question_data
                        // arrange questions for relative questions
                        var question_item = []
                        for (var i = 0; i < item.length; i++) {
                            var object = {
                                id: '',
                                val: false,
                                relative_id: '',
                                relative_type: '',
                                question_data: []
                            }
                            var q_index = question_item.findIndex(x => x.relative_id && x.relative_id == item[i].question_relative_text_id)
                            if (q_index == -1) {
                                question_item.push(object)
                                question_item[question_item.length - 1].id = item[i].id
                                question_item[question_item.length - 1].relative_id = item[i].question_relative_text_id
                                question_item[question_item.length - 1].relative_type = item[i].relative_question_type
                                question_item[question_item.length - 1].question_data.push(item[i])
                            } else {
                                question_item[q_index].question_data.push(item[i])
                            }
                        }
                        this.dialogItem.question_data = question_item
                        // manage selected questions
                        var ids = []
                        if (this.sets_action) {
                            var filter_set_questions = this.type_data.questions.filter(x => x.pivot.set == this.active_set + 1 && (x.question_type == 'relative' ? x.relative_question_type == this.relative_question_type : x.question_type == this.question_type))
                        } else {
                            var filter_set_questions = this.type_data.questions.filter(x => x.question_type == 'relative' ? x.relative_question_type == this.relative_question_type : x.question_type == this.question_type)
                        }
                        for (var i = 0; i < filter_set_questions.length; i++) {                                
                            for (var j = 0; j < this.dialogItem.question_data.length; j++) {
                                for (var k = 0; k < this.dialogItem.question_data[j].question_data.length; k++) {
                                    var index = this.dialogItem.question_data[j].question_data.findIndex(x => x.id == filter_set_questions[i].id)
                                    if (index>-1) {
                                        ids.push(this.dialogItem.question_data[j].question_data[index].id)
                                        this.dialogItem.question_data[j].question_data[index].val = true;
                                        this.dialogItem.question_data[j].val = true
                                    }
                                }
                            }
                        }
                        // take selected value on top
                        this.dialogItem.question_data.sort(function(a, b) {
                            return b.val - a.val;
                        });
                        this.mount = true
                        this.dialog = false
                        setTimeout(function() {
                            for (var i = 0; i < ids.length; i++) {
                                $('#' + ids[i]).addClass('selected')
                            }
                        }.bind(this), 200)
                        // counting question
                        this.question_count = filter_set_questions.length
                        if (this.type_data.question_type != 'relative') {
                            this.total_question = parseInt(this.type_data.total)
                        }                            
                        if (!this.sets_action && this.type_data.question_type != 'relative') {
                            this.total_question = this.dialogItem.sets * this.type_data.total
                        }
                        var relative_total_index = this.type_data.relative_type_data.findIndex(x => x.relative_question_type == this.relative_question_type)
                        if (!this.sets_action && this.type_data.question_type == 'relative'){
                            this.total_question = this.dialogItem.sets * this.type_data.relative_type_data[relative_total_index].total
                        }
                        if (this.sets_action && this.type_data.question_type == 'relative'){
                            this.total_question = this.type_data.relative_type_data[relative_total_index].total
                        }
                    });
                },
                relative_text_by_chapter(chapter_id) {
                    this.dialog = true
                    Vue.axios.post('/admin/question/relative_text_by_chapter/' + this.select_relative_chapter).then(response => {
                        this.original_relative_text = response.data.relative_text_by_chapter;
                        this.relative_text = this.original_relative_text.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['relative_text'].substring(0, 50)
                            }
                        })
                        this.select_relative_text = this.dialogItem.question_data[this.question_index].relatives.id
                        this.show_relative_text(this.select_relative_text)
                        this.dialog = false
                    });
                },
                get_chapter() {
                    if (this.type_data.question_type == 'relative') {
                        var a = {
                            value: this.type_data.relative_chapters.id,
                            text: this.type_data.relative_chapters.name
                        }
                        this.chapter.push(a)
                        this.relative_question_type = this.type_data.relative_type_data[0].relative_question_type
                        this.total_question = parseInt(this.type_data.relative_type_data[0].total)
                    } else {
                        var chapter = Object.assign([], this.dialogItem.chapters);
                        this.chapter = this.dialogItem.chapters.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['chapter_no'] + ' - ' + arr['name']
                            }
                        })
                        this.total_question = parseInt(this.type_data.total)
                    }
                    this.question_type = this.type_data.question_type
                    if (this.chapter.length == 1 || this.type_data.questions.length > 0) {
                        this.dialogItem.selected_chapter = []
                        for (var i = 0; i < this.type_data.chapters.length; i++) {
                            var index = this.chapter.findIndex(x => x.value == this.type_data.chapters[i])
                            this.dialogItem.selected_chapter.push(this.chapter[index])
                        }
                        if (this.dialogItem.selected_chapter.length == 0) {
                            this.dialogItem.selected_chapter = this.chapter
                        }
                        this.question_list();
                    }
                    if (!this.sets_action) {
                        this.total_question = this.dialogItem.sets * this.type_data.total
                    }
                },
                change_relative_question_type(type, r_t_i) {
                    this.relative_question_type = type
                    this.question_count = 0
                    this.total_question = parseInt(this.type_data.relative_type_data[r_t_i].total)
                    this.question_list()
                },
                selection_changed(id, val, q_i) {
                    this.change = 1
                    if (val == true) {
                        this.question_count = this.question_count + 1
                        $('#' + id).addClass('selected')
                    } else {
                        this.question_count = this.question_count - 1
                        $('#' + id).removeClass('selected')
                    }
                },
                save() {
                    var item = this.dialogItem.question_data
                    var question_item = []
                    for (var i = 0; i < item.length; i++) {
                        for (var j = 0; j < item[i].question_data.length; j++) {
                            question_item.push(item[i].question_data[j])
                        }
                    }
                    if (this.sets_action && this.total_question > question_item.length) {return alert('You have to select at least ' + this.total_question + ' questions')}
                    this.dialog = true
                    this.dialogItem.selected_questions = question_item.filter(x => x.val == true);
                    this.dialogItem.method = 'select'
                    this.dialogItem.relative_question_type = this.relative_question_type
                    this.dialogItem.exam_question_type_id = this.type_data.id
                    this.dialogItem.total_question = this.total_question
                    this.dialogItem.sets_action = this.sets_action
                    if (this.sets_action) {
                        this.dialogItem.active_set = this.active_set
                    }
                    Vue.axios.post('/admin/question/select_question', this.dialogItem).then(response => {
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {
                            $('.user_nav').removeClass('successful')
                        }.bind(this), 3000)
                        this.dialog = false
                        if (this.type_data.question_type != 'relative') {
                            // this.$router.push({ name: 'question_setup', params: { id: this.$route.params.id}})
                        }
                    }, error => {
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
                        this.dialog = false
                    });
                },
                change_set(index) {
                    this.active_set = index - 1
                    $('.card').removeClass('selected')
                    this.dialog = true
                    var ids = []
                    for (var i = 0; i < this.dialogItem.question_data.length; i++) {
                        for (var j = 0; j < this.dialogItem.question_data[i].question_data.length; j++) {
                            this.dialogItem.question_data[i].question_data[j].val = false
                        }
                    }
                    // get selected questions
                    var filter_set_questions = this.type_data.questions.filter(x => x.pivot.set == this.active_set + 1)
                    // loop for select in dom
                    for (var i = 0; i < filter_set_questions.length; i++) {                                
                        for (var j = 0; j < this.dialogItem.question_data.length; j++) {
                            var index = this.dialogItem.question_data[j].question_data.findIndex(x => x.id == filter_set_questions[i].id)
                            if (index>-1) {
                                ids.push(filter_set_questions[i].id)
                                this.dialogItem.question_data[j].question_data[index].val = true;
                                this.dialogItem.question_data[j].val = true
                            }                                
                        }
                    }
                    this.dialogItem.question_data.sort(function(a, b) {
                        return b.val - a.val;
                    });
                    setTimeout(function() {
                        for (var i = 0; i < ids.length; i++) {
                            $('#' + ids[i]).addClass('selected')
                        }
                        this.dialog = false
                    }.bind(this), 500)
                    this.question_count = filter_set_questions.filter(x => x.question_type == 'relative' ? x.relative_question_type == this.relative_question_type : x.question_type == this.question_type).length
                },
                selecte_trigger(index, val) {

                },
                add_relative_data(q_i) {
                    this.dialogItem.question_data[q_i].relatives.items.push({
                        'relative_question_type': '',
                        'total': ''
                    })
                },
                delete_question_data(index) {
                    this.dialogItem.question_data.splice(index, 1)
                },
                delete_relative_data(q_i, r_i) {
                    this.dialogItem.question_data[q_i].relatives.splice(r_i, 1)
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


<style> .v-btn--floating.v-btn--small {
    height: 22px;
    width: 22px;
}

.v-chip .v-chip__content {
    cursor: pointer;
}

.v-text-field {
    padding-top: 2px;
    margin-top: 2px;
}

.v-input {
    font-size: 14px;
}

.v-btn {
    height: 32px;
}

.relative_div label {
    font-size: 12px;
}

.relative_div .v-input {
    font-size: 12px;
}

</style>