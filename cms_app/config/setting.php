<?php

$boards = [
	"Dhaka","Cumilla","Rajshahi","Jessore","Chattogram","Barishal","Sylhet","Dinajpur","Mymensingh","Madrasah","Technical"
];
$districts = [
	"Bagerhat","Bandarban","Barguna","Barishal","Bhola","Bogura","Brahmanbaria","Chandpur","Chattogram",
	"Chuadanga","Cox's Bazar","Cumilla","Dhaka","Dinajpur","Faridpur","Feni","Gaibandha","Gazipur","Gopalganj",
	"Habiganj","Jamalpur","Jashore","Jhalokati","Jhenaidah","Joypurhat","Khagrachhari","Khulna","Kishoreganj",
	"Kurigram","Kushtia","Lakshmipur","Lalmonirhat","Madaripur","Magura","Manikganj","Meherpur","Moulvibazar",
	"Munshiganj","Mymensingh","Naogaon","Narail","Narayanganj","Narsingdi","Natore","Netrokona","Nilphamari",
	"Noakhali","Pabna","Panchagarh","Patuakhali","Pirojpur","Rajbari","Rajshahi","Rangamati","Rangpur","Satkhira",
	"Shariatpur","Sherpur","Sirajganj","Sunamganj","Sylhet","Tangail","Thakurgaon"
];

$passing_years = range(date("Y"), 2010);



return [
	'search_student_by'     => ['year'],
	'search_label'          => 'Year',
	'batch_label'           => 'Batch',
	'enable_echelon_field' => true,
	'reg_no_label'		   => 'Reg No',
	'allow_field' => ['exam_reg_no', 'blood_group', 'exam_time', 'reference', 'course_fee', 'booking_fee', 'discount'],
	'student_type' => [['value' => 'general', 'text' => 'General'], ['value' => 'test_only', 'text' => 'Test Only']],
	'permissions' => [
		['view_student', 'Can View Student List'],
		['add_student', 'Can Add Student'],
		['edit_student', 'Can Add and Edit Student Information'],
		['edit_attendance', 'Can Take and Edit Student Attendance'],
		['edit_schedule', 'Can Add and Edit Schedule'],
		['manage_teacher', 'Can Add and Edit Teacher'],
		['view_exam', 'Can View Exam List'],
		['manage_exam', 'Can Add and Edit Exam Marks'],
		['add_payment', 'Can Take Payment'],
		['manage_payment', 'Can Edit and Delete Payment'],
		['add_teacher_payment', 'Can Make Teacher Payment'],
		['manage_teacher_payment', 'Can Edit Teacher Payment'],
		['view_unpaid', 'Can View Unpaid Student'],
		['add_transaction', 'Can Add Transaction'],
		['manage_transaction', 'Can Edit and Delete Transaction'],
		['setting_batch', 'Can Manage Batch'],
		['setting_subject', 'Can Manage Subject'],
		['setting_exam_type', 'Can Manage Exam Type'],
		['setting_institution', 'Can Manage Institution'],
		['view_dashboard', 'Can View Dashboard'],
		['send_sms', 'Can Send SMS']
	],

	'first_login_setup_field' => [
        'nationality' => 'Nationality',
        'father_nid' => "Father's NID",
        'mother_nid' => "Mother's NID",
        'student_nid' => "Student's NID",
        'birth_certificate' => "Birth Certificate No",
        'father_mobile' => "Father's Mobile No",
        'mother_mobile' => "Mother's Mobile No",
        'parents_name_bn' => "Parents' Name in Bangla",
        'father_profession' => "Father's Profession",
        'mother_profession' => "Mother's Profession",
        'parents_income' => "Parents' Yearly Income",
        'permanent_address' => "Permanent Address",
        'academic_info' => [
            'label' => 'Academic Information',
            'options' => [
                'jsc' => 'JSC',
                'ssc' => 'SSC',
                'hsc' => 'HSC',
            ],
        ],
        'religion' => 'Religion',
        'guardian_photo' => 'Guardian Photo',
        'previous_school' => 'Previous School Details'
    ],

	'student_admission_field' => [
		[
			"title" => "Student Information",
			"fields" => [
				[ "name" => "name", "type" => "text", "label" => "Student Name", "rules" => ["required"], "colSize" => 4 ],
				[ "name" => "name_bn", "type" => "text", "label" => "Student Name (Bangla)", "colSize" => 4 ],
				[ "name" => "gender", "type" => "select", "label" => "Gender", "options" => ["Male", "Female", "Other"], "rules" => ["required"], "colSize" => 4 ],
				[ "name" => "date_of_birth", "type" => "date", "label" => "Date of Birth", "rules" => ["required"], "colSize" => 4 ],
				[ "name" => "birth_certificate_no", "type" => "text", "label" => "Birth Certificate No", "colSize" => 4 ],
				[ "name" => "religion", "type" => "select", "label" => "Religion", "options" => ["Islam", "Hindu", "Buddist", "Christian"], "colSize" => 4 ],
				[ "name" => "school_roll", "type" => "text", "label" => "School Roll", "colSize" => 4 ],
				[ "name" => "mobile", "type" => "text", "label" => "Primary Mobile", "rules" => ["required"], "colSize" => 4 ],
				[ "name" => "mobile2", "type" => "text", "label" => "Alternate Mobile", "colSize" => 4 ]
			]
		],
		[
			"title" => "Fee Information",
			"fields" => [
				[ "name" => "fee_type", "type" => "select", "label" => "Fee Type", "rules" => ["required"], "options" => ["Monthly Fee", "Course Fee"], "colSize" => 4 ],
				[ "name" => "fee", "type" => "number", "label" => "Fee", "colSize" => 4, "rules" => ["required"]],
				[ "name" => "admission_fee", "type" => "number", "label" => "Admission Fee", "colSize" => 4 ],
			]
		],

		[
			"title" => "Parents Information",
			"subsections" => [
				[
				"title" => "Father",
					"fields" => [
						[ "name" => "father_name", "type" => "text", "label" => "Father Name", "colSize" => 4 ],
						[ "name" => "father_name_bn", "type" => "text", "label" => "Father Name (Bangla)", "colSize" => 4 ],
						[ "name" => "father_nid", "type" => "text", "label" => "Father NID", "colSize" => 4 ],
						[ "name" => "father_profession", "type" => "text", "label" => "Father Profession", "colSize" => 4 ],
						[ "name" => "father_mobile", "type" => "text", "label" => "Father Mobile", "colSize" => 4 ]
					],
				],
				[
				"title" => "Mother",
					"fields" => [
						[ "name" => "mother_name", "type" => "text", "label" => "Mother Name", "colSize" => 4 ],
						[ "name" => "mother_name_bn", "type" => "text", "label" => "Mother Name (Bangla)", "colSize" => 4 ],
						[ "name" => "mother_nid", "type" => "text", "label" => "Mother NID", "colSize" => 4 ],
						[ "name" => "mother_profession", "type" => "text", "label" => "Mother Profession", "colSize" => 4],
						[ "name" => "mother_mobile", "type" => "text", "label" => "Mother Mobile", "colSize" => 4 ]
					],
				],
				[
				"title" => "Guardian (In Absence of Father/Mother)",
					"fields" => [
						[ "name" => "guardian_name", "type" => "text", "label" => "Mother Name", "colSize" => 4 ],
						[ "name" => "guardian_nid", "type" => "text", "label" => "Mother NID", "colSize" => 4 ],
						[ "name" => "guardian_mobile", "type" => "text", "label" => "Guardian Mobile", "colSize" => 4 ]
					],
				]
			],
			"fields" => [
				[ "name" => "parents_yearly_income", "type" => "number", "label" => "Parents Yearly Income", "colSize" => 4 ]
			],
		],

		[
			"title" => "Address Information",
			"subsections" => [
				[
				"title" => "Present Address",
					"fields" => [
						[ "name" => "present_address_thana", "type" => "text", "label" => "Thana", "colSize" => 4 ],
						[ "name" => "present_address_district", "type" => "autocomplete", "label" => "District", "source" => $districts, "colSize" => 4 ],
						[ "name" => "present_address_details", "type" => "text", "label" => "Address Details", "colSize" => 4 ],
					],
				],
				[
				"title" => "Permanant Address",
					"fields" => [
						[ "name" => "permanent_address_thana", "type" => "text", "label" => "Thana", "colSize" => 4 ],
						[ "name" => "permanent_address_district", "type" => "autocomplete", "label" => "District", "source" => $districts, "colSize" => 4 ],
						[ "name" => "permanent_address_details", "type" => "text", "label" => "Address Details", "colSize" => 4 ],
					],
				]
			],
		],
		
		[
			"title" => "Academic Information",
			"subsections" => [
				[
				"title" => "SSC",
					"fields" => [
						[ "name" => "ssc_school_name", "type" => "text", "label" => "SSC School Name", "colSize" => 4 ],
						[ "name" => "ssc_roll", "type" => "text", "label" => "SSC Roll", "colSize" => 4 ],
						[ "name" => "ssc_reg_no", "type" => "text", "label" => "SSC Reg No", "colSize" => 4 ],
						[ "name" => "ssc_gpa", "type" => "number", "label" => "SSC GPA", "colSize" => 4 ],
						[ "name" => "ssc_department", "type" => "select", "label" => "SSC Department", "options" => ["Science", "Business Studies", "Humanities"], "colSize" => 4 ],
						[ "name" => "ssc_passing_year", "type" => "autocomplete", "label" => "SSC Passing Year", "source" => $passing_years, "colSize" => 4 ],
						[ "name" => "ssc_board", "type" => "autocomplete", "label" => "SSC Board", "source" => $boards, "colSize" => 4 ],
					],
				],
				[
				"title" => "HSC",
					"fields" => [
						[ "name" => "hsc_college_name", "type" => "text", "label" => "HSC College Name", "colSize" => 4 ],
						[ "name" => "hsc_roll", "type" => "text", "label" => "HSC Roll", "colSize" => 4 ],
						[ "name" => "hsc_reg_no", "type" => "text", "label" => "HSC Reg No", "colSize" => 4 ],
						[ "name" => "hsc_gpa", "type" => "number", "label" => "HSC GPA", "colSize" => 4 ],
						[ "name" => "hsc_department", "type" => "select", "label" => "HSC Department", "options" => ["Science", "Business Studies", "Humanities"], "colSize" => 4 ],
						[ "name" => "hsc_passing_year", "type" => "autocomplete", "label" => "HSC Passing Year", "source" => $passing_years, "colSize" => 4 ],
						[ "name" => "hsc_board", "type" => "autocomplete", "label" => "HSC Board", "source" => $boards, "colSize" => 4 ],
					],
				],
				[
				"title" => "JSC",
					"fields" => [
						[ "name" => "jsc_school_name", "type" => "text", "label" => "JSC School Name", "colSize" => 4 ],
						[ "name" => "jsc_roll", "type" => "text", "label" => "JSC Roll", "colSize" => 4 ],
						[ "name" => "jsc_reg_no", "type" => "text", "label" => "JSC Reg No", "colSize" => 4 ],
						[ "name" => "jsc_gpa", "type" => "number", "label" => "JSC GPA", "colSize" => 4 ],
						[ "name" => "jsc_passing_year", "type" => "autocomplete", "label" => "JSC Passing Year", "source" => $passing_years, "colSize" => 4 ],
						[ "name" => "jsc_board", "type" => "autocomplete", "label" => "JSC Board", "source" => $boards, "colSize" => 4 ],
					],
				],
			],
		]

	],

	'choosable_fields' => 
	[
		'religion', 'birth_certificate',
		'father_name_bn', 'father_nid', 'father_profession',
    	'mother_name_bn', 'mother_nid', 'mother_profession',
    	'guardian_nid', 
		'permanent_address', 'permanent_address_thana', 'permanent_address_district', 'permanent_address_details',
		'ssc', 'ssc_school_name', 'ssc_roll', 'ssc_reg_no', 'ssc_gpa', 'ssc_department', 'ssc_passing_year', 'ssc_board',
		'hsc', 'hsc_college_name', 'hsc_roll', 'hsc_reg_no', 'hsc_gpa', 'hsc_department', 'hsc_passing_year', 'hsc_board',
		'jsc','jsc_school_name', 'jsc_roll', 'jsc_reg_no', 'jsc_gpa', 'jsc_passing_year', 'jsc_board',
	],
	'groupMap' => [
		'ssc' => ['ssc_school_name', 'ssc_roll', 'ssc_reg_no', 'ssc_gpa', 'ssc_department', 'ssc_passing_year', 'ssc_board'],
		'hsc' => ['hsc_college_name', 'hsc_roll', 'hsc_reg_no', 'hsc_gpa', 'hsc_department', 'hsc_passing_year', 'hsc_board'],
		'jsc' => ['jsc_school_name', 'jsc_roll', 'jsc_reg_no', 'jsc_gpa', 'jsc_passing_year', 'jsc_board'],
		'permanent_address' => ['permanent_address_thana', 'permanent_address_district', 'permanent_address_details']
	]
];
