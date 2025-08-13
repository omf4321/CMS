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
         <h3 class="headline">Manage Schedule List</h3>
         <v-divider class="my-3"></v-divider>
         <template>
            <div>
               <v-toolbar flat color="white">
                  <v-text-field
                    v-model="search"
                    append-icon="search"
                    label="Search"
                    single-line
                    hide-details
                  ></v-text-field>
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
                     <td class="text-xs-left">{{ props.item.echelons.branches.name }}</td>
                     <td class="text-xs-left">{{ props.item.echelons.name }}</td>
                     <td class="text-xs-left">{{ props.item.batches ?  props.item.batches.name : ''}}</td>
                     <td class="text-xs-left">{{ props.item.date_from }}</td>
                     <td class="text-xs-left">{{ props.item.date_to }}</td>
                     <td class="justify-center layout px-0">
                        <router-link :to="{ name: 'schedule_view', params: { id: props.item.id }}">
                          <v-btn small color="primary"class="mr-2 mid-btn">
                             view
                          </v-btn>
                        </router-link>
                        <router-link :to="{ name: 'schedule_update', params: { id: props.item.id }}">
                          <v-btn small color="info"class="mr-2 mid-btn">
                             update topic
                          </v-btn>
                        </router-link>
                        <!-- @delete_item -->
                        <v-btn small class="mid-btn" color="warning"@click="schedule_list_delete(props.item)">
                           delete
                        </v-btn>
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
                 { text: 'Batch', value: 'batches.name' },
                 { text: 'Date From', value: 'date_from' },
                 { text: 'Date To', value: 'date_to' },
                 { text: 'Actions', value: 'name', sortable: false, align: 'center' }
               ],
               // @list_column_data
               rows: [],
               branch: [],
               // @list_search_data
               search: '',

               // @add_item_field_data =====start
               dialog: false,
               editedIndex: -1,
               editedId: null,
               dialogItem: {
                 name: '',
                 class_no: '',
                 branch_id: '',
                 description: '',
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
   
     methods:{
       // @add_item_method
       admin_branch_list(){
           this.branch = window_branch;
           if (this.branch.length==1) {
               this.dialogItem.branch_id = this.branch[0].id
               this.schedule_list();
           }
           this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
       },
       // @list_method
       schedule_list(){
         this.data_load = true;
         this.axios.get('/admin/schedule/schedule_list/' + this.dialogItem.branch_id).then(response => {
             this.rows = response.data.schedule_list;
             this.data_load = false
         });
       },
       // @add_item_method
       admin_echelon_add(){
          if (this.$refs.form.validate()) {
            this.loading=true;
            this.axios.post('/admin/request/admin_echelon_add', this.dialogItem).then(response => {
                this.schedule_list();
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
       admin_echelon_edit(){
          this.loading=true;
          this.axios.post('/admin/request/admin_echelon_edit/'+this.editedId, this.dialogItem).then(response => {
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
       get_branch(){
          if (this.branch.length==1) {
              this.dialogItem.branch_id=this.branch[0].value
          }
       },
       // @admin_add_edit
       save(){
          if (this.editedIndex > -1) {
            this.admin_echelon_edit()
          } else {
            this.admin_echelon_add()
          }
       },
       // @delete_item_method
       schedule_list_delete(item){
         var con = confirm("Want to delete?");
         if (con) {
            const index = this.rows.findIndex(x => x.id==item.id);
            this.axios.post('/admin/schedule/schedule_list_delete/'+item.id).then(response => {
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


<style>
  .v-alert{
      position: absolute;
      z-index: 1;
      right: 10px;
      top: 10px;
      width: 300px;
      height: 50px;
  }
</style>