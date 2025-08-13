<template>
    <div class="teacher_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Manage Teacher</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 pb-1">
                        <v-text-field v-model="search_filter" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <!-- @add_item_dialog -->
                        <v-dialog v-model="dialog" persistent max-width="900" transition="dialog-bottom-transition" scrollable>
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="addTeacher">New Teacher</v-btn>
                            <v-card>
                                <v-card tile>
                                    <v-toolbar card dark color="primary">
                                        <v-btn icon dark @click="dialog = false">
                                            <v-icon>close</v-icon>
                                        </v-btn>
                                        <v-toolbar-title>{{formTitle}}</v-toolbar-title>
                                        <v-spacer></v-spacer>
                                        <v-toolbar-items>
                                            <v-btn dark flat :disabled="!valid || loading" :loading="loading" @click="save">Save</v-btn>
                                        </v-toolbar-items>
                                    </v-toolbar>
                                    <v-card-text>
                                        <v-container grid-list-md>
                                            <!-- @add_item_field -->
                                            <template>
                                                <v-form ref="form" v-model="valid" lazy-validation>
                                                    <v-layout row wrap>
                                                        <v-flex lg3>
                                                            <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg2>
                                                            <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'required']" label="Branch" required></v-select>
                                                        </v-flex>
                                                        <v-flex lg3>
                                                            <v-text-field v-model="dialogItem.email" :disabled="editedIndex!=-1 && !edit_credential" :rules="[rules.required, rules.emailRules]" label="Email" type="text"></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg3 v-if="editedIndex==-1 || edit_credential ">
                                                            <v-text-field v-model="dialogItem.password" :rules="[rules.required]" label="Password" type="text"></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg1>
                                                            <v-select v-model="dialogItem.gender" :items="gender" :rules="[v => !!v || 'required']" label="Gender" required></v-select>
                                                        </v-flex>
                                                        <v-flex lg1 v-if="editedIndex>-1 && !edit_credential && is_admin()">
                                                            <v-btn small flat class="tiny-btn pos-rel" style="text-transform: none; top: 10px" color="info" @click="edit_credential = true">Change Credential</v-btn>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row wrap>
                                                        <v-flex lg3>
                                                            <v-text-field v-model="dialogItem.mobile" :rules="[rules.required, rules.mobile]" label="Contact No" type="number"></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg3>
                                                            <v-text-field v-model="dialogItem.mobile2" label="Contact No (2)" type="number"></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg4>
                                                            <v-text-field v-model="dialogItem.address" label="Address" type="text"></v-text-field>
                                                        </v-flex>
                                                        <v-flex lg2>
                                                            <template>
                                                                <v-dialog ref="join_date_ref" lazy full-width width="290px">
                                                                    <v-text-field slot="activator" v-model="dialogItem.join_date" label="Join Date" :rules="[v => !!v || 'required']" readonly></v-text-field>
                                                                    <v-date-picker ref="picker" v-model="dialogItem.join_date"></v-date-picker>
                                                                </v-dialog>
                                                            </template>
                                                        </v-flex>
                                                    </v-layout>
                                                    <v-layout row>
                                                        <v-flex lg5>
                                                            <v-radio-group v-model="dialogItem.salary_type" @change="triger_monthly_pay()" row>
                                                                <v-radio label="Per Class Pay" value="per_class"></v-radio>
                                                                <v-radio label="Monthly Pay" value="monthly"></v-radio>
                                                            </v-radio-group>
                                                        </v-flex>
                                                        <v-flex lg4>
                                                            <v-switch v-model="swith" :label="swith?'Active':'Inactive'"></v-switch>
                                                        </v-flex>
                                                    </v-layout>
                                                    <div v-if="dialogItem.salary_type == 'monthly'">
                                                        <v-layout>
                                                            <v-flex lg3>
                                                                <v-text-field v-model="dialogItem.monthly_salary" label="Monthly Salary" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg3 v-if="swith">
                                                                <v-autocomplete class="mb-3" v-model="dialogItem.salary_start" :items="month" label="Salary Started" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                            </v-flex>
                                                            <v-flex lg3 v-if="!swith">
                                                                <v-autocomplete class="mb-3" v-model="dialogItem.salary_end" :items="month" label="Salary End" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                            </v-flex>
                                                            <v-flex lg2>
                                                                <v-text-field v-model="dialogItem.year" label="Year" type="number" :rules="[v => !!v || 'required']"></v-text-field>
                                                            </v-flex>
                                                        </v-layout>
                                                    </div>
                                                    <div>
                                                        <v-list>
                                                            <v-layout row wrap>
                                                                <v-flex md4 v-for="(role, j) in roles" :key="j">
                                                                    <v-list-item @click="add_role(role)">
                                                                        <v-list-item-action>
                                                                            <v-checkbox :input-value=checkbox_check(role.name)></v-checkbox>
                                                                        </v-list-item-action>
                                                                        <v-list-item-content>
                                                                            <v-list-item-title style="font-weight: 500">{{role.name | capitalize}}</v-list-item-title>
                                                                            <v-list-item-sub-title>{{role.description?role.description.toLowerCase():''}}</v-list-item-sub-title>
                                                                        </v-list-item-content>
                                                                    </v-list-item>
                                                                </v-flex>
                                                            </v-layout>
                                                        </v-list>
                                                        <!-- @teacher_payment -->
                                                        <div v-if="checkbox_check('teacher') && dialogItem.salary_type=='per_class'">
                                                            <v-layout class="mt-3">
                                                                <div class="title font-weight-regular">Teacher's Payment Details</div>
                                                            </v-layout>
                                                            <v-divider class="mt-1"></v-divider>
                                                            <v-layout>
                                                                <v-flex lg4>
                                                                    <v-text-field v-model="payments[0].amount" label="Amount for Most Often" :rules="[rules.required]" required type="number"></v-text-field>
                                                                </v-flex>
                                                                <v-flex lg5>
                                                                    <v-btn small color="success" dark class="mb-2" @click="add_payment">+ Add Other Payment</v-btn>
                                                                    <div class="ml-2">If this teacher has different payment amount for any class or batch add other payments.</div>
                                                                </v-flex>
                                                            </v-layout>
                                                            <div v-if="payments.length>1">
                                                                <v-layout v-for="(payment, i) in payments" :key="i" v-if="i>0">
                                                                    <v-flex lg3>
                                                                        <v-select v-model="payment.echelon_id" :items="echelon" label="Class" required @change="get_batch(payment.echelon_id)"></v-select>
                                                                    </v-flex>
                                                                    <v-flex lg3>
                                                                        <v-autocomplete v-model="payment.batch_id" :items="batch" label="Batch"></v-autocomplete>
                                                                    </v-flex>
                                                                    <v-flex lg3>
                                                                        <v-text-field v-model="payment.amount" label="Amount" :rules="[rules.required]" required type="number"></v-text-field>
                                                                    </v-flex>
                                                                    <v-flex lg1>
                                                                        <v-btn outline small fab color="indigo" @click="delete_payment(i)">
                                                                            <v-icon>close</v-icon>
                                                                        </v-btn>
                                                                    </v-flex>
                                                                </v-layout>
                                                            </div>
                                                        </div>
                                                        <div v-if="checkbox_check('invigilator') && dialogItem.salary_type=='per_class'">
                                                            <v-layout class="mt-3">
                                                                <div class="title font-weight-regular">Invigilator's Payment Details</div>
                                                            </v-layout>
                                                            <v-divider class="mt-1"></v-divider>
                                                            <v-layout>
                                                                <v-flex lg3>
                                                                    <v-text-field v-model="dialogItem.invigilator_payments.per_hour_amount" label="Per Hour Amount" :rules="[rules.required]" required type="number"></v-text-field>
                                                                </v-flex>
                                                            </v-layout>
                                                        </div>
                                                        <div class="mt-3" v-if="checkbox_check('script_checker')  && dialogItem.salary_type=='per_class'">
                                                            <v-layout style="position:relative">
                                                                <div class="title font-weight-regular">Script's Payment Details</div>
                                                                <v-btn small color="success" dark class="mb-2" @click="add_script_payment" style="position:absolute; left: 220px; top: -10px;">+ Add Payment</v-btn>
                                                            </v-layout>
                                                            <v-divider class="mt-3 mb-2"></v-divider>
                                                            <div>
                                                                <v-layout v-for="(payment, i) in script_payments" :key="i">
                                                                    <v-flex lg3>
                                                                        <v-text-field v-model="payment.marks_range" label="Marks Range" :rules="[rules.required]" required type="number"></v-text-field>
                                                                    </v-flex>
                                                                    <v-flex lg3>
                                                                        <v-text-field v-model="payment.amount" label="Amount" :rules="[rules.required]" required type="number"></v-text-field>
                                                                    </v-flex>
                                                                    <v-flex lg1>
                                                                        <v-btn outline small fab color="indigo" @click="delete_script_payment(i)">
                                                                            <v-icon>close</v-icon>
                                                                        </v-btn>
                                                                    </v-flex>
                                                                </v-layout>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </v-form>
                                            </template>
                                        </v-container>
                                    </v-card-text>
                                </v-card tile>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search_filter" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.gender }}</td>
                            <td class="text-xs-left">{{ props.item.branches.name }}</td>
                            <td class="text-xs-left">
                                <span v-for="role in props.item.users.roles" class="mr-2">{{ role.name | capitalize}}</span>
                            </td>
                            <td class="text-xs-left">{{ props.item.mobile }}</td>
                            <td class="text-xs-left">{{ props.item.salary_type }}</td>
                            <td class="justify-center layout px-0" v-if="check_permission('manage_teacher')">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon small @click="teacher_delete(props.item)">
                                    delete
                                </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
        </v-container>
    </div>
</template>
<script>
    export default {
        data() {
                return {
                    // @list_header_data
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Name', value: 'name'}, {text: 'Gender', value: 'gender'}, {text: 'Branch Name', value: 'branch_id'}, {text: 'Roles', value: 'roles'}, {text: 'Contanct No', value: 'mobile'}, {text: 'Salary Type', value: 'salary_type'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    // @list_search_data
                    search_filter: '',
                    search_filter1: '',

                    // @add_item_field_data =====start
                    branch: [],
                    batch: [],
                    echelon: [],
                    roles: [{'name': 'teacher', 'description': 'attending to class lecture'}, {'name': 'invigilator', 'description': 'attending to exam invigilation'}, {'name': 'script_checker', 'description': 'attending to check exam script'}], //@edit_item_data
                    gender: [{'value': 'male', 'text': 'Male'}, {'value': 'female', 'text': 'Female'}],
                    selected_role: [],
                    payments: [{
                        'echelon_id': '',
                        'batch_id': '',
                        'amount': ''
                    }],
                    script_payments: [{'marks_range': '', 'amount': ''}],
                    month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
                    swith: true,
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        name: '',
                        email: '',
                        gender: '',
                        branch_id: '',
                        mobile: '',
                        mobile2: '',
                        address: '',
                        photo: '',
                        monthly_salary: '',
                        salary_type: 'per_class',
                        join_date: '',
                        invigilator_payments: {
                            'per_hour_amount': ''
                        },
                        roles: [],
                        payments: [],
                        script_payments: [],
                        status: 'active',
                        salary_start: '',
                        salary_end: '',
                        year: ''
                    },
                    rules: {
                        required: value => !!value || 'required.',
                        min: value => value && value.length >= 6 || 'min 6 character',
                        emailRules: value => {
                            const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                            return pattern.test(value) || 'Invalid e-mail.'
                        }
                    },
                    valid: true,
                    // @add_item_field_data =====end    

                    // @accesories_data
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    data_load: false,
                    edit_credential: false

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'manage_teacher');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.teacher_list()
                this.admin_branch_list()
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Teacher' : 'Edit Teacher'
                }
            },

            filters: {
                capitalize: function(value) {
                    if (!value) return ''
                    value = value.toString().replace('_', ' ')
                    return value.charAt(0).toUpperCase() + value.slice(1)
                }
            },

            watch: {
                dialog(val) {
                    val || this.close()
                }
            },

            methods: {
                // @add_item_method
                teacher_list() {
                        this.data_load = true;
                        this.axios.get('/admin/teacher/teacher_list').then(response => {
                            this.rows = response.data.teacher_list.filter(x => x.act_as_teacher == 0);
                            this.data_load = false
                        });
                    },
                    admin_branch_list() {
                        this.branch = window_branch;
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].id
                            this.admin_echelon_list(this.dialogItem.branch_id);
                            this.admin_batch_list();
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
                            this.batch = response.data.batch_list_by_branch;
                            this.batch = this.batch.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                            this.batch = this.batch
                        });
                    },
                    admin_batch_echelon_list(id) {
                        this.axios.get('/admin/request/batch_list_by_branch_and_echelon/' + this.dialogItem.branch_id + '/' + id).then(response => {
                            this.batch = response.data.batch_list_by_branch_and_echelon;
                            this.batch = this.batch.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                        });
                    },
                    get_batch(id) {
                        this.admin_batch_echelon_list(id)
                    },
                    // @add_item_method
                    teacher_add() {
                        if (this.$refs.form.validate()) {
                            this.loading = true;
                            this.axios.post('/admin/teacher/teacher_add', this.dialogItem).then(response => {
                                this.teacher_list()
                                this.close()
                                this.loading = false;
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                if ("email" in error.response.data.errors){
                                    alert(error.response.data.errors.email[0])
                                }
                                else if ("roles" in error.response.data.errors){
                                    alert(error.response.data.errors.roles[0])
                                }
                                else {
                                    alert(error.response.data.message)
                                }
                                this.loading = false;
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    addTeacher() {
                        this.editedIndex = -1
                        this.dialogItem.salary_type = 'per_class'
                        this.swith = true
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                        }
                    },
                    // @edit_item_method
                    teacher_edit() {
                        if (this.$refs.form.validate()) {
                            this.loading = true;
                            this.axios.post('/admin/teacher/teacher_edit/' + this.editedId, this.dialogItem).then(response => {
                                this.teacher_list()
                                this.close()
                                this.loading = false;
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                this.loading = false;
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    editItem(item) {
                        this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                        this.dialogItem = Object.assign({}, item)
                        this.selected_role = this.rows[this.editedIndex].users.roles
                        this.editedId = this.rows[this.editedIndex].id;
                        this.swith = this.rows[this.editedIndex].status == 'active' ? true : false
                        this.edit_credential = false

                        if (this.dialogItem.salary_type == 'monthly') {
                            if (this.swith == true) {
                                this.dialogItem.salary_start = parseInt(this.rows[this.editedIndex].salary_start ? this.rows[this.editedIndex].salary_start.toString().split(".")[0] : '')
                            } else {
                                this.dialogItem.salary_end = parseInt(this.rows[this.editedIndex].salary_end ? this.rows[this.editedIndex].salary_end.toString().split(".")[0] : '')
                            }
                            this.dialogItem.year = parseInt(this.rows[this.editedIndex].salary_start ? this.rows[this.editedIndex].salary_start.toString().split(".")[1] : '')
                        }

                        this.inlitalise_payments()

                        if (this.rows[this.editedIndex].invigilator_payments) {
                            this.dialogItem.invigilator_payments = this.rows[this.editedIndex].invigilator_payments;
                        }
                        if (this.rows[this.editedIndex].script_payments.length > 0) {
                            this.script_payments = this.rows[this.editedIndex].script_payments;
                        }
                        if (this.rows[this.editedIndex].per_class_payments.length > 0) {
                            this.payments = this.rows[this.editedIndex].per_class_payments;
                        }
                        this.dialogItem.email = this.rows[this.editedIndex].users.email
                        this.dialog = true
                    },
                    // @admin_add_edit
                    save() {
                        this.dialogItem.status = this.swith ? 'active' : 'inactive';
                        this.dialogItem.payments = []
                        this.dialogItem.script_payments = []
                        this.dialogItem.roles = []
                        for (var i = 0; i < this.selected_role.length; i++) {
                            this.dialogItem.roles.push(this.selected_role[i].name)
                        }
                        for (var i = 0; i < this.script_payments.length; i++) {
                            this.dialogItem.script_payments.push(this.script_payments[i])
                        }
                        for (var i = 0; i < this.payments.length; i++) {
                            this.dialogItem.payments.push(this.payments[i])
                        }
                        if (this.editedIndex > -1) {
                            this.teacher_edit()
                        } else {
                            this.teacher_add()
                        }
                    },
                    // @delete_item_method
                    teacher_delete(item) {
                        var con = confirm("Want to delete?");
                        if (con) {
                            const index = this.rows.findIndex(x => x.id == item.id);
                            this.axios.post('/admin/teacher/teacher_delete/' + item.id).then(response => {
                                this.rows.splice(index, 1)
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    checkbox_check(name) {
                        var index = this.selected_role.findIndex(x => x.name == name);
                        if (index == -1) {
                            return false
                        } else {
                            return true
                        }
                    },
                    add_role(role) {
                        var index = this.selected_role.findIndex(x => x.name == role.name);
                        if (index == -1) {
                            this.selected_role.push(role)
                        } else {
                            if (index > -1) {
                                this.selected_role.splice(index, 1)
                            }
                        }
                        console.log(this.selected_role)
                    },
                    add_payment() {
                        let a = {
                            'echelon_id': '',
                            'batch_id': '',
                            'amount': ''
                        }
                        this.payments.push(a)
                    },
                    delete_payment(index) {
                        this.payments.splice(index, 1)
                    },
                    add_script_payment() {
                        let a = {
                            'marks_range': '',
                            'amount': ''
                        }
                        this.script_payments.push(a)
                    },
                    delete_script_payment(index) {
                        this.script_payments.splice(index, 1)
                    },
                    // @add_item_method_dialog
                    close() {
                        this.$refs.form.reset()
                        this.dialog = false
                        // this.teacher_list()
                        this.selected_role = []
                        this.dialogItem.roles = [];
                        this.inlitalise_payments()
                    },
                    triger_monthly_pay() {
                        if (this.dialogItem.salary_type == 'monthly') {
                            this.selected_role = []
                            this.inlitalise_payments()
                        }
                    },
                    inlitalise_payments() {
                        this.payments = [{
                            'echelon_id': '',
                            'batch_id': '',
                            'amount': ''
                        }]
                        this.script_payments = [{
                            'marks_range': '',
                            'amount': ''
                        }]
                        this.dialogItem.invigilator_payments = {
                            'per_hour_amount': ''
                        }
                    }
            }
    }
</script>