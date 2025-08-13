<template>
  <div>
    Branch Manager Dashboard  
  </div>
</template>

<script>
  export default {
    data() {
            return {
              drawer: null,
              csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              items: [
                { 'icon': 'lightbulb_outline', 'text': 'Notes' },
                { 'icon': 'touch_app', 'text': 'Reminders' },
                { 'divider': true },
                { 'heading': 'Labels' },
                { 'icon': 'add', 'text': 'Create new label' },
                { 'divider': true },
                { 'icon': 'archive', 'text': 'Archive' },
                { 'icon': 'delete', 'text': 'Trash' },
                { 'divider': true },
                { 'icon': 'settings', 'text': 'Settings' },
                { 'icon': 'chat_bubble', 'text': 'Trash' },
                { 'icon': 'help', 'text': 'Help' },
                { 'icon': 'phonelink', 'text': 'App downloads' },
                { 'icon': 'keyboard', 'text': 'Keyboard shortcuts' }
              ]
            }
    },
    beforeRouteEnter (to, from, next) {
        let role = window_admin_role
        if (role=="branch_manager") {next()}
        else if (role=="admin" || role=='super_admin') {next()}
    },
    props: ['source'],
    methods:{
      logout(){
        
      }
    }
  }
</script>

<style>
  #keep
    .v-navigation-drawer__border{
      display: none
    }
</style>