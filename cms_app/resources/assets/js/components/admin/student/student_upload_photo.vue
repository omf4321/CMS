<!-- =======Operation========
   @page_headline
   @list (title, table)
   @add_item (button, title, field, submit)
   @edit_item
   @delete_item
   @accesories
    -->
<!-- =======Operation========
   @page_headline
   @list (title, table)
   @add_item (button, title, field, submit)
   @edit_item
   @delete_item
   @accesories
    -->
<template>
    <div class="student_upload_phoo">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Manage Students</h3>
            <v-divider class="my-3"></v-divider>
            <template>
                <div>
                    <!-- @accesories_alert -->
                    <v-alert :value="alert" transition="scale-transition" :color="alert_color" :icon="alert_icon" outline>
                        {{message}}
                    </v-alert>
                    <v-card>
                        <v-card tile>
                            <v-toolbar card dark color="#E0E0E0">
                                <v-toolbar-title class="black--text">Upload Photo</v-toolbar-title>
                            </v-toolbar>
                            <v-card-text class="grey lighten-4 pa-0">
                                <!-- @add_item_field -->
                                <template>
                                    <v-container grid-list-lg>
                                        <v-layout row justify-left style="text-align: center">
                                            <v-flex>
                                                <v-form ref="form" v-model="valid" lazy-validation>
                                                    <input type="file" id="file_input" name="image" accept="image/*" style="display: none" @change="setImage" />
                                                    <v-btn color="info" :disabled="loading" :loading="loading" @click="click_upload"> + Add Photo
                                                    </v-btn>
                                                    <v-text-field class="mt-3" v-if="imgSrc != ''" v-model="reg_no" label="Reg No" type="number" :rules="[v => !!v || 'required']" required style="width: 170px; margin: auto"></v-text-field>
                                                    <div v-if="imgSrc != ''" style="width: 170px; height:225px; border: 1px solid gray; margin: auto">
                                                        <vue-cropper ref='cropper' :guides="true" :view-mode="2" drag-mode="move" :aspectRatio="1 / 1.2" :min-container-width="170" :min-container-height="225" :minCropBoxWidth="155" :minCropBoxheight="210" :background="true" :rotatable="true" :cropBoxResizable="false" :cropBoxMovable="false" :src="imgSrc" alt="Source Image" :img-style="{ 'width': '170px', 'height': '225px' }">
                                                        </vue-cropper>
                                                    </div>
                                                    <v-btn class="mt-3" small color="warning" v-if="imgSrc != ''" @click="rotate">Rotate</v-btn>
                                                    <v-btn class="mt-3" small color="success" v-if="imgSrc != ''" @click="uploadImage" :disabled="!valid || loading">Upload</v-btn>
                                                </v-form>
                                            </v-flex>
                                        </v-layout>
                                    </v-container>
                                </template>
                            </v-card-text>
                        </v-card tile>
                    </v-card>
                </div>
            </template>
        </v-container>
    </div>
</template>
<script>
   import VueCropper from 'vue-cropperjs';
   import 'cropperjs/dist/cropper.css';
   export default {
     components: { VueCropper},
     data() {
             return {
               alert: false,
               alert_color: '',
               alert_icon: '',
               imgSrc: '',
               crop_data: '',
               reg_no: '',
               input_file: '',
               rules: {
                 required: value => !!value || 'required.'
               },
               loading: false,
               valid: true,
               message: '',
               rows: [],
               file_name: '',
               year: 2019
   
            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
       let p = window_admin_permissions.findIndex(x => x.name == 'add_student' || x.name == 'edit_student');
       if (p>-1 || window_admin_role == 'admin' || window_admin_role == 'superadmin') {next()} else{next('/')}
     },
     // @load_method
     created(){
       
     },
   
     methods:{
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
               this.loading = false
               this.$refs.cropper.replace(event.target.result);
             };
             reader.readAsDataURL(file);
           } else {
             alert('Sorry, FileReader API not supported');
           }
         },
         uploadImage() {
            if (this.$refs.form.validate()) {
               this.loading = true
              // get image data for post processing, e.g. upload or setting image src
               this.crop_data = this.$refs.cropper.getData();
               let form_data = new FormData();
               form_data.append('reg_no', this.reg_no)
               form_data.append('photo', this.input_file)
               form_data.append('left', this.crop_data.x)
               form_data.append('top', this.crop_data.y)
               form_data.append('width', this.crop_data.width)
               form_data.append('height', this.crop_data.height)
               this.axios.post('/admin/student/upload_image', form_data).then(response => {
                  this.imgSrc = ""
                  this.$refs.cropper.destroy()
                  this.loading = false
                  this.alert_color = "success"
                  this.alert_icon = "check_circle"
                  this.message = "Updated Successfully"
                  this.file_name = response.data.file_name
                  this.year = new Date(response.data.date_of_admit).getFullYear();
                  this.alert = true
                  this.check_idb(response.data.id)
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
         check_idb(id){
            if (!window_idb) {return false}
            var year = parseInt(this.year)
            var data = []
            var data_status = false
            var idb;
            var date = new Date();
            var c_date = date.getDate()
            // window.indexedDB.deleteDatabase("studentDatabase");
            var idb_request = window.indexedDB.open("studentDatabase_" + year + c_date, 1);
            idb_request.onsuccess = function(event) {
                idb = idb_request.result;
                // read all
                var objectStore = idb.transaction("student").objectStore("student");
                var x = objectStore.getAll();
                x.onsuccess = function() {
                    data_status = true
                    data = x.result
                };
            };
            var i = 0
            var interval = setInterval(function() {
                if (data_status) {
                  clearInterval(interval); 
                  this.rows = data; 
                  var index = this.rows.findIndex(x => x.id == id)
                  if (index > -1) {
                    this.rows[index].photo = this.file_name
                    this.initialize_idb()
                  }
                  this.data_load = false; 
                }
                if (i == 10) {clearInterval(interval);}
                i++
            }.bind(this), 500)
        },
        initialize_idb(){
            if (!window_idb) {return false}
            // const studentDate = this.rows
            var year = this.year
            var data = this.rows
            var data_status = false
            var idb;
            var date = new Date();
            var c_date = new Date().getDate()
            var p_date = date.setDate(date.getDate() - 1)
            p_date = new Date(p_date).getDate()
            window.indexedDB.deleteDatabase("studentDatabase_" + year + c_date);
            var idb_request = window.indexedDB.open("studentDatabase_" + year + c_date, 1);
            idb_request.onsuccess = function(event) {
                idb = idb_request.result;
            };
            idb_request.onerror = function(event) {
               alert("Unable to retrieve daa from database!");
            };
            idb_request.onupgradeneeded = function(event) {
                idb = event.target.result;
                var objectStore = idb.createObjectStore("student", {keyPath: "id"});
                for (var i = 0; i < data.length; i++) {
                    objectStore.add(data[i]);
                }
            }
        }
     } //end method
   }
</script>

<style>
   .student_upload_phoo .v-alert{
   position: absolute;
   z-index: 1;
   right: 10px;
   top: 10px;
   width: 300px;
   height: 50px;
   }
   
   .student_upload_phoo .v-label{
      color: black!important;
      font-size: 14px;
   }
   .student_upload_phoo .error--text{
      color: #ff5252 !important;
   }
   .student_upload_phoo .v-input input, .v-select__selections, .v-textarea textarea{
      font-size: 14px;
      font-weight: 600;
   }
   .student_upload_phoo .v-form .v-text-field{
      padding-top: 0px;
   }
   .student_upload_phoo .v-form .layout .flex{
      padding-bottom: 2px!important;
      padding-top: 5px!important;
   }
   .student_upload_phoo .v-dialog:not(.v-dialog--fullscreen){
      max-height: 100%!important;
   }

</style>