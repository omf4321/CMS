<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="student_payment_report">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">Student Payment Report<span class="float-right">{{total_paid}}</span></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-text-field :clearable=true class="m-r-10" v-model="dialogItem.reg_no" label="Reg No" single-line hide-details></v-text-field>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id">
                            </v-autocomplete>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" :items="batch" label="Batch" v-model="dialogItem.batch_id">
                            </v-autocomplete>
                            <v-dialog full-width class="m-r-10" lazy ref="date_ref" width="290px">
                                <v-text-field :clearable=true label="From" readonly slot="activator" :value="dialogItem.show_from_date"></v-text-field>
                                <v-date-picker ref="picker" @change="change_from_date" v-model="dialogItem.from_date"></v-date-picker>
                            </v-dialog>
                            <v-dialog class="m-r-10" full-width lazy ref="date_ref_1" width="290px">
                                <v-text-field :clearable=true label="To" readonly slot="activator" :value="dialogItem.show_to_date"></v-text-field>
                                <v-date-picker ref="picker_1" @change="change_to_date" v-model="dialogItem.to_date"></v-date-picker>
                            </v-dialog>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_student_payment_list">Go</v-btn>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <div v-for="(std_payment, p_i) in student_payment" class="m-t-15">
                        <div class="fw-600 fs-15" style="border-bottom: 1px solid">{{std_payment.users.name}} - {{std_payment.total}}</div>
                        <table class="table table-bordered fs-12 m-t-10">
                            <thead>
                              <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Reg No</th>
                                <th>Class</th>
                                <th>Batch</th>
                                <th>Amount</th>
                                <th>Paid</th>
                                <th>Status</th>
                                <th>Invoice ID</th>
                                <th>Date</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="payment in std_payment.payments">
                                <td>{{payment.students.id}}</td>
                                <td>{{payment.students.name}}</td>
                                <td>{{payment.students.reg_no}}</td>
                                <td>{{payment.students.batches.echelons.name}}</td>
                                <td>{{payment.students.batches.name}}</td>
                                <td>{{payment.amount}}</td>
                                <td>{{payment.paid}}</td>
                                <td>{{payment.status}}</td>
                                <td>{{payment.id}}</td>
                                <td>{{payment.date | moment("D MMM YY")}}</td>
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
                                <v-text-field v-model="editedItem.paid" label="Paid" :rules="[rules.required]" required></v-text-field>
                                <v-btn :disabled="!valid" @click="edit_student_payment_list"> submit </v-btn>
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
                    headers: [{text: 'ID', align: 'left', value: 'students.id'}, {text: 'Name', value: 'students.name'}, {text: 'Reg No', value: 'students.reg_no'}, {text: 'Class', value: 'students.batches.echelons.name'}, {text: 'Batch', value: 'students.batches.name'}, {text: 'Amount', value: 'amount'}, {text: 'Paid', value: 'paid'}, {text: 'Status', value: 'status'}, {text: 'Invoice ID', value: 'id'}, {text: 'Date', value: 'date'}, {text: 'Received', value: 'users.name'}, {text: 'Action', value: 'status', sortable: false, align: 'center'}],
                    // @list_column_data
                    student_payment: [],
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
                    total_paid: 0,
                    total_unpaid: 0,
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name=='add_payment' || x.name == 'manage_payment');
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
                        this.axios.post('/admin/payment/get_student_payment_list', this.dialogItem).then(response => {
                            var item = []
                            item = response.data.student_payment_list;
                            this.data_load = false
                            var total_paid = 0
                            var total_unpaid = 0
                            for ( var i = 0; i < item.length; i++ ) {
                                if (item[i].status == 'paid') {
                                    total_paid += item[i].paid
                                }
                                if (item[i].status == 'unpaid') {
                                    total_unpaid += item[i].paid
                                }
                            }
                            this.total_paid = total_paid
                            this.total_unpaid = total_unpaid
                            this.payment_filter_by_receiver(item)
                            this.dialog = false
                        });
                    },
                    payment_filter_by_receiver(data){
                        var item = []
                        for (var i = 0; i < data.length; i++) {
                            var receiver_object = {received_id: '', users: '', payments: [], total: 0}
                            var receiver_index = -1
                            receiver_index = item.findIndex(x => x.received_id == data[i].received_id)
                            if (receiver_index==-1) {
                                item.push(receiver_object)
                                item[item.length-1].received_id = data[i].received_id
                                item[item.length-1].users = data[i].users
                                item[item.length-1].payments.push(data[i])
                                item[item.length-1].total += data[i].paid
                            }
                            else{
                                item[receiver_index].payments.push(data[i])
                                item[receiver_index].total += data[i].paid
                            }
                        }
                        this.student_payment = item
                    },
                    
                    // @edit_item_method
                    edit_student_payment_list() {
                        this.dialog = true;
                        this.axios.post('/admin/payment/edit_student_payment_list/' + this.editedId, this.editedItem).then(response => {        
                            this.student_payment[this.editedIndex].paid = this.editedItem.paid;                    
                            this.close()
                            this.dialog = false;
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                                $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
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
                        this.editedIndex = this.student_payment.findIndex(x => x.id == item.id);
                        this.editedItem = Object.assign({}, item)
                        this.editedId = this.student_payment[this.editedIndex].id;
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

 .student_payment_report table tr td {
    font-size: 12px!important;
 }
</style>