<!-- =======Operation========
@page_headline
@list (title, table)
@add_item (button, title, field, submit)
@edit_item
@delete_item
@accesories
 -->

<template>
    <div class="schedule_view">
        <v-container fluid>
            <!-- @page_headline -->
            <h3 class="headline">Print Dately Schedule</h3>
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
                                        <v-autocomplete v-model="dialogItem.batch_id" :items="batch" :rules="[v => !!v || 'required']" label="Print on Batch" :clearable=true @change="get_other_batch"></v-autocomplete>
                                    </div>
                                    <div class="col-md-3">
                                        <v-combobox v-model="dialogItem.other_batch_id" :items="other_batch" :rules="[v => !!v || 'required']" label="Applicable Other Batch" small-chips multiple hide-selected :clearable=true></v-combobox>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <v-menu v-model="date_from_dialog" :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="dialogItem.date_from" label="Date From" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'required']"></v-text-field>
                                            </template>
                                            <v-date-picker @change="date_from_dialog = false" v-model="dialogItem.date_from"></v-date-picker>
                                        </v-menu>
                                    </div>
                                    <div class="col-md-3">
                                        <v-menu v-model="date_to_dialog" :close-on-content-click="false" :nudge-right="40" lazy transition="scale-transition" offset-y full-width min-width="290px">
                                            <template v-slot:activator="{ on }">
                                                <v-text-field v-model="dialogItem.date_to" label="Date To" prepend-icon="event" readonly v-on="on" :rules="[v => !!v || 'required']"></v-text-field>
                                            </template>
                                            <v-date-picker @change="date_to_dialog = false" v-model="dialogItem.date_to"></v-date-picker>
                                        </v-menu>
                                    </div>
                                    <div class="col-md-3">
                                        <v-text-field v-model="dialogItem.title"  label="Title" :clearable=true></v-text-field>
                                    </div>
                                    <div class="col-md-3">
                                        <v-text-field v-model="dialogItem.subtitle"  label="Subtitle" :clearable=true></v-text-field>
                                    </div>
                                    <div class="col-md-3">
                                        <v-btn color="primary" :disabled="!valid || loading" :loading="loading" class="mb-2" @click="get_schedule_list">Get</v-btn>
                                    </div>
                                </div>
                            </v-container>
                        </v-form>
                    </template>
                </div>
            </template>
        </v-container>
        <div class="container" v-if="!found_schedule">
            Not found any schedule
        </div>
        <div class="container schedule_list" v-if="schedule_data.length">
            <v-btn small color="info" @click="download">Print</v-btn>
            <div id="print_content">
                <img src="/image/logo.png" style="display: block; margin: auto; height: 45px;" />
                <h2 class="text-center solaiman fw-600 m-b-5 m-t-10">{{echelon_bangla_text}} শ্রেণি</h2>
                <div class="text-center m-b-15 m-t-5 solaiman fw-600 fs-16">{{this.apply_batch.toUpperCase()}}</div>
                <h3 class="text-center m-b-5 m-t-5 solaiman fw-600">{{dialogItem.title}}</h3>
                <h3 class="text-center m-b-15 m-t-0 solaiman fw-600 fs-20">{{dialogItem.subtitle}}</h3>
                <table class="table">
                    <thead>
                        <tr v-for="(list, s_i) in schedule_data">
                            <td
                                class="schedule_date"
                                v-if="list.count_class>0"
                                :rowspan="list.count_class"
                                style="padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 16px; color: #222; width: 20%; text-align: center; font-weight: bold;"
                            >
                                <p class="m-b-5" style="font-family: SolaimanLipi;">{{list.date | moment("D MMM YY") | digit_to_bangla}}</p>
                                <p class="m-b-0" style="font-family: SolaimanLipi;">{{list.date | moment("ddd") | day_in_bangla}}</p>
                            </td>
                            <!-- class -->
                            <td
                                v-if="list.schedule_type=='class'"
                                class="schedule_type"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 16px; color: #222; width: 10%; text-align: center;"
                            >
                                {{list.schedule_type == 'class' ? 'ক্লাস' : (list.schedule_type == 'exam' ? 'পরীক্ষা' : 'বন্ধ')}}
                            </td>
                            <!-- exam -->
                            <td
                                v-if="list.schedule_type!='class'"
                                class="schedule_type"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 16px; color: #222; width: 10%; text-align: center; background: #c8c8c8 !important; -webkit-print-color-adjust: exact; "
                            >
                                {{list.schedule_type == 'class' ? 'ক্লাস' : (list.schedule_type == 'exam' ? 'পরীক্ষা' : 'বন্ধ')}}
                            </td>
                            <!-- class -->
                            <td
                                v-if="list.schedule_type=='class'"
                                class="schedule_subject"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 16px; color: #222; width: 20%; text-align: center;"
                            >
                                {{list.subjects ? list.subjects.bangla_text : ''}}
                            </td>
                            <!-- exam -->
                            <td
                                v-if="list.schedule_type!='class'"
                                class="schedule_subject"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 16px; color: #222; width: 20%; text-align: center; background: #c8c8c8 !important; -webkit-print-color-adjust: exact; ">
                                {{list.subjects ? list.subjects.bangla_text : ''}}
                            </td>
                            <!-- class -->
                            <td
                                v-if="list.schedule_type=='class'"
                                class="schedule_topic"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 14px; color: #222; width: 50%; text-align: left;">
                                <span>{{list.chapter_text}}</span>
                                <span v-if="list.chapter_text && list.topic">: </span>
                                <span>{{list.topic}}</span>
                            </td>
                            <!-- exam -->
                            <td
                                v-if="list.schedule_type!='class'"
                                class="schedule_topic"
                                style="font-family: SolaimanLipi; padding: 10px 10px; vertical-align: middle; border: 2px solid #222; font-size: 14px; color: #222; width: 50%; text-align: left; background: #c8c8c8 !important; -webkit-print-color-adjust: exact; ">
                                <span>{{list.chapter_text}}</span>
                                <span v-if="list.chapter_text && list.topic">: </span>
                                <span>{{list.topic}}</span>
                            </td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
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
               branch: [],
               echelon: [],
               original_batch: [],
               batch: [],
               original_subject: [],
               subject: [],
               schedule_type: [{'value': 'class', 'text': 'Class'}, {'value': 'exam', 'text': 'Exam'}, {'value': 'close', 'text': 'Close'}],
               day: [{'value': 'sat', 'text': 'Sat'}, {'value': 'sun', 'text': 'Sun'}, {'value': 'mon', 'text': 'Mon'}, {'value': 'tue', 'text': 'Tue'}, {'value': 'wed', 'text': 'Wed'}, {'value': 'thu', 'text': 'Thu'}, {'value': 'fri', 'text': 'Fri'}],
               // @list_search_data
               start_month: '',
               end_month: '',
               schedule_month: '',
               schedule_year: '',
               schedule_data: {subjects: {echelons: {}}},
               schedule_list: [],
               pdf_data: '',
               valid: true,
               loading: false,
               dialogItem: {other_batch_id: [], date_from: '', date_to: '', title: ''},
               other_batch: [],
               echelon_bangla_text: '',
               apply_batch: '',
               found_schedule: true,
               dialog: false,
               date_from_dialog: false,
               date_to_dialog: false
            } //end return
     },
     // @router_permission
     beforeRouteEnter (to, from, next) {
        let p = window_admin_permissions.findIndex(x => x.name=='schedule');
        if (p>-1 || window_admin_role == 'superadmin' || window_admin_role == 'admin') {next()} else {next('/')}
     },
     // @load_method
     created(){
       this.admin_branch_list()
     },
     filters: {
        day_in_bangla: function(value){
          if (!value) {return ''}
          if (value == 'Sun') {return value.replace('Sun', 'রবিবার')}
          if (value == 'Mon') {return value.replace('Mon', 'সোমবার')}
          if (value == 'Tue') {return value.replace('Tue', 'মঙ্গলবার')}
          if (value == 'Wed') {return value.replace('Wed', 'বুধবার')}
          if (value == 'Thu') {return value.replace('Thu', 'বৃহস্পতিবার')}
          if (value == 'Fri') {return value.replace('Fri', 'শুক্রবার')}
          if (value == 'Sat') {return value.replace('Sat', 'শনিবার')}
        },
        digit_to_bangla: function(value){
          if (value == 2019) {return '২০১৯'}
          if (value == 2020) {return '২০২০'}
          var month = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']
          var bangla_month = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগষ্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর']
          var x = new Date(value).getMonth()
          value = value.toString()
          value = value.toLowerCase().replace(month[x], bangla_month[x])

          var numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
          var bangla_num = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯']
          if (!value) {return ''}
          for (var i = 0; i < value.length; ++i) {
            var index = numbers.findIndex(x => x == value[i])
            value = value.replace(numbers[index], bangla_num[index])
          }

          return value;
        }
     },
   
     methods:{
       // @add_item_method
       admin_branch_list(){
             this.branch = window_branch;
             if (this.branch.length==1) {
                 this.dialogItem.branch_id = this.branch[0].id
                 this.admin_echelon_list(this.dialogItem.branch_id);
                 this.admin_batch_list()
             }
             this.branch = this.branch.map(arr => { return { value: arr['id'], text: arr['name'] } }) 
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
        get_batch() {
            var batch = Object.assign([], this.original_batch);
            let filter_batch = batch.filter((x) => {
                return x.echelon_id == this.dialogItem.echelon_id;
            });
            this.batch = filter_batch.map((arr) => {
                return { value: arr["id"], text: arr["name"] };
            });
            var index = window_echelons.findIndex(x => x.id == this.dialogItem.echelon_id)
            if (index>-1) {
                this.echelon_bangla_text = window_echelons[index].bangla_text
            }
        },
        get_other_batch(){
            this.other_batch = this.batch.filter((x) => {
                return x.value != this.dialogItem.batch_id
            })
            var schedule_title = this.$cookies.get('schedule_title')
            if (schedule_title) {
                var x = JSON.parse(schedule_title)
                if (x.length) {
                    var index = x.findIndex(y => y.batch_id == this.dialogItem.batch_id)
                    if (index>-1) {
                      this.dialogItem.title = x[index].title
                    }
                }
            }
        },
        get_item() {
            if (this.branch.length > 1) {
                this.admin_echelon_list(this.dialogItem.branch_id);
            }
        },
        get_schedule_list(){
            var index = this.batch.findIndex(x => x.value == this.dialogItem.batch_id)
            var b = []
            if (index>-1) {
                b.push(this.batch[index].text)
            }

            for (var i = 0; i < this.dialogItem.other_batch_id.length; i++) {
                b.push(this.dialogItem.other_batch_id[i].text)
            }
            this.apply_batch = b.join(', ')
            this.dialog = true
            this.axios.post('/admin/schedule/daily_schedule_by_date', this.dialogItem).then(response => {
                this.dialog = false
                this.schedule_data = response.data.daily_schedule
                if (this.schedule_data.length) {this.found_schedule = true} else {this.found_schedule = false}
                var original_list = response.data.daily_schedule
                this.start_month = new Date(this.dialogItem.date_from).getMonth()
                this.end_month = new Date(this.dialogItem.date_to).getMonth()
                this.schedule_year = new Date(this.dialogItem.date_to).getFullYear()
                if (this.start_month != this.end_month) {
                   this.schedule_month = this.get_month_bangla(this.start_month) + '-' + this.get_month_bangla(this.end_month)
                }
                else {this.schedule_month = this.get_month_bangla(this.start_month)}

                var date = ''
                var new_list = []
                var count_class = 0
                for (var i = 0; i < this.schedule_data.length; i++) {
                    if (this.schedule_data[i].date != date) {
                        count_class = original_list.filter(x => x.date == this.schedule_data[i].date).length
                        date = this.schedule_data[i].date
                    }
                    this.schedule_data[i].count_class = count_class
                    count_class = 0
                }
            }, error => {
                this.dialog = true
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
       },
       daily_schedule_by_id(){
         this.axios.get('/admin/schedule/daily_schedule_by_id/' + this.$route.params.id).then(response => {
             this.schedule_list = response.data.daily_schedule_by_id
             var original_list = response.data.daily_schedule_by_id
             var date = ''
             var new_list = []
             var count_class = 0
             for (var i = 0; i < this.schedule_list.length; i++) {
               if (this.schedule_list[i].date != date) {
                  count_class = original_list.filter(x => x.date == this.schedule_list[i].date).length
                  date = this.schedule_list[i].date
               }
               this.schedule_list[i].count_class = count_class
               count_class = 0
             }

         }); 
        },
        get_month_bangla(i){
        var month = [{num: 1, name: 'jan', bangla: 'জানুয়ারি'}, {num: 2, name: 'feb', bangla: 'ফেব্রুয়ারি'}, {num: 3, name: 'mar', bangla: 'মার্চ'}, {num: 4, name: 'apr', bangla: 'এপ্রিল'}, {num: 5, name: 'may', bangla: 'মে'}, {num: 6, name: 'jun', bangla: 'জুন'}, {num: 7, name: 'jul', bangla: 'জুলাই'}, {num: 8, name: 'aug', bangla: 'আগষ্ট'}, {num: 9, name: 'sep', bangla: 'সেপ্টেম্বর'}, {num: 10, name: 'oct', bangla: 'অক্টোবর'}, {num: 11, name: 'nov', bangla: 'নভেম্বর'},{num: 12, name: 'dec', bangla: 'ডিসেম্বর'}]
        return month[i].bangla
        },
        generate(){
            this.loading = true
            this.axios.post('/admin/schedule/generate_schedule', this.dialogItem).then(response => {
                $('.user_nav').addClass('successful')
                this.loading = false
                this.created = true
                this.exist = false
                setTimeout(function () { $('.user_nav').removeClass('successful') }.bind(this), 3000)
            }, error => {
                this.loading = false
                $('.user_nav').addClass('denied')
                setTimeout(function () { $('.user_nav').removeClass('denied') }.bind(this), 3000)    
            });
        },
        download() {
          var schedule_title = this.$cookies.get('schedule_title')
          var data = {'batch_id': this.dialogItem.batch_id, 'title': this.dialogItem.title}
          if (schedule_title) {
              var x = JSON.parse(schedule_title)
              var index = x.findIndex(y => y.batch_id == this.dialogItem.batch_id)
              if (index>-1) {
                x[index].title = this.dialogItem.title
              } else {
                x.push(data)
              }
          } else {
            var x = []
            x.push(data)
          }
          var x = JSON.stringify(x)
          this.$cookies.set('schedule_title', x);
          var logo = 20
          var h1 = $('#print_content h2').outerHeight()
          var h2 = $('#print_content h3').outerHeight()
          var tr = 0
          $('#print_content table tr').each(function(){
              tr = tr + $(this).height()
              if (logo + h1 + h2 + tr > 880) {
                if ($(this).find('td.schedule_date').length>0) {
                    $(this).before('<tr class="fake_tr" style="opacity:0; border:0!important;"><td>d</td></tr>');
                    tr = 0; h1= 0; h2 = 0; logo = 0;
                }
                else {
                    if ($(this).next('tr').find('td.schedule_date').length>0) {
                        $(this).after('<tr class="fake_tr" style="opacity:0; border:0!important;"><td>d</td></tr>');
                        tr = 0; h1= 0; h2 = 0; logo = 0;
                    }
                    else {
                      $(this).prev('tr').has('td.schedule_date').before('<tr class="fake_tr" style="opacity: 0; border:0!important;"><td>d</td></tr>');
                    }
                }
              }
          })
          $('.fake_tr td').attr('style', 'border:0!important; opacity:0;');
          $('#print_content').printThis({
              importCSS: true,
              loadCSS: ["/css/print_portrait.css"]
          });
          $('.fake_tr').remove()
       },
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
       // @add_item_method_close_dialog
        clear(){
            this.search = ''
            this.search_filter = ''
            this.search_filter1 = ''
        }
     }
   }
</script>


<style>
  .schedule_view .v-alert{
      position: fixed;
      z-index: 1;
      right: 10px;
      top: 70px;
      width: 300px;
      height: 50px;
  }
  .schedule_view .v-text-field label, .schedule_view .v-text-field input {
    font-size: 14px;
  }
  .schedule_view #edit_class_no {
     color: #FF5722!important;
     font-weight: 500;
     font-size: 30px;
  }
  .schedule_view .v-text-field{
    padding-top: 2px;
    margin-top: 2px;
  }
  .schedule_view .v-input{
    font-size: 14px;
  }
  .schedule_view .v-btn{
    height: 32px;
  }
</style>