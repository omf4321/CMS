<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="salary">
        <v-container fluid>
            <h3 class="headline">Salaries</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="salary_form" v-model="valid" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <v-autocomplete @change="get_salary" v-model="dialogItem.admin_id" :items="admin" label="Admin"></v-autocomplete>  
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field v-model="dialogItem.amount" label="Amount" :rules="[v => !!v || 'required']" type="number"></v-text-field>
                                    </div> 
                                    <div class="col-lg-2">
                                        <v-text-field v-model="dialogItem.bonus" type="number" label="Bonus"></v-text-field>
                                    </div> 
                                </div>
                                <div class="m-t-10">
                                    <v-btn small color="success" @click="add_salary">Add</v-btn>
                                </div>
                            </v-form>
                            <hr v-if="salaries.length">
                            <div class="row" v-if="salaries.length">
                                <div class="col-lg-6">
                                    <h4>Salary Detail</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Admin</th>
                                            <th>Month</th>
                                            <th>Amount</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(salary, s_i) in salaries">
                                            <td>{{salary.users.name}}</td>
                                            <td>{{salary.month | moment('MMM YY')}}</td>
                                            <td>{{salary.amount + salary.bonus}}</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
                            <hr v-if="transaction_list.length">
                            <div class="row" v-if="transaction_list.length">
                                <div class="col-lg-6">
                                    <h4>Transaction Detail</h4>
                                    <table class="table table-bordered fs-12 m-t-10">
                                        <thead>
                                          <tr>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                        <tr v-for="(transaction, t_i) in transaction_list">
                                            <td>{{transaction.users.name}}</td>
                                            <td>{{transaction.amount}}</td>
                                            <td>{{transaction.created_at | moment('D MMM YY')}}</td>
                                            <td><v-icon v-if="today(transaction.created_at)" class="fs-14" @click="delete_salary(transaction.id, t_i)">delete</v-icon></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div> 
                        </v-container>
                    </template>
                </div>
            </template>
        </v-container>
        <!-- loader dialog -->
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

<script> export default {
    data() {
        return {
            dialog: false,
            dialogItem: {},
            salaries: [],
            message: '',
            valid: true,
            admin: [],
            rules: {
                required: value => !!value || 'Required.'
            },
            transaction_list: [],

        } //end return
    }
    , // @router_permission
    created() {
        this.get_admin()
    },
    beforeRouteEnter (to, from, next) {
        let p=window_admin_permissions.findIndex(x=> x.name == 'manage_transaction');
        if (p>-1 || window_admin_role=='superadmin' || window_admin_role=='admin') {
            next()
        }
        else {
            next('/')
        }
    },
    methods: {
        get_salary(){         
            if (!this.dialogItem.admin_id) {return false}       
            this.dialog = true
            this.axios.get('/admin/transaction/get_salary/' + this.dialogItem.admin_id).then(response => {
                this.transaction_list = response.data.transaction_list
                this.salaries = response.data.salaries
                this.dialog = false
            });
        },
        get_admin(){
            this.axios.get('/admin/transaction/get_admin').then(response => {
                this.admin = response.data.admin
                this.dialog = false
                this.admin = this.admin.map(arr => {return {value: arr['id'], text: arr['name'] } })
            });
        },
        add_salary(){           
            if(this.$refs.salary_form.validate()){                
                this.dialog = true                
                this.axios.post('/admin/transaction/add_salary', this.dialogItem).then(response => {
                    this.get_salary()
                    this.$refs.salary_form.reset()
                    this.dialog = false
                    $('.user_nav').addClass('success')
                    setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
                }, error => {
                    this.dialog = false
                    $('.user_nav').addClass('denied')
                    setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                    alert(error.response.data.salary)
                });
            }
        },
        delete_salary(id, t_i){
            var con = confirm("Are you sure to delete?")
            if (!con) {return false}            
            this.dialog = true                
            this.axios.post('/admin/transaction/delete_salary/' + id, this.dialogItem).then(response => {
                this.get_salary()
                this.dialog = false
                $('.user_nav').addClass('success')
                setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
            });
        },
        today(date){
            // Create date from input value
            var inputDate = new Date(date);

            // Get today's date
            var todaysDate = new Date();

            // call setHours to take the time out of the comparison
            if(inputDate.setHours(0,0,0,0) == todaysDate.setHours(0,0,0,0)) {
                return true
            }
            return false
        }
    }
}

</script>

<style>
    .salary .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .salary .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>