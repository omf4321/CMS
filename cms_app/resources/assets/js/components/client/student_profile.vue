<template>
    <div>
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Student's Profile</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <template>
                        <v-container>
                            <div class="row">
                                <div class="col-md-6 m-r-15 m-b-50 fs-13">
                                    <h4>Academic Information</h4>
                                    <hr>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">User Name</strong>{{dialogItem.students.name}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Status</strong>{{dialogItem.students.status.toUpperCase()}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Date of Admit</strong>{{dialogItem.students.date_of_admit | moment("d MMM YY")}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Reg No</strong>{{dialogItem.username}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Batch</strong>{{dialogItem.students.batches.name}}
                                    </div>
                                    <div class="m-b-5">
                                        <strong class="m-r-10">Contact No</strong>{{dialogItem.students.mobile}}
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
                                    <h4 class="m-t-40">Profile Picture</h4>
                                    <hr>
                                    <v-form ref="form" v-model="valid" lazy-validation>
                                        <input type="file" id="file_input" name="image" accept="image/*" style="display: none" @change="setImage" />
                                        <div v-if="!dialogItem.students.photo && !imgSrc" class="text-center m-b-10">No Profile Picture</div>
                                        <div class="m-b-10" v-if="dialogItem.students.photo && !imgSrc">
                                            <img class="m-auto block" :src="'/storage/avatar/' + dialogItem.students.batches.name + '/' + dialogItem.students.photo">
                                        </div>
                                        <v-btn class="m-auto tiny-btn m-t-5" style="display:block!important" color="info" :disabled="loading" :loading="loading" @click="click_upload"> + Change Photo
                                        </v-btn>
                                        <div v-if="imgSrc != ''" style="width: 170px; height:225px; border: 1px solid gray; margin: auto; margin-top: 10px">
                                            <vue-cropper ref='cropper' :guides="true" :view-mode="2" drag-mode="move" :aspectRatio="1 / 1.2" :min-container-width="170" :min-container-height="225" :minCropBoxWidth="155" :minCropBoxheight="210" :background="true" :rotatable="true" :cropBoxResizable="false" :cropBoxMovable="false" :src="imgSrc" alt="Source Image" :img-style="{ 'width': '170px', 'height': '225px' }">
                                            </vue-cropper>
                                        </div>
                                        <div class="text-center">
                                            <v-btn class="mt-3" small color="warning" v-if="imgSrc != ''" @click="rotate">Rotate</v-btn>
                                            <v-btn class="mt-3" small color="success" v-if="imgSrc != ''" @click="uploadImage" :disabled="!valid || loading">Upload</v-btn>
                                        </div>
                                    </v-form>
                                </div>

                                <div class="col-md-5">
                                    <h4>Personal Information</h4>
                                    <hr>
                                    <v-form ref="form" v-model="valid" lazy-validation>
                                        <v-text-field v-model="dialogItem.name" label="Name" :rules="[rules.required]" required></v-text-field>
                                        <v-text-field v-model="dialogItem.email" label="Email" type="text"></v-text-field>
                                        <v-select v-model="dialogItem.students.gender" :items="gender" :rules="[v => !!v || 'required']" label="Gender" required></v-select>
                                        <v-text-field v-model="dialogItem.students.mobile2" label="Contact No (2)" type="number"></v-text-field>
                                        <template>
                                            <v-dialog ref="menu" v-model="menu" :return-value.sync="date" lazy full-width width="290px">
                                                <v-text-field slot="activator" v-model="dialogItem.students.date_of_birth" label="Date of Birth" :rules="[v => !!v || 'required']" readonly></v-text-field>
                                                <v-date-picker ref="picker" v-model="dialogItem.students.date_of_birth" :max="new Date().toISOString().substr(0, 10)" min="2000-01-01" @change="save_birthday()"></v-date-picker>
                                            </v-dialog>
                                        </template>
                                        <v-text-field v-model="dialogItem.students.father_name" label="Father's Name" type="text" :rules="[v => !!v || 'required']"></v-text-field>
                                        <v-text-field v-model="dialogItem.students.mother_name" label="Mother's Name" type="text" :rules="[v => !!v || 'required']"></v-text-field>
                                        <v-text-field v-model="dialogItem.students.address" label="Address" type="text"></v-text-field>
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
    import VueCropper from 'vue-cropperjs';
    import 'cropperjs/dist/cropper.css';
    export default {
        components: { VueCropper},
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
                        students: {
                            name: '',
                            gender: '',
                            mobile: '',
                            mobile2: '',
                            address: '',
                            subjects: [],
                            address: '',
                            status: '',
                            roles: '',
                            batches: {name: ''}
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
                    show1: false,
                    date: '',
                    menu: '',
                    imgSrc: '',

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
                this.student_profile();
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
                student_profile() {
                    this.dialog = true
                    Vue.axios.get('/client/student_profile').then(response => {
                        this.dialogItem = response.data.user[0];
                        this.dialog = false
                    });
                },
                update_profile() {
                    if (this.$refs.form.validate()) {
                        this.loading = true
                        this.error_alert_1 = false
                        this.success_alert_1 = false
                        Vue.axios.post('/client/update_profile', this.dialogItem).then(response => {
                            this.success_alert_1 = true;
                            this.alert = response.data.message
                            this.loading = false
                            $('.user_nav').addClass('successful')
                            setTimeout(function() {
                                $('.user_nav').removeClass('successful')
                            }.bind(this), 3000)
                        }, error => {
                            this.error_alert_1 = true;
                            this.alert = 'An error has occured'
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
                        Vue.axios.post('/client/update_password', this.password).then(response => {
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
                save_birthday(date) {
                    this.$refs.menu.save(date)
                },
                click_upload(){
                    if (this.imgSrc) {
                       this.imgSrc = ""
                       this.$refs.cropper.destroy()
                    }
                    document.getElementById('file_input').click()
                 },
                 read_image(e){
                    var input = e.target.files[0];
                    let reader = new FileReader();
                    reader.readAsDataURL(input);
                    reader.onload = e => {
                       this.imgSrc = e.target.result;
                    }
                },
                setImage(e) {
                   const file = e.target.files[0];
                   this.loading = true
                   this.input_file = file
                   if (!file.type.includes('image/')) {
                     alert('Please select an image file');
                     return;
                   }
                   if (typeof FileReader === 'function') {
                     const reader = new FileReader();
                     reader.onload = (event) => {
                       this.imgSrc = event.target.result;
                       this.$refs.cropper.replace(event.target.result);
                     };
                     reader.readAsDataURL(file);
                    } else {
                        alert('Sorry, FileReader API not supported');
                    }
                    this.loading = false
                 },
                 uploadImage() {
                    if (this.$refs.form.validate()) {
                       this.loading = true
                      // get image data for post processing, e.g. upload or setting image src
                       this.crop_data = this.$refs.cropper.getData();
                       let form_data = new FormData();
                       form_data.append('photo', this.input_file)
                       form_data.append('left', this.crop_data.x)
                       form_data.append('top', this.crop_data.y)
                       form_data.append('width', this.crop_data.width)
                       form_data.append('height', this.crop_data.height)
                       Vue.axios.post('/client/upload_image', form_data).then(response => {
                          this.imgSrc = ""
                          this.$refs.cropper.destroy()
                          this.loading = false
                          this.alert_color = "success"
                          this.alert_icon = "check_circle"
                          this.message = "Updated Successfully"
                          this.dialogItem.students.photo = response.data.file_name
                          this.alert = true
                          setTimeout(function () { this.alert=false; }.bind(this), 4000)
                       }, error => {
                          if (error.response.status == 422) {
                             this.message = error.response.data.student_check
                          }
                          else {
                             this.message = "An error has occured"
                          }
                          this.loading = false
                          this.alert_color = "error"
                          this.alert_icon = "warning"
                          this.alert = true
                          setTimeout(function () { this.alert=false; }.bind(this), 4000)
                       });
                    }
                 },
                 rotate() {
                   // guess what this does :)
                   this.$refs.cropper.rotate(90);
                 },
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