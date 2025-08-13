<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="teacher_payment">
        <v-container fluid>
            <!-- @page_headline -->
            <!-- <v-alert :value="is_online()" color="error" icon="warning" outline style="position:fixed; top: 0px; right: 200px; border: 0px solid!important; z-index: 4; " > No Internet Connection ! </v-alert> -->
            <h3 class="headline">Teacher Payment
            </h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="search_form" v-model="valid" lazy-validation>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0 m-r-10" md3>
                                        <v-autocomplete :rules="[v => !!v || 'required']" v-model="dialogItem.teacher_id" :items="teacher" label="Teacher"></v-autocomplete>   
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0 m-r-10" md2>
                                        <v-autocomplete v-model="dialogItem.month" :items="month" :rules="[v => !!v || 'Month is required']" label="Month"></v-autocomplete>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0 m-r-10" md2>
                                        <v-select v-model="dialogItem.year" :items="year" :rules="[v => !!v || 'Year is required']" label="Year"></v-select>
                                    </v-flex>                                    
                                    <v-flex class="pb-0 pt-0 m-r-5" md2>
                                        <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_teacher_payment" :disabled="!valid">Go</v-btn>
                                    </v-flex>
                                </v-layout>
                            </v-form>
                            <v-form ref="paid_form" lazy-validation>
                                <v-layout v-if="found_payment">
                                    <v-flex class="pb-0 pt-0 m-r-10" md2>
                                        <v-text-field :disabled="!is_admin()" :rules="[v => !!v || 'required']" v-model="dialogItem.amount" label="Amount"></v-text-field>  
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0 m-r-10" md2>
                                        <v-text-field :rules="[v => !!v || 'required']" v-model="dialogItem.paid" label="Paid"></v-text-field>  
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0 m-r-10" md2>
                                        <v-select :rules="[v => !!v || 'required']" v-model="dialogItem.status" :items="status" label="Status"></v-select>
                                    </v-flex>
                                    <v-flex class="pb-0 pt-0 m-r-5" md2>
                                        <v-btn small outline class="pos-rel m-l-5" style="top: 12px" @click="save_teacher_payment">Save</v-btn>
                                    </v-flex>
                                </v-layout>
                            </v-form>
                            <div style="color: #ff5722; margin-top: -10px" class="m-b-5" v-if="payment_msg">{{payment_msg}}</div>
                            <div style="color: #ff5722" class="m-b-5" v-if="message">{{message}}</div>
                            <div v-if="found_payment">
                                <v-flex class="pb-0 pt-0 m-r-10" md6 v-if="teacher_payment.length">
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Month</th>
                                            <!-- <th>Date To</th> -->
                                            <th>Status</th>
                                            <th>Invoice ID</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in teacher_payment">
                                            <td>{{payment.created_at | moment("D MMM YY")}}</td>
                                            <td>{{payment.amount}}</td>
                                            <td>{{payment.paid}}</td>
                                            <td>{{payment.month | moment("MMM YY")}}</td>
                                            <!-- <td>{{payment.date_to | moment("D MMM YY")}}</td> -->
                                            <td>{{payment.status}}</td>
                                            <td>{{payment.id}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </v-flex>
                            </div>                           
                            <div style="width: 80%; margin: auto; padding-top: 50px;" v-if="found_payment" id="print_content">
                                <h4 class="text-center">Payment Details <span class="float-right cur-p" @click="print"><v-icon>print</v-icon></span></h4>
                                <div v-if="print_check" class="fw-600 m-t-20 fs-15" style="margin-bottom: -10px">{{teacher_name}} - {{(dialogItem.year + '-' + dialogItem.month + '-01')  | moment("MMMM YY")}} <span class="fs-16"> ({{dialogItem.amount}}) </span></div>
                                <div v-if="due_amount" class="m-t-10" style="color: #ff5722">Previous Due: {{due_amount}}</div>
                                <hr>
                                <div v-if="class_payment.length">
                                    <h4 class="fs-14 fw-600">Class Payment ({{class_payment_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Type</th>
                                            <th>Batch Name</th>
                                            <th>Total Class</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in class_payment">
                                            <td>{{payment.new_batch_id ? 'Special' : 'General'}}</td>
                                            <td>{{payment.batch_name}}</td>
                                            <td>{{payment.count_class}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="invigilator_payment.length">
                                    <h4 class="fs-14 fw-600">Invigilation Payment ({{invigilator_payment_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Type</th>
                                            <th>Total Exam</th>
                                            <th>Total Minutes</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in invigilator_payment">
                                            <td>General</td>
                                            <td>{{payment.count_class}}</td>
                                            <td>{{payment.total_minutes}}</td>
                                            <td>{{payment.amount}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="script_payment.length">
                                    <h4 class="fs-14 fw-600">Script Payment ({{script_payment_total}})</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Type</th>
                                            <th>Mark Level</th>
                                            <th>Total Script</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="payment in script_payment">
                                            <td>General</td>
                                            <td>{{payment.mark}}</td>
                                            <td>{{payment.count_script}}</td>
                                            <td>{{payment.amount}}</td>
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
            dialogItem: {date_from: '', date_to: '', status: 'paid', year: new Date().getFullYear()},
            teacher_payment: [],
            message: '',
            valid: true,
            found_payment: false,
            invoice_month: [],
            print_check: false,
            due_amount: 0,
            invoice_item: {},
            teacher: [],
            invigilator_payment: [],
            script_payment: [],
            class_payment: [],
            due_amount: 0,
            class_payment_total: 0,
            invigilator_payment_total: 0,
            script_payment_total: 0,
            status: [{value: 'paid', text: 'Paid'}, {value: 'unpaid', text: 'Unpaid'}],
            rules: {
                required: value => !!value || 'Required.'
            },
            teacher_name: '',
            month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
            year: [{'value': 2019, 'text': 2019}, {'value': 2020, 'text': 2020}, {'value': 2021, 'text': 2021}],
            payment_msg: ''

        } //end return
    }
    , // @router_permission
    beforeRouteEnter (to, from, next) {
        let p=window_admin_permissions.findIndex(x=> x.name=='add_teacher_payment' || x.name == 'manage_teacher_payment');
        if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
            next()
        }
        else {
            next('/')
        }
    }, // @load_method
    created() {
        this.admin_branch_list()
    },
    methods: {
        admin_branch_list() {
            this.branch = window_branch;
            if (this.branch.length == 1) {
                this.dialogItem.branch_id = this.branch[0].id
                this.teacher_list();
            }
            this.branch = this.branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
        },
        get_teacher_payment(){
            if (this.$refs.search_form.validate()) {                
                this.dialog = true
                this.message = ''
                this.axios.post('/admin/payment/get_teacher_payment/' + this.dialogItem.teacher_id, this.dialogItem).then(response => {
                    this.dialog = false                
                    this.teacher_payment = response.data.teacher_payment;
                    this.invigilator_payment = response.data.invigilator_payment;
                    this.script_payment = response.data.script_payment;
                    this.class_payment = response.data.class_payment;
                    this.due_amount = response.data.due_amount;
                    this.class_payment_total = response.data.class_payment_total; 
                    this.invigilator_payment_total = response.data.invigilator_payment_total; 
                    this.script_payment_total = response.data.script_payment_total; 
                    if (this.class_payment.length || this.invigilator_payment.length || this.script_payment.length) {
                        this.found_payment = true
                    }
                    this.dialogItem.amount = response.data.final_total
                    this.teacher_name = this.teacher[this.teacher.findIndex(x => x.value == this.dialogItem.teacher_id)].text
                    this.payment_msg = response.data.payment_msg
                }, error => {
                    this.dialog = false
                    this.message = error.response.data.msg
                });
            }
        },
        save_teacher_payment(){
            if ((this.class_payment_total + this.invigilator_payment_total + this.script_payment_total) == 0 && this.due_amount > 0) {
                    return alert ('No payment found. Only has due amount. Select the month that has the due amount')
            }
            if ((this.class_payment_total + this.invigilator_payment_total + this.script_payment_total + this.due_amount) == 0) {
                    return alert ('No payment found.')
            }
            if (this.dialogItem.amount<this.dialogItem.paid) {return alert('Paid is greater than amount')}
            if(this.$refs.paid_form.validate()){
                this.message = ''
                this.dialog = true                
                this.axios.post('/admin/payment/save_teacher_payment', this.dialogItem).then(response => {
                    this.$refs.paid_form.reset()
                    this.get_teacher_payment()
                    $('.user_nav').addClass('success')
                    setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
                }, error => {
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    this.message = error.response.data.msg
                });
            }
        },
        teacher_list(){
            this.axios.get('/admin/request/teacher_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                this.teacher = response.data.teacher_list_by_branch;
                this.teacher = this.teacher.map(arr => { return { value: arr['id'], text: arr['name'] } })
            }); 
        },  
        change_date(){
            this.dialogItem.show_date = this.$moment(this.dialogItem.date).format('DD MMM YY')
            this.dialogItem.show_next_payment_date = this.$moment(this.dialogItem.next_payment_date).format('DD MMM YY');
        },
        print(){
            this.print_check = true
            setTimeout(function() {
                $('#print_content').printThis({
                  importCSS: true,
                  loadCSS: ["/css/print_portrait.css"]
                });           
            }.bind(this), 200)
        },
        get_prev_print_item(item){
            this.dialogItem.receipt_id = item.id;
            var due_amount = item.amount - (this.teacher_payment.monthly_fee * item.month_count)
            var paid_month = this.$moment(item.month)    
            // invoice item
            this.invoice_item.status = item.status
            this.invoice_item.receipt_id = item.id
            this.invoice_item.date = item.date
            this.invoice_item.next_payment_date = item.next_payment_date
            this.invoice_item.amount = item.amount
            this.invoice_item.paid= item.paid   
            this.print = true     
            this.print_invoice(paid_month, this.teacher_payment.monthly_fee, due_amount, item.month_count, 'old_invoice')
        },
        change_month(){
            var month = this.dialogItem.month
            var year = this.dialogItem.year
            var c_month = this.dialogItem.current_month
            var c_year = this.dialogItem.current_year
            var changed_date = year + '-' + month + '-01';
            var current_date = c_year + '-' + c_month + '-01';
            if (this.teacher_payment.payment_status == 'advance') {
                var y = this.teacher_payment.last_month_of_pay
                current_date = this.$moment(y).add(1, 'M')                
            }
            var x = this.$moment(changed_date).diff(this.$moment(current_date), 'months', true)
            if (x>=0) {
                var new_amount = this.teacher_payment.monthly_fee * Math.round(x)
                this.dialogItem.amount = this.teacher_payment.amount + new_amount
                this.dialogItem.show_next_payment_date = this.$moment(changed_date).add(1, 'M').format('DD MMM YY')
            }
            else {
                var x = this.dialogItem.current_month + 0
                var y = this.dialogItem.current_year + 0
                var z = this.teacher_payment.monthly_fee + 0
                // for remount month and year
                this.teacher_payment.monthly_fee = 0
                setTimeout(function() {
                    this.teacher_payment.monthly_fee = z
                    this.dialogItem.month = x
                    this.dialogItem.year = y;
                    this.dialogItem.amount = this.teacher_payment.amount
                    this.dialogItem.show_next_payment_date = this.$moment(current_date).add(1, 'M').format('DD MMM YY')
                }.bind(this), 200)
            }
            this.$forceUpdate()
        }
    }
}

</script>

<style>
    .teacher_payment .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .teacher_payment .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>