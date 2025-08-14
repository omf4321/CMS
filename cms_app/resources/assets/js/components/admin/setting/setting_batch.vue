<template>
    <div class="setting_batch p-t-100">
        <v-container>
            <template>
                <v-container>
                    <template>
                        <div class="d-flex align-center justify-space-between">
                            <div class="d-flex align-center">
                                <v-icon color="teal lighten-1" large class="mr-2">settings</v-icon>
                                <span class="attractive-heading-1 teal--text darken-2">Manage Batch</span>
                            </div>
                            <v-btn v-if="check_permission('edit_student')" color="#a94442" class="white--text" rounded @click="editItem('new')">
                                <v-icon class="register-icon" left>add</v-icon>
                                <span class="d-none d-md-inline">CREATE NEW BATCH</span>
                            </v-btn>
                        </div>
                    </template>
                </v-container>
            </template>
            <v-divider class="my-3"></v-divider>
            <v-toolbar rounded color="teal lighten-1" dark elevation="2" class="custom-toolbar">
                <v-layout>
                    <h4 class="pa-5 d-none d-sm-block">Batch List</h4>
                    <v-select style="max-width: 150px" class="py-5" outlined dense hide-details v-model="search.status" :items="status" label="Status" required></v-select>
                    <v-text-field class="pa-5" dense outlined hide-details v-model="search.search_text" append-icon="search" label="Search" ></v-text-field>
                    <!-- <v-btn class="tiny-btn m-t-25" small color="default" @click="admin_batch_list">Go</v-btn> -->
                </v-layout>
            </v-toolbar>

            <v-dialog v-model="dialog" persistent max-width="600px" class="p-t-20">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details v-model="dialogItem.description" label="Description" required></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row dense>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'Class is required']" label="Class" required></v-select>
                                </v-col>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details v-model="dialogItem.course_id" :items="course" :rules="[v => !!v || 'Course is required']" label="Course" required></v-select>
                                </v-col>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details v-model="dialogItem.group_id" :items="group" :rules="[v => !!v || 'Group is required']" label="Group" required></v-select>
                                </v-col>
                            </v-row>

                            <h4 class="fw-600 m-t-15">Time and Roll</h4>
                            <hr class="m-t-5 m-b-10">
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details v-model="dialogItem.reg_base" label="Reg Base" :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details v-model="dialogItem.time" label="Time" :rules="[rules.required]" required></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-menu outlined dense ref="menu1" v-model="date1_modal" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field outlined hide-details dense v-model="dialogItem.start_from" label="Start From" readonly v-bind="attrs" v-on="on" :rules="[rules.required]" required></v-text-field>
                                        </template>
                                        <v-date-picker v-model="dialogItem.start_from" no-title scrollable @input="date1_modal = false"></v-date-picker>
                                    </v-menu>
                                </v-col>

                                <v-col cols="12" sm="6">
                                    <v-menu outlined dense ref="menu2" v-model="date2_modal" :close-on-content-click="false" transition="scale-transition" offset-y min-width="auto">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field outlined hide-details dense v-model="dialogItem.end_to" label="End To" readonly v-bind="attrs" v-on="on" :rules="[rules.required]" required></v-text-field>
                                        </template>
                                        <v-date-picker v-model="dialogItem.end_to" no-title scrollable @input="date2_modal = false"></v-date-picker>
                                    </v-menu>
                                </v-col>
                            </v-row>

                            <h4 class="fw-600 m-t-15">Fees</h4>
                            <hr class="m-t-5 m-b-10">
                            <v-row dense>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details v-model="dialogItem.fee_type" :items="fee_type" :rules="[v => !!v || 'Fee Type is required']" label="Fee Type" required></v-select>
                                </v-col>
                                <v-col cols="12" sm="4" v-if="dialogItem.fee_type == 'monthly_fee' || dialogItem.fee_type == 'both'" >
                                    <v-text-field outlined dense hide-details v-model="dialogItem.monthly_fee" label="Monthly Fee" :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="4" v-if="dialogItem.fee_type == 'course_fee' || dialogItem.fee_type == 'both'" >
                                    <v-text-field outlined dense hide-details v-model="dialogItem.course_fee" label="Course Fee" :rules="[rules.required]" required></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row dense>
                                <v-col cols="12" sm="4" v-if="dialogItem.fee_type == 'monthly_fee' || dialogItem.fee_type == 'both'" >
                                    <v-select outlined dense hide-details v-model="dialogItem.monthly_fee_mode" :items="monthly_fee_mode" :rules="[v => !!v || 'The Field is required']" label="Monthly Fee Mode" required></v-select>
                                </v-col>
                                <v-col cols="12" sm="4">
                                    <v-text-field outlined dense hide-details v-model="dialogItem.admission_fee" label="Admission Fee"></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="4" v-if="dialogItem.admission_fee > 0">
                                    <v-select outlined dense hide-details v-model="dialogItem.admission_fee_mode" :items="admission_fee_mode" :rules="[v => !!v || 'The Field is required']" label="Admission Fee Mode" required></v-select>
                                </v-col>
                            </v-row>
                            <hr class="m-t-10 m-b-10">
                            <v-row dense>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details v-model="dialogItem.active" :items="status" :rules="[v => !!v || 'The Field is required']" label="Status" required></v-select>
                                </v-col>
                            </v-row>
                            
                            <v-btn :disabled="!valid || loading" :loading="loading" color="success" class="mr-4 m-t-25" @click="save">
                                Submit
                            </v-btn>
                            <v-btn class="m-t-25" color="error" @click="close">Close</v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-dialog>

            <v-data-table :headers="headers" :items="filteredRows" :search="search.search_text" :loading="data_load" class="elevation-1 small-font-table custom-data-table" :options.sync="pagination">
                <template v-slot:progress>
                    <v-progress-linear color="blue" indeterminate></v-progress-linear>
                </template>

                <template v-slot:item="{ item }">
                    <tr>
                        <td>{{ item.name }}</td>
                        <td>{{ item.echelons.name }}</td>
                        <td>{{ item.courses.name }}</td>
                        <td>{{ item.groups.name }}</td>
                        <td>{{ item.reg_base }}</td>
                        <td>{{ item.monthly_fee }}</td>
                        <td>{{ item.course_fee }}</td>
                        <td>{{ item.start_from }}</td>
                        <td>{{ item.end_to }}</td>
                        <td>{{ item.time }}</td>
                        <td class="text-center">
                            <v-icon small color="success" class="mr-2" @click="editItem('edit',item)">edit</v-icon>
                            <v-icon small color="error" class="mr-2" @click="admin_batch_delete(item)">delete</v-icon>
                            <v-icon small color="info" @click="inactive_student(item)">highlight_off</v-icon>
                        </td>
                    </tr>
                </template>
            </v-data-table>
        </v-container>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                headers: [
                    { text: "Name", value: "name" },
                    { text: "Class", value: "echelons.name" },
                    { text: "Course", value: "courses.name" },
                    { text: "Group", value: "groups.name" },
                    { text: "Reg Base", value: "reg_base" },
                    { text: "Monthly Fee", value: "monthly_fee" },
                    { text: "Course Fee", value: "course_fee" },
                    { text: "Start From", value: "start_from" },
                    { text: "End To", value: "end_to" },
                    { text: "Time", value: "time" },
                    { text: "Actions", value: "actions", sortable: false, align: "center" },
                ],
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
                rows: [],
                branch: [],
                echelon: [],
                course: [],
                group: [],
                search: {'search_text': "", 'status': '1'},
                dialog: false,
                editedIndex: -1,
                editedId: null,
                dialogItem: {
                    name: "",
                    branch_id: "",
                    echelon_id: "",
                    course_id: "",
                    group_id: "",
                    reg_base: "",
                    monthly_fee: "",
                    course_fee: "",
                    time: "",
                    start_from: "",
                    end_to: "",
                    description: "",
                },

                rules: {
                    required: (value) => !!value || "Required.",
                },
                fee_type: [{'value': 'monthly_fee', 'text': 'Monthly Fee'}, {'value': 'course_fee', 'text': 'Course Fee'}, {'value': 'both', 'text': 'Both'}],
                monthly_fee_mode: [{'value': 'current', 'text': 'For Running Month'}, {'value': 'previous', 'text': 'For Previous Month'}],
                admission_fee_mode: [{'value': 'seperate', 'text': 'Take Seperately'}, {'value': 'combine', 'text': 'With Other Fee'}],
                status: [{'value': '1', 'text': 'Active'}, {'value': '0', 'text': 'Inactive'}],
                valid: true,
                loading: false,
                data_load: false,
                date1_modal: false,
                date2_modal: false,
            };
        },

        beforeRouteEnter(to, from, next) {
            let p = window.appData.admin_permissions.findIndex((x) => x.name === "setting_batch");
            if (p > -1 || window.appData.admin_role === "superadmin" || window.appData.admin_role === "admin") {
                next();
            } else {
                next("/");
            }
        },

        created() {
            this.admin_batch_list();
            this.admin_branch_list();
        },

        computed: {
            formTitle() {
                return this.editedIndex === -1 ? "New Batch" : "Edit Batch";
            },
            filteredRows() {
                if (this.search.status === null) {
                    return this.rows;
                }
                return this.rows.filter(row => row.active == this.search.status);
            }
        },

        watch: {
            dialog(val) {
                if (!val) this.close();
            },
            
        },

        methods: {
            admin_branch_list() {
                this.branch = window.appData.branch;
                if (this.branch.length === 1) {
                    this.dialogItem.branch_id = this.branch[0].id;
                    this.admin_echelon_list(this.dialogItem.branch_id);
                    this.admin_course_list(this.dialogItem.branch_id);
                    this.admin_group_list(this.dialogItem.branch_id);
                }
                this.branch = this.branch.map((arr) => ({
                    value: arr.id,
                    text: arr.name,
                }));
            },

            admin_echelon_list() {
                this.echelon = window.appData.echelons.map((arr) => ({
                    value: arr.id,
                    text: arr.name,
                }));
            },

            admin_course_list(id) {
                this.axios.get("/admin/request/course_list_by_branch/" + id).then((response) => {
                    this.course = response.data.course_list_by_branch.map((arr) => ({
                        value: arr.id,
                        text: arr.name,
                    }));
                });
            },

            admin_group_list(id) {
                this.axios.get("/admin/request/group_list_by_branch/" + id).then((response) => {
                    this.group = response.data.group_list_by_branch.map((arr) => ({
                        value: arr.id,
                        text: arr.name,
                    }));
                });
            },

            admin_batch_list() {
                this.data_load = true;
                this.axios.get("/admin/request/admin_batch_list").then((response) => {
                    this.rows = response.data.admin_batch_list;
                    this.data_load = false;
                });
            },

            get_item() {
                if (this.branch.length > 1) {
                    this.admin_echelon_list(this.dialogItem.branch_id);
                    this.admin_course_list(this.dialogItem.branch_id);
                    this.admin_group_list(this.dialogItem.branch_id);
                }
            },

            admin_batch_add() {
                if (this.$refs.form.validate()) {
                    this.loading = true;
                    this.axios.post("/admin/request/admin_batch_add", this.dialogItem).then(
                        () => {
                            this.admin_batch_list();
                            this.close();
                            $(".user_nav").addClass("successful");
                            this.loading = false;
                            setTimeout(() => {
                                $(".user_nav").removeClass("successful");
                            }, 3000);
                        },
                        () => {
                            this.$notifyClass.denied()
                            this.loading = false;
                        }
                    );
                }
            },

            admin_batch_edit() {
                this.loading = true;
                this.axios.post("/admin/request/admin_batch_edit/" + this.editedId, this.dialogItem).then(
                    () => {
                        this.admin_batch_list();
                        this.close();
                        this.$notifyClass.success()
                        this.loading = false;
                    },
                    () => {
                        $(".user_nav").addClass("denied");
                        setTimeout(() => {
                            $(".user_nav").removeClass("denied");
                        }, 3000);
                        this.loading = false;
                    }
                );
            },

            get_branch() {
                if (this.branch.length === 1) {
                    this.dialogItem.branch_id = this.branch[0].value;
                }
            },

            save() {
                if (this.editedIndex > -1) {
                    this.admin_batch_edit();
                } else {
                    this.admin_batch_add();
                }
            },

            admin_batch_delete(item) {
                if (confirm("Want to delete?")) {
                    const index = this.rows.findIndex((x) => x.id === item.id);
                    this.axios.post("/admin/request/admin_batch_delete/" + item.id).then(
                        () => {
                            this.rows.splice(index, 1);
                            this.$notifyClass.success()
                        },
                        () => {
                            this.$notifyClass.denied()
                        }
                    );
                }
            },

            inactive_student(item) {
                if (confirm("All student will be inactive. Do you want to continue?")) {
                    this.axios.post("/admin/request/inactive_student/" + item.id).then(
                        () => {
                            this.$notifyClass.success();
                        },
                        () => {
                             this.$notifyClass.denied()
                        }
                    );
                }
            },

            editItem(option,item) {
                if(option == 'new'){
                    this.dialog = true
                    this.$refs.form.reset();
                } else{
                    this.editedIndex = this.rows.findIndex((x) => x.id === item.id);
                    this.dialogItem = Object.assign({}, item);
                    this.editedId = this.rows[this.editedIndex].id;
                    this.dialogItem.active = item.active == 1 ? '1' : '0'
                    this.dialog = true
                }
                
            },

            close() {
                this.dialog = false;
                setTimeout(() => {
                    this.$refs.form.reset();
                }, 1000);
                
            },
        },
    };
</script>

<style scoped>
    .setting_batch .v-card__text .container {
        padding: 0 24px;
    }

    .setting_batch .v-card__text {
        padding: 0 16px 16px;
    }

    .setting_batch table.v-table tbody td:not(:first-child),
    .setting_batch table.v-table tbody th:first-child,
    .setting_batch table.v-table tbody th:not(:first-child),
    .setting_batch table.v-table thead td:first-child,
    .setting_batch table.v-table thead td:not(:first-child),
    .setting_batch table.v-table thead th:not(:first-child) {
        padding: 0;
    }

    .setting_batch table.v-table tbody td:first-child,
    .setting_batch table.v-table thead th:first-child {
        padding: 0 0 0 20px;
    }
</style>
