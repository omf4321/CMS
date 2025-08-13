<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="send_sms">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="fs-20 m-t-0">Send SMS</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <v-form ref="form" v-model="valid" lazy-validation>
                        <div style="width: 90%">
                            <v-layout>
                                <v-flex class="m-r-10" md2>
                                    <v-autocomplete v-model="dialogItem.sms_type" :items="sms_type"></v-autocomplete>
                                </v-flex>
                                <v-flex class="m-r-10" md4 v-if="dialogItem.sms_type!='one_to_one'">
                                    <v-combobox multiple hide-selected ref="combobox_echelon" small-chips :clearable=true :disabled="echelon.length==0" @change="get_batch()" :items="echelon" label="Class" v-model="dialogItem.echelons">
                                    </v-combobox>
                                </v-flex>
                                <v-flex class="m-r-10" md4 v-if="dialogItem.sms_type!='one_to_one'">
                                    <v-combobox multiple hide-selected ref="combobox_batch" small-chips :clearable=true :disabled="batch.length==0" :items="batch" label="Batch" v-model="dialogItem.batches">
                                    </v-combobox>
                                </v-flex>
                                <v-flex class="m-r-10" md2>
                                    <v-select v-model="dialogItem.student_status" :items="student_status"></v-select>
                                </v-flex>
                                <v-flex class="m-r-10" md2>
                                    <v-autocomplete v-model="dialogItem.month" label="From Month" :items="month"></v-autocomplete>
                                </v-flex>
                                <v-flex class="m-r-10" md2>
                                    <v-text-field v-model="dialogItem.year" label="Year"></v-text-field>
                                </v-flex>
                            </v-layout>
                            <v-layout>
                                <v-text-field v-model="dialogItem.campaign_name" label="Campaign Name"></v-text-field>
                            </v-layout>
                            <v-layout v-if="dialogItem.sms_type=='one_to_one'">
                                <v-text-field type="number" v-model="dialogItem.reg_no" label="Reg No"></v-text-field>
                            </v-layout>
                            <v-layout v-if="dialogItem.sms_type=='one_to_one'">
                                <v-text-field type="number" v-model="dialogItem.mobile" label="Mobile"></v-text-field>
                            </v-layout>
                            
                            <v-layout>
                                <v-btn class="tiny-btn" color="info" @click="btn_update_text('std_name')">#std name</v-btn>
                                <v-btn class="tiny-btn m-l-15" color="info" @click="btn_update_text('reg_no')">#reg no</v-btn>
                            </v-layout>

                            <v-layout>
                                <v-textarea :clearable=true class="fs-14" counter rows="3" v-model="dialogItem.sms" :rules="[v => !!v || 'SMS is required']" label="SMS" required></v-textarea>
                            </v-layout>
                            <v-layout>
                                <v-select style="max-width: 150px" v-model="dialogItem.sms_language" label="SMS Language" :items="language"></v-select>
                                <v-autocomplete style="max-width: 200px" class="m-l-15" :items="destination_number_data" v-model="dialogItem.destination_number" label="Destination"></v-autocomplete>
                            </v-layout>
                        </div>
                    </v-form>
                    <v-btn color="success" @click="send">Send</v-btn>
                </div>
            </template>
        </v-container>
        <v-dialog v-model="sms_dialog" persistent width="800">
            <v-card>
                <v-card-title class="p-t-10 p-b-10 pos-rel">
                    <h4 class="fs-20">SMS Report <v-icon class="pos-a" style="right: 15px;" @click="sms_dialog=false">close</v-icon></h4>
                </v-card-title>
                <hr class="m-b-5 m-t-5">
                <v-card-text class="p-t-5">
                    <div class="p-r-15 p-l-15">Current Balance: <span class="fw-600">{{ sms_balance }}</span> <span class="float-right">SMS Failed: {{sms_failed.length}}/{{sms_report.length}}</span></div>
                    <div :class="{'text-danger': sms_report[0]!='Ok', 'fs-15': true, 'p-15': true}" v-if="dialogItem.sms_type == 'one_to_one'">{{sms_report[0]}}</div>
                    <v-container grid-list-md>
                        <table class="table table-bordered fs-12" v-if="dialogItem.sms_type!='one_to_one'">
                            <thead>
                              <tr>
                                <th>Reg No</th>
                                <th>Name</th>
                                <th>SMS</th>
                                <th>Status</th>
                              </tr>
                            </thead>
                            <tbody>
                            <tr v-for="report in sms_report">
                                <td>{{report.reg_no}}</td>
                                <td>{{report.name}}</td>
                                <td>{{report.sms}}</td>
                                <td :class="{'text-danger': report.status !='OK'}">{{report.status == 'OK' ? 'Sent Successfully' : report.status}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </v-container>
                </v-card-text>
            </v-card>
        </v-dialog>
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
                    // @list_header_data
                    sms_type: [{value: 'list_sms', text: 'List SMS'}, {value: 'one_to_one', text: 'One SMS to One'}],

                    language: [{value: 'bangla', text: 'Bangla'}, {value: 'english', text: 'English'}],

                    student_status: [{value: 'present', text: 'Present'}, {value: 'droped', text: 'Droped'}, {value: 'inactive', text: 'Inactive'}, {value: 'all', text: 'All'}],

                    month: [{'value': 1, 'text': 'Jan'}, {'value': 2, 'text': 'Feb'}, {'value': 3, 'text': 'Mar'}, {'value': 4, 'text': 'Apr'}, {'value': 5, 'text': 'May'}, {'value': 6, 'text': 'Jun'}, {'value': 7, 'text': 'Jul'}, {'value': 8, 'text': 'Aug'}, {'value': 9, 'text': 'Sep'}, {'value': 10, 'text': 'Oct'}, {'value': 11, 'text': 'Nov'}, {'value': 12, 'text': 'Dec'}],
                    // @list_column_data
                    branch: [],
                    batch: [],
                    original_batch: [],
                    echelon: [],
                    dialogItem: {
                        sms_type: 'list_sms',
                        student_status: 'present',
                        echelons: [],
                        batches: [],
                        reg_no: '',
                        year: '',
                        sms_language: 'bangla',
                        destination_number: 'student'
                    },
                    rules: {
                        required: value => !!value || 'Required.'
                    },

                    // @accesories_data
                    valid: true,                    
                    dialog: false,
                    sms_report: [],
                    sms_balance: '',
                    sms_dialog: false,
                    sms_failed: [],
                    destination_number_data: [{'value': 'guardian', 'text': 'SMS to Guardian'}, {'value': 'student', 'text': 'SMS to Student'}]                    

                } //end return
            },
            // @router_permission
            beforeRouteEnter(to, from, next) {
                let p = window_admin_permissions.findIndex(x => x.name == 'sms_send');
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
                        var echelons = this.dialogItem.echelons.map(x => {return x.value})
                        var batch = Object.assign([], this.original_batch);
                        let filter_batch = batch.filter(x=>{return echelons.includes(x.echelon_id)})
                        this.batch = filter_batch.map(arr => { return { value: arr['id'], text: arr['name'] } })
                    },
                    // @add_item_method
                    get_item() {
                        if (this.branch.length > 1) {
                            this.admin_echelon_list(this.dialogItem.branch_id);
                        }
                    },
                    // @list_method
                    send() {
                        if (!this.dialogItem.sms) {return alert('SMS is empty')}
                        if (this.dialogItem.sms.length>159) {return alert('Maximum 160 character.')}
                        if (this.dialogItem.echelons.length==0 && this.dialogItem.sms_type != 'one_to_one') {return alert('Class is empty')}
                        if (this.dialogItem.sms_type != 'list_sms' && this.dialogItem.sms.indexOf("[")>-1) {return alert('You can only use context in list sms')}
                        if (this.dialogItem.sms_type=='one_to_one' && !this.dialogItem.reg_no && !this.dialogItem.mobile) {return alert('Fill Mobile or Reg No')}
                        if (this.$refs.form.validate()){
                            this.dialog = true
                            this.axios.post('/admin/sms/send_sms', this.dialogItem).then(response => {
                                this.sms_report = response.data.sms_report
                                this.sms_balance = response.data.balance                                
                                this.sms_dialog = true
                                this.dialog = false
                                if (this.dialogItem.sms_type == 'one_to_one') {return false}
                                this.sms_failed = this.sms_report.filter(x => x.status != 'Ok')
                            }, error => {                                
                                this.dialog = false
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {$('.user_nav').removeClass('denied') }.bind(this), 3000)
                            });
                        }
                    },                    
                    get_branch() {
                        if (this.branch.length == 1) {
                            this.dialogItem.branch_id = this.branch[0].value
                        }
                    },
                    // open dialog
                    editItem(item) {
                        this.editedIndex = this.student_payment.findIndex(x => x.id == item.id);
                        this.editedItem = Object.assign({}, item)
                        this.editedId = this.student_payment[this.editedIndex].id;
                        this.edit_dialog = true
                    },
                    btn_update_text(option)
                    {
                        if (option == 'std_name') {
                            this.dialogItem.sms = !this.dialogItem.sms ? "[student_name]" : this.dialogItem.sms + "[student_name]"
                            this.$forceUpdate()
                        }
                        if (option == 'reg_no') {
                            this.dialogItem.sms = !this.dialogItem.sms ? "[reg_no]" : this.dialogItem.sms + "[reg_no]"
                            this.$forceUpdate()
                        }
                    },
                    // @add_item_method_dialog
                    close() {
                        this.edit_dialog = false
                        this.$refs.form.reset()
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.$refs.form.reset()
                    },
                    change_from_date(){
                        this.dialogItem.show_from_date = this.$moment(this.dialogItem.from_date).format('DD MMM YY')
                    },
                    change_to_date(){
                        this.dialogItem.show_to_date = this.$moment(this.dialogItem.to_date).format('DD MMM YY');
                    },
            }
    }
</script>