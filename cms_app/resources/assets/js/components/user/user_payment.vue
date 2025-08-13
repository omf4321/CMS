<template>
    <div class="user_home">
        <v-container fluid>
            <h3 v-if="step=='none'" class="fs-20 m-t-10 dash_title">Payment Lists</h3>          
            <v-divider v-if="step=='none'" class="my-3"></v-divider>
            <!-- todays schedule -->
            <div v-if="payment_list.length>0 && step=='none'">
                <div class="row fs-10 text-center fw-600">
                    <div class="col-xs-3 p-r-2 p-l-2">Issue</div>
                    <div class="col-xs-2 p-r-2 p-l-2">Amount</div>
                    <div class="col-xs-2 p-r-2 p-l-2">Paid</div>
                    <div class="col-xs-2 p-r-2 p-l-2">Status</div>
                    <div class="col-xs-3 p-r-2 p-l-2">Action</div>
                </div>
                <hr class="m-t-5 m-b-8" style="border-color: #444">
                <div class="row fs-11 text-center" v-for="(payment, p_i) in payment_list">
                    <div class="col-xs-3 p-r-2 p-l-2">{{payment.created_at | moment("D MMM YY")}}</div>
                    <div class="col-xs-2 p-r-2 p-l-2">{{payment.amount}}</div>
                    <div class="col-xs-2 p-r-2 p-l-2">{{payment.paid}}</div>
                    <div :class="{'col-xs-2': true, 'text-success': payment.status != 'unpaid', 'text-danger': payment.status == 'unpaid', 'fw-600': true}">{{payment.status.substring(0,4).toUpperCase()}}</div>
                    <div class="col-xs-3 p-r-2 p-l-2">
                        <v-icon class="inline-block fs-16 m-r-5 cur-p" color="primary" @click="get_payment_detail(payment)">double_arrow</v-icon>
                        <v-icon v-if="payment.status != 'confirmed'" class="inline-block fs-16 cur-p" @click="confirm_payment(payment.id)" color="success">check</v-icon>
                    </div>
                    <div style="clear: both; float: none" class="container p-t-0 p-b-0">                        
                        <hr class="m-t-5 m-b-5" style="border-color: #444">
                    </div>
                </div>
            </div>
            <div style="margin: auto; padding-top: 10px;" v-if="step=='detail'">
                <h4 class="text-center pos-rel">Payment Details ({{dialogItem.amount}}) <v-icon style="right: 0px; top:-5px" @click="step = 'none'" class="float-right pos-a">close</v-icon></h4>
                <div class="fw-600 m-t-20 fs-12 text-center">From {{dialogItem.date_from | moment("D MMM YY")}} to {{dialogItem.date_to | moment("D MMM YY")}}</div>
                <div v-if="due_amount" class="m-t-10">Previous Due: {{due_amount}}</div>
                <hr>
                <div v-if="class_payment.length">
                    <h4 class="fs-12 fw-600">Class Payment ({{class_payment_total}})</h4>
                    <table class="table table-bordered fs-12 m-t-10">
                        <thead class="fs-10">
                          <tr>
                            <th>Type</th>
                            <th>Batch Name</th>
                            <th>Total Class</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payment in class_payment" class="fs-11">
                            <td>{{payment.new_batch_id ? 'Special' : 'General'}}</td>
                            <td>{{payment.batch_name}}</td>
                            <td>{{payment.count_class}}</td>
                            <td>{{payment.amount}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="invigilator_payment.length">
                    <h4 class="fs-12 fw-600">Invigilation Payment ({{invigilator_payment_total}})</h4>
                    <table class="table table-bordered fs-12 m-t-10">
                        <thead class="fs-10">
                          <tr>
                            <th>Type</th>
                            <th>Total Exam</th>
                            <th>Total Minutes</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payment in invigilator_payment"  class="fs-11">
                            <td>General</td>
                            <td>{{payment.count_class}}</td>
                            <td>{{payment.total_minutes}}</td>
                            <td>{{payment.amount}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="script_payment.length">
                    <h4 class="fs-12 fw-600">Script Payment ({{script_payment_total}})</h4>
                    <table class="table table-bordered fs-12 m-t-10">
                        <thead class="fs-10">
                          <tr>
                            <th>Type</th>
                            <th>Mark Level</th>
                            <th>Total Script</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                        <tr v-for="payment in script_payment" class="fs-11">
                            <td>General</td>
                            <td>{{payment.mark}}</td>
                            <td>{{payment.count_script}}</td>
                            <td>{{payment.amount}}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </v-container>
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

<script>
    export default {
        data() {
                return {                    
                    payment_list: [],                    
                    dialog: false,
                    schedule_alert: false,
                    alert_type: '',                    
                    step: 'none',
                    teacher_id: window_teacher_id,
                    dialogItem: {},
                    invigilator_payment: [],
                    script_payment: [],
                    class_payment: [],
                    due_amount: 0,
                    class_payment_total: 0,
                    invigilator_payment_total: 0,
                    script_payment_total: 0,
                }
            },
            props: ['source'],
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            created() {
                this.get_payment_list()
            },
            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                },
                ordinal_number: function(value) {
                    if (value == 1) {return value = value + 'st'}
                    if (value == 2) {return value = value + 'nd'}
                    if (value == 3) {return value = value + 'rd'}
                    if (value > 3) {return value = value + 'th'}
                }
            },
            methods: {
                    get_payment_list() {
                        this.dialog = true
                        Vue.axios.get('/user/payment/get_payment_list').then(response => {
                            this.payment_list = response.data.payment_list;
                            this.dialog = false
                        });
                    },
                    get_payment_detail(payment){
                        this.dialog = true
                        this.dialogItem.date_from = payment.date_from
                        this.dialogItem.date_to = payment.date_to
                        Vue.axios.post('/user/payment/get_payment_detail', payment).then(response => {
                            this.dialog = false
                            this.invigilator_payment = response.data.invigilator_payment;
                            this.script_payment = response.data.script_payment;
                            this.class_payment = response.data.class_payment;
                            this.due_amount = response.data.due_amount;
                            this.class_payment_total = response.data.class_payment_total; 
                            this.invigilator_payment_total = response.data.invigilator_payment_total; 
                            this.script_payment_total = response.data.script_payment_total; 
                            this.dialogItem.amount = this.class_payment_total + this.invigilator_payment_total + this.script_payment_total + this.due_amount
                            this.step = 'detail'
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    },
                    confirm_payment(id){
                        var con = confirm("Are you confirm?")
                        if (!con) {return false}
                        this.dialog = true
                        Vue.axios.post('/user/payment/confirm_payment/' + id).then(response => {
                            this.dialog = false
                            this.payment_list()
                        }, error => {
                            this.dialog = false
                            $('.user_nav').addClass('denied')
                            setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                        });
                    },                    
            }
    } 
</script>

<style>
  .v-input--checkbox .v-input__slot{
    margin-bottom: 0px!important;
  }
  .v-input--checkbox.v-input--selection-controls{
    margin-top: 0px;
    padding-top: 0px;
  }
    .v-input--checkbox .v-input--selection-controls:not(.v-input--hide-details) .v-input__slot{
    margin-bottom: 0px;
  }
  .v-messages {
    display: none;
  }
  .calculator-btn .v-btn__content{
    font-size: 25px!important;
  }
</style>