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
         <h3 class="headline">Manage Admin Role</h3>
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
                  <v-toolbar-title>Admin Role List</v-toolbar-title>
                  <v-spacer></v-spacer>
                  <v-text-field
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                  ></v-text-field>
                  <!-- @add_item_dialog -->
                  
                  <v-dialog 
                  v-model="dialog" 
                  persistent 
                  fullscreen
                  hide-overlay
                  transition="dialog-bottom-transition"
                  scrollable
                  >
                    <!-- @add_item_button -->
                     <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, dialogItem.editable=1">New Role</v-btn>
                     <v-card>
                        <v-card tile>
                          <v-toolbar card dark color="primary">
                            <v-btn icon dark @click.native="dialog = false">
                              <v-icon>close</v-icon>
                            </v-btn>
                            <v-toolbar-title>{{formTitle}}</v-toolbar-title>
                            <v-spacer></v-spacer>
                            <v-toolbar-items>
                              <v-btn dark flat 
                              :disabled="!valid || loading"
                              :loading="loading"
                              @click="save"
                              >Save</v-btn>
                            </v-toolbar-items>
                          </v-toolbar>
                          <v-card-text>
                           <v-container grid-list-md>
                              <!-- @add_item_field -->
                              <template>
                                 <v-form ref="form" v-model="valid" lazy-validation>
                                    <v-text-field
                                       :disabled="dialogItem.editable==0"
                                       v-model="dialogItem.name"
                                       label="Name"
                                       :rules="[rules.required]"
                                       required
                                       ></v-text-field>
                                    <!-- @add_item_submit -->

                                    <v-list>
                                        <v-layout row wrap>
                                          <v-flex md3
                                          v-for="(permission, j) in permissions"
                                          :key="j"
                                          >
                                          <v-list-item 
                                          @click="add_permission(permission)"
                                          >
                                            <v-list-item-action>
                                              <v-checkbox 
                                              :input-value=checkbox_check(permission.id)
                                              ></v-checkbox>
                                            </v-list-item-action>
                                            <v-list-item-content >
                                              <v-list-item-title style="font-weight: 500">{{permission.name.replace('_', ' ')}}</v-list-item-title>
                                              <v-list-item-sub-title>{{permission.description?permission.description.toLowerCase():''}}</v-list-item-sub-title>
                                            </v-list-item-content>
                                          </v-list-item>
                                          </v-flex>
                                        </v-layout>
                                    </v-list>
                                 </v-form>
                              </template>
                           </v-container>
                        </v-card-text>
                          
                      </v-card tile>
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
                     <td class="text-xs-left">{{ props.item.guard_name }}</td>
                     <td class="text-xs-left">{{ props.item.created_at }}</td>
                     <td class="justify-center layout px-0">
                        <v-icon
                           small
                           class="mr-2"
                           @click="admin_permissions_by_role(props.item)"
                           >
                           edit
                        </v-icon>
                        <!-- @delete_item -->
                        <v-icon
                           small
                           @click="admin_user_delete(props.item)"
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
                 { text: 'Guard', value: 'guard_name' },
                 { text: 'Created At', value: 'created_at' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               // @list_search_data
               search: '',

               // @add_item_field_data =====start
               permissions: [], //@edit_item_data
               selected_permission: [], 
               check_value: 0,
               dialog: false,
               editedIndex: -1,
               editedId: null,
               dialogItem: {
                 name: '',
                 permission: [],
                 editable: 1
               },      
               rules: {
                 required: value => !!value || 'Required.',
                 min: value => !!value && value.length >= 6 || 'Min 8 characters',
                 passwordMatch: value => value == this.dialogItem.password || 'The password you entered don\'t match',
                 emailRules: value => {
                   const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                   return pattern.test(value) || 'Invalid e-mail.'
                 }
               },
               show1: false,
               show2: false, 
               valid: true, 
               // @add_item_field_data =====end    

               // @accesories_data
               success_alert: false,
               error_alert: false,
               loading: false,
               data_load: false

            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let role = window_admin_role
        if (role=="admin" || role=='superadmin') {next()}
        else {next('/')}
     },
     // @load_method
     created(){
       this.admin_role_list();
       this.admin_permission_list()
     },
     
     computed: {
       formTitle () {
         return this.editedIndex === -1 ? 'New Role' : 'Edit Role'
       }
     },
   
     watch: {
       dialog (val) {
         val || this.close()
       }
     },
   
     methods:{
       // @list_method
       admin_role_list(){
         this.axios.get('/admin/request/admin_role_list').then(response => {
             this.rows = response.data.admin_role_list;
         }); 
       },
       // @add_item_method
       admin_permission_list(){
         this.data_load = true;
         this.axios.get('/admin/request/admin_permission_list').then(response => {
             this.permissions = response.data.admin_permission_list;
             this.data_load = false
         });
       },
       admin_permissions_by_role(item){
         this.axios.get('/admin/request/admin_permissions_by_role/'+item.id).then(response => {
             this.selected_permission = response.data.admin_permissions_by_role;
             this.editedIndex = this.rows.findIndex(x => x.id==item.id);
             // this.dialogItem = Object.assign({}, item)
             this.editedId = this.rows[this.editedIndex].id;
             this.dialogItem.name = this.rows[this.editedIndex].name;
             this.dialogItem.editable = this.rows[this.editedIndex].editable;
             this.dialog = true
         });
       },
       // @add_item_method
       admin_role_add(){
          if (this.$refs.form.validate()) {
            this.loading=true;
            for (var i = 0; i < this.selected_permission.length; i++) {
               this.dialogItem.permission.push(this.selected_permission[i].id)
            }
            this.axios.post('/admin/request/admin_role_add', this.dialogItem).then(response => {
                this.admin_role_list();
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
       admin_role_edit(){
          this.loading=true;
          for (var i = 0; i < this.selected_permission.length; i++) {
               this.dialogItem.permission.push(this.selected_permission[i].id)
          }
          this.axios.post('/admin/request/admin_role_edit/'+this.editedId, this.dialogItem).then(response => {
              this.admin_role_list()
              this.close()
              $('.user_nav').attr('style', 'background-color: #4caf50!important')
              setTimeout(function () { $('.user_nav').attr('style', 'background-color: #ffc107!important') }.bind(this), 3000)
              this.loading=false;
          }, error => {
              $('.user_nav').addClass('denied')
              $('.user_nav').attr('style', 'background-color: #f44336!important')
              setTimeout(function () { $('.user_nav').attr('style', 'background-color: #ffc107!important') }.bind(this), 3000)    
              this.loading=false;   
          });
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            this.admin_role_edit()
          } else {
            this.admin_role_add()
          }
       },
       // @delete_item_method
       admin_user_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/request/admin_role_delete/'+item.id).then(response => {
                this.rows.splice(index, 1)
                $('.user_nav').addClass('successful')
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
         }
       },
       checkbox_check(id){
          var index = this.selected_permission.findIndex(x => x.id == id);
          if (index==-1) {
                return false
          }
          else {
              return true
          }
       },
       add_permission(permission){
          var index = this.selected_permission.findIndex(x => x.id == permission.id);
          if (index==-1) {
                this.selected_permission.push(permission)
          }
          else {
              if (index>-1 ) {this.selected_permission.splice(index, 1)}
          }
          console.log(this.selected_permission)
       },
       // @add_item_method_dialog
       close () {
         this.dialog = false
         this.$refs.form.reset()
         this.selected_permission = []
       },
     }
   }
</script>


<style>
  
</style>