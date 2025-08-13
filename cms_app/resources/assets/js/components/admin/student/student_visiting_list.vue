
<template>
    <div class="student_visiting_list">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline m-t-0">Manage Visiting List</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-t-15">
                        <v-layout>
                            <v-flex md2 class="m-r-15">
                                <v-select v-model="search_echelon" :items="echelon" label="Class" required></v-select>
                            </v-flex>
                            <v-flex md4>
                                <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                            </v-flex>
                            <v-flex md2>
                                <v-btn color="#607d8b" dark class="mb-2 white--text m-l-20 m-t-15 mid-btn" @click="sms_text = '', send_sms_dialog=true">Send SMS</v-btn>
                            </v-flex>
                        </v-layout>
                        <!-- @add_item_dialog -->
                        <v-dialog v-model="add_dialog" persistent max-width="600px">
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, get_branch()">New</v-btn>
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="fs-20">Add Visiting List</span>
                                </v-card-title>
                                <hr class="m-t-5 m-b-5">
                                <v-card-text class="m-t-5">
                                    <v-container grid-list-md>
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form ref="form" v-model="valid" lazy-validation>
                                                <v-layout>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" label="Branch" required @change="get_item"></v-select>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class" required></v-select>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md6>
                                                        <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-select v-model="dialogItem.gender" :items="gender" :rules="[v => !!v || 'required']" label="Gender"></v-select>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-text-field v-model="dialogItem.mobile" :rules="[rules.required, rules.mobile]" label="Contact No" type="number"></v-text-field>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-text-field v-model="dialogItem.mobile2" label="Contact No (2)" type="number"></v-text-field>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md3>
                                                        <v-text-field v-model="dialogItem.school_roll" label="Roll" type="number"></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex class="m-r-8" md4>
                                                        <v-combobox v-model="dialogItem.institution_name" :items="institution" :rules="[v => !!v || 'required']" label="Institution's Name" hint="Find from list or Create new one"></v-combobox>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md4>
                                                        <v-text-field v-model="dialogItem.father_name" label="Father Name"></v-text-field>
                                                    </v-flex>
                                                    <v-flex class="m-r-8" md4>
                                                        <v-text-field v-model="dialogItem.mother_name" label="Mother Name"></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex class="m-r-8">
                                                        <v-textarea v-model="dialogItem.address" auto-grow label="Comment" rows="1"></v-textarea>
                                                    </v-flex>
                                                </v-layout>
                                                <hr class="m-t-5 m-b-5">
                                                <!-- @add_item_submit -->
                                                <v-btn small color="success" :disabled="!valid || loading" :loading="loading" @click="save">
                                                    submit
                                                </v-btn>
                                                <v-btn small color="error" @click="close">close</v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="sortRows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.branches.name }}</td>
                            <td class="text-xs-left">{{ props.item.echelons.name }}</td>
                            <td class="text-xs-left">{{ props.item.institutions.name }}</td>
                            <td class="text-xs-left">{{ props.item.mobile }}</td>
                            <td class="text-xs-left">{{ props.item.address }}</td>
                            <td class="text-xs-left">{{ props.item.created_at }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)"> edit </v-icon>
                                <router-link class="p-t-15 p-b-15" :to="{ name: 'student_list', params: { visiting_list_id: props.item.id }}">
                                    <v-tooltip left>
                                        <template v-slot:activator="{ on }">
                                            <v-icon small class="mr-2" @click="editItem(props.item)" v-on="on"> send </v-icon>
                                        </template>
                                        <span>Take Admission</span>
                                    </v-tooltip>
                                </router-link>
                                <!-- @delete_item -->
                                <v-icon small @click="delete_visiting_list(props.item)"> delete </v-icon>
                            </td>
                        </template>
                    </v-data-table>
                </div>
            </template>
        </v-container>
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
        <!-- send sms dialog -->
        <v-dialog v-model="send_sms_dialog" persistent width="1000">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">Send SMS <v-icon class="pos-a" style="right: 15px;" @click="send_sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">                 
                    <v-container grid-list-md>
                        <v-textarea rows="2" counter v-model="sms_text" label="SMS"></v-textarea>
                        <v-btn small color="success" @click="send_sms">Send</v-btn>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
        <!-- loader -->
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


<script>
    export default {
        data() {
                return {
                    // @list_header_data
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Name', value: 'name'}, {text: 'Branch', value: 'branches.name'}, {text: 'Class', value: 'echelons.name'}, {text: 'School', value: 'institutions.name'}, {text: 'Mobile', value: 'mobile'}, {text: 'Comment', value: 'address'}, {text: 'Date', value: 'created_at'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    echelon: [],
                    teacher: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    add_dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        
                    },
                    institution: [],
                    gender: [{'value': 'male', 'text': 'Boy'}, {'value': 'female', 'text': 'Girl'}],
                    rules: {
                        required: value => !!value || 'required.',
                        mobile: value => !!value && value.length == 11 || '11 numbers needed',
                    },

                    // @accesories_data
                    valid: true,
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    data_load: false,
                    sms_dialog: false,
                    sms_balance: '',
                    sms_failed: [],
                    sms_report: [],
                    search_echelon: '',
                    send_sms_dialog: false,
                    sms_text: '',
                    dialog: false,

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'add_student' || x.name == 'edit_student');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.admin_branch_list();
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Subject' : 'Edit Subject'
                },
                sortRows(){
                    var items = this.rows.filter(item => item.echelon_id.toString().indexOf(this.search_echelon) > -1)
                    return items
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
                            this.admin_institution_list();
                            this.get_visiting_list()
                        }
                        this.branch = this.branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    },
                    admin_echelon_list() {                    
                        this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    },
                    // @add_item_method
                    get_item() {
                        if (this.branch.length > 1) {
                            this.admin_echelon_list(this.dialogItem.branch_id);
                        }
                    },
                    // @list_method
                    admin_institution_list() {
                        this.axios.get('/admin/request/institution_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                            this.institution = response.data.institution_list_by_branch;
                            this.institution = this.institution.map(arr => {return {value: arr['id'], text: arr['name'] } })
                        });
                    },
                    get_visiting_list(){
                        this.data_load = true
                        this.axios.get('/admin/student/get_visiting_list/' + this.dialogItem.branch_id).then(response => {
                            this.rows = response.data.visiting_list;
                            this.data_load = false
                        });
                    },
                    // @add_item_method
                    add_visiting_list() {
                        if (this.$refs.form.validate()) {
                            var a = this.dialogItem.institution_name
                            if (typeof a === 'object') {
                                a = this.dialogItem.institution_name.text
                                this.dialogItem.institution_name = a
                            }
                            this.loading = true;
                            this.axios.post('/admin/student/add_visiting_list', this.dialogItem).then(response => {
                                this.get_visiting_list()
                                this.close()
                                this.loading = false;
                                $('.user_nav').addClass('successful')                                
                                setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                            }, error => {
                                this.loading = false;
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                            });
                        }
                    },
                    // @edit_item_method
                    edit_visiting_list() {
                        if (this.$refs.form.validate()) {
                            this.loading = true;
                            var a = this.dialogItem.institution_name
                            if (typeof a === 'object') {
                                a = this.dialogItem.institution_name.text
                                this.dialogItem.institution_name = a
                            }
                            this.axios.post('/admin/student/edit_visiting_list/' + this.editedId, this.dialogItem).then(response => {
                                this.get_visiting_list()
                                this.close()
                                this.loading = false;
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                                this.$forceUpdate()
                            }, error => {
                                this.loading = false;
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    // @admin_add_edit
                    save() {
                        if (this.editedIndex > -1) {
                            this.edit_visiting_list()
                        } else {
                            this.add_visiting_list()
                        }
                    },

                    get_branch() {
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                        }
                    },
                    // @delete_item_method
                    delete_visiting_list(item) {
                        var con = confirm("Want to delete?");
                        if (con) {
                            const index = this.rows.findIndex(x => x.id == item.id);
                            this.axios.post('/admin/student/delete_visiting_list/' + item.id).then(response => {
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
                    // @edit_item_method
                    // open dialog
                    editItem(item) {
                        this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                        this.dialogItem = Object.assign({}, item)
                        this.editedId = this.rows[this.editedIndex].id;
                        this.dialogItem.institution_name = this.rows[this.editedIndex].institutions.name
                        this.add_dialog = true
                    },
                    send_sms(){
                        if (!this.sms_text) {return false}
                        if (this.sms_text.length>159) {return alert('Sms length must be in 160 character')}
                        // return console.log(this.rows)
                        var item = this.rows.filter(item => item.echelon_id.toString().indexOf(this.search_echelon) > -1).filter(item => item.institutions.name.toLowerCase().indexOf(this.search.toLowerCase()) > -1 || item.created_at.toLowerCase().indexOf(this.search.toLowerCase()) > -1)   
                        var data = {}
                        data.student = item 
                        data.sms_text = this.sms_text 
                        this.dialog = true                   
                        this.axios.post('/admin/student/send_visiting_list_sms', data).then(response => {
                            if (response.data.sms == 'sms') {
                                this.sms_report = response.data.sms_report
                                this.sms_balance = response.data.balance
                                this.sms_failed = this.sms_report.filter(x => x.status != 'OK')
                                this.dialog = false
                                this.send_sms_dialog = false
                                this.sms_dialog = true   
                            }
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
                    },
                    // @add_item_method_dialog
                    close() {
                        this.add_dialog = false
                        this.$refs.form.reset()
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.$refs.form.reset()
                    }
            }
    } 
</script>