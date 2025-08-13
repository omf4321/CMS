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
            <div style="position: fixed; bottom: 15px; right: 20px; z-index: 9">
                <div>
                  <v-chip class="m-r-15">Q: {{this.dialogItem.question_text.length}}</v-chip>
                  <v-chip>SA: {{this.dialogItem.short_answer.length}}</v-chip>
                </div>
                <v-btn class="float-right" color="success" @click="save" :disabled="!valid || loading" :loading="loading">Save</v-btn>
            </div>
         </div>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
               <template>
                  <div class="question_content">
                     <v-form ref="form" v-model="valid" lazy-validation>
                        <v-container>
                           <div class="row p-l-30 p-r-30">
                              <v-flex lg12 class="pb-0 pt-0">
                                <v-layout>
                                    <v-select class="m-r-15" v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" label="Branch"></v-select>
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
                              <v-flex lg12 class="pb-0 pt-0" v-if="!saving">
                                 <!-- question checking detail -->
                                 <div v-for="(editor, key) in editor_detail" @click="go_to_editor_question(key)" style="cursor: pointer; margin-bottom: 12px; display: inline-block; font-size: 12px"> <span class="mr-2" style="font-weight: 700">{{key + 1}}.</span>  <span class="m-r-5">Q ({{editor.qus}})</span> <span class="mr-3">A ({{editor.ans}})</span>
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
                                    <html-editor height="400" upload_type="question" :model.sync="question_editor"></html-editor>
                                    <div contenteditable="true" class="short_answer_div" v-html="short_answer_text" style="height: 200px; overflow: auto" data-placeholder="Answer"></div>
                                 </div>
                              </v-flex>
                           </div>
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
                  branch: [],
                  echelon: [],
                  subject: [],
                  original_subject: [],
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
                    question_mark: '',
                    question_text: [],
                    short_answer: [],
                    language: 'bangla',
                    branch_id: 'all_branch',
                    question_tag: [],
                    question_length: 4
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
                  // editor data
                  question_editor: '',
                  editor_detail: [],
                  saving: false,
                  qus_location: '',
                  language: [{value: 'bangla', text: 'Bangla'}, {value: 'english', text: 'English'}],
                  question_notation: 'digit',
                  question_begin_text: '',
                  question_end_text: '।',
                  question_child_notation: 'unicode',
                  question_child_begin_text: '',
                  question_child_end_text: ')',
                  question_subtag: [],
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
            admin_branch_list() {
                  this.branch = window_branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                  this.branch.unshift({value: 'all_branch', text: 'All Branch'})
                  this.get_question_tag()
                  this.get_online_exam_subject()
            },
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
            // ========================================================
            // ================== send to server method ================
           save(){
             
             this.refine_editor_text()
             if (!this.question_editor) {return alert('Editor is empty')}
             if (this.dialogItem.question_text.length==0) {return alert('No Question Found')}
             if (this.$refs.form.validate()) {
               if (this.dialogItem.short_answer.length != this.dialogItem.question_text.length) {
                return alert('Some question don\'t have answer')
               }
               this.loading = true
               setTimeout(function(){
                   this.axios.post('/admin/question/exam_question_add', this.dialogItem).then(response => {
                       // $('.user_nav').addClass('successful')
                       this.loading = false
                       this.saving = true;
                       this.question_editor = ""
                       this.editor_detail = []
                       $('.short_answer_div').html('')
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
                   var ans = $(this).parent(".qus-div").find(".qus-child:contains('#')").index()
                   var ans_for_detail = 0
                   if (ans>-1) {short_answer_var.push({index: ind, text: ans}); ans_for_detail = ans}
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
                   
                   // get element of per question
                   var elem = $(this).parent(".qus-div").clone().find('.answer').remove().end().html().replace('***', "").replace('#', "").replace(/\s*{.*?}\s*/g, '').replace(/\s*\[.*?\]\s*/g, '')
                   // question lenght
                   var qus = $(this).parent(".qus-div").find('.qus-child').length
                   var ans_length = 0
                   if (ans2) {ans_length = $(this).parent(".qus-div").find('.answer').length}
                   // options
                   var options = [];
                   $(this).parent(".qus-div").find(".qus-child").each(function(){
                      options.push($(this).clone().wrap('<div></div>').parent().html().replace('opt1: ', '').replace('opt2: ', '').replace('opt3: ', '').replace('opt4: ', '').replace('opt5: ', '').replace('qus-child last-p', 'qus-child').replace('#', ''))
                   })
                   $(this).unwrap(); //unwrap qus-div
                   var question_part = $(this).clone().find('.question').nextUntil(".qus-child").add($(this)).wrap('<div></div>').parent().html().replace('***', '')
                   $(this).unwrap() //unwrap last div wrapped
                   
                   question_text.push({'question_part': question_part, 'options': options})
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