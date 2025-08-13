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
            <h3 class="headline m-t-0">Manage Fine Rules</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 pb-1">
                        <v-text-field v-model="search" append-icon="search" label="Search" single-line hide-details></v-text-field>
                        <!-- @add_item_dialog -->

                        <v-dialog v-model="dialog" persistent max-width="600px">
                            <!-- @add_item_button -->
                            <v-btn slot="activator" color="primary" dark class="mb-2" @click="editedIndex=-1, get_branch()">New Rule</v-btn>
                            <v-card class="p-15">
                                <v-card-title style="padding-bottom: 0px">
                                    <!-- @add_item_title -->
                                    <span class="headline">{{ formTitle }}</span>
                                </v-card-title>
                                <v-card-text>
                                    <div grid-list-md>
                                        <!-- @add_item_field -->
                                        <template>
                                            <v-form ref="form" v-model="valid" lazy-validation>
                                                <v-layout>
                                                    <v-text-field class="m-r-10" v-model="dialogItem.name" label="Fine Name" :rules="[rules.required]"></v-text-field>
                                                    <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'required']" label="Branch" required @change="get_item"></v-select>
                                                </v-layout>
                                                <v-layout>
                                                    <v-autocomplete class="m-r-10" v-model="dialogItem.fine_type" :items="fine_type" @change="change_fine_type" label="On Type" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                    <v-autocomplete v-model="dialogItem.collect_fine_on" :items="collect_fine_on" label="Collect Fine When Taking" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                </v-layout>
                                                <h5 v-if="dialogItem.fine_type" class="m-t-15 fs-16 fw-600">Fine Condition</h5>
                                                <hr v-if="dialogItem.fine_type" class="m-t-5 m-b-15">
                                                <!-- attendance fine -->
                                                <v-layout v-if="dialogItem.fine_type == 'attendance' || dialogItem.fine_type == 'late_payment'" class="unlabel_field">
                                                    <div v-if="dialogItem.fine_type == 'attendance'" class="m-r-10 m-t-5">If Absent For</div>
                                                    <div v-else class="m-r-10 m-t-5">If Late Payment For</div>
                                                    <div style="width:70px">
                                                        <v-text-field class="m-r-10 text-center" v-model="dialogItem.fine_value" :rules="[rules.required]"></v-text-field>
                                                    </div>
                                                    <div class="m-t-5">Consecutive Days</div>
                                                    <v-spacer></v-spacer>
                                                    <div class="m-t-5 m-r-10">Amount: </div>
                                                    <v-text-field v-model="dialogItem.fine_amount" :rules="[rules.required]"></v-text-field>
                                                </v-layout>
                                                <!-- exam fine -->
                                                <v-layout v-if="dialogItem.fine_type == 'exam'">
                                                    <div class="m-r-10 m-t-20">If Obtain Mark is</div>
                                                    <div style="width:100px">
                                                        <v-autocomplete class="m-r-10 text-center" v-model="dialogItem.fine_condition" :items="condition" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                    </div>
                                                    <div v-if="dialogItem.fine_condition != 'absent'" style="display:flex">
                                                        <div style="width:80px">
                                                            <v-text-field class="text-center" v-model="dialogItem.fine_value" :rules="[rules.required]"></v-text-field>
                                                        </div>
                                                        <div class="m-r-10 m-t-20">% of</div>
                                                        <div style="width:100px">
                                                            <v-autocomplete class="text-center" v-model="dialogItem.fine_field" :items="field" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                        </div>
                                                    </div>
                                                </v-layout>
                                                <!-- exam fine amount -->
                                                <v-layout v-if="dialogItem.fine_type == 'exam'" style="width: 300px; margin-top: -10px">
                                                    <div class="m-t-20 m-r-10">Fine Amount: </div>
                                                    <v-text-field v-model="dialogItem.fine_amount" :rules="[rules.required]"></v-text-field>
                                                </v-layout>
                                                <h5 v-if="dialogItem.fine_type" class="m-t-20 fw-600 fs-16">Return Condition</h5>
                                                <hr v-if="dialogItem.fine_type" class="m-t-5 m-b-5">
                                                <v-checkbox style="height:25px" v-if="dialogItem.fine_type" v-model="dialogItem.has_return" label="Has Return"></v-checkbox>
                                                <!-- attendance return -->
                                                <v-layout v-if="dialogItem.fine_type == 'attendance' && dialogItem.has_return" class="m-t-10 unlabel_field">
                                                    <div class="m-r-10 m-t-5">If Present For</div>
                                                    <div class="width:70px">
                                                        <v-text-field class="m-r-10 text-center" v-model="dialogItem.return_value" :rules="[rules.required]"></v-text-field>
                                                    </div>
                                                    <div class="m-t-5">Consecutive Days</div>
                                                    <v-spacer></v-spacer>
                                                    <div class="m-t-5 m-r-10">Amount: </div>
                                                    <v-text-field v-model="dialogItem.return_amount" :rules="[rules.required]"></v-text-field>
                                                </v-layout>
                                                <!-- exam return -->
                                                <v-layout v-if="dialogItem.fine_type == 'exam' && dialogItem.has_return">
                                                    <div class="m-r-10 m-t-20">If Obtain Mark is</div>
                                                    <div style="width:100px">
                                                        <v-autocomplete class="m-r-10 text-center" v-model="dialogItem.return_condition" :items="condition" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                    </div>
                                                    <div style="display:flex">
                                                        <div style="width:80px">
                                                            <v-text-field class="text-center" v-model="dialogItem.return_value" :rules="[rules.required]"></v-text-field>
                                                        </div>
                                                        <div class="m-r-10 m-t-20">% of</div>
                                                        <div style="width:100px">
                                                            <v-autocomplete class="text-center" v-model="dialogItem.return_field" :items="field" :rules="[v => !!v || 'required']"></v-autocomplete>
                                                        </div>
                                                    </div>
                                                </v-layout>
                                                <!-- attendance return amount -->
                                                <v-layout v-if="dialogItem.fine_type == 'exam' && dialogItem.has_return" style="width: 300px; margin-top: -10px">
                                                    <div class="m-t-20 m-r-10">Return Amount: </div>
                                                    <v-text-field v-model="dialogItem.return_amount" :rules="[rules.required]"></v-text-field>
                                                </v-layout>
                                                <v-text-field label="Description" v-model="dialogItem.description"></v-text-field>
                                                <!-- @add_item_submit -->
                                                <v-btn color="success" class="m-t-20" :disabled="!valid || loading" :loading="loading" @click="save"> submit </v-btn>
                                                <v-btn color="error" class="m-t-20" @click="close">close</v-btn>
                                            </v-form>
                                        </template>
                                    </div>
                                </v-card-text>
                            </v-card>
                        </v-dialog>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                    <v-data-table v-if="!update" :headers="headers" :items="rows" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left">{{ props.item.name }}</td>
                            <td class="text-xs-left">{{ props.item.branches.name }}</td>
                            <td class="text-xs-left">{{ props.item.fine_type }}</td>
                            <td class="text-xs-left">{{ props.item.fine_amount }}</td>
                            <td class="text-xs-left">{{ props.item.return_amount }}</td>
                            <td class="text-xs-left">{{ props.item.description }}</td>
                            <td class="text-xs-left">{{ props.item.created_at }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
                                </v-icon>
                                <!-- @delete_item -->
                                <v-icon small @click="delete_fine_rule(props.item)">
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
                    { text: "Name", value: "name" },
                    { text: "Branch", value: "branches.name" },
                    { text: "Fine On", value: "fine_type" },
                    { text: "Fine Amount", value: "fine_amount" },
                    { text: "Return Amount", value: "return_amount" },
                    { text: "Description", value: "description" },
                    { text: "Created At", value: "created_at" },
                    { text: "Actions", value: "name", sortable: false, align: "center" },
                ],
                // @list_column_data
                rows: [],
                branch: [],
                // @list_search_data
                search: "",

                // @add_item_field_data =====start
                dialog: false,
                editedIndex: -1,
                editedId: null,
                dialogItem: {
                    name: "",
                    branch_id: "",
                    has_return: true,
                    created_at: "",
                },
                fine_type: [{'value': 'attendance', 'text': 'Attendance'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'late_payment', 'text': 'Late Payment'}],
                condition: [{'value': 'absent', 'text': 'Absent'}, {'value': '<', 'text': 'lessthan'}, {'value': '>', 'text': 'greterthan'}, {'value': '<=', 'text': 'lessthan equal'}, {'value': '>=', 'text': 'greterthan equal'}],
                field: [{'value': 'passmark', 'text': 'Pass Mark'}, {'value': 'fullmark', 'text': 'Full Mark'}],
                collect_fine_on: [{'value': 'attendance', 'text': 'Attendance'}, {'value': 'payment', 'text': 'Payment'}],
                rules: {
                    required: (value) => !!value || "Required.",
                },

                // @accesories_data
                valid: true,
                success_alert: false,
                error_alert: false,
                loading: false,
                data_load: false,
                update: false,
            }; //end return
        },
        // @router_permission
        beforeRouteEnter(to, from, next) {
            let p = window_admin_permissions.findIndex((x) => x.name == "setting_subject");
            if (p > -1 || window_admin_role == "superadmin" || window_admin_role == "admin") {
                next();
            } else {
                next("/");
            }
        },
        // @load_method
        created() {
            this.admin_branch_list();
        },

        computed: {
            formTitle() {
                return this.editedIndex === -1 ? "New Fine Rule" : "Edit Fine Rule";
            },
        },

        watch: {
            dialog(val) {
                val || this.close();
            },
        },

        methods: {
            // @add_item_method
            admin_branch_list() {
                this.branch = window_branch;
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].id;
                }
                    this.get_fine_rule();
                this.branch = this.branch.map((arr) => {
                    return { value: arr["id"], text: arr["name"] };
                });
            },
            change_fine_type() {
                if (this.dialogItem.fine_type == 'attendance') {
                    this.dialogItem.condition = 'absent';
                }
            },
            // @add_item_method
            get_item() {
                if (this.branch.length > 1) {
                    this.admin_echelon_list(this.dialogItem.branch_id);
                }
            },
            // @list_method
            get_fine_rule() {
                this.data_load = true;
                Vue.axios.get("/admin/fine/get_fine_rule").then((response) => {
                    this.rows = response.data.fine_rule;
                    this.data_load = false;
                });
            },
            // @add_item_method
            add_fine_rule() {
                if (this.$refs.form.validate()) {
                    this.loading = true;
                    Vue.axios.post("/admin/fine/add_fine_rule", this.dialogItem).then(
                        (response) => {
                            this.get_fine_rule();
                            this.close();
                            this.loading = false;
                            $(".user_nav").addClass("successful");
                            setTimeout(
                                function () {
                                    $(".user_nav").removeClass("successful");
                                }.bind(this),
                                3000
                            );
                        },
                        (error) => {
                            this.loading = false;
                            $(".user_nav").addClass("denied");
                            setTimeout(
                                function () {
                                    $(".user_nav").removeClass("denied");
                                }.bind(this),
                                3000
                            );
                        }
                    );
                }
            },
            // @edit_item_method
            update_fine_rule() {
                this.loading = true;
                Vue.axios.post("/admin/fine/update_fine_rule/" + this.editedId, this.dialogItem).then(
                    (response) => {
                        this.get_fine_rule();
                        this.close();
                        this.loading = false;
                        $(".user_nav").addClass("successful");
                        setTimeout(function () {$(".user_nav").removeClass("successful"); }.bind(this), 3000 );
                    },
                    (error) => {
                        this.loading = false;
                        $(".user_nav").addClass("denied");
                        setTimeout(function () {$(".user_nav").removeClass("denied"); }.bind(this), 3000 );
                    }
                );
            },
            // @admin_add_edit
            save() {
                if (this.editedIndex > -1) {
                    this.update_fine_rule();
                } else {
                    this.add_fine_rule();
                }
            },
            get_branch() {
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].value;
                }
            },
            // @delete_item_method
            delete_fine_rule(item) {
                var con = confirm("Want to delete?");
                if (con) {
                    const index = this.rows.findIndex((x) => x.id == item.id);
                    Vue.axios.post("/admin/fine/delete_fine_rule/" + item.id).then(
                        (response) => {
                            this.rows.splice(index, 1);
                            $(".user_nav").addClass("successful");
                            setTimeout(function () {$(".user_nav").removeClass("successful"); }.bind(this), 3000 );
                        },
                        (error) => {
                            $(".user_nav").addClass("denied");
                            setTimeout(function () {$(".user_nav").removeClass("denied"); }.bind(this), 3000 );
                        }
                    );
                }
            },
            // @edit_item_method
            // open dialog
            editItem(item) {
                this.editedIndex = this.rows.findIndex((x) => x.id == item.id);
                this.dialogItem = Object.assign({}, item);
                this.editedId = this.rows[this.editedIndex].id;
                if (this.dialogItem.return_amount) {
                    this.dialogItem.has_return = true
                }
                this.dialog = true;
            },
            // @add_item_method_dialog
            close() {
                this.dialog = false;
                this.$refs.form.reset();
            },
            // @add_item_method_close_dialog
            clear() {
                this.$refs.form.reset();
            },
        },
    };
</script>

<style type="text/css">
    .unlabel_field .v-text-field {
        padding-top: 0px;
        margin-top: 0px;
    }
    .unlabel_field .v-text-field input{
        font-size: 15px;
        width: 40px;
    }
</style>
