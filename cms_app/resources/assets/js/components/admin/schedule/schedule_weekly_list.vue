<template>
    <div>
        <v-container fluid="">
            <!-- @page_headline -->
            <h3 class="headline m-t-0">
                Manage Weekly Schedule List
            </h3>
            <v-divider class="my-3">
            </v-divider>
            <template>
                <div>
                    <v-toolbar color="grey lighten-2 pb-1" flat="">
                        <v-text-field append-icon="search" hide-details="" label="Search" single-line="" v-model="search">
                        </v-text-field>
                        <!-- @add_item_dialog -->
                        <v-dialog max-width="500px" persistent="" v-model="dialog">
                            <!-- @add_item_button -->
                            <v-btn @click="editedIndex=-1, get_branch()" class="mb-2" color="primary" dark="" slot="activator">
                                New List
                            </v-btn>
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="headline">
                                        {{ formTitle }}
                                    </span>
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md="">
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form lazy-validation="" ref="form" v-model="valid">
                                                <v-text-field :rules="[rules.required]" label="Name" required="" v-model="dialogItem.name">
                                                </v-text-field>
                                                <div class="m-b-10">This is identification name of a weekly schedule. Please use a unique name for later use.</div>
                                                <v-select :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" @change="get_item" label="Branch" required="" v-model="dialogItem.branch_id">
                                                </v-select>
                                                <v-select :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class" required="" v-model="dialogItem.echelon_id">
                                                </v-select>
                                                <!-- @add_item_submit -->
                                                <v-btn :disabled="!valid || loading" :loading="loading" @click="save">
                                                    submit
                                                </v-btn>
                                                <v-btn @click="close">
                                                    close
                                                </v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :loading="data_load" :search="search" class="elevation-1" hide-actions="">
                        <v-progress-linear color="blue" indeterminate="" slot="progress">
                        </v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>
                                {{ props.item.id }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.name }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.echelons.branches.name }}
                            </td>
                            <td class="text-xs-left">
                                {{ props.item.echelons.name }}
                            </td>
                            <td class="justify-center layout px-0">
                                <v-icon @click="editItem(props.item)" class="mr-2" small="">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon @click="weekly_schedule_list_delete(props.item)" small="">
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
               { text: 'Branch', value: 'echelons.branches.name' },
               { text: 'Class', value: 'echelons.name' },
               { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               branch: [],
               echelon: [],
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
                bangla_text: '',
                created_at: ''
                },      
                rules: {
                  required: value => !!value || 'Required.'
                  },
                  
               // @accesories_data
               valid: true,
               loading: false,
               data_load: false

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
      
      computed: {
        formTitle () {
          return this.editedIndex === -1 ? 'New Weekly Schedule List' : 'Edit Weekly Schedule List'
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
            this.weekly_schedule_list();
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
       weekly_schedule_list(){
        this.data_load = true;
        this.axios.get('/admin/schedule/weekly_schedule_list/' + this.dialogItem.branch_id).then(response => {
          this.rows = response.data.weekly_schedule_list;
          this.data_load = false
          });
        },
       weekly_schedule_list_add(){
        if (this.$refs.form.validate()) {
          this.loading=true;
          this.axios.post('/admin/schedule/weekly_schedule_list_add', this.dialogItem).then(response => {
            this.weekly_schedule_list();
            this.close()
            this.loading = false;
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
       weekly_schedule_list_edit(){
        this.loading=true;
        this.axios.post('/admin/schedule/weekly_schedule_list_edit/' + this.editedId, this.dialogItem).then(response => {
          this.weekly_schedule_list()
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
          this.weekly_schedule_list_edit()
          } else {
            this.weekly_schedule_list_add()
          }
          },
          get_branch(){
            if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
            }
            },
       // @delete_item_method
       weekly_schedule_list_delete(item){
        var con = confirm("Want to delete?");
        if (con) {
          const index = this.rows.findIndex(x => x.id==item.id);
          this.axios.post('/admin/schedule/weekly_schedule_list_delete/'+item.id).then(response => {
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
        this.dialogItem.branch_id = item.echelons.branch_id
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