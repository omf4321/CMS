<template>
    <div class="setting_fee_scheme p-t-100">
        <v-container>
            <!-- Header -->
            <div class="d-flex align-center justify-space-between">
                <div class="d-flex align-center">
                    <v-icon color="indigo lighten-1" large class="mr-2">attach_money</v-icon>
                    <span class="attractive-heading-1 indigo--text darken-2">Manage Fee Schemes</span>
                </div>
                <v-btn v-if="check_permission('edit_fee_scheme')" color="indigo" class="white--text" rounded @click="editItem('new')">
                    <v-icon left>add</v-icon>
                    <span class="d-none d-md-inline">CREATE NEW SCHEME</span>
                </v-btn>
            </div>

            <v-divider class="my-3"></v-divider>

            <!-- Toolbar -->
            <v-toolbar rounded color="indigo lighten-1" dark elevation="2">
                <v-layout>
                    <h4 class="pa-5 d-none d-sm-block">Fee Scheme List</h4>
                    <v-select style="max-width: 150px" class="py-5" outlined dense hide-details
                        v-model="search.type" :items="type_options" label="Type"></v-select>
                    <v-text-field class="pa-5" dense outlined hide-details
                        v-model="search.search_text" append-icon="search" label="Search"></v-text-field>
                </v-layout>
            </v-toolbar>

            <!-- Dialog -->
            <v-dialog v-model="dialog" persistent max-width="650px" class="p-t-20">
                <v-card>
                    <v-card-title>
                        <span class="headline">{{ formTitle }}</span>
                    </v-card-title>
                    <v-card-text>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details
                                        v-model="dialogItem.name" label="Name" :rules="[rules.required]" required>
                                    </v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select outlined dense hide-details
                                        v-model="dialogItem.type" :items="type_options" :rules="[rules.required]" label="Type" required>
                                    </v-select>
                                </v-col>
                            </v-row>

                            <v-row dense>
                                <v-col cols="12">
                                    <v-textarea outlined dense hide-details
                                        v-model="dialogItem.description" label="Description">
                                    </v-textarea>
                                </v-col>
                            </v-row>

                            <!-- Class selection -->
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-select
                                        outlined dense hide-details
                                        multiple
                                        v-model="dialogItem.echelon_id"
                                        :items="echelons"
                                        item-text="name"
                                        item-value="id"
                                        label="Class"
                                        @change="onEchelonChange"
                                    ></v-select>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select outlined dense hide-details multiple
                                        v-model="dialogItem.batch_ids"
                                        :items="filteredBatches"
                                        label="Applicable Batches">
                                    </v-select>
                                </v-col>
                            </v-row>

                            <!-- Amounts -->
                            <v-row dense>
                                <v-col cols="12" sm="4">
                                    <v-text-field outlined dense hide-details
                                        v-model="dialogItem.fixed_amount" label="Fixed Amount" type="number">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="12" sm="4">
                                    <v-text-field outlined dense hide-details
                                        v-model="dialogItem.percent_amount" label="Percent Amount (%)" type="number">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details
                                        v-model="dialogItem.validity_period"
                                        :items="getValidityOptions(dialogItem.type)" 
                                        label="Validity Period">
                                    </v-select>
                                </v-col>
                            </v-row>

                            <!-- Date range -->
                            <v-row dense v-if="dialogItem.validity_period === 'date_range'">
                                <v-col cols="12" sm="6">
                                    <v-menu ref="menu1" v-model="date1_modal" :close-on-content-click="false" offset-y min-width="auto">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field outlined hide-details dense v-model="dialogItem.start_date"
                                                label="Start Date" readonly v-bind="attrs" v-on="on"></v-text-field>
                                        </template>
                                        <v-date-picker v-model="dialogItem.start_date" no-title scrollable
                                            @input="date1_modal = false"></v-date-picker>
                                    </v-menu>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-menu ref="menu2" v-model="date2_modal" :close-on-content-click="false" offset-y min-width="auto">
                                        <template v-slot:activator="{ on, attrs }">
                                            <v-text-field outlined hide-details dense v-model="dialogItem.end_date"
                                                label="End Date" readonly v-bind="attrs" v-on="on"></v-text-field>
                                        </template>
                                        <v-date-picker v-model="dialogItem.end_date" no-title scrollable
                                            @input="date2_modal = false"></v-date-picker>
                                    </v-menu>
                                </v-col>
                            </v-row>

                            <!-- Recurring -->
                            <h4 class="fw-600 m-t-15" v-if="showRecurring(dialogItem.type)">Recurring</h4>
                            <hr class="m-t-5 m-b-10" v-if="showRecurring(dialogItem.type)">
                            <v-row dense v-if="showRecurring(dialogItem.type)">
                                <v-col cols="12" sm="4">
                                    <v-select outlined dense hide-details
                                        v-model="dialogItem.recurring" :items="recurring_options" label="Recurring?">
                                    </v-select>
                                </v-col>
                                <v-col cols="12" sm="4" v-if="dialogItem.recurring == 1 && dialogItem.type === 'other'">
                                    <v-select outlined dense hide-details
                                        v-model="dialogItem.recurrence_cycle" :items="recurrence_cycle_options" label="Cycle">
                                    </v-select>
                                </v-col>
                                <v-col cols="12" sm="4" v-if="dialogItem.recurring == 1 && dialogItem.type === 'late_fine'">
                                    <v-text-field outlined dense hide-details type="number"
                                        v-model="dialogItem.recurrence_interval_days" label="Repeat Every (days)">
                                    </v-text-field>
                                </v-col>
                            </v-row>

                            <!-- Fines -->
                            <h4 class="fw-600 m-t-15">Fine Settings</h4>
                            <hr class="m-t-5 m-b-10">
                            <v-row dense>
                                <v-col cols="12" sm="6">
                                    <v-text-field outlined dense hide-details
                                        v-model="dialogItem.max_amount" label="Max Fine Amount" type="number">
                                    </v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6">
                                    <v-select outlined dense hide-details
                                        v-model="dialogItem.apply_on" :items="apply_on_options" label="Apply On">
                                    </v-select>
                                </v-col>
                            </v-row>

                            <v-btn :disabled="!valid || loading" :loading="loading" color="success" class="mr-4 m-t-25" @click="save">Submit</v-btn>
                            <v-btn class="m-t-25" color="error" @click="close">Close</v-btn>
                        </v-form>
                    </v-card-text>
                </v-card>
            </v-dialog>


            <!-- Data Table -->
            <v-data-table :headers="headers" :items="filteredRows" :search="search.search_text"
                :loading="data_load" class="elevation-1 small-font-table" :options.sync="pagination">
                <template v-slot:progress>
                    <v-progress-linear color="blue" indeterminate></v-progress-linear>
                </template>
                <template v-slot:item="{ item }">
                    <tr>
                        <td>{{ item.name }}</td>
                        <td>{{ item.type }}</td>
                        <td>{{ item.fixed_amount }}</td>
                        <td>{{ item.percent_amount }}</td>
                        <td>{{ item.validity_period }}</td>
                        <td>{{ item.recurring ? 'Yes' : 'No' }}</td>
                        <td class="text-center">
                            <v-icon small color="success" class="mr-2" @click="editItem('edit',item)">edit</v-icon>
                            <v-icon small color="error" @click="deleteItem(item)">delete</v-icon>
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
                { text: "Type", value: "type" },
                { text: "Fixed Amount", value: "fixed_amount" },
                { text: "Percent (%)", value: "percent_amount" },
                { text: "Validity", value: "validity_period" },
                { text: "Recurring", value: "recurring" },
                { text: "Actions", value: "actions", sortable: false, align: "center" },
            ],
            pagination: { page: 1, itemsPerPage: 10 },
            rows: [],
            batch_list: [],
            search: { search_text: "", type: null },
            dialog: false,
            editedIndex: -1,
            editedId: null,
            dialogItem: {
                name: "", type: "", description: "",
                batch_ids: [], fixed_amount: 0, percent_amount: 0,
                echelon_id: [],
                validity_period: "forever", start_date: "", end_date: "",
                recurring: 0, recurrence_cycle: "", recurrence_interval_days: 0,
                apply_on: "admission", max_amount: 0
            },
            type_options: [
                { value: 'monthly_fee_rule', text: 'Monthly Fee Rule' },
                { value: 'course_fee_rule', text: 'Course Fee Rule' },
                { value: 'late_fine', text: 'Late Fine' },
                { value: 'other', text: 'Other' }
            ],
            validity_options: [
                { value: 'forever', text: 'Forever' },
                { value: 'date_range', text: 'Date Range' }
            ],
            recurring_options: [
                { value: 0, text: 'No' },
                { value: 1, text: 'Yes' }
            ],
            recurrence_cycle_options: [
                { value: 'daily', text: 'Daily' },
                { value: 'weekly', text: 'Weekly' },
                { value: 'monthly', text: 'Monthly' },
                { value: 'yearly', text: 'Yearly' }
            ],
            apply_on_options: [
                { value: 'admission', text: 'On Admission' },
                { value: 'admission_post_admission', text: 'Admission + Post Admission' },
                { value: 'post_admission', text: 'Post Admission' }
            ],
            valid: true,
            loading: false,
            data_load: false,
            date1_modal: false,
            date2_modal: false,
            rules: {
                required: v => !!v || "Required."
            },
            echelons: window.appData.echelons
        }
    },
    computed: {
        formTitle() {
            return this.editedIndex === -1 ? "New Fee Scheme" : "Edit Fee Scheme";
        },
        filteredRows() {
            if (!this.search.type) return this.rows;
            return this.rows.filter(r => r.type === this.search.type);
        },
        filteredBatches() {
            if (!this.dialogItem.echelon_id.length) return [];
            return this.batch_list.filter(batch => this.dialogItem.echelon_id.includes(batch.echelon_id))
        }
    },
    created() {
        this.fetchFeeSchemes();
        this.fetchBatches();
    },
    methods: {
        fetchBatches() {
            this.axios.get("/admin/request/admin_batch_list").then(res => {
                this.batch_list = res.data.admin_batch_list.map(b => ({ value: b.id, text: b.name, echelon_id: b.echelon_id }));
            });
        },
        fetchFeeSchemes() {
            this.data_load = true;
            this.axios.get("/admin/fee-schemes").then(res => {
                this.rows = res.data;
                this.data_load = false;
            });
        },
        save() {
            if (!this.$refs.form.validate()) return;
            this.loading = true;
            const url = this.editedIndex > -1
                ? `/admin/request/fee_scheme_edit/${this.editedId}`
                : "/admin/request/fee_scheme_add";
            this.axios.post(url, this.dialogItem).then(() => {
                this.fetchFeeSchemes();
                this.close();
                this.loading = false;
            }).catch(() => {
                this.loading = false;
            });
        },
        editItem(option, item) {
            if (option === 'new') {
                this.dialogItem = {
                    name: "", type: "", description: "",
                    batch_ids: [], echelon_id: [], fixed_amount: 0, percent_amount: 0,
                    validity_period: "forever", start_date: "", end_date: "",
                    recurring: 0, recurrence_cycle: "", recurrence_interval_days: 0,
                    apply_on: "admission", max_amount: 0
                };
                this.dialog = true;
            } else {
                this.editedIndex = this.rows.findIndex(r => r.id === item.id);
                this.dialogItem = Object.assign({}, item);
                this.editedId = item.id;
                this.dialog = true;
            }
        },
        deleteItem(item) {
            if (!confirm("Delete this fee scheme?")) return;
            this.axios.post(`/admin/request/fee_scheme_delete/${item.id}`).then(() => {
                this.fetchFeeSchemes();
            });
        },
        showRecurring(type) {
            return type === 'late_fine' || type === 'other';
        },

        // Control validity options based on type
        getValidityOptions(type) {
            if (type === 'monthly_fee_rule' || type === 'course_fee_rule') {
                this.dialogItem.validity_period = 'date_range'; // fix validity
                return [
                    { text: 'Date Range', value: 'date_range' }
                ];
            }
            // For late_fine and other, allow any options
            return this.validity_options;
        },
        onEchelonChange() {
            // Clear previously selected batches when class changes
            this.dialogItem.batch_ids = [];
        },
        close() {
            this.dialog = false;
        }
    }
}
</script>

<style scoped>
.setting_fee_scheme .v-card__text {
    padding: 0 16px 16px;
}
</style>
