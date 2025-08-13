<template>
    <div>
        <v-container fluid>
            <h3 class="headline">Bank Transaction</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="bank_transaction_form" v-model="valid" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <v-autocomplete v-model="dialogItem.bank_account_id" :items="bank_account" label="Account"></v-autocomplete>  
                                    </div>
                                    <div class="col-lg-4">
                                        <v-text-field v-model="dialogItem.name" :rules="[v => !!v || 'required']" label="Transaction Name"></v-text-field>  
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field v-model="dialogItem.amount" label="Amount" :rules="[v => !!v || 'required']" type="number"></v-text-field>
                                    </div>
                                </div>
                                <div>
                                    <label class="radio-inline fw-13">
                                        <input type="radio" name="optradio" value="deposit" v-model="dialogItem.status">Deposit
                                    </label>
                                    <label class="radio-inline fw-13">
                                        <input type="radio" name="optradio" value="withdrawal" v-model="dialogItem.status">Withdrawal
                                    </label>
                                </div>
                                <div class="m-t-10">
                                    <v-btn small color="success" @click="add_bank_transaction">Add</v-btn>
                                </div>
                            </v-form>
                            <hr v-if="transaction_list.length">
                            <div class="row" v-if="transaction_list.length">
                                <div class="col-lg-6">
                                    <h4>Transaction Detail</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(transaction, t_i) in transaction_list">
                                            <td>{{transaction.name}}</td>
                                            <td :class="{'text-danger' : transaction.status == 'withdrawal'}">{{transaction.amount}}</td>
                                            <td>{{transaction.created_at | moment('D MMM YY')}}</td>
                                            <td><v-icon v-if="today(transaction.created_at)" class="fs-14" @click="delete_bank_transaction(transaction.id, t_i)">delete</v-icon></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
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
            salaries: [],
            message: '',
            valid: true,
            bank_account: [],
            rules: {
                required: value => !!value || 'Required.'
            },
            transaction_list: [],

        } //end return
    }
    , // @router_permission
    created() {
        this.get_bank_account()
    },
    methods: {
        get_bank_transaction(){         
            if (!this.dialogItem.bank_account_id) {return false}       
            this.dialog = true
            this.axios.get('/admin/transaction/get_bank_transaction/' + this.dialogItem.bank_account_id).then(response => {
                this.transaction_list = response.data.bank_transaction                
                this.dialog = false
            });
        },
        get_bank_account(){
            this.axios.get('/admin/transaction/get_bank_account').then(response => {
                this.bank_account = response.data.bank_account
                this.dialog = false
                this.dialogItem.bank_account_id = this.bank_account[0].id
                this.bank_account = this.bank_account.map(arr => {return {value: arr['id'], text: arr['name'] } });
                if (this.bank_account.length == 1) {this.get_bank_transaction()}
            });
        },
        add_bank_transaction(){           
            if(this.$refs.bank_transaction_form.validate()){
                if (!this.dialogItem.status) {return alert('Choose type of Transaction')}                
                this.dialog = true                
                this.axios.post('/admin/transaction/add_bank_transaction', this.dialogItem).then(response => {
                    this.transaction_list.push(response.data.bank_transaction)
                    this.$refs.bank_transaction_form.reset()
                    this.dialog = false
                    $('.user_nav').addClass('success')
                    setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
                }, error => {
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    alert(error.response.data.salary)
                });
            }
        },
        delete_bank_transaction(id, t_i){
            var con = confirm("Are you sure to delete?")
            if (!con) {return false}            
            this.dialog = true                
            this.axios.post('/admin/transaction/delete_bank_transaction/' + id).then(response => {
                this.transaction_list.splice(t_i, 1)
                this.dialog = false
                $('.user_nav').addClass('success')
                setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
            });
        },
        today(date){
            // Create date from input value
            var inputDate = new Date(date);

            // Get today's date
            var todaysDate = new Date();

            // call setHours to take the time out of the comparison
            if(inputDate.setHours(0,0,0,0) == todaysDate.setHours(0,0,0,0)) {
                return true
            }
            return false
        }
    }
}

</script>

<style>
    .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>