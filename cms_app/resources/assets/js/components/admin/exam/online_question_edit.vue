<template>
    <div class="question_edit">
        <v-container fluid class="p-t-10">
            <!-- @page_headline -->
            <div>
                <div class="row">
                    <div class="col-lg-12">
                        <h3 class="fs-20 inline-block">Edit Question <v-icon class="fs-20" small @click="question_list">refresh</v-icon></h3>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step==1">
                        <v-select v-model="search_by" :items="search_by_item" label="Search By" :clearable=true @change="question_list"></v-select>
                    </div>
                    <div class="col-lg-2 col-xs-6" v-if="step<3">
                        <v-text-field v-model="search_text" label="Search"></v-text-field>
                    </div>
                    <div class="col-lg-2 col-xs-6 pos-rel" style="display: inline-flex">
                        <v-text-field v-model="search_id" label="Search By ID" @keyup.enter="goto_question_id"></v-text-field>
                        <v-chip style="display:inline-table; top: 7px; height: 35px; width: 70px; margin-left: 10px; text-align: center" class="" @click="goto_question_id">Go</v-chip>
                    </div>
                    <div v-if="step >= 3" class="pos-rel col-lg-2 col-xs-6" style="top: 13px; display: inline-flex">
                        <v-chip label @click="prev">Previous Question</v-chip>
                        <v-chip label @click="next">Next Question</v-chip>
                    </div>
                    <div v-if="step > 1" class="col-lg-2 col-xs-6">
                        <span class="float-right m-t-23 cur-p" @click="go_back">Go Back ⇾</span>
                    </div>
                </div>
                <v-btn v-if="step>=3" color="success" @click="save" style="position: fixed; bottom: 10px; right: 20px; z-index: 9" :disabled="!valid || loading" :loading="loading">Save</v-btn>
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
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container class="p-0">
                                <div v-if="step==1" class="step_1_div card p-15 cur-p" v-for="(search_item, s_i) in itemsSorted">
                                    <div class="step_1" @click="goto_step2(s_i)">
                                        <div class="inline-block" style="min-width:50px">#{{s_i + 1}}</div>
                                        <div class="inline-block fs-16 fw-700">{{search_item.name}}</div>
                                    </div>
                                    <div>
                                        <div class="inline-block" style="min-width:50px"></div>
                                        <div class="inline-block">{{search_item.questions.length}} Questions</div>
                                    </div>
                                </div>
                                <hr class="m-t-15 m-b-15">               
                                <div class="step_2_div" style="display: none">
                                    <!-- step2 when not combine -->
                                    <div class="p-l-30 p-r-30 step_2" v-if="step==2">
                                        <h4 class="fs-16 fw-700 m-l-10">{{question_list_data[search_by_index].name}} ({{question_list_data[search_by_index].questions.length}} Questions)</h4>
                                        <hr class="m-t-5 m-b-10">
                                        <div >
                                                <!-- question -->
                                            <ol class="solaiman">
                                                <li class="mr-2 cur-p pos-rel qus-list" v-for="(question, q_i) in itemsSorted">
                                                    <span style="position: absolute; right: 0px">
                                                        <span v-for="subtag in question.subtag">{{subtag}}</span>
                                                        ({{question.id}})
                                                    <v-icon class="fs-20" @click="delete_question(question.id, q_i)">delete</v-icon>
                                                    </span>
                                                    <p class="m-b-3 question_p" @click="goto_step3(question, q_i)" v-html="question.question_text"></p>
                                                    <p v-if="question.language == 'bangla'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_bangla[op_key] + option.option_text"></p>
                                                    <p v-if="question.language == 'english'" v-for="(option, op_key) in question.options" class="inline-block m-b-8" v-html="option_char_english[op_key] + option.option_text"></p>
                                                </li>
                                            </ol>
                                            <!-- question div end -->
                                        </div>
                                    </div>
                                </div>
                                <div class="step_3_div" style="display: none" v-if="step==3">
                                    <template>
                                      <div class="question_content">
                                         <v-form ref="form" v-model="valid" lazy-validation>
                                            <v-container>
                                               <div class="row p-l-30 p-r-30">
                                                  <v-flex lg12 class="pb-0 pt-0">
                                                    <v-layout>
                                                        <v-text-field disabled class="m-r-15" v-model="dialogItem.id" label="Question ID"></v-text-field>
                                                        <v-text-field class="m-r-15" v-model="dialogItem.question_mark" label="Per Question Mark*" :rules="[v => !!v || 'required']" type="number" required></v-text-field>
                                                        <v-text-field class="m-r-15" v-model="dialogItem.question_length" label="Number of Options*" type="number" :rules="[v => !!v || 'required']" @input="valid_1=true"></v-text-field>
                                                        <v-combobox :rules="[v => !!v || 'required']" v-model="dialogItem.question_tag" :items="question_tag" hide-selected label="Tags*" :clearable=true persistent-hint></v-combobox>
                                                    </v-layout>
                                                    <v-layout>
                                                        <v-combobox class="m-r-15" v-model="dialogItem.question_subtag" :items="question_subtag" hide-selected label="Sub Tags" :clearable=true multiple persistent-hint small-chips></v-combobox>
                                                        <v-autocomplete class="m-r-15" v-model="dialogItem.subject_id" :items="subject" label="Subject"></v-autocomplete>
                                                        <v-text-field class="m-r-15" v-model="dialogItem.topic" label="Topic"></v-text-field>
                                                        <v-autocomplete v-model="dialogItem.language" :items="language" label="Language"></v-autocomplete>
                                                    </v-layout>
                                                  </v-flex>
                                                  <v-flex lg12 class="pb-0 pt-0">
                                                     <!-- question checking detail -->
                                                     <div v-for="(editor, key) in editor_detail" @click="go_to_editor_question(key)" style="cursor: pointer; margin-bottom: 12px; display: inline-block; font-size: 12px"> <span class="mr-2" style="font-weight: 700">{{key + 1}}.</span>  <span class="m-r-5">Q ({{editor.qus}})</span>
                                                     </div>
                                                     <!-- question editor -->
                                                     <div class="m-b-10">
                                                        <div v-if="qus_location">Image Location: {{qus_location}}</div>
                                                     </div>
                                                     <div class="pos-rel question_editor">
                                                        <!-- editor notation -->
                                                        <div class="editor_notation pos-a">
                                                            <!-- editor notation -->
                                                           <v-radio-group v-model="question_notation" row>
                                                              <v-radio label="Ro" value="roman"></v-radio>
                                                              <v-radio label="Ch" value="char"></v-radio>
                                                              <v-radio label="Dig" value="digit"></v-radio>
                                                           </v-radio-group>
                                                           <input class="notation_input" v-model="question_begin_text">
                                                           <input class="notation_input mr-2" v-model="question_end_text">
                                                           <v-radio-group v-model="question_child_notation" row>
                                                              <v-radio label="Un" value="unicode"></v-radio>
                                                              <v-radio label="Ro" value="roman"></v-radio>
                                                              <v-radio label="Ch" value="char"></v-radio>
                                                           </v-radio-group>
                                                           <input class="notation_input" v-model="question_child_begin_text">
                                                           <input class="notation_input" v-model="question_child_end_text">
                                                           <button small class="default notation_input" @click.prevent="refine_editor_text" style="    width: 60px; background: #607d8b; color: #fff; float: right; top: 10px; padding: 2px 7px">Refine</button>
                                                        </div>
                                                        <!-- file input -->
                                                        <input type="file" name="add_image" @change="add_image" multiple style="display: none" accept="image/*">
                                                        <input type="file" name="link_image" @change="link_image" multiple style="display: none" accept="image/*">
                                                        <html-editor height="200" v-if="update_editor" upload_type="question" :model="question_editor"></html-editor>
                                                        <v-layout>
                                                            <div class="m-r-15 pos-rel" style="top: 20px">Answer (write option number) :</div>
                                                            <v-text-field v-model="dialogItem.answer" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                                        </v-layout>
                                                     </div>
                                                  </v-flex>
                                               </div>
                                            </v-container>
                                         </v-form>
                                      </div>
                                   </template>
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
                search_by: '',
                search_by_item: [{value: 'tag', text: 'Tags'}, {value: 'subject', text: 'Subjects'}],
                relative_text: [],
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
                search_by_index: '',
                question_index: '',
                search_text: '',
                search_id: '',
                question_subtag: [],
                language: [{value: 'bangla', text: 'Bangla'}, {value: 'english', text: 'English'}],
                qus_location: '',
                update_editor: false,
                option_char_bangla: ["ক. ","খ. ","গ. ","ঘ. ","ঙ. "],
                option_char_english: ["a. ","b. ","c. ","d. ","e. "]
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
                    this.axios.get('/admin/exam/get_online_question/' + this.search_by).then(response => {
                        this.question_list_data = response.data.search_by_list;                        
                    });
                },
                
                refine_editor_text(){
               if (this.dialogItem.question_length=="") {this.dialogItem.question_length=null; return this.valid_1 = false}
               this.organize_editor_text()
               var question_text = []
               var answer_text = []
               var detail = []
               var relative_text = []
               var all_tag = []
               var question_length = this.dialogItem.question_length
               
               // getting relative text
               $('.question_editor p.relative').each(function(){
                   // var qus_index = $(this).text().substring(0,1)
                   var total_elem = $(this).nextUntil("p.relative").add($(this)).wrapAll('<div class="relative-div"></div>')
                   var total_qus = $(this).parent('.relative-div').children('.question').length
                   $(this).unwrap()
                   var x = '###'
                   var y = ''
                   if ($(this).next('.rel-child').length>0) {y = $(this).next('.rel-child').html()}
                   if ($(this).next('.rel-child').next('.rel-child').length>0) {
                     y = y + $(this).next('.rel-child').next('.rel-child').html()
                   }
                   var text = $(this).html().replace(x, "");
                   var a = {'relative_text': text + y, 'relative_qus_length': total_qus}
                   relative_text.push(a)
               })
               // getting short answer, question text, tag and editor detial            
               $('.question_editor p.question').each(function(ind){                
                   // wrap full one question element
                   var total_elem = $(this).nextUntil("p.question, p.relative").add($(this)).wrapAll('<div class="qus-div"></div>')                 
                   // get element of per question
                   var elem = $(this).parent(".qus-div").clone().find('.answer').remove().end().html().replace('***', "").replace('#', "").replace(/\s*{.*?}\s*/g, '').replace(/\s*\[.*?\]\s*/g, '')
                   // question lenght
                   var qus = $(this).parent(".qus-div").find('.qus-child').length
                   var ans_length = 0                   
                   // options
                   var options = [];
                   $(this).parent(".qus-div").find(".qus-child").each(function(){
                      options.push($(this).clone().wrap('<div></div>').parent().html().replace('opt1: ', '').replace('opt2: ', '').replace('opt3: ', '').replace('opt4: ', '').replace('opt5: ', '').replace(' last-p', '').replace('#', ''))
                   })
                   $(this).unwrap(); //unwrap qus-div
                   var question_part = $(this).clone().find('.question').nextUntil(".qus-child").add($(this)).wrap('<div></div>').parent().html().replace('***', '')
                   $(this).unwrap() //unwrap last div wrapped
                   
                   question_text.push({'question_part': question_part, 'options': options})
                   // question detail of editor
                   var a = {'stem': total_elem.length - qus - ans_length, 'qus': qus}
                   detail.push(a)                   
               })
               // getting deatil answer
               $('.answer_editor p.question').each(function(){
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
                 this.qus_location = q_src.replace('/'+filename, '')
               }
   
               var a_src = $('.answer_editor img').not('.data_image').attr('src')
               if (a_src) {
                 a_src = a_src.split('///').pop()
                 var filename = a_src.split('/').pop()
                 this.ans_location = a_src.replace('/'+filename, '')
               }
   
               var r_src = $('.relative_text_editor img').not('.data_image').attr('src')
               if (r_src) {
                 r_src = r_src.split('///').pop()
                 var filename = r_src.split('/').pop()
                 this.relative_location = r_src.replace('/'+filename, '')
               }
               
               $('.question').prev().addClass('last-p') //add class for bottom border
               this.editor_detail = detail //editor detail shows
               // storing data
               this.dialogItem.question_text = question_text
               // check if any question has no desire question length
               setTimeout(function () { 
                   var index = this.editor_detail.findIndex(x => x.qus != this.dialogItem.question_length)
                   if (index>-1) {alert('Question no ' + (index + 1) + ' do not have ' + this.dialogItem.question_length + ' question childs')}
               }.bind(this), 500)
           },
                // formating text of question editor
                organize_editor_text(){
               var p = $('.note-editable');
               p.contents()
                 .filter(function() { return this.nodeType == 3; }) // Select all textnodes
                 .wrap('<p>') // Place them inside paragraph elements
               $('br', p).remove();
               $('.note-editable p:empty').remove();
               $('.note-editable style, .short_answer_div style').remove()
               $('o\\:p').remove()
               // symbolise
               $('.note-editable *, .short_answer_div *').each(function(){
                   var a = $(this).css('font-family')
                   if (a == 'Symbol' || a == 'Math5') {$(this).addClass('symbol')}
               })
               // return false
               // remove style attribute
               $('.note-editable *, .short_answer_div *').removeAttr("style")
               // unwrap unblock element
               $('.note-editable *:not(p,div,ul,li,sup,sub,table,tbody,tr,td,img,.symbol,.question,.qus-child)').contents().unwrap();
               // replace element into p element
               $('.note-editable *:not(table,tbody,tr,td,sup,sub,img,.answer,.question,.qus-child,.answer,.relative,.rel-child,.symbol)').wrap('<p></p>').contents().unwrap()
   
               // unwrap element don't have text
               $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child,sup,sub,.rel-child)').each(function(){
                   var a = $(this).clone().children().remove().end().text()
                   if (a=='') {$(this).contents().unwrap()}
               })
               // remove empty element
               $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child, .relative, .rel-child)').each(function() {
                   var $this = $(this);
                   if($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                   $this.remove();
               });
               // managing table
               $('.note-editable table').each(function(){
                   if($(this).closest('.rel-child').length==0){
                     $(this).wrap('<div></div>')
                   }
               })
               $('.note-editable table col').remove()
               // remove extra space
               $(".short_answer_div, .question_editor .note-editable").html(function (i, html) {
                   return html.replace(/&nbsp;/g, ' ');
               });
               // remove comments
               $('.short_answer_div, .note-editable *').contents().each(function() {
                   if(this.nodeType === Node.COMMENT_NODE) {
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
               $(".question_editor .note-editable p").not('.qus-child, .relative, .rel-child, .answer').html(function(_, html) {
                   $(this).html($(this).html().trim())
                   if (notation=='char') {
                       var reg_1 = new RegExp("([A-Ha-hJ-Uj-uWYZwyz][A-Ha-hJ-Uj-uWYZwyz])("+"\\"+question_end_text+")", 'g');
                       var reg_2 = new RegExp("(ক|খ|গ|ঘ|ঙ|চ|ছ|জ|ঝ|ঞ|ট|ঠ|ড|ঢ|ণ|ত|থ|দ|ধ|ন|প|ফ|ব|ভ|ম|[A-Za-z])("+"\\"+question_end_text+")", 'g');
                       var a = $(this).text().trim().substring(0,2).match(reg_1)
                       if (!a) {a = $(this).text().trim().substring(0,2).match(reg_2)}
                   }
                   if (notation =='roman') {
                       var reg_1 = new RegExp("(IX|IV|V?I{1,3}|L?X{1,3})|(ix|iv|v?i{1,3}|l?x{1,3})("+"\\"+question_end_text+")", 'g');
                       var a = $(this).text().trim().substring(0,4).match(reg_1)
                   }
                   if (notation=='digit') {
                       var reg_1 = new RegExp("([০-৯]{1,3}|[0-9]{1,3})("+"\\"+question_end_text+")", 'g');
                       var a = $(this).text().trim().substring(0,4).match(reg_1)
                   }
                   if (a) {
                     return html.replace(a[0], "***").replace(question_begin_text, '').replace(/\s\s+/g, ' ');
                   }
               });
               $('.note-editable p:contains("***")').each(function(){
                   $(this).addClass('question').removeClass('qus-child').removeClass('relative').removeClass('rel-child')
               });
               $('.question_editor .note-editable p:contains("###")').each(function(){
                   $(this).nextUntil('.question').addClass('rel-child')
                   var x = $(this).html().replace('###', '')
                   $(this).html(x)
                   $(this).addClass('relative').removeClass('qus-child').removeClass('answer')
               });
               $('.question_editor .note-editable p:contains("##")').each(function(){
                     var a = $(this).text().substr($(this).text().indexOf("##")).replace()
                     if (a) {
                         var x = $(this).next('.relative').before( "<p class='answer'>" + a.replace('##','') + "</p>" );    
                         if (!x.length) {x = $(this).next('.question').before( "<p class='answer'>" + a.replace('##','') + "</p>" );}                
                         if (!x.length) {x = $(this).nextAll().after( "<p class='answer'>" + a.replace('##','') + "</p>" );}
                         if (!x.length) {x = $(this).after( "<p class='answer'>" + a.replace('##','') + "</p>" );}
                         var b = $(this).html().replace(a, '')
                         $(this).html(b)
                     }
               });
               // return false
               // replace or change question child char
               $(".question_editor .note-editable p").not('.question,.relative, .answer, .rel-child').html(function(_, html) {
                   var regex_match = []
                   if (notation_child =='roman') {
                       var reg_1 = new RegExp("(IX|IV|V?I{1,3}|L?X{1,3})|(ix|iv|v?i{1,3}|l?x{1,3})("+"\\"+question_child_end_text+")", 'g');
                       regex_match = $(this).text().trim().match(reg_1)
                   }
                   if (notation_child =='char') {
                       var reg_1 = new RegExp("([A-Ha-hJ-Uj-uWYZwyz]{1,3})("+"\\"+question_child_end_text+")", 'g');
                       regex_match = $(this).text().match(reg_1)
                   }
                   if (notation_child =='unicode') {
                       var reg_1 = new RegExp("(ক|খ|গ|ঘ|ঙ|চ|ছ|জ|ঝ|ঞ)("+"\\"+question_child_end_text+")", 'g');
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
                       
                       if (text_replace) {var splited_text = text_replace.split('*')}
                       if (notation_child=='char' || notation_child=='roman') {
                         var char_list = ["a) ", "b) ", "c) ", "d) ", "e) ", "f) ", "g) ", "h) ", "i) ", "j) ", "k) ", "l) ", "m) ", "n) ", "o) ", "p) ", "q) ", "r) ", "s) ", "t) ", "u) "]
                       }
                       if (notation_child=='unicode') {
                         var char_list = ["ক) ", "খ) ", "গ) ", "ঘ) ", "ঙ) ", "চ) ", "ছ) ", "জ) ", "ঝ) ", "ঞ) "]
                       }
                       var refine_text = []
                       var new_text = ""
                       var hash_index = -1
                       for (var i = 0; i < splited_text.length; i++) {
                           if (splited_text[i].trim().length>0 && splited_text[i].trim().substring(0,1)!='#') {
                               refine_text.push(splited_text[i].trim())
                           }
                           if (splited_text[i].trim().substring(0,1)=='#') {
                               hash_index = i
                           }
                       }
                       for (var i = 0; i < refine_text.length; i++) {
                           if (refine_text[i].indexOf('#') > -1) {hash_index = i + 1}
                           if (i == hash_index) {hash='#'} else {hash = ""}
                           new_text =  new_text + '<p class="qus-child">'+ hash + char_list[i] + refine_text[i].replace('#', '') + '</p>'
                       }
                       $(this).html(new_text)
                   }
               });
               
               // unwrap element that has parent p tag
               $('.note-editable > p *').not('.symbol, img, sub, sup, .answer').each(function() {
                   if ($(this).parent().is('p')) {
                       $(this).unwrap()
                   }
               });
               // replace question child stating character
               $('.question_editor .note-editable p.question').each(function() {
                   var total_elem = $(this).nextUntil("p.question, p.relative").add($(this)).wrapAll('<div class="qus-div"></div>')
                   $(this).parent('.qus-div').children('.qus-child').html(function(index,html){
                       var regex_match = []
                       if (notation_child =='roman' || notation_child=='char') {
                           var char_list = ["a)", "b)", "c)", "d)"]
                           var reg_1 = new RegExp("([a-d])(\\))", 'g');
                           regex_match = $(this).text().match(reg_1)
                       }
                       if (notation_child =='unicode') {
                           // var char_list = ["ক) ", "খ) ", "গ) ", "ঘ) "]
                           var char_list = ["opt1: ", "opt2: ", "opt3: ", "opt4: ", "opt5: "]
                           var reg_1 = new RegExp("(ক|খ|গ|ঘ|ঙ)(\\))", 'g');
                           regex_match = $(this).text().match(reg_1)
                       }
                       if (regex_match) {return html.replace(regex_match[0], char_list[index])}
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
                goto_step3(question, question_index) {
                    this.question_index = question_index
                    var question_text = question.question_text.replace('question">', 'question">***')
                    for (var i = 0; i < question.options.length; i++) {
                        question_text = question_text + question.options[i].option_text.replace('"qus-child">', '"qus-child">opt' + (i + 1) + ': ')
                        if (question.options[i].id == question.answer_option_id) {
                            this.dialogItem.answer = i + 1
                        }
                    }
                    this.question_editor = question_text
                    
                    this.dialogItem.id = question.id
                    this.dialogItem.subject_id = question.subject_id
                    this.dialogItem.question_mark = question.question_mark
                    this.dialogItem.question_length = question.question_length
                    this.dialogItem.relative_text_id = question.question_relative_text_id
                    this.dialogItem.question_tag = {value: question.question_tag_id, text: this.question_tag[this.question_tag.findIndex(x => x.value == question.question_tag_id)].text}
                    this.dialogItem.language = {value: question.language, text: this.language[this.language.findIndex(x => x.value == question.language).text]}

                    this.dialogItem.topics = question.topic;
                    this.dialogItem.question_subtag = question.subtag

                    // this.question_index = this.question_list_data[this.search_by_index].chapters[this.chapter_index].questions.findIndex(x => x.id == this.dialogItem.id)
                    this.update_editor = false
                    $('.step_3_div').hide()
                    this.search_text = ""
                    this.step = 3
                    this.search_id = ""
                    setTimeout(function() {
                        $('.step_3_div').fadeIn('slow'); this.update_editor = true 
                    }.bind(this), 250)
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
                goto_question_id() {
                    var id = this.search_id
                    var item = this.question_list_data;
                    var has_questions = 0
                    var questions = {}
                    for (var i = 0; i < item.length; i++) {
                        for (var j = 0; j < item[i].questions.length; j++) {
                            if (id == item[i].questions[j].id) {
                                has_questions = 1;
                                this.search_by_index = i;
                                this.question_index = j
                                questions = item[i].questions[j]
                            }
                        }
                        if (has_questions == 1) {
                            break
                        }
                    }
                    if (has_questions == 0) {
                        return alert("Not Found Any Question!!!")
                    }
                    setTimeout(function() {
                        this.goto_step3(questions, this.question_index)
                    }.bind(this), 100)
                },
                prev() {
                    if (this.question_index == 0) {
                        return alert("No Previous Question!!")
                    }
                    this.step = 4
                    setTimeout(function() {
                        this.goto_step3(this.question_list_data[this.search_by_index].questions[this.question_index - 1], this.question_index - 1)
                    }.bind(this), 100)
                    
                },
                next() {
                    if (this.question_index == this.question_list_data[this.search_by_index].questions.length - 1) {
                        return alert("No Next Question!!")
                    }
                    this.step = 4
                    setTimeout(function() {
                        this.goto_step3(this.question_list_data[this.search_by_index].questions[this.question_index + 1], this.question_index + 1)
                    }.bind(this), 100)
                    
                },
                delete_question(id, index) {
                    var confirmation = confirm("Do you want to delete?")
                    if (!confirmation) {
                        return false
                    }
                    this.axios.post('/admin/question/question_delete/' + id).then(response => {
                        this.question_list_data[this.search_by_index].questions.splice(index, 1)
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
                // upload image to editor
               link_image(){
                 var files = $('input[name=link_image]')[0].files
                 var elem = $('.question_editor .note-editable')
                 var id_prefix = 'qus'
       
                 for (var i = 0; i < files.length; i++) {
                     elem.find('img').each(function(index){
                         var x = $(this).attr('src').split('/').pop()
                         $(this).removeAttr('id')
                         $(this).addClass('data_image')
                         $(this).attr('id', id_prefix + index)
                         if (files[i].name == x) {
                             var reader = new FileReader();
                             reader.onload = function(){
                               var dataURL = reader.result;
                               var output = document.getElementById(id_prefix + index);
                               output.src = dataURL;
                             };
                             reader.readAsDataURL(files[i]);
                         }
                     })
                 }
                 $('input[name=link_image]').val("")
               },
               add_image(){
                 var files = $('input[name=link_image]')[0].files
                 var elem = $('.question_editor .note-editable')
                 var id_prefix = 'qus'
       
                 for (var i = 0; i < files.length; i++) {
                     elem.find('img').each(function(index){
                         var x = $(this).attr('src').split('/').pop()
                         $(this).removeAttr('id')
                         $(this).addClass('data_image')
                         $(this).attr('id', id_prefix + index)
                         if (files[i].name == x) {
                             var reader = new FileReader();
                             reader.onload = function(){
                               var dataURL = reader.result;
                               var output = document.getElementById(id_prefix + index);
                               output.src = dataURL;
                             };
                             reader.readAsDataURL(files[i]);
                         }
                     })
                 }
                 $('input[name=link_image]').val("")
               }
        } //end method
    } 
</script>

<style>
  .checkbox_flex .v-list__tile__action{
    min-width: 20px;
  }
  .checkbox_flex .v-icon {
    font-size: 18px;
  }
  .checkbox_flex .v-input--selection-controls__input{
    margin-right: 0px;
  }
  .checkbox_flex .v-list__tile__title{
    font-size: 12px;
  }
  .checkbox_flex .v-list__tile{
    padding: 0px 6px;
  }
  .v-text-field label, .v-text-field input {
    font-size: 14px;
  }
  
  #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .note-editor{
    z-index: 1;
  }
  .note-editable p{
    margin-bottom: 5px!important;
  }
  .note-editable p.relative{
    border-top: 1px solid;
  }
  .note-popover .popover-content, .panel-heading.note-toolbar{
    z-index: 7!important;
  }
  .symbol {
    font-family: Symbol
  }
  .note-editable td {
    text-align: center
  }
  .editor_notation {
      z-index: 2;
      right: 10px;
      top: 0px;
      left: 230px;
  }
  .editor_notation .notation_input {
      border: 1px solid #989898;
      width: 40px;
      position: relative;
      top: -12px;
      margin-right: 5px;
      text-align: center;
      font-weight: 600;
  }
  .editor_notation .v-radio {
      margin-right: 10px;
  }
  .editor_notation .v-input--radio-group {
      display: inline-block;
  }
  .editor_notation .v-input--radio-group label {
      font-size: 12px;
      top: -2px!important;
  }
  .editor_notation .v-input--radio-group .v-input--selection-controls__input{
      margin-right: 0px;
  }
  .editor_notation .v-input--radio-group .v-input--selection-controls__input i{
      font-size: 17px;
  }
  .editor_notation .v-input--radio-group .v-input--selection-controls__ripple{
      height: 25px;
      width: 25px;
      left: -12px;
      top: calc(50% - 22px);
      margin: 9px;
  }
  .editor_notation .v-input--radio-group__input {
      position: relative;
      top: -9px;
  }
  .v-menu__content.v-autocomplete__content {
      z-index: 3!important;
  }
  .answer{
    color: green
  }
  .relative, .rel-child{
    color: orange
  }
</style>