<!doctype html>
<html>
    <head>
        <link href="http://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.css" rel="stylesheet">
        <link href='{{ asset('css/style.css?'.time()) }}' rel="stylesheet">
        <link href='{{ asset('css/util.css?'.time()) }}' rel="stylesheet">
    </head>
    <body>
        {{-- {{$foo}} --}}
        <div class="container schedule_list">
            <h2 class="text-center fw-600 m-b-5 solaiman">ষষ্ঠ শ্রেণি</h2>
            <h3 class="text-center m-b-15 m-t-5 fw-600 solaiman">সেপ্টেম্বর মাসের সিডিউল - ২০১৯</h3>
          <div style="width: 50%; float: left; margin-right: 10px;">
            <table class="table table-bordered">
              @for ($i = 0; $i < 10; $i++)
                <tr>
                  <td class="schedule_date">
                    ২১ সেপ্টম্বর 19
                  </td>
                  <td class="schedule_type">ক্লাস</td>
                  <td class="schedule_subject">বাংলা ১ম পত্র</td>
                  <td class="schedule_topic">সৃজনশীল আলোচনা</td>
                </tr>
              @endfor
            </table>
          </div>
          <div class="float-right">ddddddddddddddddd</div>
       </div>
    </body>
</html>
