
<template>
    <div>
        <v-container fluid>
            <router-link :to="{ name: 'user_question_list'}">
                <v-btn small color="primary" class="mr-2 tiny-btn pos-rel" style="top: 8px">
                    View
                </v-btn>
            </router-link>
        </v-container>
    </div>
</template>

<script>
    export default {
        data() {
                return {
                    

                } //end return
            },
            // @router_permission
            beforeRouteEnter (to, from, next) {
                if (window_user_role == 'teacher' && window_authorise != 'authorised') {return next('/authentication')}
                let p = window_admin_permissions.findIndex(x => x.name=='question_check');
                let r = window_user_role.findIndex(x => x == 'teacher')
                if (p > -1 || r > -1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
            },
            // @load_method
            created() {
                // this.schedule_list();
            },                        

            methods: {
                schedule_list() {
                    this.data_load = true;
                    Vue.axios.get('/admin/question/question_makable_list_by_date/' + this.search_date).then(response => {
                        this.rows = response.data.schedule_list;
                        this.pagination.descending = false
                        this.data_load = false
                    });
                },                
            }
    } 
</script>


<style>
  .v-alert{
      position: absolute;
      z-index: 1;
      right: 10px;
      top: 10px;
      width: 300px;
      height: 50px;
  }
</style>