<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="transaction_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Transaction List <span class="float-right">{{total_amount}}</span></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-text-field :clearable=true class="m-r-10" v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="category.length==0" :items="category" label="Category" v-model="dialogItem.category_id">
                            </v-autocomplete>
                            <v-dialog full-width class="m-r-10" lazy ref="date_ref" width="290px">
                                <v-text-field :clearable=true label="From" readonly slot="activator" :value="dialogItem.show_from_date"></v-text-field>
                                <v-date-picker ref="picker" @change="change_from_date" v-model="dialogItem.from_date"></v-date-picker>
                            </v-dialog>
                            <v-dialog class="m-r-10" full-width lazy ref="date_ref_1" width="290px">
                                <v-text-field :clearable=true label="To" readonly slot="activator" :value="dialogItem.show_to_date"></v-text-field>
                                <v-date-picker ref="picker_1" @change="change_to_date" v-model="dialogItem.to_date"></v-date-picker>
                            </v-dialog>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_transaction_list">Go</v-btn>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.detail }}</td>
                            <td class="text-xs-left">{{ props.item.categories.name }}</td>
                            <td class="text-xs-left">{{ props.item.bills ? props.item.bills.name : '' }}</td>
                            <td class="text-xs-left">{{ props.item.amount }}</td>
                            <td class="text-xs-left">{{ props.item.users.name }}</td>
                            <td class="text-xs-left">{{ props.item.created_at | moment('DD MMM YY') }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
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
                                <v-text-field disabled v-model="editedItem.amount" label="Amount" :rules="[rules.required]"></v-text-field>
                                <v-text-field v-model="editedItem.detail" label="detail"></v-text-field>
                                <v-autocomplete :rules="[v => !!v || 'required']" v-model="editedItem.category_id" :items="category" label="Category"></v-autocomplete>
                                <v-text-field v-model="editedItem.bill_id" label="Bill ID"></v-text-field>
                                <v-btn :disabled="!valid" @click="edit_transaction_list"> submit </v-btn>
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
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Detail', value: 'detail'}, {text: 'Category', value: 'categories.name'}, {text: 'Bill', value: 'bills.name'}, {text: 'Amount', value: 'amount'}, {text: 'Inputer', value: 'users.name'}, {text: 'Date', value: 'created_date'}, {text: 'Action', value: 'status', sortable: false, align: 'center'}],
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
                let p=window_admin_permissions.findIndex(x=> x.name == 'manage_transaction');
                if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
                    next()
                }
                else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.get_transaction_list()
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Subject' : 'Edit Subject'
                }
            },

            watch: {
                dialog(val) {
                    val || this.close()
                }
            },

            methods: {
                // @add_item_method                
                get_transaction_list() {
                    this.data_load = true;
                    this.axios.post('/admin/transaction/get_transaction_list', this.dialogItem).then(response => {
                        this.rows = response.data.transactions;
                        this.data_load = false
                        var total = 0
                        for ( var i = 0; i < this.rows.length; i++ ) {
                            total += this.rows[i].amount
                        }
                        this.total_amount = total
                        this.category = response.data.categories.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    });
                },
                
                // @edit_item_method
                edit_transaction_list() {
                    this.dialog = true;
                    this.axios.post('/admin/transaction/edit_transaction_list/' + this.editedId, this.editedItem).then(response => {
                        this.rows[this.editedIndex].detail = this.editedItem.detail;
                        this.rows[this.editedIndex].category_id = this.editedItem.category_id;
                        this.rows[this.editedIndex].categories.id = this.editedItem.category_id;
                        var index = this.category.findIndex(x => x.value == this.editedItem.category_id)
                        this.rows[this.editedIndex].categories.name = this.category[index].text
                        this.rows[this.editedIndex].bill_id = this.editedItem.bill_id;
                        this.rows[this.editedIndex].bills.id = this.editedItem.bill_id;
                        this.rows[this.editedIndex].bills.name = this.editedItem.bill_id; 
                        this.close()
                        this.dialog = false;
                        $('.user_nav').addClass('successful')
                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                    }, error => {
                        alert(error.response.data.msg)
                        this.dialog = false;
                        $('.user_nav').addClass('denied')
                        setTimeout(function() {
                            $('.user_nav').removeClass('denied')
                        }.bind(this), 3000)
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
                change_date(){
                    this.editedItem.date = this.editedItem.show_date ? this.$moment(this.editedItem.show_date).format('YYYY-MM-DD') : this.editedItem.date
                    this.editedItem.next_payment_date = this.editedItem.show_next_payment_date ? this.$moment(this.editedItem.show_next_payment_date).format('YYYY-MM-DD') : this.editedItem.next_payment_date
                }
            }
    }
</script>

<style>

 .transaction_list table tr td {
    font-size: 12px!important;
 }
</style>