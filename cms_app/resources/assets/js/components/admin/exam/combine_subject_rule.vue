<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="schedule_generate">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Combine Subject Rule</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <v-layout v-if="step == 'list'">
                                    <v-flex class="pb-0 pt-0" xs12 md2>
                                        <v-btn color="primary" class="mb-2" @click="editItem('new')"> + Create New</v-btn>
                                    </v-flex>
                                </v-layout>
                                <div v-if="step == 'list'">
                                    <v-flex class="pb-0 pt-0 m-r-10" md6 v-if="combine_subject_rule_list.length">
                                        <table class="table table-bordered fs-12 m-t-10">
                                            <thead>
                                              <tr>
                                                <th>Rule Name</th>
                                                <th>Class</th>
                                                <th>Date</th>
                                                <th>Action</th>
                                              </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(list, i) in combine_subject_rule_list">
                                                <td>{{list.name}}</td>
                                                <td>{{list.echelons.name}}</td>
                                                <td>{{list.created_at | moment("D MMM YY")}}</td>
                                                <td>
                                                    <v-icon small class="mr-2" @click="editItem('edit', list)"> edit </v-icon>
                                                    <v-icon small class="mr-2" @click="delete_combine_subject_rule(list.id, i)"> delete </v-icon>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </v-flex>
                                </div>
                                <div v-if="step == 'update'">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" label="Branch" required></v-select>
                                        </div>
                                        <div class="col-md-2">
                                            <v-autocomplete :disabled="edited_index > -1" @change="get_subject" class="m-r-15" :clearable=true :items="echelon" label="Class" v-model="dialogItem.echelon_id"></v-autocomplete>
                                        </div>
                                        <div class="col-md-2">
                                            <v-text-field :rules="[v => !!v || 'required']" label="Combination Name" v-model="dialogItem.name">
                                            </v-text-field>
                                        </div>
                                        <div class="col-md-2">
                                            <v-autocomplete class="m-r-15" :clearable=true :items="optional_type" label="Optional Type" v-model="dialogItem.optional_type"></v-autocomplete>
                                        </div>
                                        <!-- <div class="col-md-2 col-xs-4 p-l-5">
                                            <v-btn small outline class="tiny-btn pos-rel m-l-5" @click="filter_schedule">Go</v-btn>
                                        </div> -->
                                    </div>
                                    <h3> Subject Combination <v-btn flat icon color="info" @click="add_subject_combination"><v-icon dark>add</v-icon></v-btn> </h3>
                                    <div class="row" v-for="(combination, key) in dialogItem.subject_combinations" v-bind:key="key">
                                        <div class="col-md-2 col-xs-10">
                                            <v-text-field v-model="combination.subject_name" :rules="[v => !!v || 'required']" label="Subject Name" required></v-text-field>
                                        </div>
                                        <div class="col-xs-2 col-md-2">
                                            <v-autocomplete :items="subject" :rules="[v => !!v || 'required']" label="1st Subject (Paper)" v-model="combination.first_subject_id"> </v-autocomplete>
                                        </div>
                                        <div class="col-xs-2 col-md-2">
                                            <v-autocomplete :items="subject" :rules="[v => !!v || 'required']" label="2nd Subject (Paper)" v-model="combination.second_subject_id"> </v-autocomplete>
                                        </div>
                                        <div class="col-md-2 col-xs-10">
                                            <v-text-field v-model="combination.full_mark" :rules="[v => !!v || 'required']" label="Total Full Mark" required></v-text-field>
                                        </div>
                                        <div class="col-md-2 col-xs-2">
                                            <v-btn outline small fab color="info" @click="delete_subject_combination(key)" style="height: 30px; width: 30px;"><v-icon>close</v-icon></v-btn>
                                        </div>
                                        <hr style="float: none; clear: both">
                                    </div>
                                    <v-layout>
                                        <v-btn color="primary" :disabled="!valid || loading" :loading="loading" class="mb-2" @click="save">Submit</v-btn>
                                        <v-btn color="error" class="mb-2" @click="step = 'list'">Close</v-btn>
                                    </v-layout>
                                </div>
                                
                            </v-container>
                        </v-form>
                    </template>
                </div>
            </template>
            <v-dialog v-model="dialog" hide-dialog persistent width="300">
                <v-card color="#009688" dark>
                    <v-card-text>
                        Please Wait..
                        <v-progress-linear indeterminate color="white" class="mb-0"></v-progress-linear>
                    </v-card-text>
                </v-card>
            </v-dialog>
        </v-container>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                // @list_column_data
                branch: [],
                echelon: [],
                original_batch: [],
                batch: [],
                // @add_item_field_data =====start
                dialog: false,
                dialogItem: {
                    name: "",
                    branch_id: "",
                    echelon_id: "",
                    batches: [],
                    date_from: "",
                    date_to: "",
                    subject_combinations: [],
                },
                rules: {
                    required: (value) => !!value || "Required.",
                },
                // @accesories_data
                valid: true,
                loading: false,
                created: false,
                exist: false,
                edited_id: 0,
                edited_index: -1,
                combine_subject_rule_list: [],
                optional_type: [{value: 'no_optional', text: 'No Optional'}, {value: 'has_optional', text: 'Has Optional'}],
                step: 'list',
                subject: [],
                original_subject: []
            }; //end return
        },
        // @router_permission
        beforeRouteEnter(to, from, next) {
            let p = window_admin_permissions.findIndex((x) => x.name == "edit_schedule");
            if (p > -1 || window_admin_role == "superadmin" || window_admin_role == "admin") {
                next();
            } else {
                next("/");
            }
        },
        // @load_method
        created() {
            this.admin_branch_list();
        },

        watch: {
        },

        methods: {
            // @add_item_method
            admin_branch_list() {
                this.branch = window_branch;
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].id;
                    this.admin_echelon_list(this.dialogItem.branch_id);
                    this.get_combine_subject_rule()
                    this.admin_subject_list();
                }
                this.branch = this.branch.map((arr) => {
                    return { value: arr["id"], text: arr["name"] };
                });
            },
            admin_echelon_list() {
                this.echelon = window_echelons.map((arr) => {
                    return { value: arr["id"], text: arr["name"] };
                });
            },
            admin_subject_list() {
                this.axios.get('/admin/request/subject_list_by_branch/' + this.dialogItem.branch_id).then(response => {
                    this.original_subject = response.data.subject_list_by_branch;
                });
            },
            get_subject() {
                var echelon_id = this.dialogItem.echelon_id
                if (echelon_id == 8) {echelon_id = 7}
                var subject = Object.assign([], this.original_subject);
                let filter_subject = subject.filter(x => {return x.echelon_id == echelon_id })
                this.subject = filter_subject.map(arr => {return {value: arr['id'], text: arr['name'] } })
                if(this.edited_index == -1 && this.dialogItem.subject_combinations.length == 0){
                    this.add_subject_combination()
                }
            },
            get_branch() {
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].value;
                }
            },
            get_combine_subject_rule(){
                this.dialog = true;
                 this.axios.get('/admin/exam/get_combine_subject_rule/' + this.dialogItem.branch_id).then(response => {
                     this.combine_subject_rule_list = response.data.combine_subject_rule;
                     this.dialog = false
                 });
            },
            save(action) {
                if (!this.$refs.form.validate()) {return false}
                this.dialog = true;
                var url = '/admin/exam/add_combine_subject_rule'
                if(this.edited_index > -1){
                    url = '/admin/exam/edit_combine_subject_rule/' + this.edited_id
                }
                this.axios.post(url, this.dialogItem).then((response) => {
                        $(".user_nav").addClass("successful");
                        this.dialog = false;
                        setTimeout(function () {$(".user_nav").removeClass("successful"); }.bind(this), 3000 );
                        this.step = 'list'
                        this.get_combine_subject_rule()
                    },
                    (error) => {
                        this.dialog = false;
                        $(".user_nav").addClass("denied");
                        setTimeout(function () {$(".user_nav").removeClass("denied"); }.bind(this), 3000 );
                    }
                );
            },
            delete_combine_subject_rule(id, index) {
                var con = confirm("Are you sure?")
                if (!con) {return false}
                this.dialog = true;                    
                var url = '/admin/exam/delete_combine_subject_rule/' + id

                this.axios.post(url).then((response) => {
                        $(".user_nav").addClass("successful");
                        this.dialog = false;
                        this.step = 'list'
                        setTimeout(function () {$(".user_nav").removeClass("successful"); }.bind(this), 3000 );
                        this.get_combine_subject_rule()
                    },
                    (error) => {
                        this.dialog = false;
                        $(".user_nav").addClass("denied");
                        setTimeout(function () {$(".user_nav").removeClass("denied"); }.bind(this), 3000 );
                    }
                );
            },
            editItem (action, item) {
                this.edited_index = -1
                this.edited_id = null
                this.dialogItem = {'subject_combinations': [], 'branch_id': this.branch[0].value}
                if(action == 'edit'){
                    this.edited_index = this.combine_subject_rule_list.findIndex(x => x.id==item.id);
                    this.dialogItem = Object.assign({}, item)
                    this.get_subject()
                    this.edited_id = this.combine_subject_rule_list[this.edited_index].id;
                    this.dialogItem.subject_combinations = []
                    for (var i = 0; i < item.combine_subjects.length; i++) {
                        var x = { subject_name: item.combine_subjects[i].subject_name, first_subject_id: item.combine_subjects[i].first_subject_id, second_subject_id: item.combine_subjects[i].second_subject_id, full_mark: item.combine_subjects[i].full_mark };
                        this.dialogItem.subject_combinations.push(x);
                    }
                }
                this.step = 'update'
            },
            add_subject_combination() {
                var x = { subject_name: "", first_subject_id: "", second_subject_id: "", full_mark: 0 };
                this.dialogItem.subject_combinations.push(x);
                this.$forceUpdate()
            },
            delete_subject_combination(index) {
                this.dialogItem.subject_combinations.splice(index, 1);
                this.$forceUpdate()
            },
            // @add_item_method_close_dialog
            clear() {
                this.search = "";
                this.search_filter = "";
                this.search_filter1 = "";
            },
        },
    };
</script>



<style>
  .schedule_generate .v-alert{
      position: fixed;
      z-index: 1;
      right: 10px;
      top: 70px;
      width: 300px;
      height: 50px;
  }
  .schedule_generate .v-text-field label, .schedule_generate .v-text-field input {
    font-size: 14px;
  }
  .schedule_generate #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .schedule_generate .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .schedule_generate .v-input{
    font-size: 14px;
  }
  .schedule_generate .v-btn{
    height: 32px;
  }
</style>