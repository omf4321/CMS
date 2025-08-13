<template>
    <div class="client_home">
        <v-container fluid>
            <!-- Study Materials -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title"> Study Materials </h4>
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
                                <div class="fs-12">Worksheet</div>
                                <div class="fs-12">Chapter 10</div>
                            </div>
                            <div :class="{'col-xs-4':true, 'p-l-5':true}">
                                <v-btn outline color="primary" class="tiny-btn" style="height: 25px; top: -3px" @click="" v-if=""><v-icon small>cloud_download</v-icon></v-btn>
                            </div>
                        </div>
                        <hr class="m-t-10 m-b-10">
                    </div>
                </div>
            </div>
            <!-- Feedback -->
            <div class="card p-l-15 p-r-15 p-t-5 p-b-10 m-b-15">
                <h4 class="fw-600 fs-17 dash_card_title"> Feedback </h4>
                <hr class="m-t-5 m-b-0" style="border-bottom: 1px solid">
                <div>
                    <!-- <p class="dash_card_subtitle">{{schedule.echelons.name}}</p> -->
                    <v-form ref="form_feedback" v-model="valid_feedback" lazy-validation>
                        <v-text-field v-model="feedback.subject" :rules="[rules.required]" label="Subject"></v-text-field>
                        <v-textarea v-model="feedback.comment" :rules="[rules.required]" label="Comment"></v-textarea>
                        <v-btn small class="success" :disabled="!valid_feedback || loading" :loading="loading" @click="">Send</v-btn>
                    </v-form>
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
                    attendance_date_to: '',
                    valid_feedback: true,
                    feedback: {subject: '', comment: ''},
                    rules: {
                        required: value => !!value || 'Required.'
                    },
                    loading: false,
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