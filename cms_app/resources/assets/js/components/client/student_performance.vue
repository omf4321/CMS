<template>
    <div class="client_home">
        <v-container fluid>
        <!-- Attendance -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title">Attendance <span class="float-right fw-500 fs-12"><span class="m-r-10 cur-p">Present</span><span class="cur-p">Absent</span></span> </h4>
                <hr class="m-t-5 m-b-0" style="border-bottom: 1px solid">
                <v-layout class="m-b-15">
                    <template>
                        <v-dialog full-width lazy width="290px" class="m-r-10">
                            <v-text-field label="Date To" readonly slot="activator" v-model="attendance_date_from"></v-text-field>
                            <v-date-picker v-model="attendance_date_from"></v-date-picker>
                        </v-dialog>
                    </template>
                    <template>
                        <v-dialog full-width lazy width="290px" class="m-r-10">
                            <v-text-field label="Date To" readonly slot="activator" v-model="attendance_date_to"></v-text-field>
                            <v-date-picker v-model="attendance_date_to"></v-date-picker>
                        </v-dialog>
                    </template>
                    <v-btn outline color="primary" class="tiny-btn" @click="" style="top: 15px">GO</v-btn>
                </v-layout>
                <div>
                    <!-- <p class="dash_card_subtitle">{{schedule.echelons.name}}</p> -->
                    <div class="m-b-10" v-for="n in 7">
                        <div class="row">
                            <div class="col-xs-6 p-r-5">
                                <div class="fw-500 fs-13">10 Jul 20, Sun</div>
                            </div>
                            <div :class="{'col-xs-6':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-13">Present</div>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
            </div>
        <!-- Exam -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title">Exam Records </h4>
                <hr class="m-t-5 m-b-0" style="border-bottom: 1px solid">
                <v-layout class="m-b-15">
                    <template>
                        <v-dialog full-width lazy width="290px" class="m-r-10">
                            <v-text-field label="Date To" readonly slot="activator" v-model="attendance_date_from"></v-text-field>
                            <v-date-picker v-model="attendance_date_from"></v-date-picker>
                        </v-dialog>
                    </template>
                    <template>
                        <v-dialog full-width lazy width="290px" class="m-r-10">
                            <v-text-field label="Date To" readonly slot="activator" v-model="attendance_date_to"></v-text-field>
                            <v-date-picker v-model="attendance_date_to"></v-date-picker>
                        </v-dialog>
                    </template>
                    <v-btn outline color="primary" class="tiny-btn" @click="" style="top: 15px">GO</v-btn>
                </v-layout>
                <div>
                    <!-- <p class="dash_card_subtitle">{{schedule.echelons.name}}</p> -->
                    <div class="m-b-10" v-for="n in 5">
                        <div class="row">
                            <div class="col-xs-4 p-r-5">
                                <div class="fw-600 fs-13">10 Jul 20</div>
                                <div class="fs-12">Chem</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-12">F.Marks: 40</div>
                                <div class="fs-12">H.Marks: 34</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true}">
                                <div class="fs-12">Ob: 14</div>
                                <div class="fs-12">Sub: 8</div>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
            </div>
        <!-- Payments -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title">Payments </h4>
                <hr class="m-t-5 m-b-15" style="border-bottom: 1px solid">
                <div>
                    <div class="m-b-10">
                        <div class="row">
                            <div :class="{'col-xs-12':true, 'p-r-5':true}">
                                <v-layout>
                                    <div class="fs-14 m-r-10" style="color: #f44336">You didn't pay for August, 2020</div>
                                    <v-btn color="success" class="tiny-btn m-t-0 m-b-0 p-r-4 p-l-4" @click="" v-if="">Pay</v-btn>
                                </v-layout>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
                <div>
                    <!-- <p class="dash_card_subtitle">{{schedule.echelons.name}}</p> -->
                    <div class="m-b-10" v-for="n in 3">
                        <div class="row">
                            <div class="col-xs-4 p-r-5">
                                <div class="fw-600 fs-13">10 Jul 20</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true, 'p-r-5':true}">
                                <div class="fs-12">Month: July</div>
                                <div class="fs-12">Total: 1500</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true}">
                                <div class="fs-12">Paid: 1300</div>
                                <div class="fs-12">Due: 200</div>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
            </div>
        </v-container>
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    attendance_date_from: '',
                    attendance_date_to: ''
                }
            },
            props: ['source'],
            beforeRouteEnter(to, from, next) { 
                if (window_authorise != 'authorised') {return next('/authentication')}  
                else { return next()}
            },
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            created() {
                
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
</style>