<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="recharge_history">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">SMS Reacharge History</h3>
            <v-divider class="my-3"></v-divider>
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" style="border-top: 5px solid red;">
                    <v-container fluid>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px">Your Balance is {{balance}} BDT</div>
                        <v-btn :href="'/bkash-payment-sms'" color="info">Recharge Now</v-btn>
                    </v-container>
            </div>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <h4>Recharge History</h4>
                            <v-spacer></v-spacer>
                            <v-flex lg4>
                                <v-text-field :clearable=true class="m-r-25" v-model="search" label="Search" single-line hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                        <v-data-table :headers="headers" :items="sms_history" :search="search" :loading="data_load" class="elevation-1" :pagination.sync="pagination">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left"><a class="block black--text">{{ props.item.id }}</a></td>
                            <td class="text-xs-left"><a class="block black--text">{{ props.item.created_at }}</a></td>
                            <td class="text-xs-left"><a class="block black--text">{{ props.item.validity_date }}</a></td>
                            <td class="text-xs-left"><a class="block black--text">{{ props.item.amount }}</a></td>
                            <td class="text-xs-left"><a class="block black--text">{{ props.item.sms_quantity }}</a></td>
                            <td class="text-xs-left">{{ props.item.validity_for }}</td>
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
                    headers: [{text: 'ID', value: 'id'}, {text: 'Invoice Date', value: 'created_at'}, {text: 'Validity Date', value: 'validity_date'}, {text: 'Amount', value: 'amount'}, {text: 'SMS Quantity', value: 'sms_quantity'}, {text: 'Validity For', value: 'validity_for'},],
                    sms_history: [],
                    // @list_search_data
                    search: '',
                    data_load: false,
                    pagination: {
                        'sortBy': 'created_at',
                        'descending': true,
                        'page': 1,
                        'rowsPerPage': 5
                    },
                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name=='pay_bill');
                if (p > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {
                    next()
                } else {
                    next('/')
                }
            },
            // @load_method
            created() {
                this.get_sms_invoice_item();
            },

            methods: {
                get_sms_invoice_item() {
                    this.data_load = true;
                    this.axios.get('/admin/get_sms_recharge_history').then(response => {
                        this.sms_history = response.data.sms_history;
                        this.balance = response.data.balance;
                        this.data_load = false
                    });
                }
            }
    }
</script>