<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="admin_transaction">
        <v-container fluid>
            <!-- @page_headline -->
            <!-- <v-alert :value="is_online()" color="error" icon="warning" outline style="position:fixed; top: 0px; right: 200px; border: 0px solid!important; z-index: 4; " > No Internet Connection ! </v-alert> -->
            <h3 class="headline">Transactions
            </h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="transaction_form" v-model="valid" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <v-text-field id="transaction_amount" :rules="[v => !!v || 'required']" v-model="dialogItem.transaction_amount" @keyup.enter="add_transaction" label="Amount with Detail"></v-text-field>  
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field v-model="dialogItem.bill_id" label="Bill ID"></v-text-field>
                                    </div> 
                                </div>
                                <div>
                                    <label :class="{'radio-inline': true, 'm-l-10' : true, 'm-t-10' : true, 'text-success': category.type == 'income', 'fs-13': true, 'fw-600': category.type == 'income'}" v-for="category in categories">
                                        <input type="radio" name="optradio" :value="category.id" v-model="dialogItem.category_id">{{category.name}}
                                    </label>
                                </div>
                                <div class="m-t-10">
                                    <v-btn small color="success" @click="add_transaction">Add Transaction</v-btn>
                                </div>
                            </v-form>
                            <hr v-if="transactions.length">
                            <div class="row" v-if="transactions.length">
                                <div class="col-lg-6">
                                    <h4>Todays Transaction ({{transaction_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Detail</th>
                                            <th>Amount</th>
                                            <th>Categories</th>
                                            <th>Bill ID</th>
                                            <th>Action</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(transaction, t_i) in transactions">
                                            <td>{{transaction.detail}}</td>
                                            <td :class="{'text-success':transaction.categories.type == 'income', 'fw-600':transaction.categories.type == 'income'}">{{transaction.amount}}</td>
                                            <td :class="{'text-success':transaction.categories.type == 'income', 'fw-600':transaction.categories.type == 'income'}">{{transaction.categories.name}}</td>
                                            <td>{{transaction.bill_id}}</td>
                                            <td><v-icon class="fs-14" @click="delete_transaction(transaction.id, t_i)">delete</v-icon></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
                            <hr v-if="bill_list.length">
                            <div class="row" v-if="bill_list.length">
                                <div class="col-lg-6">
                                    <h4>Bill Lists</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Due Date</th>
                                            <th>Biller</th>
                                            <th>ID</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="bill in bill_list">
                                            <td>{{bill.name}}</td>
                                            <td>{{bill.amount}}</td>
                                            <td>{{bill.paid}}</td>
                                            <td>{{bill.due_date | moment('D MMM YY')}}</td>
                                            <td>{{bill.billers.name}}</td>
                                            <td>{{bill.id}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
                            <hr>
                            <h4>Add Bills</h4>
                            <v-form ref="bill_form" v-model="valid_bill" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-2">
                                        <v-text-field  :rules="[v => !!v || 'required']" v-model="dialogItem.amount" label="Amount"></v-text-field>  
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field  :rules="[v => !!v || 'required']" v-model="dialogItem.name" label="Name"></v-text-field>  
                                    </div>
                                    <div class="col-lg-2">                                        
                                        <v-combobox v-model="dialogItem.biller_id" :items="biller" :rules="[v => !!v || 'required']" label="Biller" hint="Find from list or Create new one"></v-combobox>
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field v-model="dialogItem.biller_detail" label="Biller Detail"></v-text-field> 
                                    </div>                                    
                                    <div class="col-lg-2">
                                        <v-dialog full-width lazy ref="date_ref" width="290px">
                                            <v-text-field :rules="[v => !!v || 'required']" label="Due Date" readonly slot="activator" :value="dialogItem.due_date"></v-text-field>
                                            <v-date-picker ref="picker" v-model="dialogItem.due_date"></v-date-picker>
                                        </v-dialog>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="col-lg-10">
                                        <v-textarea rows="4" v-model="dialogItem.voucher_detail" label="Voucher Detail"></v-textarea>
                                    </div>
                                </div>
                                <div class="m-t-10">
                                    <v-btn small color="success" :disabled="!valid_bill" @click="add_bill">Add Bill</v-btn>
                                </div>
                            </v-form>
                        </v-container>
                    </template>
                </div>
            </template>
        </v-container>
        <!-- loader dialog -->
        <v-dialog v-model="dialog" hide-dialog persistent width="300">
            <v-card color="#009688" dark>
                <v-card-text>
                    Please Wait..
                    <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script> export default {
    data() {
        return {
            dialog: false,
            dialogItem: {},
            transactions: [],
            message: '',
            valid: true,
            valid_bill: true,
            found_payment: false,
            print_check: false,
            due_amount: 0,
            invoice_item: {},
            biller: [],
            status: [{value: 'paid', text: 'Paid'}, {value: 'unpaid', text: 'Unpaid'}],
            rules: {
                required: value => !!value || 'Required.'
            },
            teacher_name: '',
            bill_list: [],
            categories: [],
            transaction_total: 0

        } //end return
    }
    , // @router_permission
    created() {
        this.get_todays_transaction_list()
    },
    beforeRouteEnter (to, from, next) {
        let p=window_admin_permissions.findIndex(x=> x.name=='add_transaction' || x.name == 'manage_transaction');
        if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
            next()
        }
        else {
            next('/')
        }
    }, // @load_method
    methods: {
        admin_branch_list() {
            this.branch = window_branch;
            if (this.branch.length == 1) {
                this.dialogItem.branch_id = this.branch[0].id
                this.teacher_list();
            }
            this.branch = this.branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
        },
        get_todays_transaction_list(){                
            this.dialog = true
            this.axios.get('/admin/transaction/get_todays_transaction_list').then(response => {
                this.bill_list = response.data.bill_list
                this.categories = response.data.categories
                this.transactions = response.data.transactions
                this.biller = response.data.biller
                this.biller = this.biller.map(arr => {return {value: arr['id'], text: arr['name'] } })
                this.dialog = false
                this.dialogItem.account_id = response.data.accounts[0].id
                for (var i = 0; i < this.transactions.length; i++) {
                    this.transaction_total += this.transactions[i].amount
                }

            });
        },
        add_bill(){           
            if(this.$refs.bill_form.validate()){
                this.dialog = true                
                this.axios.post('/admin/transaction/add_bill', this.dialogItem).then(response => {
                    this.get_todays_transaction_list()
                    $('.user_nav').addClass('success')
                    setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
                }, error => {
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            }
        },
        add_transaction(){           
            if(this.$refs.transaction_form.validate()){
                this.dialogItem.detail = ''
                var tran_detail = this.dialogItem.transaction_amount.split(' ')
                if (isNaN(tran_detail[0])) {return alert("You must enter amount at first")}
                if (!this.dialogItem.category_id) {return alert("Please choose category")}
                this.dialogItem.transaction_amount = tran_detail[0]
                if (tran_detail.length>1) {
                    this.dialogItem.detail = ''
                    for (var i = 1; i < tran_detail.length; i++) {
                        this.dialogItem.detail = this.dialogItem.detail + ' ' + tran_detail[i]
                    }
                }
                this.dialogItem.type = this.categories[this.categories.findIndex(x => x.id == this.dialogItem.category_id)].type
                this.dialog = true                
                this.axios.post('/admin/transaction/add_transaction', this.dialogItem).then(response => {
                    this.transactions.push(response.data.transaction)
                    this.transaction_total += parseInt(this.dialogItem.transaction_amount)
                    this.dialogItem.transaction_amount = ''
                    document.getElementById("transaction_amount").focus();
                    this.dialog = false
                    $('.user_nav').addClass('success')
                    setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
                }, error => {
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            }
        },
        delete_transaction(id, t_i){
            var con = confirm("Are you sure to delete?")
            if (!con) {return false}
            this.dialog = true                
            this.axios.post('/admin/transaction/delete_transaction/' + id).then(response => {
                this.transaction_total -= parseInt(this.transactions[t_i].amount)
                this.transactions.splice(t_i, 1)
                this.dialog = false
                $('.user_nav').addClass('success')
                setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
            });
        },
    }
}

</script>

<style>
    .admin_transaction .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .admin_transaction .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>