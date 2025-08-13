
<template>
    <div class="question_print">
        <v-container fluid>
            <!-- @page_headline -->
            <div style="z-index: 2">
                <v-layout style="width: 900px">
                    <v-text-field v-model="exam_data.page_size" label="Page Size" class="m-r-15"></v-text-field>
                    <v-text-field v-model="exam_data.page_height" label="Page Height" class="m-r-15"></v-text-field>
                    <v-text-field v-model="exam_data.line_height" label="Line Space" class="m-r-15"></v-text-field>
                    <v-text-field v-model="exam_data.font_size" label="Font Size" class="m-r-15"></v-text-field>
                    <v-select v-model="exam_data.layout" :items="layout" class="m-r-15" label="Layout" @change="change_layout"></v-select>
                    <v-text-field v-if="exam_data.layout == 'layout_column'" v-model="first_page_size" label="First Page Size" class="m-r-15"></v-text-field>
                    <v-text-field v-if="exam_data.layout == 'layout_column'" v-model="first_page_height" label="First Page Height" class="m-r-15"></v-text-field>
                    <v-select v-model="input_print_set" :items="current_question_sets" @change="change_set" class="m-r-15" label="Set"></v-select>
                    <v-select v-model="print_item" :items="print_data" @change="set_print_item" label="Print Option" class="m-r-15"></v-select>
                </v-layout>
                <v-layout style="width: 900px">
                    <v-text-field v-model="exam_data.title" label="Title" class="m-r-15"></v-text-field>
                    <v-text-field v-model="exam_data.class_title" label="Class" class="m-r-15"></v-text-field>
                    <v-text-field v-model="batch_name" label="batch" class="m-r-15"></v-text-field>
                    <!-- <v-text-field v-model="dialogItem.full_marks" label="Full Marks" class="m-r-15"></v-text-field> -->
                    <v-btn small color="primary" @click="print">Print</v-btn>
                    <v-spacer></v-spacer>
                    <router-link :to="{ name: 'question_view', params: { id: $route.params.id }}">
                        <v-btn flat small color="primary" class="mr-2"> Re-Setup </v-btn>
                    </router-link>
                </v-layout>
            </div>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container class="print">
                            <!--==================== Append here from append section========== -->
                            <div id="print_content"></div>
                            <div style="clear: both; float: none"></div>

                            <!-- ======================Dom section for question ============================ -->
                            <!-- =========================================================================== -->

                            <!-- loop by set (use current set for print specific set) -->
                            <div :id="'set_' + set_i" class="question_print_view p-l-20" style="width: 492.4px" v-for="(sets, set_i) in dialogItem.question_data" :key="set_i">
                                <!-- =================================================================
                                Question Section
                                ======================================================================= -->
                                <div class="section pos-rel" v-if="print_item=='question'">
                                    <!-- exam type if bangle contain logo, exam code and set name -->
                                    <div class="exam_type fw-600 fs-20 m-b-5 text-center solaiman pos-rel" v-if="exam_data.language=='bangla'">
                                        <!-- {{exam_data.exam_types.bangla_text}} ({{exam_date | moment("MMM-YY") | digit_to_bangla}}) -->
                                        {{exam_data.title}}
                                        <!-- <span style="font-size:12px; position: absolute; right: 10px; top: 45px;">Q.Code: <span v-for="schedule in exam_data.schedules">{{schedule.batches.name}}-{{schedule.id}}</span></span> -->
                                        <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 0px; top: 50px;">
                                        <span class="pos-a" style="right: 10px; top: 45px; font-size: 16px">Set: {{question_sets[(set_i + current_set)]}}</span>
                                    </div>
                                    <!-- exam type if english contain logo, exam code and set name -->
                                    <div class="exam_type fw-600 fs-18 text-center solaiman m-b-5" v-else>{{exam_data.exam_types.name}} ({{exam_data.date | moment("MMM-YY")}})
                                        <span style="font-size:12px; position: absolute; right: 10px; top: 45px;">Code: {{exam_data.id}}</span>
                                        <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 0px; top: 50px;">
                                        <span class="pos-a" style="right: 10px; top: 65px; font-size: 16px">Set: {{question_sets[(set_i + current_set)]}}</span>
                                    </div>
                                    <!-- echelon name if bangla -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman m-b-5" v-if="exam_data.language=='bangla'">{{exam_data.class_title}} {{exam_data.subjects.bangla_text}}</div>
                                    <!-- echelon name if english -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman m-b-5" v-else>{{exam_data.class_title}} - {{exam_data.subjects.name}}</div>
                                    <!-- batch name -->
                                    <div class="batch fw-600 fs-11 text-center solaiman m-b-5">Exam Code: <span class="m-r-10" v-for="schedule in exam_data.schedules">{{schedule.id}} ({{schedule.batches.name}})</span></div>
                                    <!-- chapter name -->
                                    <div v-if="exam_data.exam_types.name != 'Final Model Test'" class="chapter fw-600 fs-13 text-center solaiman" style="max-width: 250px; margin: auto;"><span class="m-r-7">{{exam_data.chapter_text}}</span></div>
                                    <!-- full marks and time if bangla -->
                                    <div class="full_marks fw-500 fs-12 text-center solaiman" v-if="exam_data.language=='bangla'"><span>পূর্ণমাণ: {{exam_data.full_marks | digit_to_bangla}}; </span><span>সময়: {{exam_data.full_time | digit_to_bangla}} মিনিট</span></div>
                                    <!-- full marks and time if english -->
                                    <div class="full_marks fw-500 fs-12 text-center solaiman" v-else><span>Full Marks: {{exam_data.full_marks}}; </span><span>Time: {{exam_data.full_time}} minutes</span></div>
                                    <!-- headline answer instruction for all questions -->
                                    <div class="header_answer_instruction m-b-3 m-t-3 fs-15 text-center solaiman" v-if="exam_data.answer_instruction">[{{exam_data.answer_instruction}}]</div>
                                    <!-- loop by exam types [section are used to ignore when jquery count height] -->
                                    <div v-for="(type, t_i) in sets" class="type_section section">
                                        <!-- if type has any title -->
                                        <div class="m-t-5 type_title fs-16 fw-600 text-center solaiman m-b-7">{{type.title}}</div>
                                        <!-- if type has answer instruction [cq only] -->
                                        <div v-if="type.answer_instruction && (type.question_type == 'cq' || type.question_type == 'mcq')" class="type_answer_instruction m-b-3 m-t-1 fs-13 text-center solaiman">[{{type.answer_instruction}}]</div>
                                        <!-- relative text answer instruction -->
                                        <div class="type_answer_instruction m-b-0 m-t-6 fs-12 fw-600 solaiman" v-if="type.answer_instruction && type.question_type == 'relative'" style="text-align: left">{{type.answer_instruction}}</div>
                                        <p v-if="type.question_type=='relative'" v-html="type.relatives.relative_text"></p>
                                        <!-- loop by questions that are separated by relative type: type->question[]->question_item[] -->
                                        <div v-for="(question_rel, r_i) in type.question_data" class="section relative_type_section" :data-answerable="type.answerable">
                                            <!-- answer insturction for relative mcq questions -->
                                            <p :data-relative_id="question_rel.relative_id" class="relative_instruction" v-if="question_rel.relative_id && question_rel.question_type != 'relative'"></p>
                                            <!-- relative text for relative mcq questions -->
                                            <p v-if="question_rel.relative_id && type.question_type!='relative'" v-html="question_rel.question_item[0].relatives.relative_text"></p>
                                            <!-- relative type answer insturction or other type answer instruction -->
                                            <div v-if="question_rel.answer_instruction" class="fw-600 fs-12 relative_answer_instruction solaiman" :data-question_type="type.question_type">{{question_rel.answer_instruction}}</div>
                                            <!-- loop by question item, use function for separating set -->
                                            <div v-for="(question, q_i) in question_rel.question_item" class="pos-rel question_section section">
                                                <!-- question text use data attributes for applying question no by jquery -->
                                                <div :data-mark_detail="question.question_mark_detail" :data-mark="question.question_mark" :data-relative_type="question_rel.relative_type" :data-question_type="type.question_type" :id="question.id" class="question_text solaiman section" v-html="question.question_text"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- =================================================================
                                Detail answer Section
                                ======================================================================= -->
                                <div class="section" v-if="print_item=='detail_answer'">
                                    <!-- Answer Head if bangla contain answer, code, set -->
                                    <div class="exam_type fw-600 fs-20 text-center solaiman pos-rel" v-if="exam_data.language=='bangla'">
                                        <span style="display:block">{{exam_data.title}}</span>
                                        <span>উত্তরপত্র</span>
                                        <span style="font-size:12px; position: absolute; right: 10px; top: 20px;">Code: <span class="m-r-10" v-for="schedule in exam_data.schedules">{{schedule.id}} ({{schedule.batches.name}})</span></span>
                                        <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 0px; top: 30px;">
                                        <span class="pos-a" style="right: 10px; top: 42px; font-size: 16px; padding-right: 10px">Set: {{question_sets[(set_i + current_set)]}}</span>
                                    </div>
                                    <!-- Answer Head if english contain answer, code, set -->
                                    <div class="exam_type fw-600 fs-20 text-center solaiman" v-else>
                                        <div>{{exam_data.title}}</div>
                                        <div>Answer Sheet</div>
                                        <span style="font-size:12px; position: absolute; right: 0px; top: 20px;">Code: <span class="m-r-10" v-for="schedule in exam_data.schedules">{{schedule.id}} ({{schedule.batches.name}})</span></span>
                                        <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 35px; left: 0px; top: 30px;">
                                        <span class="pos-a" style="right: 10px; top: 42px; font-size: 16px; padding-right: 10px">Set: {{question_sets[(set_i + current_set)]}}</span>
                                    </div>

                                    <!-- echelon if bangla -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman" v-if="exam_data.language=='bangla'">{{exam_data.class_title}} {{exam_data.subjects.bangla_text}}</div>
                                    <!-- echelon if english -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman" v-else>{{exam_data.class_title}} {{exam_data.subjects.name}}</div>
                                    <!-- chapter -->
                                    <div class="chapter fw-600 fs-13 text-center solaiman m-b-10 answer_chapter"><span class="m-r-7" style="max-width: 250px; margin: auto;">{{exam_data.chapter_text}}</span></div>
                                    <!-- loop type->question[]->question_item (3 loops) -->
                                    <div v-for="(type, t_i) in sets" class="type_section section">
                                        <div v-for="(question_rel, r_i) in type.question_data" class="section">
                                            <div v-for="(question, q_i) in question_rel.question_item" class="pos-rel question_section section">
                                                <div :data-question_no="q_i+1" :data-question_type="type.question_type" :id="question.id" class="question_text solaiman section" v-html="question.detail_answer"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- =================================================================
                                Short Answer Section
                                ======================================================================= -->
                                <div class="section" v-if="print_item=='short_answer'">
                                    <!-- Answer Head if english contain answer, code, set -->
                                    <div class="exam_type fw-600 fs-20 text-center solaiman pos-rel" v-if="exam_data.language=='bangla'">উত্তরপত্র
                                        <span style="font-size:12px; position: absolute; right: 10px; top: 20px;">Code: {{exam_data.id}}</span>
                                        <img class="pos-a" src="/image/logo.png" style="display: block; margin: auto; height: 30px; left: 0px; top: 30px;">
                                        <span class="pos-a" style="right: 10px; top: 42px; font-size: 16px">Set: {{question_sets[(set_i + current_set)]}}</span>
                                    </div>
                                    <!-- Answer Head if english contain answer, code, set -->
                                    <div class="exam_type fw-600 fs-20 text-center solaiman" v-else>Answer Sheet</div>
                                    <!-- echelon if bangla -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman" v-if="exam_data.language=='bangla'">{{exam_data.class_title}} - {{exam_data.subjects.bangla_text}}</div>
                                    <!-- echelon if english -->
                                    <div class="echelon fw-600 fs-18 text-center solaiman" v-else>{{exam_data.class_title}} - {{exam_data.subjects.name}}</div>
                                    <!-- batch -->
                                    <div class="batch fw-600 fs-13 text-center solaiman"><span class="m-r-7">{{batch_name}}</span></div>
                                    <!-- chapter -->
                                    <div class="chapter fw-600 fs-13 text-center solaiman m-b-10 answer_chapter"><span class="m-r-7">{{exam_data.chapter_text}}</span></div>
                                    <!-- loop type->question[]->question_item (3 loops) -->
                                    <div v-for="(type, t_i) in sets" :class="{'type_section section':true, 'short_answer_mcq text-center': type.question_type == 'mcq'}">
                                        <div v-for="(question_rel, r_i) in type.question_data" class="section">
                                            <div v-for="(question, q_i) in question_rel.question_item" class="pos-rel question_section section">
                                                <div :data-question_no="q_i+1" :data-question_type="type.question_type" :id="question.id" class="question_text solaiman section" v-html="question.short_answer"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- =================================================================
                                MCQ Answer Section
                                ======================================================================= -->
                                <div class="mcq_answer_section" v-if="print_item=='mcq_answer'">
                                    <!-- loop by set -->
                                    <div v-for="(sets, set_i) in dialogItem.question_data" class="inline-block">
                                        <!-- head -->
                                        <div class="exam_type fw-600 fs-13 solaiman pos-rel p-l-15" style="text-align: left" v-if="exam_data.language=='bangla'">বহুনির্বাচনী উত্তরপত্র
                                            <span style="font-size:12px; position: absolute; right: 15px; top: 0px;">Code: {{exam_data.id}}</span>
                                            <span class="pos-a" style="right: 15px; top: 20px; font-size: 12px">Set: {{question_sets[set_i + current_set]}}</span>
                                        </div>
                                        <div class="echelon fw-600 fs-11 text-left solaiman p-l-15" style="text-align: left" v-if="exam_data.language=='bangla'">{{exam_data.title}} - {{exam_data.subjects.bangla_text}}</div>
                                        <div class="echelon fw-600 fs-12 text-center solaiman" style="width: 40%" v-else>{{exam_data.title}} - {{exam_data.subjects.name}}</div>
                                        <!-- answer image, dot section -->
                                        <div class="mcq_answer_section section pos-rel m-r-10">
                                            <img class="section" v-if="mcq_length<=20" src="/image/mcq_20.jpg" style="width: 2.25in">
                                            <img class="section" v-if="mcq_length>20 && mcq_length <=30" src="/image/mcq_30.jpg" style="width: 2.25in">
                                            <img class="section" v-if="mcq_length>30" src="/image/mcq_50.jpg" style="width: 4in">
                                            <span class="mcq_set pos-a mcq_circle" style="background: #000!important;"></span>
                                            <span class="pos-a mcq_circle mcq_black_circle" v-for="question in sets_question(set_i + current_set + 1)" :data-answer="question.mcq_answer"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--==================== Append here and break element for easy counting height========== -->
                            <div class="append_content"></div>
                        </v-container>
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
                    exam_data: {subjects: {echelons: {}}, exam_types: {}, page_height: 594, page_size: 7.25, line_height: 18, font_size: 11.5, title: '', class_title: '' },
                    chapter: [],
                    original_chapter: [],
                    question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}], layout: [{value: 'question', text: 'Question'}, {value: 'lecture', text: 'Lecture'}], language: [{value: 'bangla', text: 'Bangla'}, {value: 'english', text: 'English'}],
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
                    question_number: ['১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯', '১০', '১১', '১২', '১৩', '১৪', '১৫', '১৬', '১৭', '১৮', '১৯', '২০', '২১', '২২', '২৩', '২৪', '২৫', '২৬', '২৭', '২৮', '২৯', '৩০', '৩১', '৩২', '৩৩', '৩৪', '৩৫', '৩৬', '৩৭', '৩৮', '৩৯', '৪০', '৪১', '৪২', '৪৩', '৪৪', '৪৫', '৪৬', '৪৭', '৪৮', '৪৯', '৫০'],
                    chapter_name: '',
                    batch_name: '',
                    layout: [{'value': 'lecture', 'text': 'Portrait'}, {'value': 'question', 'text': 'Landscape'}, {'value': 'layout_column', 'text': 'Column Layout'}], 
                    print_data: [{'value': 'question', 'text': 'Question'}, {'value': 'detail_answer', 'text': 'Detail Answer'}, {'value': 'short_answer', 'text': 'Short Answer'}, {'value': 'mcq_answer', 'text': 'MCQ Answer'}],
                    print_item: 'question',
                    mcq_length: 0,
                    mcq_questions: [],
                    looping_set: 0,
                    looping_question: 0,
                    looping_type: 0,
                    question_no: [],
                    current_question_sets: [{
                        'value': 'all',
                        'text': 'All'
                    }],
                    input_print_set: 'all',
                    current_set: 0,
                    exam_date: '2012-12-12',
                    first_page_size: 9,
                    first_page_height: 600,
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

            filters: {
                day_in_bangla: function(value) {
                    if (!value) {
                        return ''
                    }
                    if (value == 'Sun') {
                        return value.replace('Sun', 'রবিবার')
                    }
                    if (value == 'Mon') {
                        return value.replace('Mon', 'সোমবার')
                    }
                    if (value == 'Tue') {
                        return value.replace('Tue', 'মঙ্গলবার')
                    }
                    if (value == 'Wed') {
                        return value.replace('Wed', 'বুধবার')
                    }
                    if (value == 'Thu') {
                        return value.replace('Thu', 'বৃহস্পতিবার')
                    }
                    if (value == 'Fri') {
                        return value.replace('Fri', 'শুক্রবার')
                    }
                    if (value == 'Sat') {
                        return value.replace('Sat', 'শনিবার')
                    }
                },
                digit_to_bangla: function(value) {
                    if (value == 2019) {
                        return '২০১৯'
                    }
                    if (value == 2020) {
                        return '২০২০'
                    }
                    var month = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']
                    var bangla_month = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
                    var x = new Date(value).getMonth()
                    value = value.toString()
                    value = value.toLowerCase().replace(month[x], bangla_month[x])

                    var numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                    var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
                    if (!value) {
                        return ''
                    }
                    if (!isNaN(value)) {
                        for (var i = 0; i < value.length; ++i) {
                            var index = numbers.findIndex(x => x == value[i])
                            value = value.replace(numbers[index], bangla_num[index])
                        }
                    }
                    return value;
                },
                strip_tags: function(value) {
                    let regex = /(<([^>]+)>)/ig;
                    return value.replace(regex, "").substring(0, 50) + '...';
                },
            },

            methods: {
                // @add_item_method
                // this.$route.params.id
                schedule_list() {
                        this.dialog = true
                        Vue.axios.get('/admin/question/question_printable_list/' + this.$route.params.id).then(response => {
                            this.exam_data = response.data.schedule_list;
                            this.exam_data.page_height = this.exam_data.page_height ? this.exam_data.page_height : 594 
                            this.exam_data.page_size = this.exam_data.page_size ? this.exam_data.page_size : 7.25
                            this.exam_data.line_height = this.exam_data.line_height ? this.exam_data.line_height : 18
                            this.exam_data.font_size = this.exam_data.font_size ? this.exam_data.font_size : 11.5
                            if (this.exam_data.language == 'bangla') {
                                this.echelon = this.exam_data.subjects.echelons.bangla_text
                            } else {
                                this.echelon = this.exam_data.subjects.echelons.name
                            }
                            var batch = []
                            for (var i = 0; i < this.exam_data.schedules.length; i++) {
                                batch.push(this.batch_name + this.exam_data.schedules[i].batches.name)
                                this.exam_date = this.exam_data.schedules[i].date
                            }
                            this.batch_name = batch.join(', ')
                            this.chapter_name = this.exam_data.chapters.map(x => {
                                return x.name
                            }).join(', ')
                            this.question_type = this.exam_data.exam_question_types.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['question_type'].toUpperCase()
                                }
                            })                            
                            this.relative_filter()
                            var mcq = this.exam_data.exam_question_types.filter(x => x.question_type == 'mcq')
                            if (mcq.length > 0) {
                                this.mcq_length = mcq[0].total
                            }
                            this.dialogItem.id = this.exam_data.id
                            this.dialogItem.topic = this.exam_data.topic
                            this.dialogItem.full_marks = this.exam_data.full_marks
                            this.dialogItem.full_time = this.exam_data.full_time
                            for (var i = 0; i < this.exam_data.sets; i++) {
                                this.current_question_sets.push({
                                    'value': i,
                                    'text': this.question_sets[i]
                                })
                            }
                            this.organise_question()
                            this.dialog = false
                                // return false
                        });
                    },
                    change_layout(){
                        if (this.exam_data.layout == 'layout_column') {
                            this.exam_data.page_size = 10.5
                            this.exam_data.page_height = 725
                        } else {
                            this.exam_data.page_size = 7.24
                            this.exam_data.page_height = 594
                        }
                    },
                    relative_filter() {
                        // var item = this.exam_data.exam_question_types
                        var item = []                        
                        if (this.exam_data.exam_question_types.length > 0) {
                            for (var i = 0; i < this.exam_data.sets; i++) {
                                var q = this.exam_data.exam_question_types.map(x => {
                                    return {
                                        id: x.id,
                                        question_type: x.question_type,
                                        total: x.total,
                                        answerable: x.answerable,
                                        questions: x.questions.filter(y => y.pivot.set == i + 1),
                                        question_data: [],
                                        chapters: x.chapters,
                                        title: x.title,
                                        answer_instruction: x.answer_instruction,
                                        seperation: x.seperation,
                                        relative_type_data: x.relative_type_data,
                                        relative_question_type: x.relative_question_type,
                                        relatives: x.relatives,
                                        sets: i + 1
                                    }
                                })
                                item.push(q)
                            }
                        }
                        if (this.input_print_set != 'all') {
                            item = item.slice(this.input_print_set, this.input_print_set + 1)
                        }
                        var question_type = ''
                        for (var set_i = 0; set_i < item.length; set_i++) {
                            for (var i = 0; i < item[set_i].length; i++) {
                                var index = item[set_i].findIndex(x => x.id == item[set_i][i].id)
                                for (var j = 0; j < item[set_i][i].questions.length; j++) {                                    
                                    var object = {
                                        relative_id: '',
                                        question_type: '',
                                        relative_type: null,
                                        answer_instruction: '',
                                        question_item: [],
                                        chapter: ''
                                    }

                                    var q_i = item[set_i][i].question_data.findIndex(x => item[set_i][i].questions[j].question_relative_text_id && x.relative_id == item[set_i][i].questions[j].question_relative_text_id && x.relative_type == item[set_i][i].questions[j].relative_question_type)
                                    if (q_i == -1) {
                                        q_i = item[set_i][i].question_data.findIndex(x => item[set_i][i].questions[j].question_type == 'partial' && x.chapter == item[set_i][i].chapters[0])
                                    }
                                    if (q_i == -1) {
                                        item[set_i][i].question_data.push(object)
                                        var last_index = item[set_i][i].question_data.length - 1
                                        item[set_i][i].question_data[last_index].relative_id = item[set_i][i].questions[j].question_relative_text_id
                                        item[set_i][i].question_data[last_index].question_type = item[set_i][i].questions[j].question_type
                                        item[set_i][i].question_data[last_index].chapter = item[set_i][i].chapters
                                        item[set_i][i].question_data[last_index].relative_type = item[set_i][i].questions[j].relative_question_type
                                        var type_data_index = item[set_i][i].relative_type_data ? item[set_i][i].relative_type_data.findIndex(x => x.relative_question_type == item[set_i][i].question_data[last_index].relative_type) : -1
                                        if (type_data_index > -1) {
                                            item[set_i][i].question_data[last_index].answer_instruction = item[set_i][i].relative_type_data[type_data_index].answer_instruction
                                        }
                                        if (item[set_i][i].question_type == 'partial' || item[set_i][i].question_type == 'general') {
                                            item[set_i][i].question_data[last_index].answer_instruction = item[set_i][i].answer_instruction
                                        }
                                        item[set_i][i].question_data[last_index].question_item.push(item[set_i][i].questions[j])
                                    } else {
                                        item[set_i][i].question_data[q_i].question_item.push(item[set_i][i].questions[j])
                                    }                                
                                }
                            } //end i for
                            
                        } //end set_i for
                        this.dialogItem.question_data = item
                        if (this.print_item == 'mcq_answer') {
                            this.set_print_item()
                        }
                    },
                    organise_question() {
                        if (this.print_item == 'mcq_answer') {
                            $('#print_content').attr('id', 'print_content_inactive')
                            $('.mcq_answer_section').attr('id', 'print_content')
                            return false
                        } else {
                            $('.mcq_answer_section').attr('id', 'print_content_inactive')
                            $('#print_content_inactive').attr('id', 'print_content')
                        }
                        var page_length = 0
                        var layout = this.exam_data.layout
                        $('.append_content .append').remove()
                        $('#print_content .page').remove()
                        $('#print_content .column_head').remove()
                        $('#print_content .question_content').remove()
                        var language = this.exam_data.language
                        var page_size = this.exam_data.page_size
                        var line_height = this.exam_data.line_height
                        var page_height = this.exam_data.page_height
                        var first_page_height = this.first_page_height
                        var first_page_size = this.first_page_size
                        var font_size = this.exam_data.font_size
                        var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
                        // add new_page class to exam_type
                        $('.question_print_view .exam_type').each(function(index) {
                            if (layout != 'layout_column') {
                                $(this).addClass('new_page') 
                            } else {
                                $(this).addClass('new_page_column')
                            }
                        })
                        if (layout == 'layout_column') {
                            $('.question').eq(0).addClass('new_page')
                        }
                        // append question id
                        $('.question_id').remove()
                        $('.question_text').each(function(index) {
                            var a = $(this).attr('data-relative_type')
                            var question_type = $(this).attr('data-question_type')
                            var m = $(this).attr('data-mark')
                            var m_d = $(this).attr('data-mark_detail')
                            $(this).find('.question').attr('data-relative_type', a).attr('data-mark', m).attr('data-question_type', question_type).attr('data-mark_detail', m_d)
                        })
                        // question no
                        var question_char = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o']
                        var question_char_bangla = ['ক', 'খ', 'গ', 'ঘ','ঙ', 'চ', 'ছ', 'জ', 'ঝ', 'ঞ']
                        var last_r_i = -1
                        $('.type_section').each(function(t_i) {
                                var a //relative answer insturction
                                var b //question no
                                var c //question lenght
                                var mark
                                var mark_detail
                                var qs_no
                                var mark_total
                                $(this).find('.relative_type_section').each(function(r_i) {
                                    var answerable = 0
                                    var answerable = $(this).attr('data-answerable')
                                    a = $(this).find('.relative_answer_instruction')
                                    b = a.attr('data-question_no')
                                    var qus_type = $(this).find('.question_text').attr('data-question_type')
                                    if (qus_type == 'partial') {
                                        qs_no = t_i
                                        last_r_i = last_r_i + 1
                                    } else {
                                        qs_no = r_i
                                        last_r_i = last_r_i + 1
                                    }
                                    if (qus_type == 'relative') {
                                        qs_no = last_r_i
                                    }
                                    if (a.length && !b) {
                                        a.attr('data-question_no', qs_no + 1);
                                    }
                                    // if question has no answer instruction but relative  question                                    
                                    var x = $(this).find('.question_section .question_text').attr('data-relative_type')
                                    if (!a.length && x) {
                                        $(this).find('.question').attr('data-question_no', qs_no + 1).addClass('fw-600');
                                    }
                                    if (a.length) {
                                        c = $(this).find('.question').length
                                        answerable = answerable ? answerable : c
                                        
                                        $(this).find('.question').each(function(q_i) {
                                            mark = $(this).attr('data-mark')
                                            mark_detail = $(this).attr('data-mark_detail')
                                            if (c == 1) {return}
                                            if (language == 'english') {
                                                $(this).attr('data-question_no', question_char[q_i].toUpperCase()).addClass('m-l-20')
                                            } else {
                                                $(this).attr('data-question_no', question_char_bangla[q_i]).addClass('m-l-20')
                                            }
                                        })
                                        mark_total = parseInt(answerable*mark)
                                        if (language == 'bangla') {
                                            var value = mark.toString()
                                            if (!isNaN(value)) {
                                                for (var i = 0; i < value.length; i++) {
                                                    value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                                }
                                            }
                                            mark = value
                                            var value = answerable.toString()
                                            if (!isNaN(value)) {
                                                for (var i = 0; i < value.length; i++) {
                                                    value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                                }
                                            }
                                            answerable = value
                                            var value = mark_total.toString()
                                            if (!isNaN(value)) {
                                                for (var i = 0; i < value.length; i++) {
                                                    value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                                }
                                            }
                                            mark_total = value
                                        }
                                        if (!mark_detail) {
                                            a.attr('data-mark_detail', mark + ' x ' + answerable + ' = ' + mark_total)
                                        }
                                        else {
                                            a.attr('data-mark_detail', mark_detail)
                                        }
                                        $(this).find('.qus-child').addClass('m-l-20')                                        
                                    }
                                })
                                $(this).find('.question').each(function(index) {
                                    var a = $(this).attr('data-question_type')
                                    if (a == 'partial' || a == 'general') {return false}
                                    if (!$(this).attr('data-relative_type')) {
                                        $(this).attr('data-question_no', index + 1)
                                    }
                                })
                            })
                            // relative instruction
                        var q = this.question_no
                        
                        $('.relative_instruction').each(function(index) {
                            var a = []
                            $(this).siblings('.question_section').find('.question').each(function(index) {
                                if (language == 'bangla') {
                                    var value = $(this).attr('data-question_no').toString()
                                    if (!isNaN(value)) {                                        
                                        for (var i = 0; i < value.length; i++) {
                                            value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                        }
                                    }
                                }
                                a.push(value)
                            })
                            $(this).text('নিচের উদ্দীপকটি পড়ে ' + a.join(',') + ' নং প্রশ্নের উত্তর দাও')
                        })                        
                        $('.question_text').each(function(index) {
                            var question_no = $(this).attr('data-question_no')
                            var question_type = $(this).attr('data-question_type')
                            var relative_type = $(this).attr('data-relative_type')
                            var a = $(this).attr('id')
                            if (question_type == 'mcq' || relative_type == 'mcq') {
                                $(this).find('.qus-child').addClass('mcq')
                            }
                            $(this).find('.question').append('<span class="question_id fs-9 m-l-5">(' + a + ')</span>')
                        })

                        $('.question_print_view p').each(function(index) {
                                var a = $(this).html().replace(/ *\[[^\]]*]/, '')
                                $(this).html(a)
                            })
                            // append element to append div so that element height will be small
                        $('.question_print_view *').not('.section, * span, p *,table *, * table').each(function(index) {
                                if ($(this).hasClass('exam_type')) {
                                    $('.append_content').append('<div class="append m-l-30" style="width: 5in;"></div>')
                                }
                                var al = $('.append').length - 1
                                $('.append:eq(' + al + ')').append($(this).clone())
                            })
                            // 
                        $('.append_content .append table').each(function(index) {
                                if ($(this).parent('.append').length > 0) {
                                    $(this).wrap()
                                }
                            })
                        // break to small part p tag and table
                        $('.append_content .append').each(function(index) {
                            var elem_height = 0
                            var prev_height = 0
                            var increment = 1;
                            var count = 0
                            $(this).children().not('.pos-a, span').each(function(c_i) {
                                elem_height = elem_height + $(this).height() + line_height
                                if (elem_height > page_height) {
                                    elem_height = $(this).height()
                                    if ($(this).children('table').length > 0) {
                                        var prev_before_table = page_height - (elem_height - $(this).height())
                                        var tr_height = 0
                                        $(this).find('tr').each(function(tr_i) {
                                            tr_height = tr_height + $(this).height()
                                            if (tr_height > prev_before_table) {
                                                var af = $(this).closest('table')
                                                var tr_length = $(this).closest('table').find('tr').length - tr_i
                                                tr_height = 0
                                                var rows = $(this).closest('table').find('tr').slice(tr_i);
                                                var $secondTable = $(this).closest('div').wrap('<div class="hello"></div>').parent().append("<div><table><tbody></tbody></table></div>").contents().unwrap();
                                                $secondTable.find("tbody").append(rows);
                                                af.find('tr').slice(tr_i).remove();
                                            }
                                        })
                                    }
                                    if ($(this).text().trim().length && $(this).height() > 50) {
                                        var tokens = $(this).text().split(' ');
                                        if (tokens.length > 35) {
                                            var d = $(this).height() / 25
                                                // if (n>48) {n=48}
                                            var n = d * (27 - font_size)
                                            var html = '<p>' + tokens.slice(0, n).join(' ') + '</p>';
                                            $(this).html(html)
                                            $(this).after('<p>' + tokens.slice(n + 1, tokens.length).join(' ') + '</p>')
                                        }
                                    }
                                }
                            })
                        })

                        // return false
                        var column_head = 100
                        var page_count = 0
                        var p_height
                        $('.append_content .append').each(function(index) {
                                var elem_height = 0
                                var new_type_break = false
                                if (layout == 'layout_column' && page_count == 0) {
                                    elem_height = elem_height - column_head
                                }
                                $(this).children().not('.pos-a, span').each(function(c_i) {
                                    if (layout == 'layout_column' && page_count < 2) {
                                        p_height = first_page_height
                                    } else {
                                        p_height = page_height
                                    }
                                    elem_height = elem_height + $(this).height()
                                    if (elem_height + 40 > p_height && $(this).hasClass('type_title')) {
                                        elem_height =  elem_height + 40
                                    }
                                    if ($(this).hasClass('mcq') && layout != 'layout_column') {
                                        if ($(this).text().length < 20) {
                                            elem_height = elem_height - 8
                                            $(this).addClass('quarter_width')
                                        }
                                        if ($(this).text().length > 20 && $(this).text().length < 40) {
                                            elem_height = elem_height - 5
                                            $(this).addClass('half_width')
                                        }
                                        if ($(this).text().length > 40) {
                                            elem_height = elem_height - 10
                                            $(this).addClass('full_width')
                                        }
                                    }
                                    if ($(this).hasClass('mcq') && layout == 'layout_column') {
                                        if ($(this).text().length > 20) {
                                            elem_height = elem_height
                                            $(this).addClass('full_width')
                                        } else {
                                            elem_height = elem_height - 5
                                            $(this).addClass('half_width')
                                        }
                                    }
                                    if (elem_height > p_height) {
                                        elem_height = $(this).height()
                                        $(this).addClass('new_page');
                                        page_count++
                                    }
                                })
                            })
                        // add page for new page item
                        $('.append_content .append').each(function(index) {
                            page_length = $(this).find('.new_page').length
                            if (layout == 'layout_column') {
                                var page = 1 + (2 * index);
                                if (page_length > 2) {
                                    page = 1 + (4 * index);
                                }
                                $(this).children('.new_page').each(function(c_i) {
                                    if (page % 2 != 0) {
                                        $('#print_content').append('<div class="page_' + page + ' page p-l-20 pos-rel" style="width:3.5in; height:' + page_size + 'in; float:left; padding-right: .25in; margin-right:0.25in; overflow: auto; line-height:' + line_height + 'px; border-right: 2px solid"></div>')
                                        $('#print_content').append('<div class="page_' + (page + 1) + ' page p-l-20 pos-rel" style="width:3.5in; height:' + page_size + 'in; float:left; overflow: auto; line-height:' + line_height + 'px"></div>')
                                    }
                                    
                                    page = page + 1
                                })
                                $('.page_' + 1).css('height', first_page_size + 'in')
                                $('.page_' + 2).css('height', first_page_size + 'in')
                            } if(page_length < 5 && layout != 'layout_column') {
                                var page = 1 + (2 * index);
                                if (page_length > 2) {
                                    page = 1 + (4 * index);
                                }
                                $(this).children('.new_page').each(function(c_i) {
                                    if (page % 2 != 0) {
                                        $('#print_content').append('<div class="page_' + page + ' page p-l-20 pos-rel" style="width:5.15in; height:' + page_size + 'in; float:left; margin-right: 0in; overflow: auto; line-height:' + line_height + 'px"></div>')
                                        $('#print_content').append('<div class="page_' + (page + 1) + ' page p-l-20 pos-rel" style="width:5.15in; height:' + page_size + 'in; float:right; margin-right: 0in; overflow: auto; line-height:' + line_height + 'px"></div>')
                                    }
                                    page = page + 1
                                })
                            }
                        })

                        if ((page_length > 4 && layout != 'layout_column') || layout == 'lecture') {
                            this.exam_data.layout = 'lecture'
                            $('#print_content .page').remove()
                            $('.question_print_view').each(function(index) {
                                $('#print_content').append('<div class="question_content p-l-20"></div>')
                                $('#print_content .question_content:eq(' + index + ')').html($(this).html())
                            })
                            $('.question_content p').addClass('m-0 text-justify solaiman m-b-2').css('line-height', line_height).attr('style', 'font-size:' + font_size + 'px!important');
                            $('.question_content .exam_type').css('page-break-before', 'always')                              
                            var value = 0;
                            $('.question,.relative_answer_instruction').each(function(c_i) {
                                if (language == 'bangla') {
                                    value = $(this).attr('data-question_no').toString()
                                    if (!isNaN(value)) {
                                        for (var i = 0; i < value.length; i++) {
                                            value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                        }
                                    }
                                } else {
                                    value = $(this).attr('data-question_no')
                                }
                                $(this).addClass('pos-rel')
                                $(this).append('<span class="question_no pos-a" style="left:-20px; top: 0px; font-size:' + font_size + 'px">' + value + '.</span>')
                            })
                            var question_sets = this.question_sets
                            $('.relative_answer_instruction').each(function(index) {
                                var value = $(this).attr('data-mark_detail')
                                $(this).append('<span class="mark_detail pos-a" style="right:0px; top: 0px; font-size:' + font_size + 'px">' + value + '</span>')
                            })
                            $('.qus-child.mcq').addClass('m-b-2 m-r-15 text-justify solaiman inline-block').css('line-height', line_height).attr('style', 'font-size:' + font_size + 'px!important');
                            return false
                        }
                        // question number for landscape
                        $('.append').each(function(index) {
                            var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
                            $(this).find('.question,.relative_answer_instruction').each(function(c_i) {
                                var question_type = $(this).attr('data-question_no')
                                if (language == 'bangla' && !isNaN($(this).attr('data-question_no'))) {
                                    var value = $(this).attr('data-question_no')
                                    for (var i = 0; i < value.length; i++) {
                                        value = value.replace(value.charAt(i), bangla_num[value.charAt(i)])
                                    }
                                } else {
                                    value = $(this).attr('data-question_no')
                                }
                                if (value) {
                                    $(this).addClass('pos-rel')
                                    $(this).append('<span class="question_no pos-a" style="left:-20px; top: 0px; font-size:' + font_size + 'px">' + value + '.</span>')
                                }
                            })
                        })
                        $('.relative_answer_instruction').each(function(index) {
                            var value = $(this).attr('data-mark_detail')
                            $(this).append('<span class="mark_detail pos-a" style="right:0px; top: 0px; font-size:' + font_size + 'px">' + value + '</span>')
                        })
                        
                        // append to page
                        $('.append_content .append').each(function(index) {
                            page_length = $(this).find('.new_page').length
                            $(this).children('.new_page').not('.pos-a, span').each(function(c_i) {
                                var a = $(this).nextUntil('.new_page').not('.pos-a, span').add($(this))
                                var p = 1 + (2 * index) + c_i;
                                if (page_length > 2) {
                                    p = 2 + (4 * index) + c_i;
                                }
                                if (c_i == 3) {
                                    p = 1 + (4 * index)
                                }
                                if (layout == 'layout_column') {
                                    $('.page_' + (c_i + 1)).append(a.clone())
                                } else {
                                    $('.page_' + p).append(a.clone())
                                    if (page_length == 1) {
                                        p = 2 + (2 * index);
                                        $('.page_' + p).append(a.clone())
                                    }
                                }
                            })
                        })
                        // return false
                        if (layout == 'layout_column') {
                            $('#print_content').prepend('<div class="column_head m-b-20"></div>')
                            setTimeout(function(){
                            $('.append_content .new_page_column').each(function(i){
                                if (i == 0) {
                                    $('.column_head').append($(this).nextUntil('.question').not('.pos-a, span').add($(this)))
                                }
                            })
                            }, 100)
                        }

                        // append logo and set
                        var question_sets = this.question_sets
                        $('.page .exam_type').each(function(index) {
                            var set = index
                            if (page_length == 1 && layout == 'question' && index % 2 == 0) {
                                var set = index / 2
                            }
                            if (page_length == 1 && layout == 'question' && index % 2 != 0) {
                                var set = (index - 1) / 2
                            }
                        })
                        
                        // add class to paragraph tag in question text
                        $('.page div').not('.type_answer_instruction,.answer_chapter').addClass('m-b-0');
                        $('.page p').addClass('m-b-2 text-justify solaiman').css('line-height', line_height).attr('style', 'font-size:' + font_size + 'px!important');
                        $('.page .qus-child.mcq').addClass('m-b-2 m-r-15 text-justify solaiman inline-block').css('line-height', line_height).attr('style', 'font-size:' + font_size + 'px!important');
                        $('.page table').attr('style', 'border: 1px solid; margin: auto;')
                        $('.page table tr').attr('style', 'border: 1px solid')
                        $('.page table td').attr('style', 'padding: 2px 5px; border: 1px solid;')
                        // $('.page p img').parent().attr('style', 'text-align: center')
                    },
                    print() {
                        // short answer mcq
                        this.organise_question()
                        // return false
                        // if mcq short answer
                        var numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                        var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
                        $('#print_content .short_answer_mcq').each(function(s_i){
                            $(this).children('.section').each(function(index){
                                var value = (index + 1).toString()
                                for (var i = 0; i < value.length; ++i) {
                                    var num = numbers.findIndex(x => x == value[i])
                                    value = value.replace(numbers[num], bangla_num[num])
                                }
                                $(this).addClass('inline-block solaiman').attr('style', 'width:100px; border:1px solid; margin: 0px!important').html('<span class="m-r-5">'+ value +'.</span>' + $(this).text())

                                if (index != 0 && Math.abs((index + 1) % 5) == 0) {
                                    $('<div></div>').insertAfter($(this))
                                }
                            })
                            
                        })
                        if (this.print_item == 'short_answer' && $('.short_answer_mcq').length) {
                            $('.question_content .exam_type').css('page-break-before', '').addClass('m-t-30')
                        }
                        // return false
                            
                        Vue.axios.post('/admin/question/question_print_update', this.exam_data).then(response => {
                            var page_length = $(this).find('.new_page').length
                            $('.user_nav').addClass('successful')
                            setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            if (page_length > 4 || this.exam_data.layout == 'lecture' || this.exam_data.layout == 'layout_column') {
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
                        }, error => {
                           $('.user_nav').addClass('denied')
                           setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                        });
                    },
                    change_set() {
                        if (this.input_print_set == 'all') {
                            this.current_set = 0
                        } else {
                            this.current_set = this.input_print_set
                        }
                        this.relative_filter()
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
                    loop_question(questions, set_i, t_i) {
                        console.log(questions)
                        var q = questions.filter(x => x.pivot.set == set_i)
                        return q;
                    },
                    sets_question(set) {
                        var item = this.mcq_questions.filter(x => x.pivot.set == set)
                        return item
                    },
                    set_print_item() {
                        if (this.print_item == 'short_answer') {this.exam_data.layout = 'lecture'}
                        var type = ''
                        if (this.print_item == 'mcq_answer') {
                            type = 'mcq'
                        } else {
                            return false
                        }                        
                        var mcq_questions = []
                        var data = this.dialogItem.question_data  
                        for (var set_i = 0; set_i < data.length; set_i++) {
                            for (var i = 0; i < data[set_i].length; i++) {
                                for (var j = 0; j < data[set_i][i].question_data.length; j++) {
                                    if (data[set_i][i].question_data[j].question_type == 'mcq') {
                                        for (var k = 0; k < data[set_i][i].question_data[j].question_item.length; k++) {
                                            mcq_questions.push(data[set_i][i].question_data[j].question_item[k])
                                        }
                                    }
                                }
                            }
                        }
                        this.mcq_questions = mcq_questions
                        if (this.mcq_length > 30) {
                            this.mcq_length = 50
                        }
                        if (this.mcq_length <= 30) {
                            this.mcq_length = 30
                        }
                        if (this.mcq_length <= 20) {
                            this.mcq_length = 20
                        }
                        // we are using 50 mcq sheet for all
                        this.mcq_length = 50
                        setTimeout(function() {
                            var mcq_length = this.mcq_length
                            var current_set = this.input_print_set
                            var answer = 0
                            $('.mcq_answer_section').each(function() {
                                var increment = 0
                                $(this).find('.mcq_black_circle').each(function(index) {
                                    var left = 39
                                    var bottom = 34.5
                                    answer = parseInt($(this).attr('data-answer')) - 1
                                    if (mcq_length == 20) {
                                        var left = 39.5
                                        var top = 184.5 + increment
                                        var i = index
                                        if (index > 2) {
                                            left = 141;
                                            top = 28.5 + increment;
                                            i = index - 3
                                        }
                                        if (index == 9 || index == 14) {
                                            increment = increment + 9.1
                                        }
                                        $(this).attr('style', 'top:' + (top + (i * 9.18)) + 'px; left:' + (left + (answer * 9.1)) + 'px;')
                                    } else if (mcq_length == 30) {
                                        var left = 39
                                        var top = 187.8 + increment
                                        var i = index
                                        if (index > 7) {
                                            left = 142;
                                            top = 28.5 + increment;
                                            i = index - 8
                                        }
                                        if (index == 14 || index == 19 || index == 24) {
                                            increment = increment + 9.65
                                        }
                                        $(this).attr('style', 'top:' + (top + (i * 9.3)) + 'px; left:' + (left + (answer * 9.1)) + 'px;')
                                    } else if (mcq_length == 50) {
                                        var left = 190
                                        var top = 58 + increment
                                        var i = index
                                        if (index == 24) {
                                            increment = 0
                                        }
                                        if (index > 23) {
                                            left = 288.5;
                                            top = 33.5 + increment;
                                            i = index - 24
                                        }
                                        if (index == 4 || index == 9 || index == 14 || index == 19 || index == 29 || index == 34 || index == 39 || index == 44 || index == 49) {
                                            increment = increment + 12.3
                                        }
                                        $(this).attr('style', 'top:' + (top + (i * 12.2)) + 'px; left:' + (left + (answer * 12.1)) + 'px; border: 4px solid #000')
                                    }
                                })
                            })
                            $('.mcq_set').each(function(index) {
                                if (current_set != 'all') {index = current_set}
                                if (mcq_length == 50) {
                                    $(this).attr('style', 'top:45.5px; left:' + (56 + (index * 12)) + 'px; border: 4px solid #000')
                                } else {
                                    $(this).attr('style', 'top:37.8px; left:' + (39 + (index * 9.2)) + 'px;')
                                }
                            })
                        }.bind(this), 500)
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.search = ''
                        this.search_filter = ''
                        this.search_filter1 = ''
                    },
                    hour_minutes(value) {
                        var language = this.exam_data.language
                        var a = Math.floor(value / 60)
                        var b = value % 60
                        if (language == 'bangla') {
                            return a > 0 ? a + ' ঘন্টা ' + b + ' মিনিট' : b + ' মিনিট'
                        }
                        return a > 0 ? a + ' Hour ' + b + ' Minutes' : b + ' Minutes'
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
  .print {
    opacity: 0
  }
</style>