<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="student_payment_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Manage Student Payment <span class="float-right">{{total_paid}}</span></h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-text-field :clearable=true class="m-r-10" v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id">
                            </v-autocomplete>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" :items="batch" label="Batch" v-model="dialogItem.batch_id">
                            </v-autocomplete>
                            <v-dialog v-model="date_from_dialog" full-width class="m-r-10" lazy ref="date_ref" width="290px">
                                <v-text-field :clearable=true label="From" readonly slot="activator" :value="dialogItem.show_from_date"></v-text-field>
                                <v-date-picker ref="picker" @change="change_from_date" v-model="dialogItem.from_date"></v-date-picker>
                            </v-dialog>
                            <v-dialog v-model="date_to_dialog" class="m-r-10" full-width lazy ref="date_ref_1" width="290px">
                                <v-text-field :clearable=true label="To" readonly slot="activator" :value="dialogItem.show_to_date"></v-text-field>
                                <v-date-picker ref="picker_1" @change="change_to_date" v-model="dialogItem.to_date"></v-date-picker>
                            </v-dialog>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_student_payment_list">Go</v-btn>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.students.id }}</td>
                            <td class="text-xs-left">{{ props.item.students.name }}</td>
                            <td class="text-xs-left">{{ props.item.students.reg_no }}</td>
                            <td class="text-xs-left">{{ props.item.students.batches.echelons.name }}</td>
                            <td class="text-xs-left">{{ props.item.students.batches.name }}</td>
                            <td class="text-xs-left">{{ props.item.amount }}</td>
                            <td class="text-xs-left">{{ props.item.paid }}</td>
                            <td class="text-xs-left">{{ props.item.status }}</td>
                            <td class="text-xs-left">{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.date | moment('DD MMM YY') }}</td>
                            <td class="text-xs-left">{{ props.item.users.name }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon small @click="delete_student_payment(props.item)">
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
                                <v-text-field v-model="editedItem.paid" label="Paid" :rules="[rules.required]"></v-text-field>
                                <v-text-field :rules="[v => !!v || 'required']" v-model="editedItem.amount" label="Amount"></v-text-field>
                                <v-dialog v-if="is_super_admin" full-width lazy ref="date_ref_1" width="290px">
                                    <v-text-field :rules="[v => !!v || 'required']" label="Date" readonly slot="activator" v-model="editedItem.date"></v-text-field>
                                    <v-date-picker @change="change_date" ref="picker_1" v-model="editedItem.show_date"></v-date-picker>
                                </v-dialog>
                                <v-dialog full-width lazy ref="date_ref_2" width="290px">
                                    <v-text-field :rules="[v => !!v || 'required']" label="Next Payment Date" readonly slot="activator" v-model="editedItem.next_payment_date"></v-text-field>
                                    <v-date-picker ref="picker_2" @change="change_date" v-model="editedItem.show_next_payment_date"></v-date-picker>
                                </v-dialog>
                                <v-autocomplete :rules="[v => !!v || 'required']" v-model="editedItem.month" :items="month" label="Month of Pay"></v-autocomplete>
                                <v-select v-model="editedItem.year" :items="year" :rules="[v => !!v || 'required']" label="Year"></v-select>
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
                    rows: [],
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
                        created_at: ''
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
                    month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
                    year: [{'value': 2019, 'text': 2019}, {'value': 2020, 'text': 2020}, {'value': 2021, 'text': 2021}],
                    date_from_dialog: false,
                    date_to_dialog: false
                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                let p=window_admin_permissions.findIndex(x=> x.name == 'manage_payment');
                if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
                    next()
                }
                else {
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
                        this.branch = window_branch
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
                        this.data_load = true;
                        this.axios.post('/admin/payment/get_student_payment_list', this.dialogItem).then(response => {
                            this.rows = response.data.student_payment_list;
                            this.data_load = false
                            var total = 0
                            for ( var i = 0; i < this.rows.length; i++ ) {
                                total += this.rows[i].paid
                            }
                            this.total_paid = total
                        });
                    },
                    
                    // @edit_item_method
                    edit_student_payment_list() {
                        this.dialog = true;
                        this.axios.post('/admin/payment/edit_student_payment_list/' + this.editedId, this.editedItem).then(response => {        
                            this.rows[this.editedIndex].paid = this.editedItem.paid;
                            this.rows[this.editedIndex].amount = this.editedItem.amount;
                            this.rows[this.editedIndex].date = this.editedItem.date;                    
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
                    delete_student_payment(item){
                        var con = confirm("Want to delete?");
                        if (con) {
                            const index = this.rows.findIndex(x => x.id == item.id);
                            this.axios.post('/admin/payment/delete_student_payment/'+item.id).then(response => {
                                this.rows.splice(index, 1)
                                $('.user_nav').addClass('successful')
                                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                            }, error => {
                                $('.user_nav').addClass('denied')
                                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
                            });
                        }
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
                        this.editedItem.month = parseInt(this.$moment(item.month).format('M'))
                        this.editedItem.year = parseInt(this.$moment(item.month).format('YYYY'))
                        this.edit_dialog = true
                        console.log(item)
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
                        this.date_from_dialog = false
                    },
                    change_to_date(){
                        this.dialogItem.show_to_date = this.$moment(this.dialogItem.to_date).format('DD MMM YY');
                        this.date_to_dialog = false
                    },
                    change_date(){
                        this.editedItem.date = this.editedItem.show_date ? this.$moment(this.editedItem.show_date).format('YYYY-MM-DD') : this.editedItem.date
                        this.editedItem.next_payment_date = this.editedItem.show_next_payment_date ? this.$moment(this.editedItem.show_next_payment_date).format('YYYY-MM-DD') : this.editedItem.next_payment_date
                    }
            }
    }
</script>

<style>

 .student_payment_list table tr td {
    font-size: 12px!important;
 }
</style>