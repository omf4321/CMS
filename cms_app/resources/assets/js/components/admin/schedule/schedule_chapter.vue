<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
   <div class="schedule_chapter">
      <v-container fluid>
         <!-- @page_headline -->
         <h3 class="headline m-t-0">Manage Chapter</h3>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
               <v-toolbar flat color="grey lighten-2 pb-1">
                  <v-text-field
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                  ></v-text-field>
                  <!-- <v-text-field
                    class="ml-3"
                    v-model="search_filter"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                  ></v-text-field> -->
               </v-toolbar>
               <template>
                  <v-form ref="form" v-model="valid" lazy-validation>
                    <v-container>
                      <v-layout>
                        <v-flex class="pb-0 pt-0"
                          xs12
                          md2
                        >
                          <v-select
                             v-model="dialogItem.branch_id"
                             :disabled="branch.length==1"
                             :items="branch"
                             :rules="[v => !!v || 'Branch is required']"
                             label="Branch"
                             required
                             @change="get_item"
                             ></v-select>
                        </v-flex>

                        <v-flex class="pb-0 pt-0"
                          xs12
                          md2
                        >
                          <v-select
                             v-model="dialogItem.echelon_id"
                             :items="echelon"
                             :rules="[v => !!v || 'Class is required']"
                             label="Class"
                             @change="get_subject"
                             required
                             ></v-select>
                        </v-flex>

                        <v-flex class="pb-0 pt-0"
                          xs12
                          md2
                        >
                          <v-autocomplete
                             v-model="dialogItem.subject_id"
                             :items="subject"
                             :rules="[v => !!v || 'Subject is required']"
                             label="Subject"
                             required
                             @change="get_chapter"
                             ></v-autocomplete>
                        </v-flex>
                        <v-flex class="pb-0 pt-0"
                          xs12
                          md3
                        >
                          <v-text-field
                             v-model="dialogItem.name"
                             id="edit_chapter"
                             label="Chapter Name"
                             required
                             :rules="[v => !!v || 'Chapter is required']"
                             @keyup.enter="type=='edit' ? editedIndex : editedIndex=-1, save()"
                             ></v-text-field>
                        </v-flex>
                        <v-flex class="pb-0 pt-0"
                          xs12
                          md2
                        >
                           <v-text-field
                             id="edit_class_no"
                             v-model="dialogItem.chapter_no"
                             label="Chapter No"
                             type="number"
                             :rules="[v => !!v || 'Chapter No is required']"
                             required
                             @keyup.enter="type=='edit' ? editedIndex : editedIndex=-1, save()"
                             ></v-text-field>
                        </v-flex>
                        <v-flex class="pb-0 pt-0" md1>
                            <v-switch class="fs-14" v-model="swich" :label="swich?'Auto':''"></v-switch>
                        </v-flex>
                      </v-layout>
                      <v-layout>
                        <v-flex class="pb-0 pt-0"
                          xs12
                          md9
                        >
                          <template class="chapter_topic">
                            <v-textarea
                              v-model="dialogItem.chapter_topics"
                              hide-selected
                              label="Add Topics"
                              rows="1"
                              :clearable=true
                              :auto-grow = true
                            ></v-textarea>
                          </template>
                        </v-flex>
                        <v-flex class="pb-0 pt-0"
                          xs12
                          md2
                        >
                          <v-btn v-if="type=='add'" color="primary" :disabled="!valid || loading" :loading="loading" class="mb-2" @click="editedIndex=-1, save()">+ Add</v-btn>
                          <v-btn v-if="type=='edit'" color="teal" :disabled="!valid || loading" :loading="loading" class="mb-2 white--text" @click="save()">Edit</v-btn>
                        </v-flex>
                      </v-layout>
                    </v-container>
                  </v-form>
                </template>
               <!-- @list_table @list_header @list_column -->
               <v-data-table
                  :headers="headers" 
                  :items="rows"
                  :search="search"
                  :loading="data_load"
                  :pagination.sync="pagination"
                  class="elevation-1"
                  >
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                     <td class="text-xs-left">
                      <v-edit-dialog
                        :return-value.sync="props.item.name"
                        lazy
                        @save="save"
                        @open="editItem(props.item, 'chapter')"
                      >
                        {{ props.item.name }}
                        <template v-slot:input>
                            <v-text-field
                              :disabled="type=='edit'"
                              :clearable = true
                              v-model="editedItem.name"
                              label="Edit Chapter"
                              single-line
                            ></v-text-field>
                          </template>
                        </v-edit-dialog>
                     </td>
                     <td class="text-xs-left">{{ props.item.subjects.echelons.name }}</td>
                     <td class="text-xs-left">{{ props.item.subjects.name }}</td>
                     <td class="text-xs-left">
                      <v-edit-dialog
                        :return-value.sync="props.item.chapter_no"
                        lazy
                        @save="save"
                        @open="editItem(props.item, 'chapter_no')"
                      >
                        {{ props.item.chapter_no }}
                        <template v-slot:input>
                            <v-text-field
                              :disabled="type=='edit'"
                              v-model="editedItem.chapter_no"
                              label="Edit No"
                              type="number"
                              single-line
                            ></v-text-field>
                          </template>
                        </v-edit-dialog>
                     </td>
                     <td class="justify-center layout px-0">
                        <v-icon
                           small
                           class="mr-2"
                           @click="pick_chapter_data(props.item, 'pick')"
                           >
                           call_made
                        </v-icon>
                        <v-icon
                           small
                           class="mr-2"
                           @click="pick_chapter_data(props.item, 'edit')"
                           >
                           edit
                        </v-icon>
                        <!-- @delete_item -->
                        <v-icon
                           small
                           @click="chapter_delete(props.item)"
                           >
                           delete
                        </v-icon>
                     </td>
                  </template>
               </v-data-table>
            </div>
         </template>
      </v-container>
   </div>
</template>
<script>
   export default {
     data() {
             return {
               // @list_header_data
               headers: [
                 { text: 'Chapter Name', value: 'name' },
                 { text: 'Class', value: 'subjects.echelons.name' },
                 { text: 'Subject', value: 'subjects.name' },
                 { text: 'Chapter No', value: 'chapter_no' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               branch: [],
               echelon: [],
               subject: [],
               original_subject: [],
               chapter_topic: [],
               original_chapter_topic: [],
               // @list_search_data
               search: '',
               search_filter: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               dialogItem: {
                 id: '',
                 name: '',
                 chapter_no: '',
                 branch_id: '',
                 echelon_id: '',
                 subject_id: '',
                 subjects: {'id':'', 'name':'', branches: {'id':'', 'name':''}, echelons: {'id':'', 'name':''}},
                 created_at: '',
                 chapter_topics: []
               }, 
               editedItem: '',
               pagination: {
                  page: 1,
                  rowsPerPage: -1, // -1 for All",
                  sortBy: 'id'
               },     
               rules: {
                 required: value => !!value || 'Required.'
               },
               
               // @accesories_data
               valid: true,
               success_alert: false,
               error_alert: false,
               loading: false,
               data_load: false,
               type: 'add',
               swich: false

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='schedule');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
     methods:{
       // @add_item_method
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.dialogItem.branch_id = this.branch[0].id
               this.admin_echelon_list(this.dialogItem.branch_id);
               this.admin_subject_list()
           }
           this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
       },
       admin_echelon_list() {                    
            this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
       },
       // @add_item_method
       get_item(){
          if (this.branch.length>1) {
              this.admin_echelon_list(this.dialogItem.branch_id);
          }
       },
       // @list_method
       admin_subject_list(row_subject_id){
         this.axios.get('/admin/request/subject_list_by_branch/'+this.dialogItem.branch_id).then(response => {
             this.original_subject = response.data.subject_list_by_branch;
             this.subject = response.data.subject_list_by_branch;
             this.subject = this.subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
         });
       },
       chapter_list(){
         this.data_load = true;
         this.axios.get('/admin/request/chapter_list_by_subject/' + this.dialogItem.subject_id).then(response => {
             this.rows = response.data.chapter_list_by_subject;
             this.data_load = false;
         }); 
       },
       chapter_topic_list(){
         this.axios.get('/admin/request/chapter_topic_by_subject/' + this.dialogItem.subject_id).then(response => {
             this.original_chapter_topic = response.data.chapter_topic_by_subject;
         }); 
       },
       get_subject(){
          var echelon_id = this.dialogItem.echelon_id
          if (this.dialogItem.echelon_id == 8) {echelon_id = 7}
          var subject = Object.assign([], this.original_subject);
          let filter_subject = subject.filter(x=>{return x.echelon_id == echelon_id})
          this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
       },
       get_chapter(){
          this.chapter_list()
          this.chapter_topic_list()
       },
       get_chapter_topic(){
          var chapter_topic = Object.assign([], this.original_chapter_topic);
          let filter_chapter_topic = chapter_topic.filter(x=>{return x.chapter_id == this.dialogItem.id})
          this.dialogItem.chapter_topics = filter_chapter_topic.map(arr => { return arr['topic_no'] ? arr['topic_no'] + '-' + arr['name']: arr['name'] }).join('\r\n')
       },
       // @add_item_method
       chapter_add(){
          if (this.$refs.form.validate() && this.loading==false) {
            this.loading = true;
            // textarea text to array
	          var texts = []
            if (!this.dialogItem.chapter_topics) {this.dialogItem.chapter_topics = []}
            if (this.dialogItem.chapter_topics.length == 0) {this.dialogItem.chapter_topics = ""}
            var lines = this.dialogItem.chapter_topics.split(/\n/);
            for (var i=0; i < lines.length; i++) {
                // only non whitespace character.
                if (/\S/.test(lines[i])) {
                  if (lines[i].search("-")==-1){return alert("Write topic name with topic number (dash) topic name. Example: 1-Articles")}
                  var a = lines[i].split('-')
                  if (a.length>2) {return alert("You cannot use multiple dash in one topic")}
                  texts.push($.trim(lines[i]));
                }
            }
            if(texts.length>0){this.dialogItem.chapter_topics = texts} else {this.dialogItem.chapter_topics = []}
            this.axios.post('/admin/schedule/chapter_add', this.dialogItem).then(response => {
                var new_dialogItem = Object.assign({}, this.dialogItem);
                this.rows.push(response.data.chapter)
                for (var i = 0; i < response.data.topic.length; i++) {
                  this.original_chapter_topic.push(response.data.topic[i])
                }
                this.pagination.descending = !this.pagination.descending
                // this.pagination.sortBy = this.headers[index].value

                $('.user_nav').addClass('successful')
                this.loading=false;
                document.getElementById("edit_chapter").select()
                if (this.swich) {
                    if (parseFloat(this.dialogItem.chapter_no) % 1 != 0) {parseFloat(this.dialogItem.chapter_no) + 0.1}
                    else {this.dialogItem.chapter_no = parseInt(this.dialogItem.chapter_no) + 1}
                }
                this.dialogItem.chapter_topics = []
                setTimeout(function () { this.loading = false }.bind(this), 1000)
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
            });
          }
       },
       // @edit_item_method
       chapter_edit(){
          this.loading=true;
          if (this.dialogItem.chapter_topics == "") {this.editedItem.chapter_topics = []}
          this.axios.post('/admin/schedule/chapter_edit/' + this.editedId, this.editedItem).then(response => {
              $('.user_nav').addClass('successful')
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
              this.loading=false;
              this.type = 'add';
              this.chapter_list()
              this.chapter_topic_list()
              this.dialogItem.chapter_topics = ""
              this.dialogItem.name = ""
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.loading=false;   
          });
       },
       pick_chapter_data(item, type){
            this.dialogItem = Object.assign({}, item);
            this.dialogItem.branch_id = parseInt(item.subjects.echelons.branch_id)
            this.dialogItem.echelon_id = parseInt(item.subjects.echelons.id)
            this.dialogItem.subject_id = parseInt(item.subject_id)
            this.get_subject()
            
            // this.dialogItem.chapter_topics = []
            this.type = 'add'
            if (type=='edit') {
              this.type='edit'
              this.get_chapter_topic()
              this.editedIndex = this.rows.findIndex(x => x.id==item.id);
              this.editedId = item.id;
            }
            $("html, body").animate({ scrollTop: 0 }, 600);
            setTimeout(function () { document.getElementById("edit_chapter").select() }.bind(this), 1000)
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            if (this.type=='edit') {
                this.editedItem = Object.assign({}, this.dialogItem);
                // textarea text to array
                var texts = []
                if (!this.editedItem.chapter_topics) {this.editedItem.chapter_topics = ""}
                var lines = this.editedItem.chapter_topics.split(/\n/);
                for (var i=0; i < lines.length; i++) {
                    // only non whitespace character.
                    if (/\S/.test(lines[i])) {
                      if (lines[i].search("-")==-1){return alert("Write topic name with topic number (dash) topic name. Example: 1-Articles")}
                      var a = lines[i].split('-')
                      if (a.length>2) {return alert("You cannot use multiple dash in one topic")}
                      texts.push($.trim(lines[i]));
                    }
                }
                if(texts.length>0){this.editedItem.chapter_topics = texts} else {this.editedItem.chapter_topics = []}
            }
            this.chapter_edit()
          } else {
            this.chapter_add()
          }
       },
       get_branch(){
          if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
          }
       },
       // @delete_item_method
       chapter_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/schedule/chapter_delete/'+item.id).then(response => {
                this.rows.splice(index, 1)
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
         }
       },
       // @edit_item_method
       // open dialog
       editItem (item, element) {
         this.editedIndex = this.rows.findIndex(x => x.id == item.id);
         this.editedId = this.rows[this.editedIndex].id;
         this.editedItem = Object.assign({}, item);
         this.editedItem.subject_id = parseInt(item.subject_id)
         this.dialogItem.chapter_topics = []
         if (element=='subject') {
            this.dialogItem.echelon_id = item.subjects.echelons.id
            this.get_subject()
         }
       },
       // @add_item_method_dialog
       close () {
         this.dialog = false
         this.$refs.form.reset()
       },
       // @add_item_method_close_dialog
       clear(){
         this.$refs.form.reset()
       }
     }
   }
</script>


<style>
  .v-alert{
      position: absolute;
      z-index: 1;
      right: 10px;
      top: 10px;
      width: 300px;
      height: 50px;
  }
  .v-text-field label, .v-text-field input {
    font-size: 14px;
  }
  .schedule_chapter .chapter_topic textarea{
    max-height: 200px;
    overflow: auto;
    font-size: 14px;
  }
  #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
</style>