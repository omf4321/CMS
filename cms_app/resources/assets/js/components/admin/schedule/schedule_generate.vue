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
            <h3 class="headline">Generate Dately Schedule</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-form ref="form" v-model="valid" lazy-validation>
                            <v-container>
                                <div class="row">
                                    <div class="col-md-3">
                                        <v-select v-model="dialogItem.branch_id" :disabled="branch.length==1" :items="branch" :rules="[v => !!v || 'Branch is required']" label="Branch" required @change="get_item"></v-select>
                                    </div>

                                    <div class="col-md-3">
                                        <v-select v-model="dialogItem.echelon_id" :items="echelon" :rules="[v => !!v || 'required']" label="Class" @change="get_batch()" required></v-select>
                                    </div>

                                    <div class="col-md-3">
                                        <v-autocomplete v-model="dialogItem.batch_id" :items="batch" :rules="[v => !!v || 'required']" label="Batch"></v-autocomplete>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <v-menu :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="dialogItem.date_from" label="Date From" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'required']"></v-text-field>
                                            </template>
                                            <v-date-picker v-model="dialogItem.date_from"></v-date-picker>
                                        </v-menu>
                                    </div>
                                    <div class="col-md-3">
                                        <v-menu :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="dialogItem.date_to" label="Date To" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'required']"></v-text-field>
                                            </template>
                                            <v-date-picker v-model="dialogItem.date_to"></v-date-picker>
                                        </v-menu>
                                    </div>
                                </div>
                                <h3> Vacation <v-btn flat icon color="info" @click="add_vaction"><v-icon dark>add</v-icon></v-btn> </h3>
                                <div class="row" v-for="(vacation, key) in dialogItem.vacation_range" v-bind:key="key">
                                    <div class="col-md-3">
                                        <v-menu :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="vacation.date_from" label="Date From" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'Date From is required']"></v-text-field>
                                            </template>
                                            <v-date-picker v-model="vacation.date_from"></v-date-picker>
                                        </v-menu>
                                    </div>
                                    <div class="col-md-3">
                                        <v-menu :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="vacation.date_to" label="Date To" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'Date From is required']"></v-text-field>
                                            </template>
                                            <v-date-picker v-model="vacation.date_to"></v-date-picker>
                                        </v-menu>
                                    </div>
                                    <div class="col-md-4 col-xs-10">
                                        <v-text-field v-model="vacation.vacation_text" :rules="[v => !!v || 'Vacation Text is required']" label="Vacation Text" required></v-text-field>
                                    </div>
                                    <div class="col-md-2 col-xs-2">
                                        <v-btn outline small fab color="info" @click="delete_vaction(key, 'range')" style="height: 30px; width: 30px;"><v-icon>close</v-icon></v-btn>
                                    </div>
                                    <hr style="float: none; clear: both">
                                </div>
                                <v-layout>
                                    <v-flex class="pb-0 pt-0" xs12 md2>
                                        <v-btn color="primary" :disabled="!valid || loading" :loading="loading" class="mb-2" @click="check_schedule()">Generate</v-btn>
                                    </v-flex>
                                </v-layout>
                                <v-layout>
                                    <v-flex lg12 v-if="exist">
                                        <div class="red--text">Fail to Generate!! A schedule already exist within your selected dates and criteria. <router-link :to="{name:'schedule_list'}">view schedule</router-link></div>
                                    </v-flex>
                                    <v-flex lg12 v-if="created">
                                        <div class="green--text subheading">A schedule is created. <router-link :to="{name:'schedule_list'}">view schedule</router-link></div>
                                    </v-flex>
                                </v-layout>
                            </v-container>
                        </v-form>
                    </template>
                </div>
            </template>
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
                    vacation_range: [],
                },
                rules: {
                    required: (value) => !!value || "Required.",
                },
                // @accesories_data
                valid: true,
                loading: false,
                created: false,
                exist: false,
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
            dialog(val) {
                val || this.close();
            },
        },

        methods: {
            // @add_item_method
            admin_branch_list() {
                this.branch = window_branch;
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].id;
                    this.admin_echelon_list(this.dialogItem.branch_id);
                    this.admin_batch_list();
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
            admin_batch_list() {
                this.axios.get("/admin/request/batch_list_by_branch/" + this.dialogItem.branch_id).then((response) => {
                    this.original_batch = response.data.batch_list_by_branch;
                    this.batch = response.data.batch_list_by_branch;
                    this.batch = this.batch.map((arr) => {
                        return { value: arr["id"], text: arr["name"] };
                    });
                });
            },
            get_weekly_schedule() {
                var weekly_schedule = Object.assign([], this.original_weekly_schedule_select_list);
                let filter_weekly_schedule = weekly_schedule.filter((x) => {
                    return x.echelon_id == this.dialogItem.echelon_id;
                });
                this.weekly_schedule_select_list = filter_weekly_schedule.map((arr) => {
                    return { value: arr["id"], text: arr["name"] };
                });
            },
            // @add_item_method
            get_item() {
                if (this.branch.length > 1) {
                    this.admin_echelon_list(this.dialogItem.branch_id);
                }
            },
            get_batch() {
                var batch = Object.assign([], this.original_batch);
                let filter_batch = batch.filter((x) => {
                    return x.echelon_id == this.dialogItem.echelon_id;
                });
                this.batch = filter_batch.map((arr) => {
                    return { value: arr["id"], text: arr["name"] };
                });
                this.get_weekly_schedule();
            },
            get_branch() {
                if (this.branch.length == 1) {
                    this.dialogItem.branch_id = this.branch[0].value;
                }
            },
            generate() {
                this.loading = true;
                this.axios.post("/admin/schedule/generate_schedule", this.dialogItem).then(
                    (response) => {
                        $(".user_nav").addClass("successful");
                        this.loading = false;
                        this.created = true;
                        this.exist = false;
                        setTimeout(
                            function () {
                                $(".user_nav").removeClass("successful");
                            }.bind(this),
                            3000
                        );
                    },
                    (error) => {
                        this.loading = false;
                        $(".user_nav").addClass("denied");
                        setTimeout(
                            function () {
                                $(".user_nav").removeClass("denied");
                            }.bind(this),
                            3000
                        );
                    }
                );
            },
            check_schedule() {
                this.created = false;
                this.exist = false;
                if (this.$refs.form.validate()) {
                    this.loading = true;
                    this.axios.post("/admin/schedule/check_schedule", this.dialogItem).then((response) => {
                        this.generate();
                    },(error) => {
                            this.loading = false;
                            this.exist = true;
                        }
                    );
                }
            },
            add_vaction() {
                var x = { date_from: "", date_to: "", vacation_text: "" };
                this.dialogItem.vacation_range.push(x);
            },
            delete_vaction(index, type) {
                this.dialogItem.vacation_range.splice(index, 1);
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