<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="biller_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Biller List</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-text-field :clearable=true class="m-r-10" v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.biller_detail }}</td>
                            <td class="text-xs-left">{{ props.item.accounts.name }}</td> 
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <v-icon small class="mr-2" @click="delete_biller_list(props.item)">
                                    delete
                                </v-icon>
                            </td>
                        </template>
                    </v-data-table>
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
        <v-dialog v-model="edit_dialog" persistent max-width="500px">
            <v-card>
                <v-card-title>
                    <!-- @add_item_title -->
                    <span class="headline">Edit Payment</span>
                </v-card-title>
                <v-card-text>
                    <v-container grid-list-md>
                        <!-- @add_item_field -->
                        <template>
                            <v-form ref="form" v-model="valid" lazy-validation>
                                <v-text-field :rules="[rules.required]" v-model="editedItem.name" label="Name"></v-text-field>
                                <v-text-field v-model="editedItem.biller_detail" label="Biller Detail"></v-text-field>
                                <v-btn :disabled="!valid" @click="edit_biller_list"> submit </v-btn>
                                <v-btn @click="close">close</v-btn>
                            </v-form>
                        </template>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    // @list_header_data
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Name', value: 'name'}, {text: 'Biller Detail', value: 'biller_detail'}, {text: 'Account', value: 'accounts.name'}, {text: 'Action', value: 'status', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],                    
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    
                    editedIndex: -1,
                    editedId: null,
                    editedItem: {},
                    dialogItem: {
                        from_date: '',
                        to_date: ''
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },

                    // @accesories_data
                    valid: true,                    
                    data_load: false,
                    edit_dialog: false,
                    dialog: false,
                    total_amount: 0,
                    category: []
                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                let p=window_admin_permissions.findIndex(x=> x.name=='add_transaction' || x.name == 'manage_transaction');
                if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
                    next()
                }
                else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.get_bill_list()
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Biller' : 'Edit Biller'
                }
            },

            watch: {
                dialog(val) {
                    val || this.close()
                }
            },

            methods: {
                // @add_item_method                
                get_bill_list() {
                    this.data_load = true;
                    this.axios.get('/admin/transaction/get_biller_list').then(response => {
                        this.rows = response.data.billers;
                        this.data_load = false                        
                    });
                },
                
                // @edit_item_method
                edit_biller_list() {
                    this.dialog = true;
                    this.axios.post('/admin/transaction/edit_biller_list/' + this.editedId, this.editedItem).then(response => {
                        this.rows[this.editedIndex].biller_detail = this.editedItem.biller_detail;
                        this.rows[this.editedIndex].name = this.editedItem.name;
                        this.close()
                        this.dialog = false;
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false;
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                delete_biller_list(item) {
                    var con = confirm("Are you sure?")
                    if(!con){return false}
                    this.dialog = true;
                    this.axios.post('/admin/transaction/delete_biller_list/' + item.id).then(response => {
                        var index = this.rows.findIndex(x => x.id == item.id)
                        this.rows.splice(index, 1)
                        this.close()
                        this.dialog = false;
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        this.dialog = false;
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    });
                },
                get_branch() {
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].value
                    }
                },
                // open dialog
                editItem(item) {
                    this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                    this.editedItem = Object.assign({}, item)
                    this.editedId = this.rows[this.editedIndex].id;
                    this.edit_dialog = true
                },
                // @add_item_method_dialog
                close() {
                    this.edit_dialog = false
                    this.$refs.form.reset()
                },
                // @add_item_method_close_dialog
                clear() {
                    this.$refs.form.reset()
                },
                change_from_date(){
                    this.dialogItem.show_from_date = this.$moment(this.dialogItem.from_date).format('DD MMM YY')
                },
                change_to_date(){
                    this.dialogItem.show_to_date = this.$moment(this.dialogItem.to_date).format('DD MMM YY');
                },
                change_due_date(){
                    this.editedItem.due_date = this.editedItem.show_due_date ? this.$moment(this.editedItem.show_due_date).format('YYYY-MM-DD') : this.editedItem.due_date;
                },
                change_date(){
                    this.editedItem.date = this.editedItem.show_date ? this.$moment(this.editedItem.show_date).format('YYYY-MM-DD') : this.editedItem.date
                    this.editedItem.next_payment_date = this.editedItem.show_next_payment_date ? this.$moment(this.editedItem.show_next_payment_date).format('YYYY-MM-DD') : this.editedItem.next_payment_date
                }
            }
    }
</script>

<style>

 .biller_list table tr td {
    font-size: 12px!important;
 }
</style>