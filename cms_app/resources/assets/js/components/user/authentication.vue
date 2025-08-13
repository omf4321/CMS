<template>
  <div> 
    <v-container>
      <div class="fs-16 fw-600">You are not authorised !!! </div>
      <span class="fs-13">Input authentication code to authorise.</span>  
      <v-text-field label="Authentication Code" v-model="authentication_code"></v-text-field>
      <v-btn small color="primary" @click="save">Submit</v-btn>
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
  export default {
    data() {
            return {
              authentication_code: '',
              dialog: false
            }
    },
    beforeRouteEnter(to, from, next) {                        
        if (window_authorise != 'authorised') {
            next()
        } else {
            next('/')
        }
    },
    methods:{
        save(){
          this.dialog = true
          Vue.axios.post('/user/update_authentication/' + this.authentication_code).then(response => {
              location.reload()
              this.dialog = false
          }, error => {
              alert(error.response.data.msg)
              this.dialog = false
              $('.user_nav').addClass('denied')
              setTimeout(function() {
                  $('.user_nav').removeClass('denied')
              }.bind(this), 3000)
          });
        }
    }
  }
</script>

<style>
  #keep
    .v-navigation-drawer__border {display: none}
</style>