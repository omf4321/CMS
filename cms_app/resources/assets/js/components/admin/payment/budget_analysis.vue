<template>
    <div>
        <v-container fluid>
            <h3 class="headline">Cost Anaylysis</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <v-form ref="balance_report_form" v-model="valid" lazy-validation>
                                <div class="row">
                                    <div class="col-lg-3">
                                        <v-dialog full-width lazy ref="date_ref_1" width="290px">
                                            <v-text-field label="Date From" :rules="[v => !!v || 'required']" readonly slot="activator" :value="dialogItem.date_from"></v-text-field>
                                            <v-date-picker ref="picker_1" v-model="dialogItem.date_from"></v-date-picker>
                                        </v-dialog>  
                                    </div>
                                    <div class="col-lg-3">
                                        <v-dialog full-width lazy ref="date_ref_2" width="290px">
                                            <v-text-field :rules="[v => !!v || 'required']" label="Date To" readonly slot="activator" :value="dialogItem.date_to"></v-text-field>
                                            <v-date-picker ref="picker_2" v-model="dialogItem.date_to"></v-date-picker>
                                        </v-dialog>  
                                    </div>
                                    <div class="col-lg-2">
                                        <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="get_balance_analysis" :disabled="!valid">Go</v-btn>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2">
                                        <v-text-field label="Payment Deficit" v-model="dialogItem.deficit_payment"></v-text-field>
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field label="Office Cost" v-model="dialogItem.office_cost"></v-text-field>
                                    </div>
                                    <div class="col-lg-2">
                                        <v-text-field label="Other Cost" v-model="dialogItem.other_cost"></v-text-field>
                                    </div>
                                    <div class="col-lg-2">
                                        <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="update_total" :disabled="!valid">Update</v-btn>
                                    </div>
                                </div>
                            </v-form>                            
                            <div class="row">
                                <div class="col-lg-6">                                
                                    <h4 class="fs-14 fw-600">Final Total</h4>
                                    <hr>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div>Student Payment</div>
                                            <div>Class Payment</div>
                                            <div>Invigilator Payment</div>
                                            <div>Script Payment</div>
                                            <div>Salary</div>
                                            <div>House Rent</div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div>{{student_payment}}</div>
                                            <div>{{class_payment}}</div>
                                            <div>{{invigilator_payment}}</div>
                                            <div>{{script_payment}}</div>
                                            <div>{{salary}}</div>
                                            <div>{{house_rent}}</div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="fw-600 fs-16">Total: {{final_total}}</div>
                                </div>
                            </div>
                            <hr>
                            <div v-if="dialogItem.signable && sign">
                                <h4 class="fw-600 fs-14">Admin Signature</h4>
                                <div>I checked above transaction</div>
                                <v-btn small outline class="tiny-btn pos-rel m-l-5" style="top: 12px" @click="balance_signature">Signature</v-btn>
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
            dialogItem: {signable: false, deficit_payment: 0, office_cost: 0, other_cost: 0},
            salaries: [],
            message: '',
            valid: true,
            bank_account: [],
            rules: {
                required: value => !!value || 'Required.'
            },
            student_payment: 0,
            deficit_payment: 0,
            class_payment: 0,
            invigilator_payment: 0,
            script_payment: 0,
            salary: 0,
            house_rent: 0,
            final_total: 0,
            sign: false,
            day: [{'value': 'today', 'text': 'Today'}, {'value': 'yesterday', 'text': 'Yesterday'}]

        } //end return
    }
    , // @router_permission
    created() {
        // this.get_bank_account()
    },
    methods: {
        get_balance_analysis(){
            this.dialog = true
            this.dialogItem.signable = false
            this.axios.post('/admin/transaction/get_balance_analysis', this.dialogItem).then(response => {
                this.student_payment = response.data.student_payment                
                this.class_payment = response.data.class_payment
                this.invigilator_payment = response.data.invigilator_payment
                this.script_payment = response.data.script_payment
                this.salary = response.data.salary
                this.house_rent = response.data.house_rent

                this.final_total = this.student_payment - (this.class_payment + this.invigilator_payment + this.script_payment + this.salary + this.house_rent)

                this.dialog = false
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)                
            });
        },
        balance_signature(){
            if (!this.dialogItem.day) {return false}                
            this.dialog = true 
            this.dialogItem.final_total = this.final_total               
            this.axios.post('/admin/transaction/balance_signature', this.dialogItem).then(response => {
                this.dialog = false
                this.sign = true
                $('.user_nav').addClass('success')
                setTimeout(function() {$('.user_nav').removeClass('success') }.bind(this), 3000)
            }, error => {
                this.dialog = false
                $('.user_nav').addClass('denied')
                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                alert(error.response.data.salary)
            });
        },
        update_total(){
            this.final_total = this.student_payment - (this.class_payment + this.invigilator_payment + this.script_payment + this.salary + this.house_rent) - (parseInt(this.dialogItem.deficit_payment) + parseInt(this.dialogItem.office_cost) + parseInt(this.dialogItem.other_cost))
        }
    }
}

</script>

<style>
    .v-input--checkbox {
        margin-top: 0px;
        padding-top: 12px;
        margin-left: 20px;
    }
    .v-input--checkbox label {
        font-size: 15px!important;
        top: 3px!important;
        font-weight: 500;
    }
</style>