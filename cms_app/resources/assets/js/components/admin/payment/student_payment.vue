<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="student_payment">
        <v-container fluid>
            <!-- @page_headline -->
            <!-- <v-alert :value="is_online()" color="error" icon="warning" outline style="position:fixed; top: 0px; right: 200px; border: 0px solid!important; z-index: 4; " > No Internet Connection ! </v-alert> -->
            <h3 class="headline">Student Payment
            </h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-layout>
                                <v-flex class="pb-0 pt-0 m-r-10" md6>
                                    <v-text-field v-on:keyup.enter="get_student_payment" v-model="dialogItem.reg_no" label="Reg No" required></v-text-field>
                                </v-flex>
                                <v-flex class="pb-0 pt-0 m-r-5" md2>
                                    <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_student_payment">Go</v-btn>
                                </v-flex>
                                <v-flex class="pb-0 pt-0 m-r-10" md2>
                                    <v-select v-model="dialogItem.student_status" :items="student_status" :rules="[v => !!v || 'Status is required']"></v-select>
                                </v-flex>
                            </v-layout>
                            <div style="color: #ff5722" class="m-b-5" v-if="message">{{message}}</div>
                            <div v-if="found_student">
                                <div style="color: #607D8B"> <span class="fw-600">{{student_payment.name}}</span> - {{student_payment.batches.echelons.name}} - {{student_payment.batches.name}} - {{student_payment.monthly_fee ? 'Monthly' : 'Coursely'}} ({{student_payment.monthly_fee ? student_payment.monthly_fee : student_payment.course_fee}})</div>
                                <v-flex class="pb-0 pt-0 m-r-10" md6 v-if="student_payment.payments.length">
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Paid</th>
                                            <th v-if="student_payment.monthly_fee">Month</th>
                                            <th>Status</th>
                                            <th>Next Pay Date</th>
                                            <th>Invoice ID</th>
                                            <th>Print</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in student_payment.payments">
                                            <td>{{payment.date | moment("D MMM YY")}}</td>
                                            <td>{{payment.paid}}</td>
                                            <td v-if="student_payment.monthly_fee">{{payment.month | moment("MMM YY")}}</td>
                                            <td :class="{'text-success': payment.status =='paid', 'text-danger': payment.status != 'paid', 'fw-600': true}">{{payment.status.toUpperCase()}}</td>
                                            <td>{{payment.next_payment_date | moment("D MMM YY")}}</td>
                                            <td>{{payment.id}}</td>
                                            <td><v-icon class="cur-p" @click="get_prev_print_item(payment)">print</v-icon></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </v-flex>
                            </div>
                            <hr class="m-t-15 m-b-5">
                            <v-form ref="form" v-model="valid" lazy-validation>
                                <div v-if="found_student">
                                    <v-layout>
                                        <v-flex class="pb-0 pt-0 m-r-10" md2>
                                            <v-text-field :disabled="!is_admin()" type="number" v-model="dialogItem.amount" label="Amount"></v-text-field>
                                        </v-flex>
                                        <v-flex class="pb-0 pt-0 m-r-10" md2>
                                            <v-text-field type="number" v-model="dialogItem.paid" :rules="[v => !!v || 'Paid is required']" label="Paid" required></v-text-field>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout >
                                        <v-flex v-if="student_payment.monthly_fee" class="pb-0 pt-0 m-r-10" md2>
                                            <v-autocomplete @change="change_month" v-model="dialogItem.month" :items="month" :rules="[v => !!v || 'Month is required']" label="Month of Pay"></v-autocomplete>
                                        </v-flex>
                                        <v-flex v-if="student_payment.monthly_fee" class="pb-0 pt-0 m-r-10" md2>
                                            <v-select @change="change_month" v-model="dialogItem.year" :items="year" :rules="[v => !!v || 'Year is required']" label="Year"></v-select>
                                        </v-flex>
                                    </v-layout>
                                    <v-layout>
                                        <v-flex class="pb-0 pt-0 m-r-10" md2>
                                            <v-dialog full-width lazy ref="date_ref_1" width="290px">
                                                <v-text-field label="Next Payment Date" readonly slot="activator" :value="dialogItem.show_next_payment_date"></v-text-field>
                                                <v-date-picker ref="picker_1" @change="change_date" v-model="dialogItem.next_payment_date"></v-date-picker>
                                            </v-dialog>
                                        </v-flex>
                                        <v-flex class="pb-0 pt-0 m-r-10" md2>
                                            <v-select v-model="dialogItem.status" :items="status" :rules="[v => !!v || 'Status is required']" label="Status"></v-select>
                                        </v-flex>
                                    </v-layout>
                                    <hr class="m-t-10 m-b-10">
                                    <v-layout>
                                        <v-btn color="success" @click="save_student_payment">Submit</v-btn>
                                        <v-checkbox v-model="dialogItem.sms_check" label="SMS" style="max-width: 100px"></v-checkbox>
                                        <v-checkbox v-model="print_check" label="Print"></v-checkbox>
                                    </v-layout>
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
        <template v-if="print">
            <div class="print" style="width: 600px; margin: auto; text-aling:center;" id="print_content">
                <v-container>
                    <img src="/image/logo.png" style="display: block; margin: auto; height: 30px;">
                    <div class="text-center fs-20 m-t-10">Thanks for being with us</div>
                    <!-- <hr> -->
                    <!-- <hr class="m-b-5 m-t-5"> -->
                    <table class="table borderless fs-13 m-t-10">                                    
                        <tbody>
                            <tr>
                                <td class="text-left fs-14 fw-600">Payment Invoice</td>
                                <td class="text-right fs-14">Status: <span class="fw-600">{{invoice_item.status.toUpperCase()}}</span></td>
                            </tr>
                            <tr>
                                <td class="text-left">
                                    <div class="m-b-5">{{student_payment.name}}</div>
                                    <div>{{student_payment.batches.echelons.name}} - {{student_payment.batches.name}}</div>
                                </td>
                                <td class="text-right">
                                    <div class="m-b-5">Reg No: {{student_payment.reg_no}}</div>
                                    <div>Student ID: {{student_payment.id}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-left">
                                    <div class="m-b-20">Receipt No: {{invoice_item.receipt_id}}</div>
                                    <div class="m-b-10" v-for="x in invoice_month">Payment of {{x.month}}</div>
                                    <div v-if="student_payment.course_fee">Remaining Amount</div>
                                </td>
                                <td class="text-right">
                                    <div class="m-b-20">{{invoice_item.date | moment('DD MMM YYYY')}}</div>
                                    <div class="m-b-10" v-for="x in invoice_month">BDT {{x.amount}}</div>
                                    <div v-if="student_payment.course_fee">BDT {{student_payment.amount}}</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right fw-600 pos-rel">
                                    <div class="float-left">Next Payment Date: {{invoice_item.next_payment_date | moment('DD MMM YYYY')}}</div>
                                    <div class="m-b-8 float-right">Total</div>
                                    <div class="m-b-8 float-none" style="clear: both">Paid</div>
                                    <div v-if="invoice_item.amount - invoice_item.paid>0">Due</div>
                                    <div class="fs-11 m-t-15 pos-a fw-500" style="top: 20px;">Authorised By BT-GEC</div>
                                </td>
                                <td class="text-right fw-600">
                                    <div class="m-b-8">BDT {{invoice_item.amount}}</div>
                                    <div class="m-b-8">BDT {{invoice_item.paid}}</div>
                                    <div v-if="invoice_item.amount - invoice_item.paid>0">BDT {{invoice_item.amount - invoice_item.paid}}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>                    
                </v-container>
            </div>
        </template>
    </div>
</template>

<script> export default {
    data() {
        return {
            dialog: false,
            dialogItem: {student_status: 'present', sms_check: false},
            month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
            status: [{'value': 'paid', 'text': 'Paid'}, {'value': 'unpaid', 'text': 'Unpaid'}],
            student_status: [{'value': 'present', 'text': 'Present'}, {'value': 'droped', 'text': 'Droped'}, {'value': 'inactive', 'text': 'Inactive'}],
            year: [{'value': 2019, 'text': 2019}, {'value': 2020, 'text': 2020}, {'value': 2021, 'text': 2021}],
            student_payment: '',
            message: '',
            valid: true,
            found_student: false,
            print: false,
            invoice_month: [],
            print_check: true,
            due_amount: 0,
            invoice_item: {}
        } //end return
    }
    , // @router_permission
    beforeRouteEnter (to, from, next) {
        if (window_latest_unpaid.overdue == 'true') {
            next('/billing_invoice')
        }
        let p=window_admin_permissions.findIndex(x=> x.name=='add_payment' || x.name == 'manage_payment');
        if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
            next()
        }
        else {
            next('/')
        }
    }, // @load_method
    created() {
        
    },
    methods: {
        get_student_payment(){
            this.dialog = true
            this.message = ''
            this.student_payment = {batches: {echelons: ''}}
            this.found_student=false
            this.axios.get('/admin/payment/get_student_payment/' + this.dialogItem.reg_no + '/' + this.dialogItem.student_status).then(response => {
                this.student_payment = response.data.student_payment;                
                this.dialogItem.student_status = 'present'
                this.dialogItem.payment_status = this.student_payment.payment_status
                this.dialogItem.id = this.student_payment.id
                this.dialogItem.invoice_id = this.student_payment.invoice_id
                this.dialogItem.payment_month_remain = this.student_payment.payment_month_remain
                this.dialogItem.status = 'paid'
                this.dialogItem.amount = this.student_payment.amount
                this.dialogItem.paid = this.student_payment.paid
                this.dialogItem.month = this.student_payment.month_of_pay
                this.dialogItem.year = this.student_payment.year
                this.dialogItem.current_month = this.student_payment.month_of_pay
                this.dialogItem.current_year = this.student_payment.year
                this.dialogItem.show_date = this.$moment(new Date()).format('DD MMM YY')
                this.dialogItem.show_next_payment_date = this.$moment(this.student_payment.next_payment_date).format('DD MMM YY');
                this.dialog = false
                if (this.student_payment) {this.found_student=true}
                if (this.student_payment.payment_status == 'no_record') {this.message="New Admission."}
                if (this.student_payment.payment_status == 'advance') {
                    this.message="Already Paid this Month."
                    this.dialogItem.payment_month_remain = 0
                }
                if (this.student_payment.payment_status == 'current_due') {
                    this.message = "Its a due payment."
                    this.dialogItem.payment_month_remain = 1
                }
                if (this.student_payment.payment_status == 'has_unpaid_invoice') {this.message = "He/She has an unpaid invoice."}
                if (this.student_payment.payment_status == 'unpaid_multiple') {
                    this.message="Unpaid for " + this.student_payment.payment_month_remain + " months"
                }
                if (this.student_payment.payment_status == 'paid') {
                    this.message = "He/She already paid"
                }
                this.$forceUpdate()
            }, error => {
                this.dialog = false
                this.message = error.response.data.msg
            });
        },
        save_student_payment(){
            if (this.student_payment.payment_status == 'paid') {return alert('He/She already paid')}
            if (this.dialogItem.amount<this.dialogItem.paid) {return alert('Paid is greater than amount')}
            var last_month = this.student_payment.last_month_of_pay
            var paid_month = this.$moment(this.dialogItem.year + '-' + this.dialogItem.month + '-01')
            var x = paid_month.diff(last_month, 'months', true)
            if (x <= 0 && this.student_payment.monthly_fee>0 && this.student_payment.payment_status != 'current_due' && this.student_payment.payment_status != 'no_record') {return alert("He/she already paid for this month")}
            if(this.$refs.form.validate()){
                this.dialog = true                
                this.axios.post('/admin/payment/save_student_payment', this.dialogItem).then(response => {
                    this.message = ''
                    this.dialogItem.receipt_id = response.data.id;
                    // invoice item
                    this.invoice_item.status = this.dialogItem.status
                    this.invoice_item.receipt_id = response.data.id;
                    this.invoice_item.date = this.dialogItem.show_date
                    this.invoice_item.next_payment_date = this.dialogItem.show_next_payment_date
                    this.invoice_item.amount = this.dialogItem.amount
                    this.invoice_item.paid= this.dialogItem.paid
                    var due_amount = this.dialogItem.amount - (this.student_payment.monthly_fee * response.data.month_count)
                    if (this.print_check) {this.print = true}
                    this.print_invoice(paid_month, this.student_payment.monthly_fee, due_amount, response.data.month_count, 'new_invoice')
                }, error => {
                    this.dialog = false
                    this.print = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {
                    $('.user_nav').removeClass('denied')
                    }.bind(this), 3000)
                });
            }
        },
        change_date(){
            this.dialogItem.show_date = this.$moment(this.dialogItem.date).format('DD MMM YY')
            this.dialogItem.show_next_payment_date = this.$moment(this.dialogItem.next_payment_date).format('DD MMM YY');
        },
        print_invoice(paid_month, amount, due_amount, month_count, invoice_type){   
            this.invoice_month = []            
            for (var i = 0; i < month_count; i++) {
                var x = paid_month.subtract(i == 0 ? 0 : 1, 'months');
                var object = {month: x.format('MMMM YY'), amount: amount, id: i + 1}               
                if (i == month_count - 1) {
                     var object = {month: x.format('MMMM YY'), amount: this.student_payment.monthly_fee + due_amount, id: i + 1}
                }
                this.invoice_month.push(object)
            }
            this.invoice_month = this.invoice_month.sort(function(a, b){
                return b.id - a.id
            })
            this.dialog = false
            $('.user_nav').addClass('successful')
            if (this.print_check || invoice_type =='old_invoice') {
                setTimeout(function() {
                    $('#print_content').printThis({
                      importCSS: true,
                      loadCSS: ["/css/print_portrait.css"]
                    });
                }.bind(this), 500)
                setTimeout(function() {
                    this.found_student = invoice_type == 'new_invoice' ? false : true
                    if(invoice_type == 'new_invoice') {this.dialogItem.reg_no = ''}
                    $('.user_nav').removeClass('successful');
                    this.print = false
                }.bind(this), 3000)
            }
            else {
                this.found_student = invoice_type == 'new_invoice' ? false : true
                this.dialogItem.reg_no = ''
                setTimeout(function() {
                    $('.user_nav').removeClass('successful');
                }.bind(this), 3000)
            }
        },
        get_prev_print_item(item){
            this.dialogItem.receipt_id = item.id;
            var due_amount = item.amount - (this.student_payment.monthly_fee * item.month_count)
            var paid_month = this.$moment(item.month)    
            // invoice item
            this.invoice_item.status = item.status
            this.invoice_item.receipt_id = item.id
            this.invoice_item.date = item.date
            this.invoice_item.next_payment_date = item.next_payment_date
            this.invoice_item.amount = item.amount
            this.invoice_item.paid= item.paid   
            this.print = true     
            this.print_invoice(paid_month, this.student_payment.monthly_fee, due_amount, item.month_count, 'old_invoice')
        },
        change_month(){
            var month = this.dialogItem.month
            var year = this.dialogItem.year
            var c_month = this.dialogItem.current_month
            var c_year = this.dialogItem.current_year
            var changed_date = year + '-' + month + '-01';
            var current_date = c_year + '-' + c_month + '-01';
            if (this.student_payment.payment_status == 'advance') {
                var y = this.student_payment.last_month_of_pay
                current_date = this.$moment(y).add(1, 'M')                
            }
            var x = this.$moment(changed_date).diff(this.$moment(current_date), 'months', true)
            if (x>=0) {
                var new_amount = this.student_payment.monthly_fee * Math.round(x)
                this.dialogItem.amount = this.student_payment.amount + new_amount
                this.dialogItem.show_next_payment_date = this.$moment(changed_date).add(1, 'M').format('DD MMM YY')
            }
            else {
                var x = this.dialogItem.current_month + 0
                var y = this.dialogItem.current_year + 0
                var z = this.student_payment.monthly_fee + 0
                // for remount month and year
                this.student_payment.monthly_fee = 0
                setTimeout(function() {
                    this.student_payment.monthly_fee = z
                    this.dialogItem.month = x
                    this.dialogItem.year = y;
                    this.dialogItem.amount = this.student_payment.amount
                    this.dialogItem.show_next_payment_date = this.$moment(current_date).add(1, 'M').format('DD MMM YY')
                }.bind(this), 200)
            }
            this.$forceUpdate()
        }
    }
}

</script>

<style>
    .student_payment .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .student_payment .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>