<!-- =======Operation========
   @page_headline
   @list (title, table)
   @add_item (button, title, field, submit)
   @edit_item
   @delete_item
   @accesories
    -->
<template>
    <div class="student_list p-t-100">
        <v-container>
            <template>
                <v-container>
                    <template>
                        <div class="d-flex align-center justify-space-between">
                            <div class="d-flex align-center">
                                <v-icon color="indigo lighten-1" large class="mr-2">person</v-icon>
                                <span class="attractive-heading-1 indigo--text darken-2">Manage Students</span>
                            </div>
                            <v-btn v-if="check_permission('edit_student')" color="success" rounded @click="get_new_student_form">
                                <v-icon class="register-icon" left>mdi-account-plus</v-icon>
                                <span class="d-none d-md-inline">REGISTER STUDENT</span>
                            </v-btn>
                        </div>
                    </template>
                </v-container>
            </template>
            <!-- <v-icon class="m-l-10 cur-p float-right pos-rel" style="top: 3px" @click="delete_idb">refresh</v-icon> -->
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar rounded color="indigo lighten-1" dark elevation="2" class="m-b-20 p-t-15 custom-toolbar">
                        <v-layout row wrap>
                            <!-- @list_title -->
                            <v-col cols="6" md="2" sm="4" class="pa-2">
                                <v-select v-if="is_admin()" dense outlined v-model="search_branch" :disabled="branch.length==1" :items="branch" label="Branch" @change="get_branch_item" hide-details></v-select>
                            </v-col>
                            <v-col cols="6" md="2" sm="4" class="pa-2">
                                <v-autocomplete dense outlined v-model="search_by" :items="search_by_item" :label="search_by_label" @change="admin_student_list('asc')" hide-details></v-autocomplete>
                            </v-col>
                            <v-col cols="6" md="2" sm="4" class="pa-2">
                                <v-select dense outlined v-model="student_filter" @change="admin_student_list('asc')" :items="students" hide-details></v-select>
                            </v-col>
                            <v-col cols="6" md="3" sm="4" class="pa-2">
                                <v-text-field dense outlined v-model="search" append-icon="search" label="Search 1" hide-details></v-text-field>
                            </v-col>
                            <v-col cols="6" md="3" sm="4" class="pa-2">
                                <v-text-field dense outlined v-model="search1" append-icon="search" label="Search 2"></v-text-field>
                            </v-col>
                            <!-- <v-col cols="2" class="pa-0 mr-2">
                                <v-text-field dense outlined v-model="search2" append-icon="search" label="Search 3"></v-text-field>
                            </v-col> -->
                            <!-- <v-col cols="1" class="pa-0">
                                <v-icon dense @click="clear">backspace</v-icon>
                            </v-col> -->
                            <!-- @add_item_dialog -->
                        </v-layout>
                    </v-toolbar>

                    
                    <v-dialog v-model="dialog" fullscreen persistent transition="dialog-bottom-transition" scrollable>
                        <!-- @add_item_button -->
                        <v-card>
                            <v-card tile>
                                <v-toolbar dark color="indigo lighten-1" style="position: sticky; top: 0; z-index: 10;">
                                    <v-btn icon dark @click.native="close">
                                        <v-icon>close</v-icon>
                                    </v-btn>
                                    <v-toolbar-title>{{formTitle}}</v-toolbar-title>
                                    <v-spacer></v-spacer>
                                    <v-toolbar-items>
                                        <!-- <v-checkbox v-if="editedIndex>-1" v-model="dialogItem.update_biometric" label="Biometric" class="pos-rel check_box" style="top: 15px"></v-checkbox> -->
                                        <v-btn dark text :disabled="!valid || loading" :loading="loading" @click="save">Save</v-btn>
                                    </v-toolbar-items>
                                </v-toolbar>
                                <v-card-text class="grey lighten-5 pa-2" style="height: 100%;">

                                    <!-- @add_item_field -->
                                    <template>
                                        <v-container grid-list-lg>
                                            <v-layout row justify-left>
                                                <v-flex>
                                                    <v-form ref="form" v-model="valid" lazy-validation>
                                                        <div v-if="form_validation" style="color:red; margin-top:-15px; margin-bottom:15px">{{form_validation}}</div>
                                                        <v-layout>
                                                            <v-flex lg9>
                                                                <v-layout row>
                                                                    <v-flex lg4>
                                                                        <v-select outlined dense v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'required']" label="Branch" hide-details @change="get_item"></v-select>
                                                                    </v-flex>
                                                                    <v-flex lg4>
                                                                        <v-select outlined dense v-model="dialogItem.echelon_id" :disabled="editedIndex>-1" :items="echelon" label="Class" hide-details @change="get_batch"></v-select>
                                                                    </v-flex>
                                                                    <v-flex lg4>
                                                                        <v-text-field outlined dense disabled v-model="dialogItem.date_of_admit" label="Last Admit Date" hide-details></v-text-field>
                                                                    </v-flex>
                                                                </v-layout>

                                                                <v-layout row>
                                                                    <v-flex lg4>
                                                                        <v-autocomplete outlined dense v-model="dialogItem.batch_id" :items="batch" :disabled="editedIndex>-1" label="Batch" :rules="[v => !!v || 'required']" hide-details></v-autocomplete>
                                                                    </v-flex>
                                                                    <v-flex lg4 style="position:relative">
                                                                        <v-text-field outlined dense :disabled="editedIndex>-1" v-model="dialogItem.reg_no" :rules="[v => !!v || 'required']" label="Reg No" type="number" hide-details></v-text-field>
                                                                        <span class="reg_icon" v-if="editedIndex==-1">
                                                   <v-icon @click="get_reg_max">rotate_right</v-icon>
                                                   <v-icon @click="get_reg_droped">swap_horizontal_circle</v-icon>
                                                 </span>
                                                                    </v-flex>
                                                                    <v-flex lg4>
                                                                        <v-select outlined dense v-model="dialogItem.gender" :items="gender" :rules="[v => !!v || 'required']" label="Gender" hide-details></v-select>
                                                                    </v-flex>
                                                                </v-layout>
                                                            </v-flex>
                                                            <v-flex lg3>
                                                                <div class="text-xs-center" v-if="dialogItem.photo">
                                                                    <v-img style="margin: auto" :src="dialogItem.photo" width="90" height="100"></v-img>
                                                                </div>
                                                            </v-flex>
                                                        </v-layout>
                                                        <!-- 
                                                        <v-layout>
                                                            <v-flex lg4>
                                                                <v-text-field v-model="dialogItem.email" label="Email" type="text"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg4 v-if="change_credential || editedIndex==-1">
                                                                <v-text-field v-model="dialogItem.password" label="Password" type="text" :rules="[v => !!v || 'required']" required></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg4 v-if="editedIndex>-1 && !change_credential">
                                                                <v-btn text class="tiny-btn" color="primary" @click ="change_credential = !change_credential">Change Credential</v-btn>
                                                            </v-flex>
                                                            <v-flex lg4 v-if="editedIndex==-1 || change_credential">
                                                                <v-btn text class="tiny-btn" color="primary" @click ="generatePassword">Generate</v-btn>
                                                            </v-flex>
                                                        </v-layout> -->
                                                        <h4 class="m-t-10 fs-16 fw-600">Student's Information</h4>
                                                       <hr class="m-b-20 m-t-5" style="border-top: 2px solid #565656;">
                                                        <v-layout row>
                                                            <v-flex>
                                                                <v-text-field v-model="dialogItem.name" :rules="[v => !!v || 'required']" label="Student's Name"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg3>
                                                                <v-text-field v-model="dialogItem.mobile" :rules="[rules.required, rules.mobile]" label="Contact (G)" type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg3>
                                                                <v-text-field v-model="dialogItem.mobile2" label="Contact (S)" :rules="[rules.required, rules.mobile]"  type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg2>
                                                                <v-select v-model="dialogItem.student_type" :disabled="editedIndex>-1" :items="student_type" :rules="[v => !!v || 'required']" label="Type" required></v-select>
                                                            </v-flex>
                                                        </v-layout>
                                                        <v-layout row>
                                                            <v-flex>
                                                                <v-combobox v-model="dialogItem.institution_name" :items="institution" :rules="[v => !!v || 'required']" label="Institution's Name" hint="Find from list or Create new one"></v-combobox>
                                                            </v-flex>
                                                            <v-flex lg1>
                                                                <v-text-field v-model="dialogItem.school_roll" label="Roll" type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg4>
                                                                <v-autocomplete v-model="dialogItem.optional_id" :items="subject" label="Optional Subject"></v-autocomplete>
                                                            </v-flex>
                                                            <v-flex lg3>
                                                                <template>
                                                                    <v-dialog ref="menu" v-model="menu" :return-value.sync="date" lazy full-width width="290px">
                                                                        <v-text-field slot="activator" v-model="dialogItem.date_of_birth" label="Date of Birth" :rules="[v => !!v || 'required']" readonly></v-text-field>
                                                                        <v-date-picker ref="picker" v-model="dialogItem.date_of_birth" :max="new Date().toISOString().substr(0, 10)" min="2000-01-01" @change="save_birthday()"></v-date-picker>
                                                                    </v-dialog>
                                                                </template>
                                                            </v-flex>
                                                        </v-layout>
                                                        <v-layout row>
                                                            <v-flex lg>
                                                                <v-text-field :disabled="is_not_admin() && editedIndex>-1" v-model="dialogItem.monthly_fee" label="Monthly Fee" type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg>
                                                                <v-text-field :disabled="is_not_admin() && editedIndex>-1" v-model="dialogItem.course_fee" label="Course Fee" type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex lg>
                                                                <v-text-field :disabled="is_not_admin() && editedIndex>-1" v-model="dialogItem.admission_fee" label="Admission Fee" type="number"></v-text-field>
                                                            </v-flex>
                                                            <v-flex>
                                                                <v-autocomplete v-model="dialogItem.running_month" :items="month" :disabled="is_not_admin() && editedIndex>-1" label="Payment Start From" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                            </v-flex>
                                                            <v-flex lg>
                                                                <v-text-field v-model="dialogItem.year" :disabled="is_not_admin() && editedIndex>-1" type="number"></v-text-field>
                                                            </v-flex>
                                                        </v-layout>
                                                        <h4 class="m-t-10 fs-16 fw-600">Family Information</h4>
                                                        <hr class="m-b-20 m-t-5" style="border-top: 2px solid #565656;">
                                                        <v-layout row>
                                                            <v-flex>
                                                                <v-text-field v-model="dialogItem.father_name" :rules="[v => !!v || 'equired']" label="Father Name"></v-text-field>
                                                            </v-flex>
                                                            <v-flex>
                                                                <v-text-field v-model="dialogItem.mother_name" :rules="[v => !!v || 'equired']" label="Mother Name"></v-text-field>
                                                            </v-flex>
                                                            <v-flex>
                                                                <v-text-field v-model="dialogItem.address" label="Address"></v-text-field>
                                                            </v-flex>
                                                        </v-layout>
                                                        <!-- <v-layout row>
                                                            <v-flex>
                                                                <v-textarea v-model="dialogItem.address" auto-grow label="Address" rows="1"></v-textarea>
                                                            </v-flex>
                                                        </v-layout> -->
                                                        <div v-if="editedIndex>-1">
                                                            <label style="font-weight:600; font-size: 14px">First Admission: </label>
                                                            <span class="mr-3">{{dialogItem.first_admission}}</span>
                                                            <label style="font-weight:600; font-size: 14px">
                                                                Last Drop Month and Date: </label>
                                                            <span>{{dialogItem.inactive_month}} /  {{dialogItem.drop_date}}</span>
                                                        </div>
                                                        <!-- @add_item_submit -->
                                                    </v-form>
                                                </v-flex>
                                            </v-layout>
                                        </v-container>
                                    </template>

                                </v-card-text>
                            </v-card tile>
                        </v-card>
                    </v-dialog>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table v-if="!force_update" :headers="headers" :items="itemsSorted" :loading="data_load" :options.sync="pagination" class="elevation-1 small-font-table custom-data-table" :footer-props="{class: 'bg-grey lighten-4' }">
                        <template v-slot:progress>
                            <v-progress-linear color="blue" indeterminate></v-progress-linear>
                        </template>

                        <template v-slot:item="{ item }">
                            <tr>
                                <td :class="getRowClass(item)">{{ item.id }}</td>
                                <td :class="getRowClass(item)">{{ item.reg_no }}</td>
                                <td :class="getRowClass(item)">{{ item.name }}</td>
                                <td :class="getRowClass(item)">{{ item.mobile }}</td>
                                <td :class="getRowClass(item)">{{ item.gender === 'male' ? 'Male' : 'Female' }}</td>
                                <td :class="getRowClass(item)">{{ item.batches.echelons.name }}</td>
                                <td :class="getRowClass(item)">{{ item.batches.name }}</td>
                                <td :class="getRowClass(item)">{{ item.institutions ? item.institutions.name : '' }}</td>
                                <td :class="getRowClass(item)">{{ item.monthly_fee }}</td>
                                <td :class="getRowClass(item)">{{ item.course_fee }}</td>

                                <td v-if="check_permission('edit_student')" :class="['justify-center', 'layout', 'px-0', getStatusColor(item)]">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon small color="success" class="pa-1 rounded" v-bind="attrs" v-on="on" @click="editItem(item)">
                                                edit
                                            </v-icon>
                                        </template>
                                        <span>Edit</span>
                                    </v-tooltip>

                                    <v-tooltip left>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon v-if="item.status !== 'droped'" small color="warning" class="pa-1 rounded" v-bind="attrs" v-on="on" @click="active_transfer_dialog(item, 'active')">
                                                block
                                            </v-icon>
                                        </template>
                                        <span>Block</span>
                                    </v-tooltip>

                                    <v-tooltip left>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon v-if="item.status === 'droped'" color="accent" class="pa-1 rounded" v-bind="attrs" v-on="on" @click="active_transfer_dialog(item, 'active')">
                                                how_to_reg
                                            </v-icon>
                                        </template>
                                        <span>Activate</span>
                                    </v-tooltip>

                                    <v-tooltip left>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon small color="info" class="pa-1 rounded" v-bind="attrs" v-on="on" @click="active_transfer_dialog(item, 'transfer')">
                                                swap_horizontal_circle
                                            </v-icon>
                                        </template>
                                        <span>Transfer</span>
                                    </v-tooltip>

                                    <v-tooltip left>
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-icon small color="error" class="rounded pa-1">
                                                delete
                                            </v-icon>
                                        </template>
                                        <span>Delete</span>
                                    </v-tooltip>
                                </td>
                            </tr>
                        </template>
                    </v-data-table>

                    
                    <v-dialog v-model="active_dialog" persistent max-width="500px">
                        <!-- @add_item_button -->
                        <v-card>
                            <v-card-title>
                                <!-- @add_item_title -->
                                <span class="headline">{{active_transfer_title}}</span>
                            </v-card-title>
                            <v-card-text>
                                <v-container grid-list-md>
                                    <!-- @active_transfer_field -->
                                    <template>
                                        <v-form ref="form_active" v-model="valid" lazy-validation>
                                            <v-text-field v-model="dialogItem.id" label="ID" disabled></v-text-field>
                                            <v-select v-model="dialogItem.echelon_id" v-if="active_transfer=='transfer'" :items="echelon" label="Class" required @change="get_batch"></v-select>
                                            <v-autocomplete v-model="dialogItem.batch_id" v-if="active_transfer=='transfer'" :items="batch" label="Batch" :rules="[v => !!v || 'required']"></v-autocomplete>
                                            <div style="position:relative" v-if="active_transfer=='transfer'">
                                                <v-text-field v-model="dialogItem.reg_no" :rules="[v => !!v || 'required']" label="Reg No" type="number"></v-text-field>
                                                <span class="reg_transfer_icon">
                                         <v-icon @click="get_reg_max">rotate_right</v-icon>
                                         <v-icon @click="get_reg_droped">swap_horizontal_circle</v-icon>
                                      </span>
                                            </div>
                                            <div v-if="active_transfer=='transfer'">
                                                <v-text-field v-model="dialogItem.monthly_fee" label="Monthly Fee" type="number"></v-text-field>
                                            </div>
                                            <div v-if="active_transfer=='transfer'">
                                                <v-text-field v-model="dialogItem.course_fee" label="Course Fee" type="number"></v-text-field>
                                            </div>
                                            <div v-if="active_transfer=='transfer'">
                                                <v-text-field v-model="dialogItem.admission_fee" label="Admission Fee" type="number"></v-text-field>
                                            </div>
                                            <v-autocomplete class="mb-3" v-model="dialogItem.running_month" :items="month" :label="active_transfer_label" :rules="[v => !!v || 'required']"></v-autocomplete>
                                            <v-text-field v-model="dialogItem.year" :rules="[v => !!v || 'required']" label="Year" type="number"></v-text-field>
                                            <!-- @add_item_submit -->
                                            <v-btn :disabled="!valid || loading" :loading="loading" @click="active_transfer_save">
                                                submit
                                            </v-btn>
                                            <v-btn @click="active_dialog=false, $refs.form_active.reset()">close</v-btn>
                                        </v-form>
                                    </template>
                                </v-container>
                            </v-card-text>
                        </v-card>
                    </v-dialog>
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
                    headers: [{text: '#', value: 'id'}, {text: 'Reg', value: 'reg_no'}, {text: 'Name', value: 'name'}, {text: 'Mobile(S)', value: 'mobile'}, {text: 'Gender', value: 'gender'}, {text: 'Class', value: 'echelon_name'}, {text: 'Batch', value: 'batch_name'}, {text: 'institution', value: 'institution_name'}, {text: 'M.Fee', value: 'monthly_fee'}, {text: 'C.Fee', value: 'course_fee'},  {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    
                    pagination: {
                      page: 1,
                      itemsPerPage: 10,
                      sortBy: [],
                      sortDesc: [],
                      groupBy: [],
                      groupDesc: [],
                      multiSort: false,
                      mustSort: false
                    },
                    // @list_column_data
                    rows: [],
                    branch: [],
                    echelon: [],
                    original_echelon: [],
                    batch: [],
                    original_batch: [],
                    month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
                    all_batch: [],
                    institution: [],
                    subject: [],
                    original_subject: [],
                    gender: [{'value': 'male', 'text': 'Male'}, {'value': 'female', 'text': 'Female'}],
                    student_type: window_student_type,
                    // @list_search_data
                    search: '',
                    search1: '',
                    search2: '',
                    search_branch: '',
                    search_by: '',
                    search_by_item: [],
                    students: [{'value': 'present', 'text': 'Active'}, {'value': 'inactive', 'text': 'Inactive'}, {'value': 'droped', 'text': 'Droped'}, {'value': 'all', 'text': 'All'}],
                    transfer_type: [{'value': 'new_admission', 'text': 'New Admission'}, {'value': 'Transfer', 'text': 'Transfer'}],
                    student_filter: 'present',
                    student_filter_active: '',
                    student_filter_drop: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {id: '', reg_no: '', name: '', gender: '', branch_id: '', echelon_id: '', batch_id: '', institution_id: '', institution_name: '', school_roll: '', optional_id: '', mobile: '', mobile2: '', monthly_fee: '', course_fee: '', date_of_admit: '', date_of_birth: '', father_name: '', mother_name: '', guardian_name: '', student_type: window_student_type[0].value, address: '', running_month: '', year: '', inactive_month: '', drop_date: '', photo: '', status: '', online_exam: false, online_class: false, transfer_type: ''},
                    rules: {
                        required: value => !!value || 'required.',
                        mobile: value => !!value && value.length == 11 || '11 numbers needed',
                        min: value => value && value.length >= 6 || 'min 6 character',
                        email: value => {
                            const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                            return pattern.test(value) || 'Invalid e-mail.'
                        }
                    },

                    // @accesories_data
                    valid: true,
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    data_load: false,
                    date1_modal: false,
                    date2_modal: false,
                    date: '',
                    menu: false,
                    form_validation: null,

                    // @active_transfer_data
                    active_dialog: false,
                    active_transfer_label: '',
                    active_transfer_title: '',
                    active_transfer: '',
                    internal_reload: false,
                    force_update: false,
                    change_credential: false,
                    search_by_label: window_search_label,
                    enable_echelon_field: window_enable_echelon_field,
                    batch_label: window_batch_label

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                if (window_latest_unpaid.overdue == 'true') {
                    next('/billing_invoice')
                }
                let p = window_admin_permissions.findIndex(x => x.name == 'view_student');
                if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {               
                this.admin_branch_list();
                if (this.$route.params.booking_list_id && !this.internal_reload) {
                    this.new_student_from_booking_list()
                }
            },

            computed: {
                formTitle() {
                        return this.editedIndex === -1 ? 'New Student' : 'Edit Student'
                    },
                    itemsSorted: function() {
                        // return _.orderBy(this.items, this.sortKey, this.sortOrder);
                        // const { name, reg_no, related_batch_name, items } = this                        
                        var items = this.rows
                            .filter(item =>
                                item.id.toString().indexOf(this.search) > -1 ||
                                item.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.gender.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.institutions.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.branches.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.batches.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.batches.echelons.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 ||
                                item.reg_no.toString().indexOf(this.search) > -1 ||
                                item.mobile.toString().indexOf(this.search) > -1)
                            .filter(item =>
                                item.id.toString().indexOf(this.search1) > -1 ||
                                item.name.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.gender.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.institutions.name.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.branches.name.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.batches.name.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.batches.echelons.name.toLowerCase().indexOf(this.search1.toLowerCase()) > -1 ||
                                item.reg_no.toString().indexOf(this.search1) > -1 ||
                                item.mobile.toString().indexOf(this.search1) > -1)
                            .filter(item =>
                                item.id.toString().indexOf(this.search2) > -1 ||
                                item.name.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.gender.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.institutions.name.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.branches.name.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.batches.name.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.batches.echelons.name.toLowerCase().indexOf(this.search2.toLowerCase()) > -1 ||
                                item.reg_no.toString().indexOf(this.search2) > -1 ||
                                item.mobile.toString().indexOf(this.search2) > -1);
                        return items
                    },
            },

            watch: {
                menu(val) {
                    val && this.$nextTick(() => (this.$refs.picker.activePicker = 'YEAR'))
                }
            },

            methods: {
                // @add_item_method
                    admin_branch_list() {
                        if (window_search_by.includes('batch')) {
                            this.search_by_label = window_batch_label
                        }
                        this.branch = window_branch;
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].id
                            this.search_branch = this.branch[0].id
                            this.admin_batch_list()
                            this.admin_institution_list(this.dialogItem.branch_id);
                            this.original_batch = window_batches
                            this.original_echelon = window_echelons
                            if (this.enable_echelon_field) {
                                this.echelon = this.original_echelon.map(arr => { return { value: arr['id'], text: arr['name'] } })
                            }
                            this.search_by_list();
                        }
                        this.branch = this.branch.map(arr => {
                            return {
                                value: arr['id'],
                                text: arr['name']
                            }
                        })
                    },
                    search_by_list() {
                        if (window_search_by.includes('batch')) {
                            this.search_by_item = this.original_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                            this.batch = this.original_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                        }
                        if (window_search_by.includes('echelon')) {
                            this.search_by_item = this.original_echelon.map(arr => { return { value: arr['id'], text: arr['name'] } })
                        }
                        if (window_search_by.includes('year')) {
                            this.search_by_item = window_years.map(arr => { return { value: arr, text: arr } })
                        }
                    },
                    admin_echelon_list() {
                        this.axios.get('/admin/request/echelon_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.original_echelon = response.data.echelon_list_by_branch;
                            this.echelon = this.original_echelon.map(arr => { return { value: arr['id'], text: arr['name'] } })
                            this.search_by_list()
                        });
                    },
                    admin_batch_list() {
                        this.axios.get('/admin/request/batch_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.original_batch = response.data.batch_list_by_branch;
                            this.batch = this.original_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                            this.search_by_list()
                        });
                    },
                    update_course_fee(){
                        this.dialogItem.course_fee = this.original_batch[this.original_batch.findIndex(x => x.id == this.dialogItem.batch_id)].course_fee
                    },
                    admin_batch_echelon_list() {
                        this.axios.get('/admin/request/batch_list_by_branch_and_echelon/' + this.dialogItem.branch_id + '/' + this.dialogItem.echelon_id).then(response => {
                            this.batch = response.data.batch_list_by_branch_and_echelon;
                            this.batch = this.batch.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                        });
                    },
                    admin_institution_list() {
                        this.axios.get('/admin/request/institution_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.institution = response.data.institution_list_by_branch;
                            this.institution = this.institution.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                        });
                    },
                    admin_subject_list() {
                        this.axios.get('/admin/request/subject_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.original_subject = response.data.subject_list_by_branch;
                            var subject = Object.assign([], this.original_subject);
                            let filter_subject = subject.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                            this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
                        });
                    },
                    // @list_method
                    admin_student_list(sort) {
                        // this.check_idb()
                        this.data_load = true;                        
                        this.get_student_from_database('asc')
                    },
                    get_student_from_database(sort){
                        if (!this.search_branch) {this.data_load = false; return false}
                        if (!this.search_by) {this.data_load = false; return false}
                        this.axios.get('/admin/request/admin_student_list/' + this.search_branch + '/' + this.search_by + '/' + this.student_filter).then(response => {
                            this.rows = response.data.admin_student_list;
                            this.data_load = false
                            // this.initialize_idb('true')
                            if (sort == 'asc') {
                                this.pagination.descending = [false];
                            } else if (sort == 'desc') {
                                this.pagination.descending = [true];
                                this.pagination.sortBy = ['updated_at']
                            }
                        });
                    },
                    new_student_from_booking_list(){
                        this.axios.get('/admin/student/new_student_from_booking_list/' + this.$route.params.booking_list_id).then(response => {
                            var data = response.data.booking_list
                            if (!data) {return false}
                            this.dialogItem = Object.assign({}, data)
                            this.dialogItem.booking_list_id = this.$route.params.booking_list_id
                            // this.get_batch()
                            this.dialogItem.institution_id = data.institution_id
                            this.dialogItem.institution_name = data.institutions.name
                            this.get_new_student_form()
                            this.dialog = true
                            this.update_course_fee()
                            this.internal_reload = true
                        });
                    },
                    // @add_item_method
                    get_branch_item() {
                        if (this.branch.length > 1) {
                            this.dialogItem.branch_id = this.search_branch
                            if (window_search_by.includes('branch')) {
                                this.admin_student_list('asc');
                            }
                            if (!window_search_by.includes('batch')) {
                                this.admin_echelon_list()
                            }
                            this.admin_batch_list()
                        }
                    },
                    get_form_branch() {
                        if (this.branch.length > 1) {
                            this.admin_batch_list()
                            this.admin_institution_list()
                            this.admin_subject_list()                            
                        }
                    },
                    get_item() {
                        if (this.branch.length > 1) {
                            this.admin_echelon_list(this.dialogItem.branch_id);
                            this.admin_institution_list(this.dialogItem.branch_id);
                        }
                    },
                    get_batch() {
                        var batch = Object.assign([], this.original_batch);
                        let filter_batch = batch.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                        this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                        this.get_subject()
                        // this.admin_institution_list()
                    },
                    get_subject() {
                        if (!this.original_subject.length) {this.admin_subject_list(); return false}
                        var subject = Object.assign([], this.original_subject);
                        let filter_subject = subject.filter(x=>{return x.echelon_id == this.dialogItem.echelon_id})
                        this.subject = filter_subject.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    },
                    save_birthday(date) {
                        this.$refs.menu.save(date)
                    },
                    // @add_item_method
                    admin_student_add() {
                        if (this.$refs.form.validate()) {
                            if (this.dialogItem.reg_no.length < 4) {
                                this.form_validation = "Reg No must be greater than 3 digit"
                            } else if (!this.dialogItem.monthly_fee && !this.dialogItem.course_fee) {
                                this.form_validation = "Monthly fee or course fee needed"
                            } else if (this.dialogItem.monthly_fee && this.dialogItem.course_fee) {
                                this.form_validation = "Only monthly fee or course fee needed"
                            } else {
                                this.form_validation = "";
                                this.loading = true;
                                this.axios.post('/admin/request/admin_student_add', this.dialogItem).then(response => {
                                    this.rows.push(response.data.student)
                                    // this.initialize_idb()
                                    this.close()
                                    $('.user_nav').addClass('successful')
                                    this.loading = false;
                                    setTimeout(function() {
                                         $('.user_nav').removeClass('successful')
                                    }.bind(this), 4000)
                                    
                                }, error => {
                                    this.form_validation = error.response.data.msg
                                    $('.user_nav').addClass('denied')
                                    setTimeout(function() {
                                        $('.user_nav').removeClass('denied')
                                    }.bind(this), 3000)
                                    this.loading = false;
                                });
                            }
                        }
                    },
                    // @edit_item_method
                    admin_student_edit() {
                        if (this.$refs.form.validate()) {
                            if (this.dialogItem.reg_no.length < 4) {
                                this.form_validation = "Reg No must be greater than 3 digit"
                            } else if (this.dialogItem.mobile2 && this.dialogItem.mobile2.length != 11) {
                                this.form_validation = "Contact No 2 must be equal to 11 digit"
                            } else if (!this.dialogItem.monthly_fee && !this.dialogItem.course_fee) {
                                this.form_validation = "Monthly fee or course fee needed"
                            } else if (this.dialogItem.monthly_fee && this.dialogItem.course_fee) {
                                this.form_validation = "Only monthly fee or course fee needed"
                            } else {
                                this.form_validation = "";
                                this.loading = true;
                                this.axios.post('/admin/request/admin_student_edit/' + this.editedId, this.dialogItem).then(response => {
                                    this.rows.splice(this.editedIndex, 1)
                                    this.rows.push(response.data.student)
                                    // this.initialize_idb()
                                    this.close()
                                    $('.user_nav').addClass('successful')
                                    setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                                    this.loading = false;
                                }, error => {
                                    this.form_validation = error.response.data.msg
                                    $('.user_nav').addClass('denied')
                                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                                    this.loading = false;
                                });
                            }
                        }
                    },
                    get_new_student_form() {                        
                        // =====================
                        this.editedIndex = -1
                        this.dialogItem.editable = 1
                        this.form_validation = ''
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                            this.dialogItem.date_of_admit = new Date().getFullYear() + '-' + (new Date().getMonth() + 1) + '-' + new Date().getDate()
                        }
                        this.dialogItem.year = new Date().getFullYear();
                        this.dialogItem.photo = "";
                        this.dialog = true
                    },
                    get_reg_droped() {
                        if (this.dialogItem.batch_id) {
                            this.axios.get('/admin/request/get_reg_droped/' + this.dialogItem.batch_id).then(response => {
                                this.dialogItem.reg_no = response.data.get_reg_droped;
                                this.$forceUpdate()
                            });
                        }
                    },
                    get_reg_max() {
                        if (this.dialogItem.batch_id) {
                            this.axios.get('/admin/request/get_reg_max/' + this.dialogItem.batch_id).then(response => {
                                if (response.data.get_reg_max) {
                                    this.dialogItem.reg_no = response.data.get_reg_max;
                                    this.$forceUpdate()
                                } else {
                                    this.reg_msg = "No reg found"
                                }
                            });
                        }
                    },
                    // @admin_add_edit
                    save() {
                        // this.$refs.dialog_focus.focus()
                        setTimeout(function() {
                            this.save_function()
                        }.bind(this), 200)
                    },
                    save_function() {
                        var a = this.dialogItem.institution_name
                        if (typeof a === 'object') {
                            a = this.dialogItem.institution_name.text
                            this.dialogItem.institution_name = a
                        }

                        if (this.editedIndex > -1) {
                            this.admin_student_edit()
                        } else {
                            this.admin_student_add()
                        }
                    },
                    // @delete_item_method
                    admin_student_delete(item) {
                        var con = confirm("Want to delete?");
                        if (con) {
                            const index = this.rows.findIndex(x => x.id == item.id);
                            this.axios.post('/admin/request/admin_student_delete/' + item.id).then(response => {
                                this.rows.splice(index, 1)
                                $('.user_nav').addClass('successful')
                                // this.initialize_idb()
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                alert(error.response.data.msg)
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    // @edit_item_method
                    // open dialog
                    editItem(item) {
                        this.form_validation = ''
                        this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                        this.dialogItem = Object.assign({}, item)
                        this.dialogItem.year = new Date(this.rows[this.editedIndex].running_month).getFullYear()
                        this.dialogItem.running_month = new Date(this.rows[this.editedIndex].running_month).getMonth() + 1
                        this.editedId = this.rows[this.editedIndex].id;
                        // this.batch = this.all_batch
                        this.dialogItem.institution_name = this.rows[this.editedIndex].institutions ? this.rows[this.editedIndex].institutions.name : ""
                        // this.dialogItem.echelon_id = this.rows[this.editedIndex].batches.echelon_id
                        this.dialogItem.batch_id = this.rows[this.editedIndex].batch_id
                        this.dialogItem.email = this.rows[this.editedIndex].users ? this.rows[this.editedIndex].users.email : null
                        this.dialogItem.password = this.rows[this.editedIndex].users ? this.rows[this.editedIndex].users.password_text : null
                        this.get_subject()
                        this.dialog = true
                        if (this.rows[this.editedIndex].photo) {
                            this.dialogItem.photo = '/storage/avatar/' + this.rows[this.editedIndex].batches.name + '/' + this.rows[this.editedIndex].photo
                        }
                    },
                    // @active_transfer_method
                    // @active_transfer_method
                    active_transfer_dialog(item, active_or_transfer) {
                        if (item.status == 'present' && active_or_transfer == 'active') {
                            this.active_transfer_label = "Last Month of Payment"
                            this.active_transfer = 'active'
                            this.active_transfer_title = "Drop Student"
                        } else if (item.status == 'droped' && active_or_transfer == 'active') {
                            this.active_transfer_label = "Payment Start From"
                            this.active_transfer = 'active'
                            this.active_transfer_title = "Active Student"
                        } else {
                            this.active_transfer_label = "Payment Start From"
                            this.active_transfer = 'transfer'
                            this.active_transfer_title = "Transfer Student"
                        }
                        this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                        this.dialogItem.year = new Date().getFullYear()
                        this.editedId = this.rows[this.editedIndex].id
                        this.dialogItem.date_of_admit = new Date().getFullYear() + '-' + (new Date().getMonth() + 1) + '-' + new Date().getDate()
                        this.dialogItem.id = item.id
                        this.dialogItem.branch_id = this.rows[this.editedIndex].branch_id
                        this.dialogItem.echelon_id = this.rows[this.editedIndex].batches.echelon_id
                        this.dialogItem.batch_id = this.rows[this.editedIndex].batch_id
                        this.dialogItem.status = this.rows[this.editedIndex].status
                        this.dialogItem.reg_no = this.rows[this.editedIndex].reg_no
                        this.dialogItem.monthly_fee = this.rows[this.editedIndex].monthly_fee
                        this.dialogItem.course_fee = this.rows[this.editedIndex].course_fee
                        this.active_dialog = true
                    },
                    active_transfer_save() {
                        if (this.$refs.form_active.validate()) {
                            if (this.active_transfer == 'active') {
                                this.axios.post('/admin/request/admin_student_active/' + this.editedId, this.dialogItem).then(response => {
                                    if (response.data.admin_student_active == 'inactive') {
                                        alert('The student is not active. Re-admit him')
                                    } else if(response.data.admin_student_active == 'exist'){
                                        alert('Student is exist of this Reg No. Transfer him')
                                    }
                                    else {
                                        if (this.dialogItem.status == 'droped') {
                                            this.rows[this.editedIndex].status = 'present';
                                            this.student_filter = 'droped'
                                        } else {
                                            this.rows[this.editedIndex].status = 'droped';
                                            this.student_filter = 'present'
                                        }
                                        this.active_dialog = false
                                        $('.user_nav').addClass('successful')
                                        setTimeout(function() {
                                            $('.user_nav').removeClass('successful')
                                        }.bind(this), 3000)
                                        this.$refs.form_active.reset()
                                    }
                                    // this.initialize_idb()
                                });
                            } else {
                                this.axios.post('/admin/request/admin_student_transfer/' + this.editedId, this.dialogItem).then(response => {
                                    if (response.data.admin_student_transfer == 'exist') {
                                        alert('This reg no already exists')
                                    } else {
                                        this.student_filter = 'present';
                                        this.rows.splice(this.editedIndex, 1)
                                        this.rows.push(response.data.student)
                                        this.pagination.sortBy = ['updated_at']
                                        this.pagination.descending = [true]
                                        this.active_dialog = false
                                        $('.user_nav').addClass('successful')
                                        setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                                        this.$refs.form_active.reset()
                                    }
                                    // this.initialize_idb()
                                });
                            }
                        }
                    },
                    // @add_item_method_dialog
                    close() {
                        this.dialog = false
                        this.$refs.form.reset()
                        this.change_credential = false
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.search = ""
                        this.search1 = ""
                        this.search2 = ""
                    },
                    generatePassword() {
                        var length = 6,
                            // charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                            charset = "0123456789",
                            retVal = "";
                        for (var i = 0, n = charset.length; i < length; ++i) {
                            retVal += charset.charAt(Math.floor(Math.random() * n));
                        }

                        this.dialogItem.password = retVal;
                        this.$forceUpdate()
                    },
                    getRowClass(item) {
                        return {
                          'text-xs-left': true,
                          'drop_color': item.status === 'droped',
                          'active_color': item.status === 'inactive',
                          'd-none d-sm-table-cell' : false
                        };
                    },
                    getStatusColor(item) {
                        return {
                          'drop_color': item.status === 'droped',
                          'active_color': item.status === 'inactive'
                        };
                    }
                    
            }
    } 
</script>

<style> 
.student_list table.v-table tbody td:not(:first-child), .student_list table.v-table tbody th:first-child, .student_list table.v-table tbody th:not(:first-child), .student_list table.v-table thead td:first-child, .student_list table.v-table thead td:not(:first-child), .student_list table.v-table thead th:not(:first-child){
     padding: 0px;
}
 .student_list table.v-table tbody td:first-child, table.v-table thead th:first-child{
     padding: 0px 0px 0px 20px;
}
 .student_list .v-label{
/*     color: #444!important;*/
     font-size: 13px;
     font-weight: 600;
}
 .student_list .error--text{
     color: #ff5252 !important;
}
 .student_list .v-input input, .v-select__selections, .v-textarea textarea{
     font-size: 14px;
     font-weight: 500!important;
}
 .student_list .v-form .v-text-field{
     padding-top: 0px;
}
 .student_list .v-form .layout .flex{
     padding-bottom: 2px!important;
     padding-top: 5px!important;
}
 .student_list .v-dialog:not(.v-dialog--fullscreen){
     max-height: 100%!important;
}
 .student_list input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
     -webkit-appearance: none;
     margin: 0;
}
 .student_list .reg_icon{
     position: absolute;
     top: 13px;
     right: 7px;
}
 .student_list .reg_icon i{
     font-size: 24px;
}
 .student_list .reg_transfer_icon{
     position: absolute;
     top: 3px;
     right: 7px;
}
 .student_list .drop_color{
     background: #ffe8ba;
}
 .student_list .active_color{
     background: #ffe2e2;
}

.v-dialog__content .v-card__text {
/*    min-height: 100%;*/
}


</style>