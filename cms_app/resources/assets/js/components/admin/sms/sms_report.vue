<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="sms_reports">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">SMS Report</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <v-flex lg4>
                                <v-text-field :clearable=true class="m-r-25" v-model="search" label="Search" single-line hide-details></v-text-field>
                            </v-flex>
                            
                            <v-flex lg1></v-flex>

                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelon_id">
                            </v-autocomplete>
                            <v-autocomplete :clearable=true class="m-r-10" :disabled="echelon.length==0" :items="batch" label="Batch" v-model="dialogItem.batch_id">
                            </v-autocomplete>
                            <v-dialog v-model="date_from_dialog" full-width class="m-r-10" lazy ref="date_ref" width="290px">
                                <v-text-field :clearable=true label="From" readonly slot="activator" :value="dialogItem.from_date"></v-text-field>
                                <v-date-picker ref="picker" @change="date_from_dialog = false" v-model="dialogItem.from_date"></v-date-picker>
                            </v-dialog>
                            <v-dialog v-model="date_to_dialog" class="m-r-10" full-width lazy ref="date_ref_1" width="290px">
                                <v-text-field :clearable=true label="To" readonly slot="activator" :value="dialogItem.to_date"></v-text-field>
                                <v-date-picker ref="picker_1" @change="date_to_dialog = false" v-model="dialogItem.to_date"></v-date-picker>
                            </v-dialog>
                            <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_sms_report()">Go</v-btn>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                        <v-data-table :headers="headers" :items="sms_reports" :search="search" :loading="data_load" hide-actions class="elevation-1">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.name }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.reg_no }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.students.batches.name }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.students.batches.echelons.name }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.sms_text }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.status }}</td>
                            <td :class="{'text-xs-left':true, 'active_color': props.item.status != 'OK'}">{{ props.item.created_at | moment('DD MMM YY H:i:s') }}</td>
                            <td class="justify-center layout px-0">
                                <v-icon small class="mr-2" @click="editItem(props.item)">
                                    edit
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
                    // @list_column_data
                    headers: [{text: 'Name', align: 'left', value: 'name'}, {text: 'Reg No', value: 'reg_no'}, {text: 'Batch', value: 'students.batches.name'}, {text: 'Class', value: 'students.batches.echelons.name'}, {text: 'SMS', value: 'sms_text'}, {text: 'Status', value: 'status'}, {text: 'Sent At', value: 'created_date'}],
                    sms_reports: [],
                    branch: [],
                    batch: [],
                    original_batch: [],
                    echelon: [],
                    // @list_search_data
                    search: '',
                    data_load: false,
                    date_from_dialog: false,
                    date_to_dialog: false,
                    dialogItem: {}
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name=='view_sms_report');
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

            methods: {
                // @add_item_method
                admin_branch_list() {
                        this.branch = window_branch;
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
                    get_sms_report() {
                        if (!this.dialogItem.from_date && !this.dialogItem.to_date) {return false}
                        if (this.dialogItem.echelon_id && !this.dialogItem.batch_id) {return false}
                        this.data_load = true;
                        this.axios.post('/admin/sms/get_sms_report', this.dialogItem).then(response => {
                            var item = []
                            this.sms_reports = response.data.sms_reports;
                            this.data_load = false
                        });
                    },
                    get_branch() {
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                        }
                    }
            }
    }
</script>

<style>

 .sms_reports table tr td {
    font-size: 12px!important;
 }
 .sms_reports .active_color{
     background: #ffe2e2;
 }
</style>