
<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Online Exam Subject</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="white">
                        <!-- @list_title -->
                        <v-toolbar-title>Subject List</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <!-- @add_item_dialog -->

                        <v-dialog v-model="dialog" persistent max-width="500px">
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1">New Subject</v-btn>
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
                                                <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                                <v-text-field v-model="dialogItem.description" label="Description" required></v-text-field>
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
                            <td>{{ props.item.id }}</td>
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.description }}</td>
                            <td class="text-xs-left">{{ props.item.created_at }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon small @click="subject_delete(props.item)">
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
                    headers: [{text: 'ID', align: 'left', value: 'id'}, {text: 'Name', value: 'name'}, {text: 'Branch', value: 'branch_name'}, {text: 'Description', value: 'description'}, {text: 'Created At', value: 'created_at'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    // @list_column_data
                    rows: [],
                    branch: [],
                    // @list_search_data
                    search: '',

                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        name: '',
                        branch_id: '',
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
                    data_load: false

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'setting_group');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.get_online_exam_subject();
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
                // @list_method
                get_online_exam_subject() {
                    this.data_load = true;
                    this.axios.get('/admin/exam/get_online_exam_subject').then(response => {
                        this.rows = response.data.subjects;
                        this.data_load = false
                    });
                },
                
                // @add_item_method
                subject_add() {
                    if (this.$refs.form.validate()) {
                        this.loading = true;
                        this.axios.post('/admin/exam/add_online_exam_subject', this.dialogItem).then(response => {
                            this.get_online_exam_subject();
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
                subject_edit() {
                    this.loading = true;
                    this.axios.post('/admin/exam/edit_online_exam_subject/' + this.editedId, this.dialogItem).then(response => {
                        this.rows[this.editedIndex].name = this.dialogItem.name;
                        this.rows[this.editedIndex].description = this.dialogItem.description;
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
                // @admin_add_edit
                save() {
                    if (this.editedIndex > -1) {
                        this.subject_edit()
                    } else {
                        this.subject_add()
                    }
                },
                get_branch() {
                    if (this.branch.length == 1) {
                        this.dialogItem.branch_id = this.branch[0].value
                    }
                },
                // @delete_item_method
                subject_delete(item) {
                    var con = confirm("Want to delete?");
                    if (con) {
                        const index = this.rows.findIndex(x => x.id == item.id);
                        this.axios.post('/admin/exam/delete_online_exam_subject/' + item.id).then(response => {
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
