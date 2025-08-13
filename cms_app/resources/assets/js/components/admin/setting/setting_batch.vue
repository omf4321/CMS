<template>
    <div class="setting_batch">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Batch</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-toolbar flat color="white">
                        <!-- @list_title -->
                        <v-toolbar-title>Batch List</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <!-- @add_item_dialog -->

                        <v-dialog v-model="dialog" persistent max-width="600px">
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, get_branch()">New Batch</v-btn>
                            <v-card>
                                <v-card-title>
                                    <!-- @add_item_title -->
                                    <span class="headline">{{ formTitle }}</span>
                                </v-card-title>
                                <v-card-text>
                                    <v-container grid-list-md>
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form ref="form" v-model="valid" lazy-validation>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.description" label="Description" required></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" label="Branch" required @change="get_item"></v-select>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class" required></v-select>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-select v-model="dialogItem.course_id" :items="course" :rules="[v => !!v || 'Course is required']" label="Course" required></v-select>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-select v-model="dialogItem.group_id" :items="group" :rules="[v => !!v || 'Group is required']" label="Group" required></v-select>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.monthly_fee" label="Monthly Fee" :rules="[rules.required]" required></v-text-field>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.course_fee" label="Course Fee" :rules="[rules.required]" required></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.reg_base" label="Reg Base" :rules="[rules.required]" required></v-text-field>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-text-field v-model="dialogItem.time" label="time" :rules="[rules.required]" required mask="time-with-seconds"></v-text-field>
                                                    </v-flex>
                                                </v-layout>
                                                <v-layout>
                                                    <v-flex>
                                                        <v-dialog ref="dialog1" v-model="date1_modal" :return-value.sync="date" lazy full-width width="290px">
                                                            <v-text-field slot="activator" v-model="dialogItem.start_from" :rules="[rules.required]" label="Start From"></v-text-field>
                                                            <v-date-picker v-model="dialogItem.start_from" @input="date1_modal = false">
                                                            </v-date-picker>
                                                        </v-dialog>
                                                    </v-flex>
                                                    <v-flex>
                                                        <v-dialog ref="dialog2" v-model="date2_modal" :return-value.sync="date" lazy full-width width="290px">
                                                            <v-text-field slot="activator" v-model="dialogItem.end_to" :rules="[rules.required]" label="End To"></v-text-field>
                                                            <v-date-picker v-model="dialogItem.end_to" @input="date2_modal = false">
                                                            </v-date-picker>
                                                        </v-dialog>
                                                    </v-flex>
                                                </v-layout>
                                                <!-- @add_item_submit -->
                                                <v-btn :disabled="!valid || loading" :loading="loading" @click="save">
                                                    submit
                                                </v-btn>
                                                <v-btn @click="close">close</v-btn>
                                            </v-form>
                                        </template>
                                    </v-container>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.branches.name }}</td>
                            <td class="text-xs-left">{{ props.item.echelons.name }}</td>
                            <td class="text-xs-left">{{ props.item.courses.name }}</td>
                            <td class="text-xs-left">{{ props.item.groups.name }}</td>
                            <td class="text-xs-left">{{ props.item.reg_base }}</td>
                            <td class="text-xs-left">{{ props.item.monthly_fee }}</td>
                            <td class="text-xs-left">{{ props.item.course_fee }}</td>
                            <td class="text-xs-left">{{ props.item.start_from }}</td>
                            <td class="text-xs-left">{{ props.item.end_to }}</td>
                            <td class="text-xs-left">{{ props.item.time }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)"> edit </v-icon>
                                <!-- @delete_item -->
                                <v-icon small class="mr-2" @click="admin_batch_delete(props.item)"> delete </v-icon>
                                <v-icon small  @click="inactive_student(props.item)"> highlight_off </v-icon>
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
                    headers: [{text: 'Name', value: 'name'}, {text: 'Branch', value: 'branch_name'}, {text: 'Class', value: 'echelon_name'}, {text: 'Course', value: 'course_name'}, {text: 'Group', value: 'group_name'}, {text: 'Reg Base', value: 'reg_base'}, {text: 'Monthly Fee', value: 'monthly_fee'}, {text: 'Course Fee', value: 'course_fee'}, {text: 'Start From', value: 'start_from'}, {text: 'End To', value: 'end_to'}, {text: 'Time', value: 'time'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    echelon: [],
                    course: [],
                    group: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        name: '',
                        branch_id: '',
                        echelon_id: '',
                        course_id: '',
                        group_id: '',
                        reg_base: '',
                        monthly_fee: '',
                        course_fee: '',
                        time: '',
                        start_from: '',
                        end_to: '',
                        description: '',
                        created_at: ''
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },

                    // @accesories_data
                    valid: true,
                    success_alert: false,
                    error_alert: false,
                    loading: false,
                    data_load: false,
                    date1_modal: false,
                    date2_modal: false,
                    date: ''

                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name=='setting_batch');
                if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
            },
            // @load_method
            created() {
                this.admin_batch_list();
                this.admin_branch_list();
            },

            computed: {
                formTitle() {
                    return this.editedIndex === -1 ? 'New Batch' : 'Edit Batch'
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
                            this.admin_course_list(this.dialogItem.branch_id);
                            this.admin_group_list(this.dialogItem.branch_id);
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
                    admin_course_list(id) {
                        this.axios.get('/admin/request/course_list_by_branch/' + id).then(response => {
                            this.course = response.data.course_list_by_branch;
                            this.course = this.course.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                        });
                    },
                    admin_group_list(id) {
                        this.axios.get('/admin/request/group_list_by_branch/' + id).then(response => {
                            this.group = response.data.group_list_by_branch;
                            this.group = this.group.map(arr => {
                                return {
                                    value: arr['id'],
                                    text: arr['name']
                                }
                            })
                        });
                    },
                    // @list_method
                    admin_batch_list() {
                        this.data_load = true;
                        this.axios.get('/admin/request/admin_batch_list').then(response => {
                            this.rows = response.data.admin_batch_list;
                            this.data_load = false
                        });
                    },
                    // @add_item_method
                    get_item() {
                        if (this.branch.length > 1) {
                            this.admin_echelon_list(this.dialogItem.branch_id);
                            this.admin_course_list(this.dialogItem.branch_id);
                            this.admin_group_list(this.dialogItem.branch_id);
                        }
                    },
                    // @add_item_method
                    admin_batch_add() {
                        if (this.$refs.form.validate()) {
                            this.loading = true;
                            this.axios.post('/admin/request/admin_batch_add', this.dialogItem).then(response => {
                                this.admin_batch_list();
                                this.close()
                                $('.user_nav').addClass('successful')
                                this.loading = false;
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                                this.loading = false;
                            });
                        }
                    },
                    // @edit_item_method
                    admin_batch_edit() {
                        this.loading = true;
                        this.axios.post('/admin/request/admin_batch_edit/' + this.editedId, this.dialogItem).then(response => {
                            this.admin_batch_list();
                            this.close()
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                                $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                            this.loading = false;
                        }, error => {
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {
                                $('.user_nav').removeClass('denied')
                            }.bind(this), 3000)
                            this.loading = false;
                        });
                    },
                    get_branch() {
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                        }
                    },
                    // @admin_add_edit
                    save() {
                        if (this.editedIndex > -1) {
                            this.admin_batch_edit()
                        } else {
                            this.admin_batch_add()
                        }
                    },
                    // @delete_item_method
                    admin_batch_delete(item) {
                        var con = confirm("Want to delete?");
                        if (con) {
                            const index = this.rows.findIndex(x => x.id == item.id);
                            this.axios.post('/admin/request/admin_batch_delete/' + item.id).then(response => {
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
                    inactive_student(item) {
                        var con = confirm("All student will be inactive. Do you want to continue?");
                        if (con) {
                            this.axios.post('/admin/request/inactive_student/' + item.id).then(response => {                   
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {$('.user_nav').removeClass('successful') }.bind(this), 3000)
                            }, error => {
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                            });
                        }
                    },
                    // @edit_item_method
                    // open dialog
                    editItem(item) {
                        this.editedIndex = this.rows.findIndex(x => x.id == item.id);
                        this.dialogItem = Object.assign({}, item)
                        this.editedId = this.rows[this.editedIndex].id;
                        this.dialog = true
                    },
                    // @add_item_method_dialog
                    close() {
                        this.dialog = false
                        this.$refs.form.reset()
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.$refs.form.reset()
                    }
            }
    }
</script>

<style>
    .setting_batch .v-card__text .container {
        padding: 0px 24px;
    }
    
    .setting_batch .v-card__text {
        padding: 0px 16px 16px;
    }
    
    .setting_batch table.v-table tbody td:not(:first-child),
    .setting_batch table.v-table tbody th:first-child,
    .setting_batch table.v-table tbody th:not(:first-child),
    .setting_batch table.v-table thead td:first-child,
    .setting_batch table.v-table thead td:not(:first-child),
    .setting_batch table.v-table thead th:not(:first-child) {
        padding: 0px;
    }
    
    .setting_batch table.v-table tbody td:first-child,
    .setting_batch table.v-table thead th:first-child {
        padding: 0px 0px 0px 20px;
    }
</style>