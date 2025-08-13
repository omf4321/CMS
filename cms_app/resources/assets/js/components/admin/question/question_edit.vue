<template>
    <div class="question_edit">
        <v-container fluid class="p-t-10">
            <!-- @page_headline -->
            <div>
                <div class="row">
                    <div class="col-lg-2">
                        <h3 class="fs-20 inline-block">Manage Chapter <v-icon class="fs-20" small @click="question_list">refresh</v-icon></h3>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step==2">
                        <v-select v-model="search_question_type" :items="question_type" label="Search Type" :clearable=true></v-select>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step==1">
                        <v-select v-model="dialogItem.echelon_id" @change="get_subject" :items="echelon" label="Class"></v-select>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step==1">
                        <v-select v-model="dialogItem.subject_id" @change="question_list" :items="subject" label="Subject"></v-select>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step<3">
                        <v-text-field v-model="search_2" label="Search"></v-text-field>
                    </div>
                    <div class="col-lg-2 col-xs-6 pos-rel" style="display: inline-flex">
                        <v-text-field v-model="search_id" label="Search By ID"></v-text-field>
                        <v-chip style="display:inline-table; top: 10px" class="pos-a" @click="goto_question_id">Go</v-chip>
                    </div>
                    <div v-if="step >= 3" class="pos-rel col-lg-2 col-xs-6" style="top: 13px; display: inline-flex">
                        <v-chip label @click="prev">Previous Question</v-chip>
                        <v-chip label @click="next">Next Question</v-chip>
                    </div>
                    <div v-if="step > 1" class="col-lg-2 col-xs-6">
                        <span class="float-right m-t-23 cur-p" @click="go_back">Go Back ⇾</span>
                    </div>
                </div>
                <v-btn v-if="step>=3" color="info" @click="refine_editor_text" style="position: fixed; bottom: 10px; right: 120px; z-index: 9" :disabled="!valid_1 || loading">Check</v-btn>
                <v-btn v-if="step>=3" color="success" @click="save" style="position: fixed; bottom: 10px; right: 20px; z-index: 9" :disabled="!valid || loading" :loading="loading">Save</v-btn>
            </div>
            <v-divider class="my-3"></v-divider>
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
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container class="p-0">
                                <div class="step_1_div">
                                    <v-layout class="p-l-30 p-r-30 step_1" v-if="step==1" v-for="(subject, s_i) in itemsSorted" :key="s_i">
                                        <div>
                                            <h4 class="fs-16 fw-700 m-l-10">{{subject.echelons.name}} ⇾ {{subject.name}}</h4>
                                            <hr class="m-t-10 m-b-10">
                                            <v-layout>
                                                <div class="m-r-30 cur-p inline-block" v-for="(chapter, c_i) in subject.chapters">
                                                    <div class="chapter_list">
                                                        <span @click="goto_step2(s_i, c_i)">{{chapter.chapter_no}}. {{chapter.name}} <v-chip small color="#c1c1c1" class="ml-2">{{chapter.questions.length}}</v-chip></span>
                                                        <v-icon @click="delete_question_by_chapter('single', chapter.id, subject.id, s_i, c_i)" class="m-l-10 fs-20">delete</v-icon>
                                                    </div>
                                                </div>
                                                <div class="chapter_list inline-block cur-p" v-if="combine_question_list[s_i].questions.length>0">
                                                    <span @click="goto_step2(s_i, 'combine')">Combined Question<v-chip small color="#c1c1c1" class="ml-2">{{combine_question_list[s_i].questions.length}}</v-chip></span>
                                                    <v-icon @click="delete_question_by_chapter('multiple', '', subject.id, s_i, 'combine')" class="m-l-10 fs-20">delete</v-icon>
                                                </div>
                                            </v-layout>
                                        </div>
                                    </v-layout>
                                </div>
                                <div class="step_2_div" style="display: none">
                                    <!-- step2 when not combine -->
                                    <div class="p-l-30 p-r-30 step_2" v-if="step==2 && chapter_index!='combine'">
                                        <h4 class="fs-16 fw-700 m-l-10">{{question_list_data[subject_index].echelons.name}} ⇾ {{question_list_data[subject_index].name}} ⇾ {{question_list_data[subject_index].chapters[chapter_index].chapter_no}}. {{question_list_data[subject_index].chapters[chapter_index].name}}</h4>
                                        <hr>
                                        <div v-for="(question_type, i) in QuestionTypeSort">
                                            <div class="m-l-10 m-b-10 fw-700" style="text-transform: uppercase;text-decoration: underline;">{{question_type.type}} - {{question_type.count}}</div>
                                            <!-- question div -->
                                            <div>
                                                <!-- question -->
                                                <div>
                                                    <ol class="solaiman">
                                                        <li class="mr-2 cur-p pos-rel qus-list" v-if="question.question_type == question_type.type && question.question_type != 'relative'" v-for="(question, q_i) in questionSort">
                                                            <span style="position: absolute; right: 0px">
                                          <span>{{question.question_tag}} ({{question.id}})</span>
                                                            <v-icon class="fs-20" @click="delete_question(question.id, q_i, false)">delete</v-icon>
                                                            </span>
                                                            <p class="m-b-10 question_p" @click="goto_step3(question, q_i)" v-html="question.question_text"></p>
                                                        </li>
                                                    </ol>
                                                </div>
                                                <!-- relative question -->
                                                <div v-if="question_list_data[subject_index].chapters[chapter_index].relatives">
                                                    <div v-for="(relative, r_i) in relativeQuestionSort">
                                                        <p class="m-l-10 m-b-10 fs-15 text-justify" style="color: #795548; font-style: italic; max-height: 45px; overflow: hidden" v-if="relative.question_type == question_type.type" v-html="relative.relative_text"></p>
                                                        <ul style="list-style: none" class="list-inline" v-if="relative.relative_question_type.split(',').length>1">
                                                            <li style="background-color:#607D8B; padding: 2px 10px; border-radius:18px; color: #fff; margin:2px 5px 2px 15px; cursor: pointer; font-size: 12px" @click="relative_question_type_filter = ''">All</li>
                                                            <li style="background-color:#607D8B; padding: 2px 10px; border-radius:18px; color: #fff; margin:2px 5px; cursor: pointer; font-size: 12px" v-for="(type, t_i) in relative.relative_question_type.split(',')" @click="relative_question_type_filter = type">{{type.toUpperCase()}}</li>
                                                        </ul>
                                                        <ol class="solaiman">
                                                            <li class="mr-2 cur-p pos-rel qus-list" v-if="question.question_type == question_type.type" v-for="(question, q_i) in relative.questions">
                                                                <span style="position: absolute; right: 0px">
                                            <span>{{question.question_tag}} ({{question.id}})</span>
                                                                <v-icon class="fs-20" @click="delete_question(question.id, q_i, true)">delete</v-icon>
                                                                </span>
                                                                <p class="m-b-0 question_p" @click="goto_step3(question, q_i)" v-html="question.question_text"></p>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- question div end -->
                                        </div>
                                    </div>
                                    <!-- step 2 when combine -->
                                    <div class="p-l-30 p-r-30 step_2" v-if="step==2 && chapter_index=='combine'">
                                        <h4 class="fs-16 fw-700 m-l-10">{{question_list_data[subject_index].echelons.name}} ⇾ {{question_list_data[subject_index].name}} ⇾ Combine Questions</h4>
                                        <hr>
                                        <div v-for="(question_type, i) in QuestionTypeSort">
                                            <div class="m-l-10 m-b-10 fw-700" style="text-transform: uppercase;text-decoration: underline;">{{question_type.type}} - {{question_type.count}}</div>
                                            <div>
                                                <div>
                                                    <ol class="solaiman">
                                                        <li class="mr-2 cur-p pos-rel" v-if="question.question_type == question_type.type && !question.question_relative_text_id" v-for="(question, q_i) in questionSort">
                                                            <span style="position: absolute; right: 0px">
                                          <span>{{question.question_tag}} ({{question.id}})</span>
                                                            <v-icon class="fs-20" @click="delete_question(question.id, q_i, false)">delete</v-icon>
                                                            </span>
                                                            <p class="m-b-0 question_p" @click="goto_step3(question, q_i)" v-html="question.question_text"></p>
                                                        </li>
                                                    </ol>
                                                </div>
                                                <!-- relative of combine -->
                                                <div v-if="combine_question_list[subject_index].relatives">
                                                    <div v-for="(relative, r_i) in combine_question_list[subject_index].relatives">
                                                        <p class="m-l-10 m-b-10 fs-15 text-justify" style="color: #795548; font-style: italic; max-height: 45px; overflow: hidden" v-if="relative.question_type == question_type.type" v-html="relative.relative_text"></p>
                                                        <ol class="solaiman">
                                                            <li class="mr-2 cur-p pos-rel" v-if="question.question_type == question_type.type" v-for="(question, q_i) in relative.questions">
                                                                <span style="position: absolute; right: 0px">
                                            <span>{{question.question_tag}} ({{question.id}})</span>
                                                                <v-icon class="fs-20" @click="delete_question(question.id, q_i, true)">delete</v-icon>
                                                                </span>
                                                                <p class="m-b-0 question_p" @click="goto_step3(question, q_i)" v-html="question.question_text"></p>
                                                            </li>
                                                        </ol>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="step_3_div" style="display: none">
                                    <div class="row p-l-30 p-r-30 step_3" v-if="step==3">
                                        <div class="col-lg-2 pb-0 pt-0 pos-rel">
                                            <v-select v-model="dialogItem.echelon_id" :items="echelon" disabled label="Class" @change="get_subject" required></v-select>
                                            <v-autocomplete v-model="dialogItem.subject_id" :items="subject" disabled label="Subject" @change="get_chapter" required></v-autocomplete>
                                            <v-select v-model="dialogItem.question_type" :items="question_type" :rules="[v => !!v || 'Type is required']" label="Question Type" required></v-select>
                                            <v-text-field v-model="dialogItem.question_mark" label="Question Mark" :rules="[v => !!v || 'Type is required']" required></v-text-field>
                                            <v-text-field v-model="dialogItem.question_mark_detail" label="Mark Detail" required></v-text-field>
                                            <v-text-field v-model="dialogItem.board_question_no" label="Board Question No" type="number"></v-text-field>
                                            <v-select v-model="dialogItem.question_status" :items="question_status" label="Question Status"></v-select>
                                            <v-textarea outlined v-model="dialogItem.short_answer" label="Short Answer" rows="25"></v-textarea>
                                        </div>
                                        <div class="col-lg-7 pb-0 pt-0">
                                            <input type="file" name="insert_image" @change="image_upload" multiple style="display: none">
                                            <div class="relative_text_editor">
                                                <html-editor v-if="dialogItem.relative_text_id" placeholder="Relative Text" height="150" :model="relative_text_editor"></html-editor>
                                            </div>
                                            <div v-for="(editor, key) in editor_detail" @click="go_to_editor_question(key)" style="cursor: pointer; margin-bottom: 12px; display: inline-block">
                                                <span class="mr-2" style="font-weight: 700">{{key + 1}}.</span> <span class="mr-1">S ({{editor.stem}})</span> <span class="mr-1">Q ({{editor.qus}})</span> <span class="mr-3">T ({{editor.tag}})</span>
                                            </div>
                                            <div class="pos-rel question_editor">
                                                <div class="editor_notation pos-a">
                                                    <v-radio-group v-model="question_notation" row>
                                                        <v-radio label="Rom" value="roman"></v-radio>
                                                        <v-radio label="Char" value="char"></v-radio>
                                                        <v-radio label="Digit" value="digit"></v-radio>
                                                    </v-radio-group>
                                                    <input class="notation_input" v-model="question_begin_text">
                                                    <input class="notation_input mr-2" v-model="question_end_text">
                                                    <v-radio-group v-model="question_child_notation" row>
                                                        <v-radio label="Uni" value="unicode"></v-radio>
                                                        <v-radio label="Rom" value="roman"></v-radio>
                                                        <v-radio label="Char" value="char"></v-radio>
                                                    </v-radio-group>
                                                    <input class="notation_input" v-model="question_child_begin_text">
                                                    <input class="notation_input" v-model="question_child_end_text">
                                                </div>
                                                <html-editor height="250" :model="question_editor"></html-editor>
                                            </div>
                                            <div class="pos-rel answer_editor">
                                                <html-editor height="500" :model="answer_editor" placeholder="Detail Answer"></html-editor>
                                            </div>
                                        </div>
                                        <!-- right side -->
                                        <div class="col-lg-3 pb-0 pt-0">
                                            <v-text-field v-model="dialogItem.id" label="Question ID" class="id_input" disabled></v-text-field>
                                            <v-text-field v-model="dialogItem.question_length" label="Question Length" type="number" :rules="[v => !!v || 'Type is required']" @input="valid_1=true"></v-text-field>
                                            <v-combobox v-model="dialogItem.chapters" :items="chapter" hide-selected label="Add Chapters" hint="Hit double backspace to delete" :clearable=true multiple persistent-hint small-chips @change="get_chapter_topic" :rules="[v => !!v || 'Chapter is required']"></v-combobox>
                                            <v-autocomplete v-if="dialogItem.question_type=='relative'" disabled v-model="dialogItem.relative_text_id" :items="relative_text" :clearable=true></v-autocomplete>
                                            <v-combobox v-model="dialogItem.question_tag" :items="question_tag" hide-selected label="Question Tag" :clearable=true multiple persistent-hint small-chips></v-combobox>
                                            <v-text-field v-model="dialogItem.question_title" label="Question Title"></v-text-field>
                                            <v-list class="checkbox_flex" v-if="this.chapter_topic.length>0">
                                                Topics
                                                <v-layout row wrap>
                                                    <div v-for="(topic, j) in chapter_topic">
                                                        <v-list-item @click="">
                                                            <v-list-item-action>
                                                                <v-checkbox v-model="topic.val"></v-checkbox>
                                                            </v-list-item-action>
                                                            <v-list-item-content>
                                                                <v-list-item-title style="font-weight: 500; font-size: 12px">{{topic.topic_no}}-{{topic.text}}</v-list-item-title>
                                                            </v-list-item-content>
                                                        </v-list-item>
                                                    </div>
                                                </v-layout>
                                            </v-list>
                                        </div>
                                    </div>
                                </div>
                            </v-container>
                        </v-form>
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
                echelon: [],
                subject: [],
                original_subject: [],
                chapter: [],
                original_chapter: [],
                chapter_topic: [],
                original_chapter_topic: [],
                question: [],
                question_tag: [],
                combine_question_list: [],
                question_list_data: [],
                original_question_list_data: [],
                question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}],
                question_status: [{value: 'examable', text: 'Examable'}, {value: 'lecturable', text: 'Lecturable'}, {value: 'both', text: 'Both'}],
                relative_text: [],
                original_relative_text: [],
                // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                short_answer: [],
                short_answer_text: '',
                dialogItem: {
                    id: '',
                    subject_id: '',
                    question_type: '',
                    question_mark: '',
                    board_question_no: '',
                    question_status: '',
                    chapters: [],
                    topics: [],
                    question_text: '',
                    short_answer: '',
                    detail_answer: '',
                    question_tag: '',
                    question_length: "0",
                    relative_text: [],
                    relative_textarea: '',
                    relative_text_id: '',
                },
                editedItem: '',
                rules: {
                    required: value => !!value || 'Required.'
                },

                // @accesories_data
                valid: true,
                valid_1: true,
                success_alert: false,
                error_alert: false,
                loading: false,
                question_editor: '',
                answer_editor: '',
                relative_text_editor: '',
                editor_detail: [],
                question_notation: 'digit',
                question_begin_text: '',
                question_end_text: ')',
                question_child_notation: 'unicode',
                question_child_begin_text: '',
                question_child_end_text: ')',
                step: 1,
                subject_index: '',
                chapter_index: '',
                question_index: '',
                question_type_header: [],
                search_1: '',
                search_2: '',
                search_question_type: '',
                search_id: '',
                relative_question_type_filter: ''
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
                var items = this.question_list_data
                if (this.search_2) {
                    items = items.map(arr => {
                        return {
                            id: arr['id'],
                            name: arr['name'],
                            echelons: arr['echelons'],
                            search_question_typequestions: arr['questions'],
                            relatives: arr['relatives'],
                            chapters: arr['chapters'].map(x => {
                                return {
                                    questions: x['questions'],
                                    name: x.name.toLowerCase().indexOf(this.search_2.toLowerCase()) > -1 ? x.name : ''
                                }
                            }).filter(x => x.name != "")
                        }
                    })
                }

                var items1 = this.original_question_list_data
                items1 = items1.filter(item => item.echelons.name.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1 || item.name.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1)

                var items3 = items.filter(x => x.chapters.length > 0)

                if (this.search_1 && this.search_2) {
                    return items
                }
                if (!this.search_1 && this.search_2) {
                    return items3
                }
                return items1;
            },
            // search question type
            QuestionTypeSort: function() {
                var items = this.question_type_header
                if (!this.search_question_type) {
                    return items
                }
                items = items.filter(item => item.type == this.search_question_type)
                return items
            },
            // search question
            questionSort: function() {
                if (this.chapter_index == 'combine') {
                    var items = this.combine_question_list[this.subject_index].questions
                } else {
                    var items = this.question_list_data[this.subject_index].chapters[this.chapter_index].questions
                }
                items = items.filter(item => item.question_text.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1 || item.question_type.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1).filter(item => item.question_text.toLowerCase().indexOf(this.search_2.toLowerCase()) > -1 || item.question_type.toLowerCase().indexOf(this.search_2.toLowerCase()) > -1)
                return items;
            },
            // search relative question
            relativeQuestionSort: function() {
                if (this.chapter_index == 'combine') {
                    var items = this.combine_question_list[this.subject_index].relatives
                } else {
                    var items = this.question_list_data[this.subject_index].chapters[this.chapter_index].relatives
                }
                if (items.length > 0) {
                    items = items.map(x => {
                        return {
                            id: x.id,
                            relative_question_type: x.relative_question_type,
                            chapter_id: x.chapter_id,
                            relative_text: x.relative_text,
                            question_type: x.question_type,
                            questions: x.questions.map(y => {
                                return {
                                    id: y.id,
                                    question_type: y.question_type,
                                    question_mark: y.question_mark,
                                    question_mark_detail: y.question_mark_detail,
                                    board_question_no: y.board_question_no,
                                    question_length: y.question_length,
                                    question_title: y.question_title,
                                    question_tag: y.question_tag,
                                    question_status: y.question_status,
                                    short_answer: y.short_answer,
                                    detail_answer: y.detail_answer,
                                    topics: y.topics,
                                    relatives: y.relatives,
                                    question_relative_text_id: y.question_relative_text_id,
                                    chapters: y.chapters,
                                    relative_question_type: y.relative_question_type,
                                    question_text: y.question_text.toLowerCase().indexOf(this.search_1.toLowerCase()) > -1 ? y.question_text : ''
                                }
                            }).filter(z => z.question_text && z.relative_question_type && z.relative_question_type.toLowerCase().indexOf(this.relative_question_type_filter.toLowerCase()) > -1)
                        }
                    }).filter(xx => xx.questions.length > 0)
                    return items;
                }
            }
        },
        filters: {
            strip_tags: function(value) {
                if (!value) return ''
                return value.replace(/<\/?[^>]+(>|$)/g, "")
            }
        },
        // @load_method
        created() {
            this.admin_branch_list();
        },

        methods: {
            // @add_item_method
            admin_branch_list() {
                    this.branch = window_branch;
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].id
                        this.admin_echelon_list(this.dialogItem.branch_id);
                        this.admin_subject_list()
                        // this.relative_text_list()
                    }
                    this.branch = this.branch.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                admin_echelon_list() {
                    this.echelon = window_echelons.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                admin_subject_list(row_subject_id) {
                    Vue.axios.get('/admin/request/subject_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                        this.original_subject = response.data.subject_list_by_branch;
                        this.subject = response.data.subject_list_by_branch;
                        this.subject = this.subject.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['name']
                            }
                        })
                    });
                },
                chapter_list(id) {
                    Vue.axios.get('/admin/request/chapter_list_by_class/' + id).then(response => {
                        this.original_chapter = response.data.chapter_list_by_class;
                        this.chapter = response.data.chapter_list_by_class;
                        this.chapter = this.chapter.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['name']
                            }
                        })
                    });
                },
                chapter_topic_list(id) {
                    Vue.axios.get('/admin/request/chapter_topic_by_class/' + id).then(response => {
                        this.original_chapter_topic = response.data.chapter_topic_list;
                    });
                },
                question_list() {
                    Vue.axios.get('/admin/question/question_list_by_chapter/' + this.dialogItem.subject_id).then(response => {
                        this.question_list_data = response.data.question_list_single;
                        this.original_question_list_data = response.data.question_list_single;
                        var combine_list = response.data.question_list_multiple;
                        combine_list = combine_list.map(arr => {
                            return {
                                id: arr['id'],
                                echelons: arr['echelons'],
                                name: arr['name'],
                                chapters: arr['chapters'],
                                questions: {}
                            }
                        })
                        for (var i = 0; i < combine_list.length; i++) {
                            var push_question_data = []
                            var push_relative_data = []
                            for (var j = 0; j < combine_list[i].chapters.length; j++) {
                                for (var k = 0; k < combine_list[i].chapters[j].questions.length; k++) {
                                    if (push_question_data.findIndex(x => x.id == combine_list[i].chapters[j].questions[k].id) == -1) {
                                        push_question_data.push(combine_list[i].chapters[j].questions[k])
                                    }
                                }
                                for (var k = 0; k < combine_list[i].chapters[j].relatives.length; k++) {
                                    if (push_relative_data.findIndex(x => x.id == combine_list[i].chapters[j].relatives[k].id) == -1) {
                                        push_relative_data.push(combine_list[i].chapters[j].relatives[k])
                                    }
                                }
                            }
                            combine_list[i].questions = push_question_data
                            combine_list[i].relatives = push_relative_data
                        }
                        this.combine_question_list = combine_list

                    });
                },
                relative_text_list() {
                    var id = this.question_list_data[this.subject_index].chapters[this.chapter_index].id
                    Vue.axios.get('/admin/question/relative_text_list/' + id).then(response => {
                        this.original_relative_text = response.data.relative_text_list;
                    });
                },
                get_subject() {
                    var echelon_id = this.dialogItem.echelon_id
                    if (this.dialogItem.echelon_id == 8) {
                        echelon_id = 7
                    }
                    var subject = Object.assign([], this.original_subject);
                    let filter_subject = subject.filter(x => {
                        return x.echelon_id == echelon_id
                    })
                    this.subject = filter_subject.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                get_chapter() {
                    var chapter = Object.assign([], this.original_chapter);
                    let filter_chapter = chapter.filter(x => {
                        return x.subject_id == this.dialogItem.subject_id
                    })
                    this.chapter = filter_chapter.map(arr => {
                        return {
                            value: arr['id'],
                            text: arr['name']
                        }
                    })
                },
                get_chapter_topic() {
                    if (this.dialogItem.chapters.length > 0) {

                        var chapter_topic = Object.assign([], this.original_chapter_topic);
                        var push_data = []
                        for (var i = 0; i < this.dialogItem.chapters.length; i++) {
                            let filter_chapter_topic = chapter_topic.filter(x => {
                                return x.chapter_id == this.dialogItem.chapters[i].value
                            })
                            var x = filter_chapter_topic.map(arr => {
                                return {
                                    topic_no: arr['topic_no'],
                                    value: arr['id'],
                                    text: arr['name'],
                                    val: 0
                                }
                            })
                            for (var j = 0; j < x.length; j++) {
                                push_data.push(x[j])
                            }
                        }
                        this.chapter_topic = push_data
                    }

                    this.get_relative_text()
                },
                get_relative_text() {
                    var relative_text = Object.assign([], this.original_relative_text);
                    if (this.dialogItem.chapters.length > 0) {
                        let filter_relative_text = relative_text.filter(x => {
                            return x.chapter_id == this.dialogItem.chapters[0].value
                        })
                        this.relative_text = filter_relative_text.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['relative_text'].substring(0, 30)
                            }
                        })
                    }
                },
                refine_editor_text() {
                    if (this.dialogItem.question_length == "") {
                        this.dialogItem.question_length = null;
                        return this.valid_1 = false
                    }
                    this.organize_editor_text()
                    var question_text = []
                    var answer_text = []
                    var detail = []
                    var short_answer_var = []
                    var relative_text = []
                    var all_tag = []
                    var all_chapter = []
                    var question_length = this.dialogItem.question_length

                    // getting relative text
                    $('.question_editor p.relative').each(function() {
                            // var qus_index = $(this).text().substring(0,1)
                            var total_elem = $(this).nextUntil("p.relative").add($(this)).wrapAll('<div class="relative-div"></div>')
                            var total_qus = $(this).parent('.relative-div').children('.question').length
                            $(this).unwrap()
                            var x = '###'
                            var text = $(this).html().replace(x, "");
                            var a = {
                                'relative_text': text,
                                'relative_qus_length': total_qus
                            }
                            relative_text.push(a)
                        })
                        // getting short answer, question text, tag and editor detial            
                    $('.question_editor p.question').each(function(ind) {
                            // wrap full one question element
                            var total_elem = $(this).nextUntil("p.question, p.relative").add($(this)).wrapAll('<div class="qus-div"></div>')
                                // get answer for mcq
                            var total_length = $(this).parent(".qus-div").children().length
                            var ans = $(this).parent(".qus-div").find(".qus-child:contains('#')").text().substring(1, 2)
                            var ans_for_detail = 0
                            if (ans.length > 0 && ans[0] != "") {
                                short_answer_var.push({
                                    index: ind,
                                    text: ans
                                });
                                ans_for_detail = 1
                            }
                            // get answer with closet #
                            if (!ans && $(this).is(':contains("#")')) {
                                var ans1 = $(this).text().substring($(this).text().indexOf('#') + 1);
                                short_answer_var.push({
                                    index: ind,
                                    text: ans1
                                })
                                ans_for_detail = 1
                            }
                            if (!ans && !ans1) {
                                var ans2 = $(this).parent(".qus-div").find(".answer").html()
                                if (ans2) {
                                    short_answer_var.push({
                                        index: ind,
                                        text: ans2.replace('##', '')
                                    });
                                    ans_for_detail = 1
                                }
                            }
                            // get tag
                            var tags = $(this).parent(".qus-div").text().split('[')
                            var tag = []
                            for (var i = 1; i < tags.length; i++) {
                                tag = tags[i].split(']')[0].split(',')
                            }
                            all_tag.push(tag)
                                // get chapter
                            var chapters = $(this).text().split('{')
                            var chapter = []
                            for (var i = 1; i < chapters.length; i++) {
                                chapter = chapters[i].split('}')[0].split(',')
                            }
                            all_chapter.push(chapter)

                            // get element of per question
                            var elem = $(this).parent(".qus-div").clone().find('.answer').remove().end().html().replace('***', "").replace('#', "").replace(/\s*{.*?}\s*/g, '').replace(/\s*\[.*?\]\s*/g, '')
                                // question lenght
                            var qus = $(this).parent(".qus-div").find('.qus-child').length
                            var ans_length = 0
                            if (ans2) {
                                ans_length = $(this).parent(".qus-div").find('.answer').length
                            }

                            $(this).unwrap();
                            // save data
                            question_text.push(elem)
                                // question detail of editor
                            var a = {
                                'stem': total_elem.length - qus - ans_length,
                                'qus': qus,
                                'tag': tag.length,
                                'ans': ans_for_detail
                            }
                            detail.push(a)
                        })
                        // getting deatil answer
                    $('.answer_editor p.question').each(function() {
                            // wrap full one question element
                            var total_elem = $(this).nextUntil("p.question").add($(this)).wrapAll('<div class="qus-div"></div>')
                                // get element of one question
                            var elem = $(this).parent(".qus-div").html().replace('***', "").replace('#', "");
                            $(this).unwrap();
                            // save data
                            answer_text.push(elem)
                        })
                        // get image location
                    this.qus_location = ""
                    this.ans_location = ""
                    this.relative_location = ""
                    var q_src = $('.question_editor img').not('.data_image').attr('src')
                    if (q_src) {
                        q_src = q_src.split('///').pop()
                        var filename = q_src.split('/').pop()
                        this.qus_location = q_src.replace('/' + filename, '')
                    }

                    var a_src = $('.answer_editor img').not('.data_image').attr('src')
                    if (a_src) {
                        a_src = a_src.split('///').pop()
                        var filename = a_src.split('/').pop()
                        this.ans_location = a_src.replace('/' + filename, '')
                    }

                    var r_src = $('.relative_text_editor img').not('.data_image').attr('src')
                    if (r_src) {
                        r_src = r_src.split('///').pop()
                        var filename = r_src.split('/').pop()
                        this.relative_location = r_src.replace('/' + filename, '')
                    }

                    $('.question').prev().addClass('last-p') //add class for bottom border
                    this.editor_detail = detail //editor detail shows
                        // refine and reproduce short answer
                    var texts = [];
                    if (short_answer_var.length > 0) {
                        texts = short_answer_var
                    }
                    if (short_answer_var.length == 0 && $('.short_answer_div').html().length > 0) {
                        var a = $('.short_answer_div').clone().children().remove().end().html()
                        var ind_plus = 0
                        if (a.trim().length > 0) {
                            texts.push({
                                index: 0,
                                text: '<div>' + a + '</div>'
                            });
                            ind_plus = 1
                        }
                        $('.short_answer_div div').each(function(ind) {
                            texts.push({
                                index: ind + ind_plus,
                                text: $(this)[0].outerHTML
                            })
                        })

                    }
                    if (question_text.length == 0) {
                        this.short_answer_text = "";
                        $('.short_answer_div').html("")
                    }
                    // storing data
                    this.dialogItem.question_text = question_text
                    this.dialogItem.short_answer = texts
                    this.dialogItem.detail_answer = answer_text
                    this.dialogItem.relative_text = relative_text
                    for (var i = 0; i < all_tag.length; i++) {
                        for (var j = 0; j < this.dialogItem.question_tag.length; j++) {
                            all_tag[i].push(this.dialogItem.question_tag[j])
                        }
                    }
                    for (var i = 0; i < all_chapter.length; i++) {
                        for (var j = 0; j < this.dialogItem.chapters.length; j++) {
                            all_chapter[i].push(this.dialogItem.chapters[j].chapter_no)
                        }
                    }
                    this.all_chapter = all_chapter
                    this.question_tag = all_tag
                        // check if any question has no desire question length
                    setTimeout(function() {
                        var index = this.editor_detail.findIndex(x => x.qus != this.dialogItem.question_length)
                        if (index > -1) {
                            alert('Question no ' + (index + 1) + ' do not have ' + this.dialogItem.question_length + ' questions')
                        }
                    }.bind(this), 500)
                },
                // formating text of question editor
                organize_editor_text() {
                    var p = $('.note-editable');
                    p.contents()
                        .filter(function() {
                            return this.nodeType == 3;
                        }) // Select all textnodes
                        .wrap('<p>') // Place them inside paragraph elements
                    $('br', p).remove();
                    $('.note-editable p:empty').remove();
                    $('.note-editable style, .short_answer_div style').remove()
                    $('o\\:p').remove()
                        // symbolise
                    $('.note-editable *, .short_answer_div *').each(function() {
                            var a = $(this).css('font-family')
                            if (a == 'Symbol' || a == 'Math5') {
                                $(this).addClass('symbol')
                            }
                        })
                        // remove style attribute
                    $('.note-editable *, .short_answer_div *').removeAttr("style")
                        // unwrap unblock element
                    $('.note-editable *:not(p,div,ul,li,sup,sub,table,tbody,tr,td,img,.symbol,.question,.qus-child)').contents().unwrap();
                    // unwrap unblock element of short_answer
                    $('.short_answer_div *:not(p,div,sup,sub,img,.symbol)').contents().unwrap();
                    // replace element into p element
                    $('.note-editable *:not(table,tbody,tr,td,sup,sub,img,.symbol,.question,.qus-child)').wrap('<p></p>').contents().unwrap()

                    // unwrap element don't have text
                    $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child,sup,sub)').each(function() {
                            var a = $(this).clone().children().remove().end().text()
                            if (a == '') {
                                $(this).contents().unwrap()
                            }
                        })
                        // unwrap element don't have text of short_answer
                    $('.short_answer_div *:not(img,.symbol)').each(function() {
                            var a = $(this).clone().children().remove().end().text()
                            if (a == '') {
                                $(this).contents().unwrap()
                            }
                        })
                        // remove empty element
                    $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child)').each(function() {
                        var $this = $(this);
                        if ($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                            $this.remove();
                    });
                    // remove empty element of short answer
                    $('.short_answer_div *:not(img,.symbol)').each(function() {
                        var $this = $(this);
                        if ($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                            $this.remove();
                    });
                    // managing table
                    $('.note-editable table').wrap('<div></div>')
                    $('.note-editable table col').remove()
                        // remove extra space
                    $(".question_editor .note-editable").html(function(i, html) {
                        return html.replace(/&nbsp;/g, ' ');
                    });
                    // remove comments
                    $('.note-editable *').contents().each(function() {
                        if (this.nodeType === Node.COMMENT_NODE) {
                            $(this).remove();
                        }
                    });
                    // replace or change question steam starting digit or char
                    var notation = this.question_notation
                    var notation_child = this.question_child_notation
                    var question_begin_text = this.question_begin_text
                    var question_end_text = this.question_end_text
                    var question_child_end_text = this.question_child_end_text
                    var question_child_begin_text = this.question_child_begin_text
                    $(".question_editor .note-editable p").not('.qus-child, .relative, .answer').html(function(_, html) {
                        $(this).html($(this).html().trim())
                        if (notation == 'char') {
                            var reg_1 = new RegExp("([A-Ha-hJ-Uj-uWYZwyz][A-Ha-hJ-Uj-uWYZwyz])(" + "\\" + question_end_text + ")", 'g');
                            var reg_2 = new RegExp("(ক|খ|গ|ঘ|ঙ|চ|ছ|জ|ঝ|ঞ|ট|ঠ|ড|ঢ|ণ|ত|থ|দ|ধ|ন|প|ফ|ব|ভ|ম|[A-Za-z])(" + "\\" + question_end_text + ")", 'g');
                            var a = $(this).text().trim().substring(0, 2).match(reg_1)
                            if (!a) {
                                a = $(this).text().trim().substring(0, 2).match(reg_2)
                            }
                        }
                        if (notation == 'roman') {
                            var reg_1 = new RegExp("(IX|IV|V?I{1,3}|L?X{1,3})|(ix|iv|v?i{1,3}|l?x{1,3})(" + "\\" + question_end_text + ")", 'g');
                            var a = $(this).text().trim().substring(0, 3).match(reg_1)
                        }
                        if (notation == 'digit') {
                            var reg_1 = new RegExp("([০-৯]{1,3}|[0-9]{1,3})(" + "\\" + question_end_text + ")", 'g');
                            var a = $(this).text().trim().substring(0, 3).match(reg_1)
                        }
                        if (a) {
                            return html.replace(a[0], "***").replace(question_begin_text, '').replace(/\s\s+/g, ' ');
                        }
                    });
                    $('.note-editable p:contains("***")').each(function() {
                        $(this).addClass('question').removeClass('qus-child').removeClass('relative')
                    });
                    $('.question_editor .note-editable p:contains("###")').each(function() {
                        $(this).addClass('relative').removeClass('qus-child').removeClass('answer')
                    });
                    $('.question_editor .note-editable p:contains("##")').each(function() {
                        $(this).addClass('answer').removeClass('qus-child').removeClass('relative')
                    });
                    // replace or change question child char
                    $(".question_editor .note-editable p").not('.question,.relative, .answer').html(function(_, html) {
                        var regex_match = []
                        if (notation_child == 'roman') {
                            var reg_1 = new RegExp("(IX|IV|V?I{1,3}|L?X{1,3})|(ix|iv|v?i{1,3}|l?x{1,3})(" + "\\" + question_child_end_text + ")", 'g');
                            regex_match = $(this).text().trim().match(reg_1)
                        }
                        if (notation_child == 'char') {
                            var reg_1 = new RegExp("([A-Ha-hJ-Uj-uWYZwyz]{1,3})(" + "\\" + question_child_end_text + ")", 'g');
                            regex_match = $(this).text().match(reg_1)
                        }
                        if (notation_child == 'unicode') {
                            var reg_1 = new RegExp("(ক|খ|গ|ঘ|ঙ|চ|ছ|জ|ঝ|ঞ)(" + "\\" + question_child_end_text + ")", 'g');
                            regex_match = $(this).text().match(reg_1)
                        }
                        if (regex_match) {
                            var hash = ""
                                // if(regex_match.length==1 && $(this).is(':contains("#")')){hash = "#"} 
                            var text = $(this).html()
                            var text_replace
                            for (var i = 0; i < regex_match.length; i++) {
                                text_replace = text.replace(question_child_begin_text + regex_match[i], '*')
                                text = text_replace
                            }

                            if (text_replace) {
                                var splited_text = text_replace.split('*')
                            }
                            if (notation_child == 'char' || notation_child == 'roman') {
                                var char_list = ["a) ", "b) ", "c) ", "d) "]
                            }
                            if (notation_child == 'unicode') {
                                var char_list = ["ক) ", "খ) ", "গ) ", "ঘ) "]
                            }
                            var refine_text = []
                            var new_text = ""
                            var hash_index = -1
                            for (var i = 0; i < splited_text.length; i++) {
                                if (splited_text[i].trim().length > 0 && splited_text[i].trim().substring(0, 1) != '#') {
                                    refine_text.push(splited_text[i].trim())
                                }
                                if (splited_text[i].trim().substring(0, 1) == '#') {
                                    hash_index = i
                                }
                            }
                            for (var i = 0; i < refine_text.length; i++) {
                                if (refine_text[i].indexOf('#') > -1) {
                                    hash_index = i + 1
                                }
                                if (i == hash_index) {
                                    hash = '#'
                                } else {
                                    hash = ""
                                }
                                new_text = new_text + '<p class="qus-child">' + hash + char_list[i] + refine_text[i].replace('#', '') + '</p>'
                            }
                            $(this).html(new_text)
                        }
                    });

                    // unwrap element that has parent p tag
                    $('.note-editable > p *').not('.symbol, img, sub, sup').each(function() {
                        if ($(this).parent().is('p')) {
                            $(this).unwrap()
                        }
                    });
                    // replace question child stating character
                    $('.question_editor .note-editable p.question').each(function() {
                        var total_elem = $(this).nextUntil("p.question, p.relative").add($(this)).wrapAll('<div class="qus-div"></div>')
                        $(this).parent('.qus-div').children('.qus-child').html(function(index, html) {
                            var regex_match = []
                            if (notation_child == 'roman' || notation_child == 'char') {
                                var char_list = ["a)", "b)", "c)", "d)"]
                                var reg_1 = new RegExp("([a-d])(\\))", 'g');
                                regex_match = $(this).text().match(reg_1)
                            }
                            if (notation_child == 'unicode') {
                                var char_list = ["ক) ", "খ) ", "গ) ", "ঘ) "]
                                var reg_1 = new RegExp("(ক|খ|গ|ঘ)(\\))", 'g');
                                regex_match = $(this).text().match(reg_1)
                            }
                            if (regex_match) {
                                return html.replace(regex_match[0], char_list[index])
                            }
                        })
                        $(this).unwrap()
                    });

                },
                image_upload(type) {
                    var files = $('input[name=insert_image]')[0].files
                    if (type == 'question') {
                        var elem = $('.question_editor .note-editable')
                    }
                    if (type == 'answer') {
                        var elem = $('.answer_editor .note-editable')
                    }
                    for (var i = 0; i < files.length; i++) {
                        elem.find('img').each(function(index) {
                            var x = $(this).attr('src').split('/').pop()
                            $(this).attr('id', index)
                                // return console.log(i)
                            if (files[i].name == x) {
                                var reader = new FileReader();
                                reader.onload = function() {
                                    var dataURL = reader.result;
                                    var output = document.getElementById(index);
                                    output.src = dataURL;
                                };
                                reader.readAsDataURL(files[i]);
                            }
                        })
                    }
                    $('input[name=insert_image]').val("")
                },
                save() {
                    var x = $('.question_editor .note-editable').html()
                    this.dialogItem.question_text = x
                    var y = $('.relative_text_editor .note-editable').html()
                    this.dialogItem.relative_textarea = y
                    var z = $('.answer_editor .note-editable').html()
                    this.dialogItem.detail_answer = z
                    if (!this.question_editor) {
                        return alert('Editor is empty')
                    }
                    if (this.dialogItem.question_text.length == 0) {
                        return alert('No Question Found')
                    }
                    if (this.$refs.form.validate()) {
                        this.loading = true
                            // resolve topic
                        this.dialogItem.topics = []
                        var topics = this.chapter_topic
                        let filter_topic = topics.filter(x => {
                            return x.val == true
                        })
                        this.dialogItem.topics = topics.map(arr => {
                            return arr['value']
                        })
                        setTimeout(function() {
                            Vue.axios.post('/admin/question/question_edit/' + this.dialogItem.id, this.dialogItem).then(response => {
                                // $('.user_nav').addClass('successful')
                                this.loading = false
                                this.question_list()
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                // $('.user_nav').addClass('denied')
                                this.loading = false
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }.bind(this), 500)
                    }
                },
                goto_step2(subject_index, chapter_index) {
                    this.subject_index = subject_index
                    this.chapter_index = chapter_index
                    this.relative_text_list()
                    if (chapter_index != 'combine') {
                        var question_list = Object.assign([], this.question_list_data);
                        var data = question_list[subject_index].chapters[chapter_index].questions.sort(function(a, b) {
                            return a.question_type - b.question_type;
                        });
                    } else {
                        var question_list = Object.assign([], this.combine_question_list);
                        var data = question_list[subject_index].questions
                    }
                    var question_type = [];
                    for (var i = 0; i < data.length; i++) {
                        if (question_type.length == 0 || question_type.findIndex(x => x.type == data[i].question_type) == -1) {
                            var x = {
                                'type': data[i].question_type,
                                'count': 1
                            }
                            question_type.push(x)
                        }
                    }
                    for (var i = 0; i < question_type.length; i++) {
                        var x = data.filter(x => x.question_type == question_type[i].type)
                        question_type[i].count = x.length
                    }
                    this.question_type_header = question_type
                    $('.step_2_div').hide()
                    this.search_1 = ""
                    this.search_2 = ""
                    this.search_question_type = ""
                    this.step = 2
                    $('.step_2_div').fadeIn('slow')
                    this.chapter_list(this.question_list_data[subject_index].echelon_id)
                    this.chapter_topic_list(this.question_list_data[subject_index].echelon_id)
                },
                goto_step3(question, question_index) {
                    this.question_index = question_index
                    this.question_editor = question.question_text
                    if (this.chapter_index == 'combine') {
                        this.dialogItem.id = question.id
                        this.dialogItem.echelon_id = this.combine_question_list[this.subject_index].echelons.id
                        this.dialogItem.subject_id = this.combine_question_list[this.subject_index].id
                        this.dialogItem.question_type = question.question_type
                        this.dialogItem.question_mark = question.question_mark
                        this.dialogItem.question_mark_detail = question.question_mark_detail
                        this.dialogItem.board_question_no = question.board_question_no
                        this.dialogItem.question_status = question.question_status
                        this.dialogItem.short_answer = question.short_answer
                        this.dialogItem.question_length = question.question_length
                        this.dialogItem.question_title = question.question_title
                        var chapters = []
                        this.dialogItem.chapters = question.chapters.map(x => {
                            return {
                                value: x.id,
                                text: x.name
                            }
                        })
                        this.dialogItem.relative_textarea = ""
                        this.dialogItem.relative_text_id = question.question_relative_text_id
                        if (question.question_relative_text_id) {
                            this.relative_text_editor = question.relatives.relative_text
                            this.dialogItem.relative_textarea = this.relative_text_editor
                        }
                        this.answer_editor = question.detail_answer
                        this.dialogItem.question_tag = []
                        if (question.question_tag) {
                            this.dialogItem.question_tag = question.question_tag.split(',')
                        }
                        this.get_chapter()
                        this.get_chapter_topic()
                        if (question.topics.length > 0) {
                            for (var i = 0; i < this.chapter_topic.length; i++) {
                                if (question.topics.findIndex(x => x.id == this.chapter_topic[i].value) > -1) {
                                    this.chapter_topic[i].val = 1
                                }

                            }
                        }
                        this.question_index = this.combine_question_list[this.subject_index].questions.findIndex(x => x.id == this.dialogItem.id)
                    } else {
                        this.dialogItem.id = question.id
                        this.dialogItem.echelon_id = this.question_list_data[this.subject_index].echelons.id
                        this.dialogItem.subject_id = this.question_list_data[this.subject_index].id
                        this.dialogItem.question_type = question.question_type
                        this.dialogItem.question_mark = question.question_mark
                        this.dialogItem.question_mark_detail = question.question_mark_detail
                        this.dialogItem.board_question_no = question.board_question_no
                        this.dialogItem.question_status = question.question_status
                        this.dialogItem.short_answer = question.short_answer
                        this.dialogItem.question_length = question.question_length
                        this.dialogItem.question_title = question.question_title
                        this.dialogItem.chapters = question.chapters.map(x => {
                            return {
                                value: x.id,
                                text: x.name
                            }
                        })
                        this.dialogItem.relative_textarea = ""
                        this.dialogItem.relative_text_id = question.question_relative_text_id
                        if (question.question_relative_text_id) {
                            this.relative_text_editor = question.relatives.relative_text
                            this.dialogItem.relative_textarea = this.relative_text_editor
                        }
                        this.answer_editor = question.detail_answer
                        this.dialogItem.question_tag = []
                        if (question.question_tag) {
                            this.dialogItem.question_tag = question.question_tag.split(',')
                        }
                        this.get_chapter()
                        this.get_chapter_topic()
                        if (question.topics.length > 0) {
                            for (var i = 0; i < this.chapter_topic.length; i++) {
                                if (question.topics.findIndex(x => x.id == this.chapter_topic[i].value) > -1) {
                                    this.chapter_topic[i].val = 1
                                }

                            }
                        }
                        this.question_index = this.question_list_data[this.subject_index].chapters[this.chapter_index].questions.findIndex(x => x.id == this.dialogItem.id)
                    } //end of else
                    $('.step_3_div').hide()
                    this.search_1 = ""
                    this.search_2 = ""
                    this.search_question_type = ""
                    this.step = 3
                    this.search_id = ""
                    setTimeout(function() {
                        $('.step_3_div').fadeIn('slow')
                    }.bind(this), 250)
                },
                go_back() {
                    this.search_1 = ""
                    this.search_2 = ""
                    this.search_question_type = ""
                    if (this.step == 2) {
                        this.step = 1
                    }
                    if (this.step == 3) {
                        this.goto_step2(this.subject_index, this.chapter_index)
                    }
                    var step = this.step
                    $('.step_' + step + '_div').hide()
                    $('.step_' + step + '_div').fadeIn('slow')
                },
                goto_question_id() {
                    var id = this.search_id
                    var item = this.original_question_list_data;
                    var has_questions = 0
                    var questions = ""
                    for (var i = 0; i < item.length; i++) {
                        for (var j = 0; j < item[i].chapters.length; j++) {
                            for (var k = 0; k < item[i].chapters[j].questions.length; k++) {
                                if (item[i].chapters[j].questions[k].id == id) {
                                    this.subject_index = i
                                    this.chapter_index = j
                                    this.question_index = k
                                    has_questions = 1
                                    questions = item[i].chapters[j].questions[k]
                                    break
                                }
                            }
                            if (has_questions == 1) {
                                break
                            }
                        }
                        if (has_questions == 1) {
                            break
                        }
                    }
                    if (has_questions == 0) {
                        item = this.combine_question_list
                        for (var i = 0; i < item.length; i++) {
                            for (var j = 0; j < item[i].questions.length; j++) {
                                if (item[i].questions[j].id == id) {
                                    this.subject_index = i
                                    this.chapter_index = 'combine'
                                    this.question_index = j
                                    has_questions = 1
                                    questions = item[i].questions[j]
                                    break
                                }
                            }
                            if (has_questions == 1) {
                                break
                            }
                        }
                    }
                    if (has_questions == 0) {
                        return alert("Not Found Any Question!!!")
                    }
                    this.step = 4
                    setTimeout(function() {
                        this.goto_step3(questions, this.question_index)
                    }.bind(this), 100)
                },
                prev() {
                    if (this.chapter_index == 'combine') {
                        if (this.question_index == 0) {
                            return alert("No Previous Question!!")
                        }
                        this.step = 4
                        setTimeout(function() {
                            this.goto_step3(this.combine_question_list[this.subject_index].questions[this.question_index - 1], this.question_index - 1)
                        }.bind(this), 100)
                    } else {
                        if (this.question_index == 0) {
                            return alert("No Previous Question!!")
                        }
                        this.step = 4
                        setTimeout(function() {
                            this.goto_step3(this.question_list_data[this.subject_index].chapters[this.chapter_index].questions[this.question_index - 1], this.question_index - 1)
                        }.bind(this), 100)
                    }
                },
                next() {
                    if (this.chapter_index == 'combine') {
                        if (this.question_index == this.combine_question_list[this.subject_index].questions.length - 1) {
                            return alert("No Next Question!!")
                        }
                        this.step = 4
                        setTimeout(function() {
                            this.goto_step3(this.combine_question_list[this.subject_index].questions[this.question_index + 1], this.question_index + 1)
                        }.bind(this), 100)
                    } else {
                        if (this.question_index == this.question_list_data[this.subject_index].chapters[this.chapter_index].questions.length - 1) {
                            return alert("No Next Question!!")
                        }
                        this.step = 4
                        setTimeout(function() {
                            this.goto_step3(this.question_list_data[this.subject_index].chapters[this.chapter_index].questions[this.question_index + 1], this.question_index + 1)
                        }.bind(this), 100)
                    }
                },
                delete_question(id, index, relative) {
                    var confirmation = confirm("Do you want to delete?")
                    if (!confirmation) {
                        return false
                    }
                    Vue.axios.post('/admin/question/question_delete/' + id).then(response => {
                        if (this.chapter_index == 'combine' && relative) {
                            this.combine_question_list[this.subject_index].relatives.questions.splice(index, 1)
                        }
                        if (this.chapter_index == 'combine' && !relative) {
                            this.combine_question_list[this.subject_index].questions.splice(index, 1)
                        }
                        if (this.chapter_index != 'combine' && relative) {
                            this.combine_question_list[this.subject_index].relatives.questions.splice(index, 1)
                        }
                        if (this.chapter_index != 'combine' && !relative) {
                            this.question_list_data[this.subject_index].chapters[this.chapter_index].questions.splice(index, 1)
                        }
                        $('.user_nav').attr('style', 'background-color: #4caf50!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    }, error => {
                        this.loading = false
                        $('.user_nav').attr('style', 'background-color: #f44336!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    });
                },
                delete_question_by_chapter(type, chapter_id, subject_id, subject_index, chapter_index) {
                    var confirmation = confirm("Do you want to delete?")
                    if (!confirmation) {
                        return false
                    }
                    var form_data = {}
                    form_data.type = type
                    form_data.chapter_id = chapter_id
                    form_data.subject_id = subject_id
                    Vue.axios.post('/admin/question/question_delete_by_chapter', form_data).then(response => {
                        if (chapter_index == 'combine') {
                            this.combine_question_list[subject_index].questions = []
                            if (this.question_list_data[subject_index].chapters.length == 0) {
                                this.question_list_data.splice(subject_index, 1)
                            }
                        } else {
                            this.question_list_data[subject_index].chapters.splice(chapter_index, 1)
                            if (this.question_list_data[subject_index].chapters.length == 0 && this.combine_question_list[subject_index].questions.length == 0) {
                                this.question_list_data.splice(subject_index, 1)
                            }
                        }
                        $('.user_nav').attr('style', 'background-color: #4caf50!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    }, error => {
                        $('.user_nav').attr('style', 'background-color: #f44336!important')
                        setTimeout(function() {
                            $('.user_nav').attr('style', 'background-color: #ffc107!important')
                        }.bind(this), 3000)
                    });
                },
        } //end method
    } 
</script>