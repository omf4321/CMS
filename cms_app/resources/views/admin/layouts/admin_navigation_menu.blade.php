<v-list class="nav-list">
    <v-list-item link :to="{name:'admin_dashboard'}" class="p-l-32">
        <v-list-item-action>
            <v-icon>home</v-icon>
        </v-list-item-action>
        <v-list-item-title>Home</v-list-item-title>
    </v-list-item>
    <div>
        {{-- student --}}
        @can('view_student')
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>person</v-icon>
                </v-list-item-action>
                <v-list-item-title>Student</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'student_list'}">
                <v-icon class="mr-3">list</v-icon>
                <v-list-item-title>List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'student_photo'}">
                <v-icon class="mr-3">insert_photo</v-icon>
                <v-list-item-title>Upload Photo</v-list-item-title>
            </v-list-item>            
            <v-list-item :to="{name:'student_visiting_list'}">
                <v-icon class="mr-3">people</v-icon>
                <v-list-item-title>Visiting List</v-list-item-title>
            </v-list-item>
        </v-list-group>
        @endcan
        {{-- Attendance --}}
        @can('edit_attendance')
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>emoji_people</v-icon>
                </v-list-item-action>
                <v-list-item-title>Attendance</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'attendance_list'}">
                <v-icon class="mr-3">list</v-icon>
                <v-list-item-title>List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'individual_attendance'}">
                <v-icon class="mr-3">fingerprint</v-icon>
                <v-list-item-title>Individual Attendance</v-list-item-title>
            </v-list-item>
        </v-list-group>
        @endcan
        {{-- setting --}}
        @if(auth()->user()->can('setting_batch') || auth()->user()->can('setting_subject') || auth()->user()->can('setting_exam_type'))
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>settings</v-icon>
                </v-list-item-action>
                <v-list-item-title>Setting</v-list-item-title>
            </v-list-item>
            @role('superadmin')
            <v-list-item :to="{name: 'setting_echelon'}">
                <v-icon class="mr-3">class</v-icon>
                <v-list-item-title>Class</v-list-item-title>
            </v-list-item>
            @endrole
            @can('setting_batch')
            <v-list-item :to="{name: 'setting_batch'}">
                <v-icon class="mr-3">group_work</v-icon>
                <v-list-item-title>Batch</v-list-item-title>
            </v-list-item>
            @endcan
            @can('setting_group')
            <v-list-item :to="{name: 'setting_group'}">
                <v-icon class="mr-3">style</v-icon>
                <v-list-item-title>Group</v-list-item-title>
            </v-list-item>
            @endcan
            @can('setting_institution')
            <v-list-item :to="{name: 'setting_institution'}">
                <v-icon class="mr-3">location_city</v-icon>
                <v-list-item-title>Institution</v-list-item-title>
            </v-list-item>
            @endcan
            @can('setting_subject')
            <v-list-item :to="{name: 'setting_subject'}">
                <v-icon class="mr-3">subject</v-icon>
                <v-list-item-title>Subject</v-list-item-title>
            </v-list-item>
            @endcan
            @role('superadmin')
            <v-list-item :to="{name: 'setting_branch'}">
                <v-icon class="mr-3">pin_drop</v-icon>
                <v-list-item-title>Branch</v-list-item-title>
            </v-list-item>
            @endrole
            @can('setting_course')
            <v-list-item :to="{name: 'setting_course'}">
                <v-icon class="mr-3">grain</v-icon>
                <v-list-item-title>Course</v-list-item-title>
            </v-list-item>
            @endcan
        </v-list-group>
        @endif
        {{-- teacher --}}
        @can('manage_teacher')
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>perm_identity</v-icon>
                </v-list-item-action>
                <v-list-item-title>Teacher</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'teacher_list'}">
                <v-icon class="mr-3">list</v-icon>
                <v-list-item-title>List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'teacher_schedule'}">
                <v-icon class="mr-3">border_inner</v-icon>
                <v-list-item-title>Teacher Schedule</v-list-item-title>
            </v-list-item>
        </v-list-group>
        @endcan
        {{-- Schedule --}}
        @can('edit_schedule')
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>calendar_today</v-icon>
                </v-list-item-action>
                <v-list-item-title>Schedule</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'schedule_manage'}">
                <v-icon class="mr-3">edit</v-icon>
                <v-list-item-title>Manage</v-list-item-title>
            </v-list-item>
            {{-- <v-list-item :to="{name:'schedule_chapter'}">
                <v-icon class="mr-3">blur_linear</v-icon>
                <v-list-item-title>Chapter</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'schedule_planner'}">
                <v-icon class="mr-3">notes</v-icon>
                <v-list-item-title>Schedule Planner</v-list-item-title>
            </v-list-item> --}}
            <v-list-item :to="{name:'schedule_weekly'}">
                <v-icon class="mr-3">update</v-icon>
                <v-list-item-title>Weekly Schedule</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'schedule_daily'}">
                <v-icon class="mr-3">ballot</v-icon>
                <v-list-item-title>Daily Schedule</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'schedule_generate'}">
                <v-icon class="mr-3">toys</v-icon>
                <v-list-item-title>Schedule Generator</v-list-item-title>
            </v-list-item>
            {{-- <v-list-item :to="{name:'schedule_view'}">
                <v-icon class="mr-3">av_timer</v-icon>
                <v-list-item-title>Schedule Print</v-list-item-title>
            </v-list-item> --}}
            <v-list-item :to="{name:'edit_schedule'}">
                <v-icon class="mr-3">border_inner</v-icon>
                <v-list-item-title>Edit Schedule</v-list-item-title>
            </v-list-item>
        </v-list-group>
        @endcan
        {{-- Exam --}}
        @if(auth()->user()->can('view_exam') || auth()->user()->can('manage_exam'))
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>description</v-icon>
                </v-list-item-action>
                <v-list-item-title>Exam</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'exam_mark_edit'}">
                <v-icon class="mr-3">note_alt</v-icon>
                <v-list-item-title>Exam List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'exam_merit_list'}">
                <v-icon class="mr-3">format_list_numbered_rtl</v-icon>
                <v-list-item-title>Merit List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'exam_individual_list'}">
                <v-icon class="mr-3">insert_chart_outlined</v-icon>
                <v-list-item-title>Individual Mark</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'combine_subject_rule'}">
                <v-icon class="mr-3">expand</v-icon>
                <v-list-item-title>Subject Combine Rule</v-list-item-title>
            </v-list-item>
        </v-list-group>   
        @endif    
        {{-- payment --}}
        @if(auth()->user()->can('add_payment') || auth()->user()->can('manage_payment'))
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>featured_play_list</v-icon>
                </v-list-item-action>
                <v-list-item-title>Payments</v-list-item-title>
            </v-list-item>
            @can('add_payment')
            <v-list-item :to="{name:'student_payment'}">
                <v-icon class="mr-3">payment</v-icon>
                <v-list-item-title>Make Payment</v-list-item-title>
            </v-list-item>
            @endcan
            @can('manage_payment')
            <v-list-item :to="{name:'student_payment_list'}">
                <v-icon class="mr-3">list</v-icon>
                <v-list-item-title>Student Payment</v-list-item-title>
            </v-list-item>
            @endcan
            @can('add_payment')
            <v-list-item :to="{name:'student_payment_report'}">
                <v-icon class="mr-3">info</v-icon>
                <v-list-item-title>Student Payment Report</v-list-item-title>
            </v-list-item>
            @endcan
            <v-list-item :to="{name:'teacher_payment'}">
                <v-icon class="mr-3">chrome_reader_mode</v-icon>
                <v-list-item-title>Teacher Payment</v-list-item-title>
            </v-list-item>
            @can('view_unpaid')
            <v-list-item :to="{name:'student_unpaid_report'}">
                <v-icon class="mr-3">money_off</v-icon>
                <v-list-item-title>Student Unpaid Report</v-list-item-title>
            </v-list-item>
            @endcan
        </v-list-group>
        @endif

        @if(auth()->user()->can('add_transaction') || auth()->user()->can('manage_transaction'))
        <v-list-group no-action>
            
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>swap_horizontal_circle</v-icon>
                </v-list-item-action>
                <v-list-item-title>Transaction</v-list-item-title>
            </v-list-item>
            @can('add_transaction')
            <v-list-item :to="{name:'transaction'}">
                <v-icon class="mr-3">add</v-icon>
                <v-list-item-title>Add Transaction</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'bill_list'}">
                <v-icon class="mr-3">receipt</v-icon>
                <v-list-item-title>Bill List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'biller_list'}">
                <v-icon class="mr-3">import_contacts</v-icon>
                <v-list-item-title>Biller</v-list-item-title>
            </v-list-item>
            @endcan

            @can('manage_transaction')
            <v-list-item :to="{name:'transaction_list'}">
                <v-icon class="mr-3">swap_horiz</v-icon>
                <v-list-item-title>Transaction List</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'salary'}">
                <v-icon class="mr-3">business_center</v-icon>
                <v-list-item-title>Salary</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'balance_report'}">
                <v-icon class="mr-3">money</v-icon>
                <v-list-item-title>Balance</v-list-item-title>
            </v-list-item>
            @endcan       
        </v-list-group>
        @endif

        @if(auth()->user()->can('add_transaction') || auth()->user()->can('manage_transaction'))
        <v-list-group no-action>            
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>payment</v-icon>
                </v-list-item-action>
                <v-list-item-title>Billing</v-list-item-title>
            </v-list-item>
            @can('manage_transaction')
            <v-list-item :to="{name:'billing_invoice'}">
                <v-icon class="mr-3">add_card</v-icon>
                <v-list-item-title>My Invoices</v-list-item-title>
            </v-list-item>
            @endcan       
        </v-list-group>
        @endif

        {{-- sms --}}
        @can('send_sms')
        <v-list-group no-action>
            <v-list-item slot="activator">
                <v-list-item-action>
                    <v-icon>textsms</v-icon>
                </v-list-item-action>
                <v-list-item-title>SMS</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'send_sms'}">
                <v-icon class="mr-3">sms</v-icon>
                <v-list-item-title>Send SMS</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'sms_report'}">
                <v-icon class="mr-3">filter_frames</v-icon>
                <v-list-item-title>SMS Report</v-list-item-title>
            </v-list-item>
            <v-list-item :to="{name:'recharge_history'}">
                <v-icon class="mr-3">move_to_inbox</v-icon>
                <v-list-item-title>Recharge</v-list-item-title>
            </v-list-item>
        </v-list-group>
        @endcan
        {{-- Role, Admin, User --}}
        @hasanyrole('superadmin|admin')
        <v-list-item :to="{name: 'admin_user'}" class="p-l-32">
            <v-list-item-action>
                <v-icon>verified_user</v-icon>
            </v-list-item-action>
            <v-list-item-title>Admin User</v-list-item-title>
        </v-list-item>
        @endhasanyrole
        @hasanyrole('superadmin|admin')
        <v-list-item :to="{name: 'admin_role'}" class="p-l-32">
            <v-list-item-action>
                <v-icon>device_hub</v-icon>
            </v-list-item-action>
            <v-list-item-title>Admin Role</v-list-item-title>
        </v-list-item>
        @endhasanyrole
        @role('superadmin')
        <a href="/admin/clear">
            <v-list-item class="p-l-32">
                <v-list-item-action>
                    <v-icon>clear_all</v-icon>
                </v-list-item-action>
                <v-list-item-title>Clear Cache</v-list-item-title>
            </v-list-item>
        </a>
        @endcan
    </div>
</v-list>