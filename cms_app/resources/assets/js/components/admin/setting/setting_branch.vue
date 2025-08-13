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
         <h3 class="headline">Manage Branch</h3>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
              <!-- @accesories_alert -->
              <v-alert
                :value="success_alert"
                transition="scale-transition"
                color="success"
                icon="check_circle"
                outline
              >
                Updated Successfully
              </v-alert>
              <!-- @accesories_alert -->
              <v-alert
                :value="error_alert"
                transition="scale-transition"
                color="danger"
                icon="warning"
                outline
              >
                An error has occured
              </v-alert>
               <v-toolbar flat color="white">
                  <!-- @list_title -->
                  <v-toolbar-title>Branch List</v-toolbar-title>
                  <v-spacer></v-spacer>
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
                     <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1">New Branch</v-btn>
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
                                    <v-text-field
                                       v-model="dialogItem.description"
                                       label="Description"
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
                     <td class="text-xs-left">{{ props.item.description }}</td>
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
                           @click="admin_branch_delete(props.item)"
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
                 { text: 'Description', value: 'description' },
                 { text: 'Created At', value: 'created_at' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               // @list_search_data
               search: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               dialogItem: {
                 name: '',
                 description: '',
               },      
               rules: {
                 required: value => !!value || 'Required.'
               },
               
               // @accesories_data
               valid: true,
               success_alert: false,
               error_alert: false,
               loading: false,
               data_load: false

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
       let role = window_admin_role
       if (role=='superadmin') {next()}
       else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list();
     },
     
     computed: {
       formTitle () {
         return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
       }
     },
   
     watch: {
       dialog (val) {
         val || this.close()
       }
     },
   
     methods:{
       // @list_method
       admin_branch_list(){
         this.data_load = true;
         this.axios.get('/admin/request/admin_branch_list').then(response => {
             this.rows = response.data.admin_branch_list;
             this.data_load = false
         });
       },
       // @add_item_method
       admin_branch_add(){
          if (this.$refs.form.validate()) {
            this.loading=true;
            this.dialogItem.admin_role_id = this.selected_role
            this.axios.post('/admin/request/admin_branch_add', this.dialogItem).then(response => {
                this.admin_branch_list();
                this.close()
                $('.user_nav').addClass('successful')
                this.loading=false;
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
               $('.user_nav').addClass('denied')
               setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
               this.loading=false; 
            });
          }
       },
       // @edit_item_method
       admin_branch_edit(){
          this.loading=true;
          this.axios.post('/admin/request/admin_branch_edit/'+this.editedId, this.dialogItem).then(response => {
              this.rows[this.editedIndex].name = this.dialogItem.name;
              this.rows[this.editedIndex].description = this.dialogItem.description;
              this.close()
              $('.user_nav').addClass('successful')
              setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
              this.loading=false;
          }, error => {
              $('.user_nav').addClass('denied')
              setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)  
              this.loading=false;   
          });
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            this.admin_branch_edit()
          } else {
            this.admin_branch_add()
          }
       },
       // @delete_item_method
       admin_branch_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/request/admin_branch_delete/'+item.id).then(response => {
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

