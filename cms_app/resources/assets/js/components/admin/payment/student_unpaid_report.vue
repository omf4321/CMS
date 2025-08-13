<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="student_unpaid_report">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">Student Unpaid Report<span class="float-right">{{total_amount}}</span></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-text-field :clearable=true class="m-r-10" v-model="dialogItem.search" label="Search" single-line hide-details></v-text-field>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id">
                            </v-autocomplete>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" :items="batch" label="Batch" v-model="dialogItem.batch_id">
                            </v-autocomplete>
                            <v-btn small color="primary" @click="send_sms_to_unpaid">Send SMS</v-btn>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <div v-for="(std_payment, p_i) in sortUnpaid" class="m-t-15">
                        <div class="fw-600 fs-15" style="border-bottom: 1px solid">{{std_payment.payments[0].batches.echelons.name}} - {{std_payment.payments[0].batches.name}} ({{std_payment.total}})</div>
                        <table class="table table-bordered fs-12 m-t-10">
                            <thead>
                              <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Reg No</th>
                                <th>Amount</th>
                                <th>Unpaid For</th>
                                <th>Mobile</th>
                                <th>Last Pay Date</th>
                                <th>Next Pay Date</th>
                                <th>Payment Type</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="payment in std_payment.payments" :class="{'drop_color' : payment.status == 'droped'}">
                                <td>{{payment.id}}</td>
                                <td>{{payment.name}}</td>
                                <td>{{payment.reg_no}}</td>
                                <td>{{payment.amount}}</td>
                                <td>{{payment.month_diff}} month</td>
                                <td>{{payment.mobile}} <span v-if="payment.mobile2">, {{payment.mobile2}}</span></td>
                                <td>{{payment.payments.length ? payment.payments[0].date : '' | moment("D MMM YY")}}</td>
                                <td>{{payment.payments.length ? payment.payments[0].next_payment_date : '' | moment("D MMM YY")}}</td>
                                <td>{{payment.monthly_fee ? 'Monthly' : 'Coursely'}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
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
        <!-- sms dialog -->
        <v-dialog v-model="sms_dialog" persistent width="1000">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">SMS Report <v-icon class="pos-a" style="right: 15px;" @click="sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">
                    <div class="p-r-15 p-l-15">Current Balance: <span class="fw-600">{{parseFloat(sms_balance).toFixed(2)}}</span> <span class="float-right">SMS Failed: {{sms_failed.length}}/{{sms_report.length}}</span></div>                    
                    <v-container grid-list-md>
                        <table class="table table-bordered fs-12">
                            <thead>
                              <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>SMS</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="report in sms_report">
                                <td>{{report.reg_no}}</td>
                                <td>{{report.name}}</td>
                                <td>{{report.sms}}</td>
                                <td :class="{'text-danger': report.status !='OK'}">{{report.status}}</td>
                            </tr>
                            </tbody>
                        </table>
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
                    headers: [{text: 'ID', align: 'left', value: 'students.id'}, {text: 'Name', value: 'students.name'}, {text: 'Reg No', value: 'students.reg_no'}, {text: 'Class', value: 'students.batches.echelons.name'}, {text: 'Batch', value: 'students.batches.name'}, {text: 'Amount', value: 'amount'}, {text: 'Paid', value: 'paid'}, {text: 'Status', value: 'status'}, {text: 'Invoice ID', value: 'id'}, {text: 'Date', value: 'date'}, {text: 'Received', value: 'users.name'}, {text: 'Action', value: 'status', sortable: false, align: 'center'}],
                    // @list_column_data
                    unpaid_student: [{echelon_id: '', batch_id: '', payments: [{batches: {echelons: ''}, payments: []}]}],
                    branch: [],
                    batch: [],
                    original_batch: [],
                    echelon: [],
                    teacher: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    
                    editedIndex: -1,
                    editedId: null,
                    editedItem: {},
                    dialogItem: {
                        name: '',
                        branch_id: '',
                        echelon_id: '',
                        teacher_id: '',
                        bangla_text: '',
                        created_at: '',
                        reg_no: ''
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
                    sms_dialog: false,
                    sms_balance: '',
                    sms_failed: [],
                    sms_report: [],
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'view_unpaid');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.admin_branch_list();
                this.get_student_payment_list()
            },

            computed: {
                sortUnpaid() {
                    var items = this.unpaid_student
                    if (this.dialogItem.echelon_id) {
                        items = items.filter(item => item.echelon_id == this.dialogItem.echelon_id)
                    } 
                    if (this.dialogItem.batch_id) {
                        items = items.filter(item => item.batch_id == this.dialogItem.batch_id)
                    }
                    if (this.dialogItem.search) {                        
                        items = items.map(x => {
                            var a = Object.assign({}, x)
                            a.payments = a.payments.map(y => {
                                var b = Object.assign({}, y)
                                b.name_1 = b.name.toLowerCase().indexOf(this.dialogItem.search.toLowerCase()) > -1 ? b.name : ''
                                b.reg_no_1 = b.reg_no.toString().indexOf(this.dialogItem.search.toLowerCase()) > -1 ? b.reg_no : ''
                                return b
                            }).filter(z => z.name_1 || z.reg_no_1)
                            return a
                        }).filter(xx => xx.payments.length > 0)
                    }
                    return items
                }                
            },

            methods: {
                // @add_item_method
                admin_branch_list() {
                        this.branch = window_branch;
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].id
                            this.admin_echelon_list(this.dialogItem.branch_id);
                            this.admin_batch_list()
                        }
                        this.branch = this.branch.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['name']
                            }
                        })
                    },
                    admin_echelon_list() {                    
                        this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    },
                    admin_batch_list() {
                        this.axios.get('/admin/request/batch_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.original_batch = response.data.batch_list_by_branch;
                            this.batch = this.original_batch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                        });
                    },
                    get_batch(){
                        var batch = Object.assign([], this.original_batch);
                        let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                        this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    },
                    // @add_item_method
                    get_item() {
                        if (this.branch.length > 1) {
                            this.admin_echelon_list(this.dialogItem.branch_id);
                        }
                    },
                    // @list_method
                    get_student_payment_list() {
                        this.dialog = true;
                        this.axios.get('/admin/payment/get_unpaid_student').then(response => {
                            var item = []
                            item = response.data.unpaid_student;
                            this.payment_filter_by_batch(item)
                            this.dialog = false
                        });
                    },
                    payment_filter_by_batch(data){
                        var item = []
                        for (var i = 0; i < data.length; i++) {
                            var batch_object = {echelon_id: '', batch_id: '', payments: [], total: 0}
                            var batch_index = -1
                            batch_index = item.findIndex(x => x.batch_id == data[i].batch_id)
                            if (batch_index==-1) {
                                item.push(batch_object)
                                item[item.length-1].echelon_id = data[i].batches.echelon_id
                                item[item.length-1].batch_id = data[i].batch_id
                                item[item.length-1].payments.push(data[i])
                                item[item.length-1].total += data[i].amount
                                this.total_amount += data[i].amount
                            }
                            else{
                                item[batch_index].payments.push(data[i])
                                item[batch_index].total += data[i].amount
                                this.total_amount += data[i].amount
                            }
                        }
                        item = item.sort((a, b) => {return parseInt(a.echelon_id) - parseInt(b.echelon_id) })
                        for (var i = 0; i < item.length; i++) {
                            item[i].payments.sort((a,b) => {return a.reg_no - b.reg_no})
                        }
                        this.unpaid_student = item
                    },
                    
                    // @edit_item_method
                    send_sms_to_unpaid() {
                        var con = confirm("Are you sure?")
                        if (!con) {return false}
                        var items = this.unpaid_student
                        if (this.dialogItem.echelon_id) {
                            items = items.filter(item => item.echelon_id == this.dialogItem.echelon_id)
                        } 
                        if (this.dialogItem.batch_id) {
                            items = items.filter(item => item.batch_id == this.dialogItem.batch_id)
                        }
                        if (this.dialogItem.search) {                        
                            items = items.map(x => {
                                var a = Object.assign({}, x)
                                a.payments = a.payments.map(y => {
                                    var b = Object.assign({}, y)
                                    b.name_1 = b.name.toLowerCase().indexOf(this.dialogItem.search.toLowerCase()) > -1 ? b.name : ''
                                    b.reg_no_1 = b.reg_no.toString().indexOf(this.dialogItem.search.toLowerCase()) > -1 ? b.reg_no : ''
                                    return b
                                }).filter(z => z.name_1 || z.reg_no_1)
                                return a
                            }).filter(xx => xx.payments.length > 0)
                        }
                        var item = {}
                        item.payment_by_batch = items
                        this.dialog = true;
                        this.axios.post('/admin/payment/send_sms_to_unpaid', item).then(response => {        
                            if (response.data.sms == 'sms') {
                                this.sms_report = response.data.sms_report
                                this.sms_balance = response.data.balance
                                this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                                this.dialog = false
                                this.sms_dialog = true   
                            }
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
                        this.editedIndex = this.unpaid_student.findIndex(x => x.id == item.id);
                        this.editedItem = Object.assign({}, item)
                        this.editedId = this.unpaid_student[this.editedIndex].id;
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
            }
    }
</script>

<style>

 .student_unpaid_report table tr td {
    font-size: 12px!important;
 }
.student_unpaid_report .drop_color{
     background: #ffe8ba;
}
</style>