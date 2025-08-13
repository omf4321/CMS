<template>
    <div class="admin_home p-t-100">
        <v-container class="admin_home">
            <v-toolbar rounded color="grey lighten-1" elevation="2">
                <v-toolbar-title class="attractive-heading-1"> Admin Dashboard </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-layout class="m-t-20" style="max-width: 600px;">
                    <v-select outlined dense class="m-r-15" :items="year" label="Year" v-model="dialogItem.year" @change="change_branch"></v-select>
                    <v-select outlined dense class="m-r-15" :items="branch" label="Branch" v-model="dialogItem.branch_id" @change="change_branch"></v-select>
                    <v-select outlined dense class="m-r-15" :items="echelon" label="Class" v-model="dialogItem.echelon_id" @change="change_branch"></v-select>
                    <v-autocomplete outlined dense class="m-r-15" :items="batch" label="Batch" v-model="dialogItem.batch_id" @change="change_batch"></v-autocomplete>
                </v-layout>
            </v-toolbar>
            <hr>
            <div class="card p-l-15 p-r-15 m-b-25" style="border-top: 5px solid red;" v-if="latest_unpaid.status != 'paid'">
                <v-container fluid>
                    <div style="font-size: 16px; font-weight: 600; margin-bottom: 10px" v-html="latest_unpaid.message"></div>
                    <v-btn :href="'/bkash-payment/' + latest_unpaid.id" color="info">Pay Now</v-btn>
                </v-container>
            </div>
            <!-- Counter -->
            <template>
                <v-row >
                    <v-col cols="12" md="7" class="px-md-10">
                        <v-row>
                            <v-col sm="6">
                                <template>
                                  <v-card color="red lighten-1" dark>
                                    <v-card-text class="d-flex align-center justify-space-around">
                                      <div class="text-center">
                                        <v-icon large>mdi-account-multiple</v-icon>
                                        <div class="overline">Active Students</div>
                                      </div>
                                      <v-divider vertical></v-divider>
                                      <div class="headline">{{dashboard_detail.students}}</div>
                                    </v-card-text>
                                  </v-card>
                                </template>
                            </v-col>
                            <v-col sm="6">
                                <template>
                                    <v-card color="info" dark>
                                        <v-card-text class="d-flex align-center justify-space-around">
                                            <div class="text-center">
                                                <v-icon large>emoji_people</v-icon>
                                                <div class="overline">Attendance</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">Today</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">This Month</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </template>

                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col sm="6">
                                <template>
                                    <v-card color="purple lighten-1" dark>
                                        <v-card-text class="d-flex align-center justify-space-around">
                                            <div class="text-center">
                                                <v-icon large>mdi-account-check</v-icon>
                                                <div class="overline">Admission</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">Today</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">This Month</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-col>
                            <v-col sm="6">
                                <template>
                                  <v-card color="teal darken-1" dark>
                                    <v-card-text class="d-flex align-center justify-space-around">
                                      <div class="text-center">
                                        <v-icon large>perm_identity</v-icon>
                                        <div class="overline">Active Teachers</div>
                                      </div>
                                      <v-divider vertical></v-divider>
                                      <div class="headline">{{dashboard_detail.teachers}}</div>
                                    </v-card-text>
                                  </v-card>
                                </template>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col sm="6">
                                <template>
                                  <v-card color="brown" dark>
                                    <v-card-text class="d-flex align-center justify-space-around">
                                      <div class="text-center">
                                        <v-icon large>textsms</v-icon>
                                        <div class="overline">SMS Balance</div>
                                      </div>
                                      <v-divider vertical></v-divider>
                                      <div class="headline">{{dashboard_detail.students}}</div>
                                    </v-card-text>
                                  </v-card>
                                </template>
                            </v-col>
                            <v-col sm="6">
                                <template>
                                    <v-card color="success" dark>
                                        <v-card-text class="d-flex align-center justify-space-around">
                                            <div class="text-center">
                                                <v-icon large>payment</v-icon>
                                                <div class="overline">Fees</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">Today</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                            <v-divider vertical></v-divider>
                                            <div class="text-center">
                                                <div class="">This Month</div>
                                                <div class="overline">{{dashboard_detail.attendance}}</div>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </v-col>
                        </v-row>
                        <hr>
                        <v-row>
                            <div class="text-center mt-4" style="width: 100%;">
                                <v-btn outlined color="primary" class="font-weight-medium" style="font-size: 13px;" @click="">
                                  View All Report
                                </v-btn>
                            </div>
                        </v-row>
                    </v-col>
                    
                    <v-col cols="12" md="5">
                        <v-row>
                            <v-container>
                                <template>
                                    <v-card style="height: 300px; width: 100%;">
                                        <v-card-title class="cyan darken-1" style="padding: 10px 20px;">
                                            <span class="fs-16 white--text">Today's Schedule</span>
                                            <v-spacer></v-spacer>
                                            <router-link :to="{name: 'schedule_manage'}">
                                                <span class="cur-p fs-12 fc-white">All Schedule</span>
                                            </router-link>
                                        </v-card-title>
                                        <v-container style="overflow-y: auto; height: 250px;">
                                            <div class="mb-10" v-for="sch in schedule" :key="sch.id">
                                                <v-row>
                                                    <v-col cols="3" class="pr-5">
                                                        <div class="fs-12">{{ sch.subjects ? sch.subjects.name : "" }}</div>
                                                        <div class="fs-12">{{ sch.teachers ? sch.teachers.name : "" }}</div>
                                                    </v-col>
                                                    <v-col cols="3" class="pl-5 pr-5">
                                                        <div class="fs-12">{{ sch.batches.branches.name }}</div>
                                                        <div class="fs-12">Chittagong</div>
                                                    </v-col>
                                                    <v-col cols="3" class="pl-5 pr-5">
                                                        <div class="fs-12">{{ sch.period | ordinal_number }} Period</div>
                                                        <div class="fs-12">{{ sch.time }}</div>
                                                    </v-col>
                                                    <v-col cols="3" class="pl-5">
                                                        <div class="fs-12">{{ sch.schedule_type.replace('_', ' ') }}</div>
                                                    </v-col>
                                                </v-row>
                                                <v-divider class="my-2"></v-divider>
                                            </div>
                                            <div class="fs-medium text-center" v-if="!schedule.length">No Schedule Today</div>
                                        </v-container>
                                    </v-card>
                                </template>
                            </v-container>
                        </v-row>
                        <v-row>
                            <v-container>
                                <v-card style="height: 400px; width: 100%;">
                                    <v-card-title class="cyan darken-1" style="padding: 10px 20px">
                                        <span class="fs-16 white--text">Notice Board</span>
                                        <v-spacer></v-spacer>
                                        <span class="cur-p fs-12 fc-white">More</span>
                                    </v-card-title>
                                    <v-container style="height: 350px; overflow: auto">
                                        <div v-for="n in 5">
                                            <div class="fs-12" style="color: #808080">16 SEP, 20</div>
                                            <div><span class="fs-12 fw-600" style="color: #03a9f4">Morshedul Alam</span><span class="m-l-10 fs-11" style="color: #808080">1 day ago</span></div>
                                            <div class="fs-13 m-t-5" style="color: #3d3d3d">Should print a dummy software</div>
                                            <hr class="m-t-5 m-b-15">
                                        </div>
                                    </v-container>
                                </v-card>
                            </v-container>
                        </v-row>
                    </v-col>
                </v-row>
            </template>
            <hr>
        </v-container>
        <!-- loader dialog -->
        <v-dialog v-model="dialog" hide-dialog persistent width="100" style="overflow:hidden">
            <v-card color="#555555" class="text-center p-5 p-t-17">
                <v-card-text>
                    <v-progress-circular indeterminate color="white" class="mb-0"></v-progress-circular>
                </v-card-text>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
    export default {

        data() {
            return {
                dialogItem: {branch_id: 'all', batch_id: 'all', echelon_id: 'all', year: new Date().getFullYear()},
                batch: [],
                today: this.$moment(new Date()).format("YYYY-MM-DD"),
                dialog: false,
                dashboard_detail: {'students': 0, 'teachers': 0, 'attendance': 0, 'fees': 0},
                fee_collection: {'total_fee': 0, 'collected': 0, 'due': 0},
                schedule: [],
                fee_update: true,
                latest_unpaid: window_latest_unpaid,
                year: [new Date().getFullYear() - 1, new Date().getFullYear()],
                echelon: []
            }
        },
        beforeRouteEnter(to, from, next) {
            if (window_latest_unpaid.overdue == 'true') {
                next('/billing_invoice')
            }
            let p = window_admin_permissions.findIndex(x => x.name == 'view_dashboard');
            if (p > -1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {
                next()
            } else {
                next('/')
            }
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
                if (value > 3) {return value = value + 'th'} }
        },
        props: ['source'],
        created() {
            this.admin_branch_list()          
        },
        computed: {
            eventsMap () {
                const map = {}
                this.events.forEach(e => (map[e.date] = map[e.date] || []).push(e))
                return map
            }
        },
        methods: {
            logout() {
                this.$refs.logout_form.submit()
            },
            admin_branch_list() {
                this.branch = window_branch
                this.branch = this.branch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                this.echelon = window_echelons.map(arr => {return {value: arr['id'], text: arr['name'] } })
                this.branch.push({value: 'all', text: 'All Branch'})
                this.echelon.push({value: 'all', text: 'All Branch'})
                this.change_branch()
            },
            change_branch(){
                this.dialog = true
                this.fee_update = false
                this.axios.get('/admin/get_admin_dashboard/' + this.dialogItem.branch_id + '/' + this.dialogItem.batch_id).then(response => {
                    this.batch = response.data.batch.map(arr => {return {value: arr['id'], text: arr['name'] } })
                    this.batch.push({value: 'all', text: 'All Batch'})
                    this.dashboard_detail.students = response.data.student.length
                    this.dashboard_detail.teachers = response.data.teacher
                    this.dashboard_detail.attendance = response.data.attendance
                    this.dashboard_detail.fees = response.data.fee
                    this.fee_collection.total_fee = response.data.total_fee
                    this.fee_collection.collected = response.data.fee
                    this.fee_collection.due = response.data.total_fee - response.data.fee
                    this.schedule = response.data.schedule
                    this.fee_update = true
                    this.dialog = false 
                });                
            },
            change_batch(){
                this.change_branch()
            }
        } //end of method
    }

</script>

<style>
  .admin_home .v-input--checkbox.v-input--selection-controls .v-input__slot{
    margin-bottom: 0px!important;
  }
  .admin_home .v-input--checkbox .v-messages{
    display: none;
  }
  .admin_home .v-input--checkbox.v-input--selection-controls {
    margin-top: 0px;
    padding-top: 0px;
  }
  .admin_home .my-event {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    border-radius: 2px;
    background-color: #1867c0;
    color: #ffffff;
    border: 1px solid #1867c0;
    width: 100%;
    font-size: 12px;
    padding: 3px;
    cursor: pointer;
    margin-bottom: 1px;
    text-align: center;
  }
  .admin_home .v-calendar-toolbar .v-toolbar__content{
    height: auto!important;
  }
  .admin_home .v-calendar-weekly__day-label{
    width: 100%;
  }
  .admin_home .v-calendar-weekly__day-month{
    width: 100%;
    text-align: center;
    left: 0;
    top: 20px;
  }
  .admin_home .v-calendar-weekly__head{
    text-align: center;
  }
  .admin_home .v-calendar-weekly__head-weekday{
        padding: 10px 5px;
  }
  .admin_home .v-calendar-weekly__day.v-present .v-calendar-weekly__day-label {
    width: 32px;
    left: 50%;
    transform: translate(-50%, 0);
  }
  .admin_home .bar-chart div {
    width: 20px; 
    background-color: #1976d2; 
    margin-right: 15px; 
    display: inline-block;
  }
</style>