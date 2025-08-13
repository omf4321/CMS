<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
   <div>
      <v-container fluid>
         <!-- @page_headline -->
         <h3 class="headline m-t-0">Manage Subject</h3>
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
                  <!-- @add_item_dialog -->
                  
                  <v-dialog v-model="dialog" persistent max-width="500px">
                    <!-- @add_item_button -->
                     <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, get_branch()">New Subject</v-btn>
                     <v-card>
                        <v-card-title>
                          <!-- @add_item_title -->
                           <span class="headline">{{ formTitle }}</span>
                        </v-card-title>
                        <v-card-text>
                           <v-container grid-list-md>
                              <!-- @add_item_field -->
                              <template>
                                 <v-form ref="form" v-model="valid" lazy-validation>
                                    <v-text-field
                                       v-model="dialogItem.name"
                                       label="Name"
                                       :rules="[rules.required]"
                                       required
                                       ></v-text-field>
                                    <v-select
                                       v-model="dialogItem.branch_id"
                                       :disabled="branch.length==1"
                                       :items="branch"
                                       :rules="[v => !!v || 'Branch is required']"
                                       label="Branch"
                                       required
                                       @change="get_item"
                                       ></v-select>
                                    <v-select
                                       v-model="dialogItem.echelon_id"
                                       :items="echelon"
                                       :rules="[v => !!v || 'Class is required']"
                                       label="Class"
                                       required
                                       ></v-select>
                                    <v-autocomplete
                                       v-model="dialogItem.teacher_id"
                                       :items="teacher"
                                       label="Teacher"
                                       ></v-autocomplete>
                                    <v-text-field
                                       v-model="dialogItem.bangla_text"
                                       label="Bangla Text"
                                       required
                                       ></v-text-field>
                                    <!-- @add_item_submit -->
                                    <v-btn
                                       :disabled="!valid || loading"
                                       :loading="loading"
                                       @click="save"
                                       >
                                       submit
                                    </v-btn>
                                    <v-btn @click="close">close</v-btn>
                                 </v-form>
                              </template>
                           </v-container>
                        </v-card-text>
                     </v-card>
                  </v-dialog>
               </v-toolbar>
               <!-- @list_table @list_header @list_column -->
               <v-data-table
                  v-if="!update"
                  :headers="headers" 
                  :items="rows"
                  :search="search"
                  :loading="data_load"
                  hide-actions
                  class="elevation-1"
                  >
                  <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                  <template slot="items" slot-scope="props">
                     <td>{{ props.item.id }}</td>
                     <td class="text-xs-left">{{ props.item.name }}</td>
                     <td class="text-xs-left">{{ props.item.branches.name }}</td>
                     <td class="text-xs-left">{{ props.item.echelons.name }}</td>
                     <td class="text-xs-left">{{ props.item.teachers ? props.item.teachers.name : '' }}</td>
                     <td class="text-xs-left">{{ props.item.bangla_text }}</td>
                     <td class="text-xs-left">{{ props.item.created_at }}</td>
                     <td class="justify-center layout px-0">
                        <v-icon
                           small
                           class="mr-2"
                           @click="editItem(props.item)"
                           >
                           edit
                        </v-icon>
                        <!-- @delete_item -->
                        <v-icon
                           small
                           @click="admin_subject_delete(props.item)"
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
                 { text: 'ID', align: 'left', value: 'id' },
                 { text: 'Name', value: 'name' },
                 { text: 'Branch', value: 'branches.name' },
                 { text: 'Class', value: 'echelons.name' },
                 { text: 'Teacher', value: 'teachers.name' },
                 { text: 'Description', value: 'description' },
                 { text: 'Created At', value: 'created_at' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               branch: [],
               echelon: [],
               teacher: [],
               // @list_search_data
               search: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               dialogItem: {
                 name: '',
                 branch_id: '',
                 echelon_id: '',
                 teacher_id: '',
                 bangla_text: '',
                 created_at: ''
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
               update: false

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='setting_subject');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
     
     computed: {
       formTitle () {
         return this.editedIndex === -1 ? 'New Subject' : 'Edit Subject'
       }
     },
   
     watch: {
       dialog (val) {
         val || this.close()
       }
     },
   
     methods:{
       // @add_item_method
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.dialogItem.branch_id = this.branch[0].id
               this.admin_echelon_list(this.dialogItem.branch_id);
               this.admin_subject_list();
               this.teacher_list();
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
       admin_subject_list(){
         this.data_load = true;
         this.axios.get('/admin/request/admin_subject_list').then(response => {
             this.rows = response.data.admin_subject_list;
             this.data_load = false
         });
       },
       teacher_list(){
         this.axios.get('/admin/request/teacher_list_by_branch/'+this.dialogItem.branch_id).then(response => {
             this.teacher = response.data.teacher_list_by_branch;
             this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
         }); 
       },
       // @add_item_method
       admin_subject_add(){
          if (this.$refs.form.validate()) {
            this.loading=true;
            this.axios.post('/admin/request/admin_subject_add', this.dialogItem).then(response => {
                this.admin_subject_list();
                this.close()
                this.loading=false;
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               this.loading=false; 
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
            });
          }
       },
       // @edit_item_method
       admin_subject_edit(){
          this.loading=true;
          this.axios.post('/admin/request/admin_subject_edit/'+this.editedId, this.dialogItem).then(response => {
              this.rows[this.editedIndex] = response.data.subject
              this.update = true
              setTimeout(function () { this.update = false }.bind(this), 200)
              this.close()
              this.loading=false;
              $('.user_nav').addClass('successful')
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
          }, error => {
              this.loading=false;   
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
          });
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            this.admin_subject_edit()
          } else {
            this.admin_subject_add()
          }
       },
       get_branch(){
          if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
          }
       },
       // @delete_item_method
       admin_subject_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/request/admin_subject_delete/'+item.id).then(response => {
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
       editItem (item) {
         this.editedIndex = this.rows.findIndex(x => x.id==item.id);
         this.dialogItem = Object.assign({}, item)
         this.editedId = this.rows[this.editedIndex].id;
         this.dialog = true
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