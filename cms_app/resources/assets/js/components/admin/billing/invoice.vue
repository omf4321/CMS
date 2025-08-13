<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="invoice_item">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">My Billing Information</h3>
            <v-divider class="my-3"></v-divider>
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15" style="border-top: 5px solid red;" v-if="latest_unpaid.status != 'paid'">
                    <v-container fluid>
                        <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px" v-html="latest_unpaid.message"></div>
                        <v-btn :href="'/bkash-payment/' + latest_unpaid.id" color="info">Pay Now</v-btn>
                    </v-container>
            </div>
            <template>
                <div>
                    <v-toolbar flat color="grey lighten-2 p-5">
                        <v-layout>
                            <h4>Billing Invoices</h4>
                            <v-spacer></v-spacer>
                            <v-flex lg4>
                                <v-text-field :clearable=true class="m-r-25" v-model="search" label="Search" single-line hide-details></v-text-field>
                            </v-flex>
                        </v-layout>
                    </v-toolbar>
                    <!-- @list_table @list_header @list_column -->
                        <v-data-table :headers="headers" :items="invoice_items" :search="search" :loading="data_load" class="elevation-1" :pagination.sync="pagination">
                        <v-progress-linear slot="progress" color="blue" indeterminate></v-progress-linear>
                        <template slot="items" slot-scope="props">
                            <td class="text-xs-left"><a class="block black--text" :href="'/admin/invoice/' + props.item.id">{{ props.item.id }}</a></td>
                            <td class="text-xs-left"><a class="block black--text" :href="'/admin/invoice/' + props.item.id">{{ props.item.created_at }}</a></td>
                            <td class="text-xs-left"><a class="block black--text" :href="'/admin/invoice/' + props.item.id">{{ props.item.due_date }}</a></td>
                            <td class="text-xs-left"><a class="block black--text" :href="'/admin/invoice/' + props.item.id">{{ props.item.amount }}</a></td>
                            <td><a :class="{'text-xs-left fw-600 fs-14':true, 'paid_color': props.item.status == 'paid', 'unpaid_color': props.item.status != 'paid'}" class="block" :href="'/admin/invoice/' + props.item.id">{{ props.item.status.toUpperCase() }}</a></td>
                            <td class="justify-center layout px-0" v-if="props.item.status != 'paid'">
                                <v-btn color="info m-t-10" :href="'/bkash-payment/' + props.item.id" class="tiny-btn">Pay Now</v-btn>
                                
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
                    headers: [{text: 'ID', value: 'id'}, {text: 'Invoice Date', value: 'created_at'}, {text: 'Due Date', value: 'due_date'}, {text: 'Total', value: 'amount'}, {text: 'Status', value: 'status'}, {text: 'Actions', value: 'name', sortable: false, align: 'center'}],
                    invoice_items: [],
                    // @list_search_data
                    search: '',
                    data_load: false,
                    latest_unpaid: window_latest_unpaid,
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
                this.get_invoice_item();
            },

            methods: {
                get_invoice_item() {
                    this.data_load = true;
                    this.axios.get('/admin/get_invoice').then(response => {
                        this.invoice_items = response.data.invoices;
                        this.data_load = false
                    });
                }
            }
    }
</script>

<style>

 .invoice_item table tr td {
    font-size: 12px!important;
 }
 .invoice_item .paid_color{
     color: #228B22;
 }
 .invoice_item .unpaid_color{
     color: #FF0000;
 }
</style>