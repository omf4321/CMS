<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">User Profile</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <div class="row">
                                <div class="col-md-6 m-r-15 m-b-50 fs-13">
                                    <h4>Official Information</h4>
                                    <hr>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">User Name</strong>{{dialogItem.name}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Status</strong>{{dialogItem.teachers.status.toUpperCase()}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Join Date</strong>{{dialogItem.teachers.join_date | moment("d MMM YY")}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Role</strong>
                                        <div><span class="m-r-10" v-for="role in dialogItem.teachers.roles.split(',')">{{role.replace('_', '-')}}</span></div>
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10 block">Subjects</strong>
                                        <hr class="m-t-5 m-b-5">
                                        <v-chip class="fs-11" v-for="(sub, key) in subject" :key="key">{{sub.echelons.class_no}}-{{sub.name | first_letter_word}}</v-chip>
                                    </div>
                                    <h4 class="m-t-30">Change Password</h4>
                                    <hr class="m-t-5 m-b-10">
                                    <v-form ref="form_password" v-model="valid_1" lazy-validation>
                                        <v-text-field v-model="password.current_password" :append-icon="show1 ? 'visibility' : 'visibility_off'" :rules="[rules.required, rules.min]" :type="show1 ? 'text' : 'password'" name="input-10-1" label="Current Password" hint="At least 6 characters" counter @click:append="show1 = !show1"></v-text-field>
                                        <v-text-field v-model="password.new_password" :append-icon="show1 ? 'visibility' : 'visibility_off'" :rules="[rules.required, rules.min]" :type="show1 ? 'text' : 'password'" name="input-10-1" label="New Password" hint="At least 6 characters" counter @click:append="show1 = !show1"></v-text-field>
                                        <div v-if="error_alert" style="color: #f44336">{{alert}}</div>
                                        <div v-if="success_alert" style="color: #4caf50">{{alert}}</div>
                                        <v-btn class="success" :disabled="!valid_1 || loading" :loading="loading" @click="update_password">Change</v-btn>
                                    </v-form>
                                </div>

                                <div class="col-md-5">
                                    <h4>Personal Information</h4>
                                    <hr>
                                    <v-form ref="form" v-model="valid" lazy-validation>
                                        <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                        <v-text-field v-model="dialogItem.email" :disabled="editedIndex!=-1" :rules="[rules.required, rules.email]" label="Email" type="text"></v-text-field>
                                        <v-select v-model="dialogItem.teachers.gender" :items="gender" :rules="[v => !!v || 'required']" label="Gender" required></v-select>
                                        <v-text-field v-model="dialogItem.teachers.mobile" :rules="[rules.required, rules.mobile]" label="Contact No" type="number"></v-text-field>
                                        <v-text-field v-model="dialogItem.teachers.mobile2" label="Contact No (2)" type="number"></v-text-field>
                                        <v-text-field v-model="dialogItem.teachers.address" label="Address" type="text"></v-text-field>
                                        <div v-if="error_alert_1" style="color: #f44336">{{alert}}</div>
                                        <div v-if="success_alert_1" style="color: #4caf50">{{alert}}</div>
                                        <v-btn class="success" :disabled="!valid || loading" :loading="loading" @click="update_profile">Submit</v-btn>
                                    </v-form>
                                </div>
                            </div>
                        </v-container>
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
                    subject: [],
                    gender: [{'value': 'male', 'text': 'Male'}, {'value': 'famale', 'text': 'Female'}],
                    // @add_item_field_data =====start
                    dialog: false,
                    editedIndex: -1,
                    editedId: null,
                    dialogItem: {
                        name: '',
                        email: '',
                        teachers: {
                            gender: '',
                            mobile: '',
                            mobile2: '',
                            address: '',
                            subjects: [],
                            address: '',
                            status: '',
                            roles: ''
                        }
                    },
                    password: {
                        current_password: '',
                        new_password: ''
                    },
                    editedItem: '',
                    rules: {
                        required: value => !!value || 'Required.',
                        min: v => v.length >= 6 || 'Min 6 characters',
                        mobile: value => !!value && value.length == 11 || '11 numbers needed',
                        email: value => {
                            const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                            return pattern.test(value) || 'Invalid e-mail.'
                        }
                    },

                    // @accesories_data
                    valid: true,
                    valid_1: true,
                    success_alert: false,
                    error_alert: false,
                    success_alert_1: false,
                    error_alert_1: false,
                    loading: false,
                    alert: '',
                    show1: false

                } //end return
            },
            mounted(){
                window.history.pushState(null, "", window.location.href);        
                window.onpopstate = function() {
                    window.history.pushState(null, "", window.location.href);
                };
            },
            // @load_method
            created() {
                this.teacher_profile();
            },

            filters: {
                first_letter_word: function(value) {
                    if (value.split(' ').length > 1) {
                        var matches = value.match(/\b(\w)/g);
                        var acronym = matches.join('');
                        return acronym;
                    }
                    return value.substring(0, 4)
                }
            },

            methods: {
                // @add_item_method       
                // @list_method
                teacher_profile() {
                        Vue.axios.get('/user/teacher_profile').then(response => {
                            this.dialogItem = response.data.user[0];
                            for (var i = 0; i < this.dialogItem.teachers.subjects.length; i++) {
                                this.subject.push(this.dialogItem.teachers.subjects[i])
                            }
                        });
                    },
                    update_profile() {
                        if (this.$refs.form.validate()) {
                            this.loading = true
                            this.error_alert_1 = false
                            this.success_alert_1 = false
                            Vue.axios.post('/user/update_profile', this.dialogItem).then(response => {
                                this.success_alert_1 = true;
                                this.alert = response.data.message
                                this.loading = false
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {
                                this.error_alert_1 = true;
                                this.alert = error.response.data.message
                                this.loading = false
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    update_password() {
                        if (this.$refs.form_password.validate()) {
                            this.loading = true
                            this.error_alert = false
                            this.success_alert = false
                            Vue.axios.post('/user/update_password', this.password).then(response => {
                                this.password = {}
                                this.success_alert = true;
                                this.alert = response.data.password
                                this.loading = false
                                $('.user_nav').addClass('successful')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('successful')
                                }.bind(this), 3000)
                            }, error => {                                
                                this.error_alert = true;
                                this.alert = error.response.data.password
                                this.loading = false
                                $('.user_nav').addClass('denied')
                                setTimeout(function() {
                                    $('.user_nav').removeClass('denied')
                                }.bind(this), 3000)
                            });
                        }
                    },
                    add_vaction() {
                        var x = {
                            'date_from': '',
                            'date_to': '',
                            'vacation_text': ''
                        }
                        this.dialogItem.vacation_range.push(x)
                    },
                    delete_vaction(index, type) {
                        this.dialogItem.vacation_range.splice(index, 1)
                    },
                    // @add_item_method_close_dialog
                    clear() {
                        this.search = ''
                        this.search_filter = ''
                        this.search_filter1 = ''
                    }
            }
    }
</script>

<style>
    .v-text-field label,
    .v-text-field input {
        font-size: 14px;
    }
    
    #edit_class_no {
        color: #FF5722!important;
        font-weight: 500;
        font-size: 30px;
    }
    
    .v-btn {
        height: 32px;
    }
</style>