<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->
<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Admin User</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-alert :value="success_alert" transition="scale-transition" color="success" icon="check_circle" outline>
                        Updated Successfully
                    </v-alert>
                    <!-- @accesories_alert -->
                    <v-alert :value="error_alert" transition="scale-transition" color="danger" icon="warning" outline>
                        An error has occured
                    </v-alert>
                    <v-toolbar flat color="white">
                        <!-- @list_title -->
                        <v-toolbar-title>Admin User List</v-toolbar-title>
                        <v-spacer></v-spacer>
                        <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <!-- @add_item_dialog -->
                        <v-dialog v-model="dialog" persistent max-width="500px">
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, dialogItem.branch_id=branch[0].value">New Admin</v-btn>
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
                                                <v-text-field v-if="editedIndex==-1" v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                                <v-text-field v-if="editedIndex==-1 || is_super_admin()" v-model="dialogItem.email" :rules="[rules.required, rules.emailRules]" label="E-mail" required></v-text-field>
                                                <v-text-field v-if="editedIndex==-1" v-model="dialogItem.password" :append-icon="show1 ? 'visibility_off' : 'visibility'" :rules="[rules.required, rules.min]" :type="show1 ? 'text' : 'password'" name="input-10-1" label="Password" hint="At least 8 characters" counter @click:append="show1 = !show1"></v-text-field>
                                                <v-text-field v-if="editedIndex>-1 && is_super_admin()" v-model="dialogItem.change_password" :append-icon="show1 ? 'visibility_off' : 'visibility'" :type="show1 ? 'text' : 'password'" name="change_password" label="Password" hint="At least 8 characters" counter @click:append="show1 = !show1"></v-text-field>
                                                <v-text-field v-if="editedIndex==-1" v-model="dialogItem.confirm_password" :append-icon="show2 ? 'visibility_off' : 'visibility'" :rules="[rules.required, rules.passwordMatch]" :type="show2 ? 'text' : 'password'" name="input-10-2" label="Password" hint="At least 8 characters" counter @click:append="show2 = !show2"></v-text-field>
                                                <!-- @edit_item_field -->
                                                <v-select v-model="selected_role" :items="admin_role" :item-value="admin_role.id" :item-text="admin_role.name" :rules="[v => !!v || 'Role is required']" label="Role" required></v-select>
                                                <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" label="Branch" required></v-select>
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
                            <td class="text-xs-left">{{ props.item.email }}</td>
                            <td class="text-xs-left">{{ props.item.roles[0].name }}</td>
                            <td class="text-xs-left">{{ props.item.branches.name }}</td>
                            <td class="text-xs-left">{{ props.item.created_at }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon small @click="admin_user_delete(props.item)">
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
            headers: [
                { text: 'ID', align: 'left', value: 'id' },
                { text: 'Name', value: 'name' },
                { text: 'Email', value: 'email' },
                { text: 'Role', value: 'role_name' },
                { text: 'Branch', value: 'branch_name' },
                { text: 'Created At', value: 'created_at' },
                { text: 'Actions', value: 'name', sortable: false, align: 'center' }
            ],
            // @list_column_data
            rows: [],
            // @list_search_data
            search: '',

            // @add_item_field_data =====start
            admin_role: [], //@edit_item_data
            branch: [],
            dialog: false,
            editedIndex: -1,
            editedId: null,
            dialogItem: {
                name: '',
                email: '',
                password: '',
                confirm_password: '',
                admin_role_id: '',
                role_name: '',
                branch_id: ''
            },
            selected_role: null,
            rules: {
                required: value => !!value || 'Required.',
                min: value => !!value && value.length >= 6 || 'Min 8 characters',
                passwordMatch: value => value == this.dialogItem.password || 'The password you entered don\'t match',
                emailRules: value => {
                    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    return pattern.test(value) || 'Invalid e-mail.'
                }
            },
            show1: false,
            show2: false,
            valid: true,
            // @add_item_field_data =====end    

            // @accesories_data
            success_alert: false,
            error_alert: false,
            loading: false,
            data_load: false

        } //end return
    },
    beforeRouteEnter(to, from, next) {
        let role = window_admin_role
        if (role == "admin" || role == 'superadmin') { next() } else { next('/') }
    },
    // @load_method
    created() {
        this.admin_user_list();
        this.admin_role_list();
        this.admin_branch_list();
    },

    computed: {
        formTitle() {
            return this.editedIndex === -1 ? 'New Item' : 'Edit Item'
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
            this.axios.get('/admin/request/admin_branch_list').then(response => {
                this.branch = response.data.admin_branch_list;
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].id
                }
                this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } })
            });
        },
        admin_role_list() {
            this.axios.get('/admin/request/admin_role_list').then(response => {
                this.admin_role = response.data.admin_role_list;
                this.admin_role = this.admin_role.map(arr => { return { value: arr['id'], text: arr['name'] } })
            });
        },
        // @list_method
        admin_user_list() {
            this.data_load = true;
            this.axios.get('/admin/request/admin_user_list').then(response => {
                this.rows = response.data.admin_user_list;
                this.data_load = false
            });
        },
        admin_user_list_by_id() {
            this.axios.get('/admin/request/admin_user_list').then(response => {
                this.rows = response.data.admin_user_list;
            });
        },
        // @add_item_method
        admin_user_add() {
            if (this.$refs.form.validate()) {
                this.loading = true;
                this.dialogItem.admin_role_id = this.selected_role
                this.axios.post('/admin/request/admin_user_add', this.dialogItem).then(response => {
                    this.admin_user_list();
                    this.close()
                    $('.user_nav').addClass('successful')
                    this.loading = false;
                    setTimeout(function() { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error => {
                    $('.user_nav').addClass('denied')
                    setTimeout(function() { $('.user_nav').removeClass('denied') }.bind(this), 3000)
                    this.loading = false;
                });
            }
        },
        // @edit_item_method
        admin_user_edit() {
            this.loading = true;
            var item = { 
                'admin_role_id': this.selected_role, 
                'branch_id': this.dialogItem.branch_id, 
                'change_password': this.dialogItem.change_password, 
                'email': this.dialogItem.email 
            }
            this.axios.post('/admin/request/admin_user_edit/' + this.editedId, item).then(response => {
                this.admin_user_list();
                this.close()
                $('.user_nav').addClass('successful')
                setTimeout(function() { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                this.loading = false;
            }, error => {
                $('.user_nav').addClass('denied')
                setTimeout(function() { $('.user_nav').removeClass('denied') }.bind(this), 3000)
                this.loading = false;
            });
        },
        // @admin_add_edit
        save() {
            if (this.editedIndex > -1) {
                this.admin_user_edit()
            } else {
                this.admin_user_add()
            }
        },
        // @delete_item_method
        admin_user_delete(item) {
            var con = confirm("Want to delete?");
            if (con) {
                const index = this.rows.findIndex(x => x.id == item.id);
                this.axios.post('/admin/request/admin_user_delete/' + item.id).then(response => {
                    this.rows.splice(index, 1)
                    $('.user_nav').addClass('successful')
                    setTimeout(function() { $('.user_nav').removeClass('successful') }.bind(this), 3000)
                }, error => {
                    $('.user_nav').addClass('denied')
                    setTimeout(function() { $('.user_nav').removeClass('denied') }.bind(this), 3000)
                });
            }
        },
        // @edit_item_method
        // open dialog
        editItem(item) {
            this.editedIndex = this.rows.findIndex(x => x.id == item.id);
            this.dialogItem = Object.assign({}, item)
            this.selected_role = this.rows[this.editedIndex].roles[0].id;
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
.v-alert {
    position: absolute;
    z-index: 1;
    right: 10px;
    top: 10px;
    width: 300px;
    height: 50px;
}
</style>