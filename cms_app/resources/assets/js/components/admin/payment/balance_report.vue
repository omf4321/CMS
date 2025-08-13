<template>
    <div class="balance_report">
        <v-container fluid>
            <h3 class="headline">Balance Report</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="balance_report_form" v-model="valid" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <v-dialog full-width lazy ref="date_ref_1" width="290px">
                                            <v-text-field label="Date From" :rules="[v => !!v || 'required']" readonly slot="activator" :value="dialogItem.date_from"></v-text-field>
                                            <v-date-picker ref="picker_1" v-model="dialogItem.date_from"></v-date-picker>
                                        </v-dialog>  
                                    </div>
                                    <div class="col-lg-3">
                                        <v-dialog full-width lazy ref="date_ref_2" width="290px">
                                            <v-text-field :rules="[v => !!v || 'required']" label="Date To" readonly slot="activator" :value="dialogItem.date_to"></v-text-field>
                                            <v-date-picker ref="picker_2" v-model="dialogItem.date_to"></v-date-picker>
                                        </v-dialog>  
                                    </div>
                                    <div class="col-lg-2">
                                         <v-autocomplete class="m-r-15" :clearable=true :items="day" label="Day" v-model="dialogItem.day"></v-autocomplete> 
                                    </div>
                                    <div class="col-lg-2">
                                        <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_balance_report" :disabled="!valid">Go</v-btn>
                                    </div>
                                </div>
                            </v-form>
                            <!-- student payment -->
                            <div class="row" v-if="student_payment.length">
                                <div class="col-lg-6">
                                    <h4 class="fs-14">Student Payment ({{student_payment_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in student_payment">    
                                            <td>{{payment.date | moment('D MMM YY')}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
                            <div class="row" v-if="teacher_payment.length">
                                <div class="col-lg-6">
                                    <h4 class="fs-14">Teacher Payment ({{teacher_payment_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in teacher_payment">    
                                            <td>{{payment.month | moment('D MMM YY')}} - {{payment.teachers.name}}</td>
                                            <td>{{payment.paid}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row" v-if="salary.length">
                                <div class="col-lg-6">
                                    <h4 class="fs-14">Salary ({{salary_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in salary">    
                                            <td>{{payment.date | moment('D MMM YY')}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row" v-if="transaction_expense.length">
                                <div class="col-lg-6">
                                    <h4 class="fs-14">Expense ({{transaction_expense_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in transaction_expense">    
                                            <td>{{payment.date | moment('D MMM YY')}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row" v-if="transaction_income.length">
                                <div class="col-lg-6">
                                    <h4 class="fs-14">Other Income ({{transaction_income_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in transaction_income">    
                                            <td>{{payment.date | moment('D MMM YY')}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">                                
                                    <h4 class="fs-14 fw-600">Final Total</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="col-xs-6">
                                            <div>Last Day Hand Cash</div>
                                            <div>Student Payment</div>
                                            <div>Teacher Payment</div>
                                            <div>Salary</div>
                                            <div>Expense</div>
                                            <div>Other Income</div>
                                            <div>Bank Widivdrawal</div>
                                            <div>Bank Deposit</div>
                                            <div>Fund Deposit</div>
                                        </div>
                                        <div class="col-xs-6">
                                            <div>{{yesterday_hand_cash}}</div>  
                                            <div>{{student_payment_total}}</div>
                                            <div>{{teacher_payment_total}}</div>
                                            <div>{{salary_total}}</div>
                                            <div>{{transaction_expense_total}}</div>
                                            <div>{{transaction_income_total}}</div>
                                            <div>{{bank_withdrawal}}</div>
                                            <div>{{bank_deposit}}</div>
                                            <div>{{fund_transaction}}</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="fw-600 fs-16">Hand Cash: {{final_total}}</div>
                                </div>
                            </div>
                            <hr>
                            <div v-if="dialogItem.signable && sign">
                                <h4 class="fw-600 fs-14">Admin Signature</h4>
                                <div>I checked above transaction</div>
                                <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="balance_signature">Signature</v-btn>
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
            dialogItem: {signable: false},
            salaries: [],
            message: '',
            valid: true,
            bank_account: [],
            rules: {
                required: value => !!value || 'Required.'
            },
            student_payment: [],
            student_payment_total: 0,
            teacher_payment: [],
            teacher_payment_total: 0,
            salary: [],
            salary_total: 0,
            transaction_expense: [],
            transaction_income: [],
            transaction_expense_total: 0,
            transaction_income_total: 0,
            yesterday_hand_cash: 0,
            bank_withdrawal: 0,
            bank_deposit: 0,
            fund_transaction: 0,
            final_total: 0,
            sign: false,
            day: [{'value': 'today', 'text': 'Today'}, {'value': 'yesterday', 'text': 'Yesterday'}]

        } //end return
    }
    , // @router_permission
    created() {
        // this.get_bank_account()
    },
    beforeRouteEnter (to, from, next) {
        let p=window_admin_permissions.findIndex(x=> x.name == 'manage_transaction');
        if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
            next()
        }
        else {
            next('/')
        }
    },
    methods: {
        get_balance_report(){
            this.dialog = true
            this.dialogItem.signable = false
            this.axios.post('/admin/transaction/get_balance_report', this.dialogItem).then(response => {
                this.student_payment = response.data.student_payment                
                this.teacher_payment = response.data.teacher_payment
                this.salary = response.data.salary
                this.transaction_expense = response.data.transaction_expense
                this.transaction_income = response.data.transaction_income
                this.sign = response.data.sign == 'true' ? true : false;

                this.student_payment_total = 0
                this.teacher_payment_total = 0
                this.salary_total = 0
                this.transaction_expense_total = 0
                this.transaction_income_total = 0
                this.yesterday_hand_cash = parseInt(response.data.yesterday_hand_cash)
                this.bank_withdrawal = parseInt(response.data.bank_withdrawal)
                this.bank_deposit = parseInt(response.data.bank_deposit)
                this.fund_transaction = parseInt(response.data.fund_transaction)
                this.final_total = 0

                if (this.dialogItem.day) {this.dialogItem.signable = true}

                for (var i = 0; i < this.student_payment.length; i++) {
                    this.student_payment_total += parseInt(this.student_payment[i].amount)
                }
                for (var i = 0; i < this.teacher_payment.length; i++) {
                    this.teacher_payment_total += this.teacher_payment[i].paid
                }
                for (var i = 0; i < this.salary.length; i++) {
                    this.salary_total += parseInt(this.salary[i].amount)
                }
                for (var i = 0; i < this.transaction_expense.length; i++) {
                    this.transaction_expense_total += parseInt(this.transaction_expense[i].amount)
                }
                for (var i = 0; i < this.transaction_income.length; i++) {
                    this.transaction_income_total += parseInt(this.transaction_income[i].amount)
                }
                this.final_total = (this.yesterday_hand_cash + this.student_payment_total + this.transaction_income_total + this.bank_withdrawal) - (this.teacher_payment_total + this.salary_total + this.transaction_expense_total + this.bank_deposit + this.fund_transaction)
                this.dialog = false
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)                
            });
        },
        balance_signature(){
            if (!this.dialogItem.day) {return false}                
            this.dialog = true 
            this.dialogItem.final_total = this.final_total               
            this.axios.post('/admin/transaction/balance_signature', this.dialogItem).then(response => {
                this.dialog = false
                this.sign = false
                $('.user_nav').addClass('success')
                setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                alert(error.response.data.salary)
            });
        },
    }
}

</script>

<style>
    .balance_report .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .balance_report .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>