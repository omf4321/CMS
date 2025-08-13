<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
   <div class="question_add p-b-100">
      <v-container fluid>
         <!-- @page_headline -->
         <div>
            <h3 class="headline m-t-0">Add Questions</h3>
            <div style="position: fixed; bottom: 115px; right: 55px"> <span class="block">1- {{dialogItem.short_answer.length>0 ? dialogItem.short_answer[0].text : ''  | strip_tags}}</span>
               <span>{{this.dialogItem.question_text.length}}- {{dialogItem.short_answer.length>0 ? dialogItem.short_answer[dialogItem.short_answer.length-1].text : '' | strip_tags}}</span>
            </div>
            <div style="position: fixed; bottom: 15px; right: 20px; z-index: 9">
               <div>
                  <v-chip class="m-r-15">Q: {{this.dialogItem.question_text.length}}</v-chip>
                  <v-chip>SA: {{this.dialogItem.short_answer.length}}</v-chip>
                  <v-chip>DA: {{this.dialogItem.detail_answer.length}}</v-chip>
               </div>
               <v-btn color="info" @click="refine_editor_text" :disabled="!valid_1 || loading">Check</v-btn>
               <v-btn color="success" @click="save" :disabled="!valid || loading" :loading="loading">Save</v-btn>
            </div>
         </div>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
               <template>
                  <div class="question_content">
                     <v-form ref="form" v-model="valid" lazy-validation>
                        <v-container>
                           <v-layout class="p-l-30 p-r-30">
                              <v-flex lg2 class="pb-0 pt-0">
                                 <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class*" @change="get_subject" required></v-select>
                                 <v-autocomplete v-model="dialogItem.subject_id" :items="subject" :rules="[v => !!v || 'Subject is required']" label="Subject*" @change="get_chapter" required></v-autocomplete>
                                 <v-select v-model="dialogItem.question_type" :items="question_type" :rules="[v => !!v || 'Type is required']" label="Question Type*" required></v-select>
                                 <v-text-field v-model="dialogItem.question_mark" label="Per Question Mark*" :rules="[v => !!v || 'Type is required']" type="number" required></v-text-field>
                                 <v-text-field v-model="dialogItem.question_mark_detail" label="Marks Detail" required></v-text-field>
                                 <v-text-field v-model="dialogItem.board_question_no" label="Board Question No" type="number"></v-text-field>
                                 <v-select v-model="dialogItem.question_status" :items="question_status" label="Question Status" :clearable=true></v-select>
                                 <v-text-field v-model="dialogItem.reference_file" label="Reference File"></v-text-field>
                                 <div contenteditable="true" class="short_answer_div" v-html="short_answer_text" style="height: 400px; overflow: auto" data-placeholder="Short Answer"></div>
                              </v-flex>
                              <v-flex lg7 class="pb-0 pt-0" v-if="!saving">
                                 <!-- relative text editor -->
                                 <div class="relative_text_editor" v-if="dialogItem.question_type=='relative' && hide_relative == false">
                                    <!-- file input -->
                                    <input type="file" name="relative_image" @change="image_upload" multiple style="display: none" accept="image/*">
                                    <html-editor placeholder="Relative Text" height="150" upload_type="relative" :model.sync="relative_text_editor"></html-editor>
                                 </div>
                                 <!-- question checking detail -->
                                 <div v-for="(editor, key) in editor_detail" @click="go_to_editor_question(key)" style="cursor: pointer; margin-bottom: 12px; display: inline-block; font-size: 12px"> <span class="mr-2" style="font-weight: 700">{{key + 1}}.</span>  <span class="m-r-5">S ({{editor.stem}})</span>  <span class="m-r-5">Q ({{editor.qus}})</span>  <span class="m-r-5">T ({{editor.tag}})</span><span class="mr-3">A ({{editor.ans}})</span>
                                 </div>
                                 <!-- question editor -->
                                 <div class="pos-rel question_editor">
                                    <!-- editor notation -->
                                    <div class="editor_notation pos-a">
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
                                    </div>
                                    <!-- editor -->
                                    <!-- file input -->
                                    <input type="file" name="question_image" @change="image_upload" multiple style="display: none" accept="image/*">
                                    <html-editor height="300" upload_type="question" :model.sync="question_editor"></html-editor>
                                 </div>
                                 <!-- detail answer editor -->
                                 <div class="pos-rel answer_editor">
                                    <!-- file input -->
                                    <div class="m-b-10">
                                       <div v-if="qus_location">Question Location: {{qus_location}}</div>
                                       <div v-if="ans_location">Answer Location: {{ans_location}}</div>
                                    </div>
                                    <input type="file" name="answer_image" @change="image_upload" multiple style="display: none" accept="image/*">
                                    <html-editor height="500" upload_type="answer" :model.sync="answer_editor" placeholder="Detail Answer"></html-editor>
                                 </div>
                              </v-flex>
                              <v-flex lg3 class="pb-0 pt-0">
                                 <v-text-field v-model="dialogItem.question_length" label="Question Childs*" type="number" :rules="[v => !!v || 'Type is required']" @input="valid_1=true"></v-text-field>
                                 <v-combobox ref="combobox" v-model="dialogItem.chapters" :items="chapter" hide-selected label="Chapters" :clearable=true multiple small-chips @change="get_chapter_topic"></v-combobox>
                                 <v-autocomplete v-if="dialogItem.question_type=='relative'" v-model="dialogItem.relative_text_id" :items="relative_text" label="Select Relative Text" :clearable=true @change="hide_new_relative"></v-autocomplete>
                                 <v-text-field v-if="dialogItem.question_type=='relative'" v-model="dialogItem.relative_question_type" label="Relative Type*" :rules="[rules.required, rules.dash]"></v-text-field>
                                 <div v-if="dialogItem.question_type=='relative'" class="pos-rel float-right" style="top: -15px">{{relative_type}}</div>
                                 <v-combobox v-model="dialogItem.question_tag" :items="question_tag" hide-selected label="Question Tag" :clearable=true multiple persistent-hint small-chips></v-combobox>
                                 <v-text-field v-model="dialogItem.question_title" label="Question Title*" :clearable=t rue></v-text-field>
                                 <!-- list of topic by chapter -->
                                 <v-list class="checkbox_flex" v-if="this.dialogItem.chapters.length==1">Topics
                                    <v-layout row wrap>
                                       <v-flex v-for="(topic, j) in chapter_topic" :key="j">
                                          <v-list-item @click="">
                                             <v-list-item-action>
                                                <v-checkbox v-model="topic.val"></v-checkbox>
                                             </v-list-item-action>
                                             <v-list-item-content>
                                                <v-list-item-title style="font-weight: 500; font-size: 12px">{{topic.topic_no}}-{{topic.text}}</v-list-item-title>
                                             </v-list-item-content>
                                          </v-list-item>
                                       </v-flex>
                                    </v-layout>
                                 </v-list>
                              </v-flex>
                           </v-layout>
                        </v-container>
                     </v-form>
                  </div>
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
        components: {'htmlEditor': htmlEditor},
        data() {
                return {
                  // select/combo input data
                  echelon: [],
                  subject: [],
                  original_subject: [],
                  chapter: [],
                  original_chapter: [],
                  chapter_topic: [],
                  original_chapter_topic: [],
                  question: [],
                  question_tag: [],
                  question_type: [{value: 'cq', text: 'CQ'}, {value: 'mcq', text: 'MCQ'}, {value: 'general', text: 'General'}, {value: 'relative', text: 'Relative'}, {value: 'partial', text: 'Partial'}],
                  question_status: [{value: 'examable', text: 'Examable'}, {value: 'lecturable', text: 'Lecturable'}, {value: 'both', text: 'Both'}],
                  relative_text: [],
                  original_relative_text: [],
                  // field_data
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
                    question_text: [],
                    short_answer: [],
                    detail_answer: [],
                    question_tag: [],
                    question_length: "0",
                    relative_text: [],
                    relative_textarea: '',
                    relative_text_id: '',
                    relative_question_type: '',
                    reference_file: ''
                  }, 
                  editedItem: '',
                  rules: {
                    required: value => !!value || 'Required.',
                    dash: value => !!value && value.indexOf('-') > -1 || 'dash needed'
                  },
                  
                  // @accesories_data
                  valid: true,
                  valid_1: true,
                  success_alert: false,
                  error_alert: false,
                  loading: false,
                  // refine data
                  all_chapter: [],
                  // editor data
                  question_editor: '',
                  answer_editor: '',
                  relative_text_editor: '',
                  editor_detail: [],
                  question_notation: 'digit',
                  question_begin_text: '',
                  question_end_text: '।',
                  question_child_notation: 'unicode',
                  question_child_begin_text: '',
                  question_child_end_text: ')',
                  upload_type: '',
                  hide_relative: false,
                  saving: false,
                  relative_type: '',
                  qus_location: '',
                  ans_location: '',
                  relative_location: ''
   
               } //end return
        },
        // @router_permission
        beforeRouteEnter (to, from, next) {
           let p = window_admin_permissions.findIndex(x => x.name=='question_add');
           if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin' || window_question_add) {next()} else {next('/')}
        },
        // @load_method
        created(){
          this.admin_branch_list();
        },
   
        filters: {
           strip_tags: function (value) {
             let regex = /(<([^>]+)>)/ig;
             return value.replace(regex, "").substring(0, 15) + '...';
           }
        },
   
        methods:{
          // ========================================================
          // ==========item list method from database ===============
          admin_branch_list(){
              this.branch = window_branch;
              if (this.branch.length==1) {
                  this.dialogItem.branch_id = this.branch[0].id
                  this.admin_echelon_list(this.dialogItem.branch_id);
                  this.admin_subject_list()
                  // this.relative_text_list()
              }
              this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
          },
          admin_echelon_list() {                    
               this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
           },
          admin_subject_list(row_subject_id){
            Vue.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
                this.original_subject = response.data.subject_list_by_branch;
                // this.subject = response.data.subject_list_by_branch;
                // this.subject = this.subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
            });
          },
          chapter_list(){
             Vue.axios.get('/admin/request/chapter_list_by_class/'+ this.dialogItem.echelon_id).then(response => {
                 this.original_chapter = response.data.chapter_list_by_class;
             }); 
           },
           chapter_topic_list(){
             Vue.axios.get('/admin/request/chapter_topic_by_class/' + this.dialogItem.echelon_id).then(response => {
                 this.original_chapter_topic = response.data.chapter_topic_list;
             }); 
           },
           relative_text_list(){
             if (this.dialogItem.chapters.length>0) {
               Vue.axios.get('/admin/question/relative_text_list/' + this.dialogItem.chapters[0].value).then(response => {
                   this.original_relative_text = response.data.relative_text_list;
                   this.relative_text = this.original_relative_text.map(arr => { return { value: arr['id'], text: arr['relative_text'].substring(0, 30) } })
               }); 
             }
           },
           // ========================================================
           // ==========item modified method by filtering ============
           get_subject(){
              var echelon_id = this.dialogItem.echelon_id
              if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
              var subject = Object.assign([], this.original_subject);
              let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
              this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })  
              this.chapter_list()
              this.chapter_topic_list()        
           },
           get_chapter(){
              var chapter = Object.assign([], this.original_chapter);
              let filter_chapter = chapter.filter(x=>{return x.subject_id == this.dialogItem.subject_id})
              this.chapter = filter_chapter.map(arr => { return { value: arr['id'], text: arr['chapter_no'] + '- ' + arr['name'], chapter_no: arr['chapter_no'] } }).sort(function(a,b){
                   return parseInt(a.chapter_no) - parseInt(b.chapter_no)
              })
           },
           get_chapter_topic(){
             if (this.dialogItem.chapters.length>0) {this.$refs['combobox'].blur()}
             if (this.dialogItem.chapters.length>1) {this.chapter_topic=[]}
             else{
               if (this.dialogItem.chapters.length>0) {
                 var chapter_topic = Object.assign([], this.original_chapter_topic);
                 let filter_chapter_topic = chapter_topic.filter(x=>{return x.chapter_id == this.dialogItem.chapters[0].value})
                 this.chapter_topic = filter_chapter_topic.map(arr => { return { topic_no: arr['topic_no'], value: arr['id'], text: arr['name'], val: 0 } })
               }
             }
             this.relative_text_list()
           },
           get_relative_text(){
              var relative_text = Object.assign([], this.original_relative_text);
              if (this.dialogItem.chapters.length>0) {
                  let filter_relative_text = relative_text.filter(x=>{return x.chapter_id == this.dialogItem.chapters[0].value})
                  this.relative_text = filter_relative_text.map(arr => { return { value: arr['id'], text: arr['relative_text'].substring(0, 30) } })
              }
           },
           get_relative_type(){
             var index = this.original_relative_text.findIndex(x => x.id == this.dialogItem.relative_text_id)
             this.relative_type = this.original_relative_text[index].relative_question_type
           },
           hide_new_relative(){
             if (this.dialogItem.relative_text_id) {this.hide_relative = true}
             else {this.hide_relative = false}
             this.get_relative_type()
           },
           // ========================================================
           // ================== send to server method ================
           save(){
             this.refine_editor_text()
             if (!this.question_editor) {return alert('Editor is empty')}
             if (this.dialogItem.question_text.length==0) {return alert('No Question Found')}
             var x = $('.relative_text_editor .note-editable').length
             if (x>0 && this.dialogItem.question_type=='relative') {
               x = $('.relative_text_editor .note-editable').html()
               this.dialogItem.relative_textarea = x
             }
             var ok = 1;
             if (this.dialogItem.detail_answer.length==0 || this.dialogItem.detail_answer.length==0) {ok = 0; var confirmation = confirm('Short or detail answer not included. Do you wish to continue?')}
             if (confirmation) {
               ok = 1;
             }
             if (ok==1) {
               if (this.$refs.form.validate()) {
                 this.loading = true
                 // resolve topic
                 this.dialogItem.topics = []
                 var topics = this.chapter_topic
                 let filter_topic = topics.filter(x=>{return x.val == true})
                 this.dialogItem.topics = filter_topic.map(arr => { return arr['value']})
                 this.dialogItem.all_question_tag = this.question_tag
                 this.dialogItem.all_chapter = this.all_chapter
                 setTimeout(function(){
                     Vue.axios.post('/admin/question/question_add', this.dialogItem).then(response => {
                         // $('.user_nav').addClass('successful')
                         this.loading = false
                         this.saving = true;
                         this.relative_text_editor = ""
                         this.question_editor = ""
                         this.answer_editor = ""
                         this.editor_detail = []
                         $('.short_answer_div').html('')
                         this.relative_text_list()
                         $('.question_content').hide()
                         setTimeout(function () { this.saving = false; $('.question_content').fadeIn('slow') }.bind(this), 200)
                         this.short_answer_text = ""
                         $('.user_nav').addClass('successful')
                         setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                     }, error => {
                         // $('.user_nav').addClass('denied')
                         this.loading = false
                         $('.user_nav').addClass('denied')
                         setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000) 
                     });
                 }.bind(this), 500)
               }
             }
           },
           /* ========================================================
            ================== editor text refining ================*/
           // this method getting data of question text, relative text, short answer, detail answer, tag
           refine_editor_text(){
               if (this.dialogItem.question_length=="") {this.dialogItem.question_length=null; return this.valid_1 = false}
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
                   // get answer for mcq
                   var total_length = $(this).parent(".qus-div").children().length
                   var ans = $(this).parent(".qus-div").find(".qus-child:contains('#')").text().substring(1,2)
                   var ans_for_detail = 0
                   if (ans.length>0 && ans[0]!="") {short_answer_var.push({index: ind, text: ans}); ans_for_detail = ans}
                   // get answer with closet #
                   if (!ans && $(this).is(':contains("#")')) {
                     var ans1 = $(this).text().substring($(this).text().indexOf('#') + 1);
                     short_answer_var.push({index: ind, text: ans1})
                     ans_for_detail = ans1
                   }
                   if (!ans && !ans1) {
                     var ans2 = $(this).parent(".qus-div").find(".answer").html()
                     if (ans2) {short_answer_var.push({index: ind, text: ans2.replace('##','')}); ans_for_detail = ans2.replace('##', '').substring(0,2)}
                   }
                   // get tag
                   var tags = $(this).parent(".qus-div").text().split('[')
                   var tag = []
                   for (var i = 1; i < tags.length; i++) {
                     tag = tags[i].split(']')[0].split(',')
                   }
                   all_tag.push(tag)
                   // get chapter
                   var chapters = $(this).text().split('{*')
                   var chapter = []
                   for (var i = 1; i < chapters.length; i++) {
                     chapter = chapters[i].split('*}')[0].split(',')
                   }
                   all_chapter.push(chapter)
                   
                   // get element of per question
                   var elem = $(this).parent(".qus-div").clone().find('.answer').remove().end().html().replace('***', "").replace('#', "").replace(/\s*{.*?}\s*/g, '').replace(/\s*\[.*?\]\s*/g, '')
                   // question lenght
                   var qus = $(this).parent(".qus-div").find('.qus-child').length
                   var ans_length = 0
                   if (ans2) {ans_length = $(this).parent(".qus-div").find('.answer').length}
                   
                   $(this).unwrap();
                   // save data
                   question_text.push(elem)
                   // question detail of editor
                   var a = {'stem': total_elem.length - qus - ans_length, 'qus': qus, 'tag': tag.length, 'ans': ans_for_detail}
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
               // refine and reproduce short answer
               var texts = [];
               if (short_answer_var.length>0) { 
                 texts = short_answer_var
               }
               if (short_answer_var.length == 0 && $('.short_answer_div').html().length>0) {
                   var a = $('.short_answer_div').clone().children().remove().end().html()
                   var ind_plus = 0
                   if (a.trim().length>0) {texts.push({index: 0, text: '<div>' + a + '</div>'}); ind_plus = 1}
                   $('.short_answer_div div, .short_answer_div p').each(function(ind){
                      texts.push({index: ind + ind_plus, text: $(this)[0].outerHTML})
                   })
                   
               }
               if (question_text.length == 0) {this.short_answer_text = ""; $('.short_answer_div').html("")}
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
               setTimeout(function () { 
                   var index = this.editor_detail.findIndex(x => x.qus != this.dialogItem.question_length)
                   if (index>-1) {alert('Question no ' + (index + 1) + ' do not have ' + this.dialogItem.question_length + ' question childs')}
               }.bind(this), 500)
           },
           // goto certain question of question editor
           go_to_editor_question(key){
             $('html, body').animate({
                 scrollTop: $('.note-editable').offset().top - 200
             }, 500);
             var y = $('.note-editable')[0].scrollHeight
             $('.note-editable p.question').css('color', '#000')
             var x = $('.note-editable p.question').eq(key).css('color', 'red')
             var a = 0
             $('.note-editable p.question').each(function(index){
               var b = $(this).nextUntil("p.question").add($(this)).wrapAll('<div class="qus-div"></div>')
               a = a + $(this).parent(".qus-div").height();
               $(this).unwrap()
               if (index == key) {
                 return false;
               }
             })
             if (key==0) {
               $('.note-editable').animate({
                 scrollTop: 0
               }, 500);
             }
             else {
                 $('.note-editable').animate({
                     scrollTop: a - 150
                 }, 500);
             }
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
               // unwrap unblock element of short_answer
               $('.short_answer_div *:not(p,div,sup,sub,img,.symbol)').contents().unwrap();
               // replace element into p element
               $('.note-editable *:not(table,tbody,tr,td,sup,sub,img,.answer,.question,.qus-child,.answer,.relative,.rel-child,.symbol)').wrap('<p></p>').contents().unwrap()
   
               // unwrap element don't have text
               $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child,sup,sub,.rel-child)').each(function(){
                   var a = $(this).clone().children().remove().end().text()
                   if (a=='') {$(this).contents().unwrap()}
               })
               // unwrap element don't have text of short_answer
               $('.short_answer_div *:not(img,.symbol)').each(function(){
                   var a = $(this).clone().children().remove().end().text()
                   if (a=='') {$(this).contents().unwrap()}
               })
               // remove empty element
               $('.note-editable *:not(table,tbody,tr,td,img,.symbol,.question,.qus-child, .relative, .rel-child)').each(function() {
                   var $this = $(this);
                   if($this.html().length == 0 || $this.html().replace(/\s|&nbsp;/g, '').length == 0)
                   $this.remove();
               });
               // remove empty element of short answer
               $('.short_answer_div *:not(img,.symbol)').each(function() {
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
                           var char_list = ["ক) ", "খ) ", "গ) ", "ঘ) "]
                           var reg_1 = new RegExp("(ক|খ|গ|ঘ)(\\))", 'g');
                           regex_match = $(this).text().match(reg_1)
                       }
                       if (regex_match) {return html.replace(regex_match[0], char_list[index])}
                   })
                   $(this).unwrap()
               });
               
           },
           // upload image to editor
           image_upload(){
             var files = $('input[name=question_image]')[0].files
             var elem = $('.question_editor .note-editable')
             var id_prefix = 'qus'
             if (files.length==0) {
                 files = $('input[name=answer_image]')[0].files
                 elem = $('.answer_editor .note-editable')
                 id_prefix = 'ans'
             }
             if (files.length==0) {
                 files = $('input[name=relative_image]')[0].files
                 elem = $('.relative_text_editor .note-editable')
                 id_prefix = 'rel'
             }
   
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
             $('input[name=question_image]').val("")
             $('input[name=answer_image]').val("")
             $('input[name=relative_image]').val("")
           },
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
      left: 115px;
      top: -11px;
  }
  .editor_notation .notation_input {
      border: 1px solid #989898;
      width: 20px;
      position: relative;
      top: -6px;
      margin-right: 5px;
      text-align: center;
  }
  .editor_notation .v-radio {
      margin-right: 10px;
  }
  .editor_notation .v-input--radio-group {
      display: inline-block;
  }
  .editor_notation .v-input--radio-group label {
      font-size: 12px;
      top: 3px!important;
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