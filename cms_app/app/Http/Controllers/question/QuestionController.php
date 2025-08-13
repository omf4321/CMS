<?php

namespace App\Http\Controllers\question;

use App\Http\Controllers\Controller;
use App\Model\Admin\chapter;
use App\Model\Admin\chapter_topic;
use App\Model\Admin\question;
use App\Model\Admin\question_relative_text;
use App\Model\Admin\subject;
use App\Model\Admin\topic;
use App\Model\Admin\daily_schedule;
use App\Model\Admin\exam_question_list;
use App\Model\Admin\exam_question_type;
use App\Model\Admin\exam_question;
use App\Model\Admin\question_instruction;
use Auth;
use Illuminate\Http\Request;
use DB;
use Purifier;
use Carbon\Carbon;

// Contains
// User Login Success Home Page
// User Dashboard


class QuestionController extends Controller
{
    public function __construct()
    {
        // $this->middleware('admin');
    }

    public function question_add(Request $request)
    {
        // return response()->json($request->all(), 422);
        $this->validate($request, [
           'question_text'=> 'required',
           'question_type'=> 'required',
           'question_mark'=> 'required',
           'question_length'=> 'required',
           'subject_id'=> 'required'
        ]);
        DB::beginTransaction();
        try {
            $relative_text_id = [];
            if ($request->relative_text && $request->question_type != 'relative') {
                foreach ($request->relative_text as $key => $relative_text) {
                    $relative = new question_relative_text;
                    $relative->relative_text = Purifier::clean($relative_text['relative_text']);
                    $relative->question_type = $request->question_type;
                    $relative->chapter_id = chapter::where('chapter_no', $request->all_chapter[0][0])->where('subject_id', $request->subject_id)->first()->id;
                    $relative->save();
                    for ($i=0; $i < $relative_text['relative_qus_length'] ; $i++) {
                        array_push($relative_text_id, $relative->id);
                    }
                }
            }

            $relative_id = Null;
            if ($request->relative_textarea && $request->question_type == 'relative' && !$request->relative_text_id) {
                $relative = new question_relative_text;
                $relative->relative_text = Purifier::clean($request->relative_textarea);
                $relative->question_type = $request->question_type;
                $relative->relative_question_type = explode("-", $request->relative_question_type)[1];
                $relative->chapter_id = chapter::where('chapter_no', $request->all_chapter[0][0])->where('subject_id', $request->subject_id)->first()->id;
                $relative->save();
                $relative_id = $relative->id;
            }
            if ($request->question_type == 'relative' && $request->relative_text_id) {
                $rel = question_relative_text::find($request->relative_text_id);
                $relative_id = $request->relative_text_id;
                // relative_question_type
                $new_type = explode('-', $request->relative_question_type);
                if ($rel->relative_question_type) {
                    $question_type = explode(',', $rel->relative_question_type);
                    if (!in_array($new_type[1], $question_type)) {
                        array_splice( $question_type, $new_type[0] - 1, 0, $new_type[1] );
                        $rel->relative_question_type = implode(",", $question_type);
                    }
                }
                else {
                    $rel->relative_question_type = $new_type[1];
                }
                $rel->save();

            }

            foreach ($request->question_text as $key => $q) {
                $chapters = [];
                foreach ($request->all_chapter[$key] as $i => $chapter) {
                    $ch = chapter::where('chapter_no', $chapter)->where('subject_id', $request->subject_id)->first();
                    if ($ch) {
                        array_push($chapters, $ch->id);
                    }
                }
                $question = new question;
                $question->question_title = $request->question_title;
                $question->question_type = $request->question_type;
                $question->question_mark = $request->question_mark;
                $question->question_mark_detail = $request->question_mark_detail;
                $question->board_question_no = $request->board_question_no;
                $question->question_length = $request->question_length;
                $question->question_status = $request->question_status ? : 'lecturable';
                $question->reference_file = $request->reference_file;
                $question->question_relative_text_id = $relative_id;
                if(sizeof($relative_text_id)>0){
                    $question->question_relative_text_id = $relative_text_id[$key];
                }
                $question->relative_question_type = $request->relative_question_type ? explode("-", $request->relative_question_type)[1] : Null;
                // find index of question includes in short answer
                $short_answer_index = array_search($key, array_column($request->short_answer, 'index'));
                $short_answer = $short_answer_index > -1 ? strip_tags($request->short_answer[$short_answer_index]['text']) : Null;
                // for mcq
                $mcq_answer = Null;
                if ($request->question_type=='mcq' || $question->relative_question_type == 'mcq') {
                    $unicode = array("ক", "খ", "গ", "ঘ", "ঙ");
                    $char = array("a", "b", "c", "d", "e");
                    $roman = array("i", "ii", "iii", "iv", "v");
                    $ans = array_search(mb_substr($short_answer, 0, 1, "UTF-8"),$unicode);
                    if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$char);}
                    if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$roman);}
                    if ($ans>-1) {$mcq_answer = $ans + 1;}
                }
                $question->question_tag = $request->all_question_tag[$key] ? implode(",", $request->all_question_tag[$key]) : Null;
                $question->question_text = Purifier::clean($q);
                $question->short_answer = $short_answer;
                $question->short_answer = $short_answer;
                $question->detail_answer = $request->detail_answer ? Purifier::clean($request->detail_answer[$key]) : Null;
                $question->mcq_answer = $mcq_answer;
                $question->save();
                $question->chapters()->sync($chapters);
                $question->topics()->sync($request->topics);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    public function question_edit(Request $request, $id)
    {
        $this->validate($request, [
           'question_text'=> 'required',
           'question_type'=> 'required',
           'question_mark'=> 'required',
           'question_length'=> 'required',
           'subject_id'=> 'required'
        ]);

        $chapters = [];
        foreach ($request->chapters as $key => $chapter) {
            array_push($chapters, $chapter['value']);
        }

        $relative_id = Null;
        if ($request->relative_text_id) {
            $relative = question_relative_text::find($request->relative_text_id);
            $relative->relative_text = $request->relative_textarea;
            $relative->chapter_id = $chapters[0];
            $relative->save();
            $relative_id = $relative->id;
        }
        $question = question::find($id);
        $question->question_title = $request->question_title;
        $question->question_type = $request->question_type;
        $question->question_mark = $request->question_mark;
        $question->question_mark_detail = $request->question_mark_detail;
        $question->board_question_no = $request->board_question_no;
        $question->question_length = $request->question_length;
        $question->question_status = $request->question_status;
        $question->question_relative_text_id = $relative_id;
        // $question->relative_question_type = $request->relative_question_type ? explode("-", $request->relative_question_type)[1] : Null;
        $question->question_tag = $request->question_tag ? implode(",", $request->question_tag) : Null;
        $question->question_text = $request->question_text;
        $short_answer = strip_tags($request->short_answer);
        $question->short_answer = $short_answer;
        // for mcq
        $mcq_answer = Null;
        if ($request->question_type=='mcq' || $question->relative_question_type == 'mcq') {
            $unicode = array("ক", "খ", "গ", "ঘ", "ঙ");
            $char = array("a", "b", "c", "d", "e");
            $roman = array("i", "ii", "iii", "iv", "v");
            $ans = array_search(mb_substr($short_answer, 0, 1, "UTF-8"),$unicode);
            if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$char);}
            if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$roman);}
            if ($ans>-1) {$mcq_answer = $ans + 1;}
        }
        $question->mcq_answer = $mcq_answer;
        $question->detail_answer = $request->detail_answer;;
        $question->save();
        $question->chapters()->sync($chapters);
        $question->topics()->sync($request->topics);
    }

    public function relative_text_list($id)
    {
        $relative_text_list = question_relative_text::where('chapter_id', $id)->where('question_type', 'relative')->with('questions')->get();
        foreach ($relative_text_list as $key => $relative) {
            $relative->relative_text = strip_tags($relative->relative_text);
        }
        return response()->json(['relative_text_list' => $relative_text_list]);
    }

    public function relative_text_by_chapter($id)
    {
        $relative_text_list = question_relative_text::where('chapter_id', $id)->get();
        foreach ($relative_text_list as $key => $relative) {
            $relative->relative_text = strip_tags($relative->relative_text);
        }
        return response()->json(['relative_text_by_chapter' => $relative_text_list]);
    }

    public function question_list_by_chapter($subject_id)
    {
        $question_list = subject::has('chapters.questions')->with('echelons')->where('id', $subject_id);
        $question_list_single = $question_list->with(array('chapters' => function($query) {
            $query->has('questions.chapters', '=', 1)->orderBy('chapter_no')
            ->with(array('relatives' => function($query) {
                $query->has('questions')->with('questions.relatives', 'questions.topics', 'questions.chapters');
            }))->with(array('questions' => function($query) {
                $query->has('chapters', '=', 1)->with('relatives','topics','chapters');
            }));
        }))->get();

        $question_list_multiple = $question_list->with(array('chapters' => function($query) {
            $query->has('questions.chapters', '>', 1)->orderBy('chapter_no')
            ->with(array('relatives' => function($query) {
                $query->has('questions')->with('questions.relatives', 'questions.topics');
            }))->with(array('questions' => function($query) {
                $query->has('chapters', '>', 1)->with('chapters','relatives','topics');
            }));
        }))->get();

        return response()->json(['question_list_single' => $question_list_single, 'question_list_multiple' => $question_list_multiple]);
    }

    public function question_check_list(Request $request)
    {
        // return response()->json($request->all());
        if ($request->id) {
            $question_list = question::with('relatives', 'chapters', 'topics')->where('id', $request->id);
        }
        else {
            $chapters = [];
            foreach ($request->chapter_id as $key => $chapter_id) {
                array_push($chapters, $chapter_id['value']);
            }
            $question_list = question::groupBy(DB::raw('ifnull(question_relative_text_id, id)'))->with('relatives', 'chapters', 'topics')->whereHas('chapters', function($query) use ($chapters){
                $query->whereIn('id', $chapters);
            })->selectRaw('min(question_relative_text_id) as question_relative_text_id, min(question_type) as question_type, min(id) as id, min(question_text) as question_text, min(question_status) as question_status, min(short_answer) as short_answer, min(detail_answer) as detail_answer, min(question_mark) as question_mark, min(relative_question_type) as relative_question_type, min(question_tag) as question_tag')->orderBy('id');
            if ($request->question_type) {
                $question_list = $question_list->where('question_type', $request->question_type);
            }
            if ($request->question_status) {
                $question_list = $question_list->where('question_status', $request->question_status);
            }
            if ($request->question_tag) {
                $question_list = $question_list->where('question_tag', 'LIKE', "%$request->question_tag%");
            }
        }
        $question_list = $question_list->simplepaginate(25);

        return response()->json(['question_list' => $question_list]);
    }

    public function question_check_list_for_relative($id, $type)
    {
        $question_list = question_relative_text::where('id', $id)->first()->questions()->where('relative_question_type', $type)->with('relatives', 'chapters', 'topics')->get();
        if (sizeof($question_list)==0) {
            $relative = question_relative_text::find($id);
            $arr = explode(',', $relative->relative_question_type);
            if (($key = array_search($type, $arr)) !== false) {
                unset($arr[$key]);
            }
            $relative->relative_question_type = implode($arr, ',');
            $relative->save();
            return response()->json($relative->relative_question_type, 422);
        }
        return response()->json(['question_list' => $question_list]);
    }

    public function question_check(Request $request)
    {
        // return response()->json($request->all(), 422);
        foreach ($request->question_data as $q => $question_data) {
            $chapters = [];
            foreach ($request->chapter_data[$q] as $key => $chap) {
                array_push($chapters, $chap['value']);
            }
            $topics = [];
            foreach ($request->topic_data[$q] as $key => $chap) {
                array_push($topics, $chap['value']);
            }
            // return $topics;
            $question = question::find($question_data['id']);
            $question->question_status = $question_data['question_status'];
            $question->short_answer = $question_data['short_answer'];
            $question->question_text = $question_data['question_text'];
            $question->detail_answer = array_key_exists('detail_answer', $question_data) == true ? $question_data['detail_answer'] : Null;
            $question->save();
            if ($request->relative_text && $q == 0) {
                $relative = question_relative_text::find($question->question_relative_text_id);
                $relative->relative_text = $request->relative_text;
                $relative->save();
            }
            $question->chapters()->sync($chapters, false);
            $question->topics()->sync($topics, false);
        }

    }

    public function question_check_edit(Request $request, $id)
    {
        // return response()->json($request->all());
        $this->validate($request, [
           'question_text'=> 'required',
           'question_type'=> 'required',
           'question_mark'=> 'required',
           'question_length'=> 'required'
        ]);

        if ($request->relative_textarea) {
            $relative = question_relative_text::find($request->relative_text_id);
            $relative->relative_text = $request->relative_textarea;
            $relative->save();
        }

        $question = question::find($id);
        $question->question_title = $request->question_title;
        $question->question_type = $request->question_type;
        $question->question_mark = $request->question_mark;
        $question->board_question_no = $request->board_question_no;
        $question->question_length = $request->question_length;
        $question->question_status = $request->question_status;
        $question->question_tag = $request->question_tag ? implode(",", $request->question_tag) : Null;
        $question->question_text = $request->question_text;
        $question->short_answer = strip_tags($request->short_answer);
        // for mcq
        if ($request->question_type=='mcq' || $question->relative_question_type == 'mcq') {
            $short_answer = strip_tags($request->short_answer);
            $unicode = array("ক", "খ", "গ", "ঘ", "ঙ");
            $char = array("a", "b", "c", "d", "e");
            $roman = array("i", "ii", "iii", "iv", "v");
            $ans = array_search(mb_substr($short_answer, 0, 1, "UTF-8"),$unicode);
            if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$char);}
            if ($ans==-1) {$ans = array_search(substr($short_answer, 0, 1, "UTF-8"),$roman);}
            if ($ans>-1) {$mcq_answer = $ans + 1;}
        }
        $question->mcq_answer = $mcq_answer;
        $question->detail_answer = $request->detail_answer;
        $question->save();
    }

    public function question_delete($id)
    {
        $question = question::find($id);
        $question->delete();
    }

    public function question_delete_all(Request $request)
    {
        // return response()->json($request->all());
        $question = "";
        if ($request->id) {
            return $this->question_delete($request->id);
        }
        if ($request->subject_id) {
            $question = question::whereHas('chapters', function($query) use($request){
                $query->where('subject_id', $request->subject_id);
            });
        }
        if ($request->question_type) {
            $question = $question->where('question_type', $request->question_type);
        }
        if ($request->question_status) {
            $question = $question->where('question_status', $request->question_status);
        }
        if ($request->chapter && sizeof($request->chapter)>0) {
            $chapter = [];
            foreach ($request->chapter as $key => $chap) {
                array_push($chapter, $chap->value);
            }
            $question = $question->whereHas('chapters', function($query) use($request){
                $query->whereIn('id', $chapter);
            });
        }
        if ($question) {
            $question->delete();
        }
    }

    public function question_delete_by_chapter(Request $request)
    {
        // return response()->json($request->all());
        $question = "";
        if ($request->chapter_id && $request->type == 'single') {
            $question = question::whereHas('chapters', function($query) use($request){
                $query->where('id', $request->chapter_id);
            });
        }
        if ($request->type == 'multiple') {
            $question = question::whereHas('chapters', function($query) use($request){
                $query->where('subject_id', $request->subject_id);
            })->Has('chapters', '>', 1);
        }
        if ($question) {
            $question->delete();
        }
    }

    public function save_question_list(Request $request){
        $question_list = new exam_question_list;
        $question_list->subject_id = $request->subject_id;
        $question_list->save();

        foreach ($request->schedule_ids as $key => $schedule) {
            $daily = daily_schedule::where('id', $schedule)->first();
            $daily->exam_question_list_id = $question_list->id;
            $daily->save();
        }
        return response()->json(['question_list' => exam_question_list::where('id', $question_list->id)->with('schedules.batches', 'subjects.echelons')->first()]);
    }

    public function edit_question_list(Request $request, $id){
        $question_list = exam_question_list::find($id);
        foreach ($request->schedule_ids as $key => $schedule) {
            $daily = daily_schedule::where('id', $schedule)->first();
            $daily->exam_question_list_id = $question_list->id;
            $daily->save();
        }
        return response()->json(['question_list' => exam_question_list::where('id', $question_list->id)->with('schedules.batches', 'subjects.echelons')->first()]);
    }

    public function question_makable_list($id){
        
        $daily_schedule = exam_question_list::where('id', $id)->with('subjects.echelons', 'schedules', 'chapters')
        ->with(array('exam_question_types' => function($query){
            $query->with('relatives', 'relative_chapters', 'questions.chapters', 'questions.relatives')->orderBy('serial');
        }))->first();
        if (!$daily_schedule) {
            $daily_schedule = daily_schedule::where('id', $id)->with('subjects', 'chapters', 'batches.echelons', 'exam_lists')->first();
        }
        return response()->json(['schedule_list' => $daily_schedule]);
    }

    public function question_printable_list($id){
        
        $daily_schedule = exam_question_list::where('id', $id)->with('subjects.echelons', 'schedules.batches', 'chapters', 'exam_types')
        ->with(array('exam_question_types' => function($query){
            $query->with('relatives', 'relative_chapters', 'questions.chapters', 'questions.relatives')->orderBy('serial');
        }))->first();
        return response()->json(['schedule_list' => $daily_schedule]);
    }

    public function question_makable_list_with_intruction($id)
    {
        $daily_schedule = exam_question_list::where('id', $id)->with('exam_types', 'subjects.echelons', 'chapters')
        ->with(array('exam_question_types' => function($query){
                $query->with('questions')->orderBy('serial');
        }))->first();
        $subject_ids = [];
        $question_types = [];
        $cq_title = "";
        $cq_answer_instruction = "";
        $mcq_title = "";
        $bangla_digit = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        foreach ($daily_schedule->exam_question_types as $key => $type) {
            $answerable_bangla = [];
            $title_mark_bangla = [];
            if ($daily_schedule->language == 'bangla' && $type->answerable) {
                $array_answerable = str_split($type->answerable);
                foreach ($array_answerable as $char) {                    
                    array_push($answerable_bangla, $bangla_digit[$char]);
                }
            }
            if ($type->question_type == 'cq') {
                $total = $type->answerable * 10;
                $array_title = str_split($total);
                foreach ($array_title as $char) {
                    array_push($title_mark_bangla, $bangla_digit[$char]);
                }            
                $cq_title = 'সৃজনশীল (' . join("", $title_mark_bangla) . ')';
                $cq_answer_instruction = 'সংশ্লিষ্ট উদ্দীপকটি পড়ে যেকোন ' . join("", $answerable_bangla) . ' প্রশ্নের উত্তর দাও';
            }
            if ($type->question_type == 'mcq') {
                $total = $type->answerable * 1;
                $array_title = str_split($total);
                foreach ($array_title as $char) {
                    array_push($title_mark_bangla, $bangla_digit[$char]);
                }  
                $mcq_title = 'বহুনির্বাচনী (' . join("", $title_mark_bangla) . ')';
            }
            if ($type->question_type != 'cq' && $type->question_type != 'mcq') {
                array_push($subject_ids, $type->subject_id);
                array_push($question_types, $type->question_type);
            }
        }
        $question_instruction = [];
        if (sizeof($subject_ids)>0 & sizeof($question_types)>0) {
            $question_instruction = question_instruction::whereIn('subject_id', $subject_ids)->whereIn('question_type', $question_types)->get();
        }
        return response()->json(['schedule_list' => $daily_schedule, 'question_instruction' => $question_instruction, 'cq_title' => $cq_title, 'cq_answer_instruction' => $cq_answer_instruction, 'mcq_title' => $mcq_title]);
    }


    public function question_makable_list_by_date($date){
        $question_list = exam_question_list::whereHas('schedules', function($query) use($date){
            $query->whereDate('date', '>=', $date)->orderBy('date', 'asc');
        })
        ->with('schedules.batches', 'subjects.echelons', 'chapters')->orderBy('created_at', 'asc')->get();
        return response()->json(['question_list' => $question_list]);
    }

    public function exam_schedule_by_class_date($subject_id) {
        $schedule_list = daily_schedule::where('subject_id', $subject_id)->whereDate('date', '>=', Carbon::today())->where('schedule_type', 'like', '%exam%')->with('subjects', 'batches')->orderBy('date')->get();
        return response()->json(['schedule_list' => $schedule_list]);
    }

    public function question_makable_delete($id)
    {
        exam_question_list::find($id)->delete();
    }

    public function finish_make_question($id)
    {
        exam_question_list::where('id', $id)->update(['status' => 'finish']);
    }
    
    // question_type_setup (web.php)
    public function add_exam_question_type(Request $request){
        $ids = [];
        // return $request->all();

        foreach ($request->question_data as $key => $question_data) {
            $create_new = false;
            if (isset($question_data['id'])) {
                $type = exam_question_type::find($question_data['id']);
                if ($type->question_type == $question_data['question_type'] && $type->relative_id == $question_data['relatives']['id']) {
                    # code...
                    $type->question_type = $question_data['question_type'];
                    $type->total = $question_data['total'];
                    $type->answerable = $question_data['answerable'];
                    $type->chapters = $request->chapter;
                    $type->serial = $question_data['serial'];                 
                    $type->relative_id = $question_data['relatives']['id'];
                    $type->relative_chapter_id = $question_data['relative_chapters']['id'];
                    $type_data = [];
                    foreach ($question_data['relatives']['items'] as $key => $item) {
                        $arr = ['relative_question_type' => $item['relative_question_type'], 'total' => $item['total'], 'answer_instruction'=> ''];
                        array_push($type_data, $arr);
                    }
                    $type->relative_type_data = $type_data;
                    $type->save();
                    array_push($ids, $type->id);
                }
                else {
                    $type->delete();
                    $create_new = true;
                }
            }
            if (!isset($question_data['id']) || $create_new) {
                $type = new exam_question_type;
                $type->question_type = $question_data['question_type'];
                $type->total = $question_data['total'];
                $type->answerable = $question_data['answerable'];
                $type->relative_id = $question_data['relatives'] ? $question_data['relatives']['id'] : Null;
                $type->relative_chapter_id = array_key_exists('relative_chapters', $question_data) && $question_data['relative_chapters'] ? $question_data['relative_chapters']['id'] : Null;
                $type->serial = $question_data['serial'];
                $type->chapters = $request->chapter; 
                $type->exam_question_list_id = $request->id;
                $type->save();
                array_push($ids, $type->id);
            }
        }
        // exam_question_type::where('daily_schedule_id', $request->id)->whereNotIn('id', $ids)->delete();
        if ($request->chapter || $request->sets) {            
            $question_list = exam_question_list::where('id', $request->id)->first();
            $question_list->sets = $request->sets;
            $question_list->save();
            $question_list->chapters()->sync($request->chapter);
        }
        return response()->json(['list' => exam_question_type::where('exam_question_list_id', $request->id)->get()]);
    }

    public function get_select_question_item($id, $type_id){
        $type = exam_question_type::where('id', $type_id)->with('relatives','relative_chapters', 'questions')->first();
        if ($type->relatives) {
            $type->relatives->relative_text = strip_tags($type->relatives->relative_text);
        }
        $question_list = exam_question_list::where('id', $id)->with('subjects.echelons')
        ->with(array('chapters' => function($query) {
            $query->orderBy('chapter_no');
        }))->first();
        return response()->json(['type' => $type, 'question_list' => $question_list]);
    }

    public function select_question_list(Request $request){
        if ($request->echelon_id == 8) {
            $subject_name = subject::where('id', $request->subject_id)->first()->name;
            $subject_id = subject::where('name', $subject_name)->where('echelon_id', 7)->first()->id;
        }
        $type = '';
        $chapter = [];
        foreach ($request->chapter as $key => $chap) {
            if ($request->echelon_id == 8) {
                $chapter_name = chapter::where('id', $chap['value'])->first()->name;
                $chapter_id = chapter::where('name', $chapter_name)->where('subject_id', $subject_id)->first()->id;
            }
            else {
                $chapter_id = $chap['value'];
                $chapter_no = (float) explode(" ", $chap['text'])[0];
            }
            array_push($chapter, $chapter_id);
            if (ceil($chapter_no) == $chapter_no) {
                $added_chapter = chapter::where('subject_id', $request->subject_id)->where('chapter_no', '>', $chapter_no)->where('chapter_no', '<', $chapter_no + 1)->get();
                foreach ($added_chapter as $ch) {
                    array_push($chapter, $ch->id);
                }
            }
        }
        
        if ($request->question_type=='relative') {
            $type = exam_question_type::where('id', $request->exam_question_type_id)->with('relatives','relative_chapters', 'questions')->first();
            $type->relatives->relative_text = strip_tags($type->relatives->relative_text);
            $question_list = question::where('question_type', $request->question_type)->where('relative_question_type', $request->relative_question_type)->where('question_relative_text_id', $request->relative_id);
        }
        else {
            $question_list = question::where('question_type', $request->question_type);
        }

        $question_list = $question_list->whereHas('chapters', function($query) use($chapter){
            $query->whereIn('chapter_id', $chapter);
        })->with('topics', 'chapters', 'relatives')->get();
        return response()->json(['question_list' => $question_list, 'type' => $type]);
    }

    public function select_question(Request $request)
    {
        $e = exam_question_type::find($request->exam_question_type_id);
        $chapters = $e->chapters ?: [];
        foreach ($request->selected_chapter as $c) {
            if (!in_array($c['value'], $chapters)){
                array_push($chapters, $c['value']);
            }
        }
        $e->chapters = $chapters;
        $e->save();

        if (!$request->sets_action) {
            exam_question::where('exam_question_type_id', $request->exam_question_type_id)->where('relative_question_type', $request->relative_question_type)->delete();
            $new_selected_questions = $request->selected_questions;
            $question_count = sizeof($request->selected_questions);
            for ($i=0; $i < $request->sets ; $i++) {
                if (sizeof($request->selected_questions)==1) {
                     $this->add_selected_question($request, $request->selected_questions, $i + 1);
                } else {                    
                    $selected_questions = [];
                    $question_index = array_rand($new_selected_questions, sizeof($new_selected_questions));
                    $question_per_set = 0;                
                    foreach ($question_index as $index) {
                        array_push($selected_questions, $new_selected_questions[$index]);
                        $question_per_set++;
                        if ($question_per_set <= ($request->total_question / $request->sets)) {                            
                            $ids = $this->add_selected_question($request, $selected_questions, $i + 1);
                            $remain_set = $request->sets - ($i + 1);
                            if (sizeof($new_selected_questions)>($remain_set*$request->total_question)) {
                                unset($new_selected_questions[$index]);
                            }
                        }
                    }
                    shuffle($new_selected_questions);
                } 
            }
        }
        else {
            $this->add_selected_question($request, $request->selected_questions, $request->active_set + 1);
        }
        // return response()->json($ids, 422);
        
    }
    protected function add_selected_question($request, $selected_questions, $set)
    {
        $ids = [];
        foreach ($selected_questions as $key => $question) {
            $exam_question = exam_question::where('question_id', $question['id'])->where('exam_question_type_id', $request->exam_question_type_id)->where('relative_question_type', $request->relative_question_type)->where('set', $set)->first();
            if (!$exam_question) {
                $exam_question = new exam_question;
                $exam_question->question_id = $question['id'];
                $exam_question->exam_question_type_id = $request->exam_question_type_id;
                $exam_question->method = $request->method;
                $exam_question->relative_question_type = $request->relative_question_type;
                $exam_question->set = $set;
                $exam_question->save();
                array_push($ids, $exam_question->id);
            }
            else{
                array_push($ids, $exam_question->id);
            }
        }
        $exam_delete = exam_question::whereNotIn('id', $ids)->where('exam_question_type_id', $request->exam_question_type_id)->where('relative_question_type', $request->relative_question_type)->where('set', $set)->delete();
        return $ids;
    }

    public function generate_question(Request $request)
    {
        // return response()->json($request->all(), 422);
        DB::beginTransaction();
        try {
              
            $ids = [];
            $exam_type_ids = [];
            foreach ($request->type_data as $t_i => $question_type) {
                $exam_question_type = exam_question_type::find($question_type['id']);
                $chapters = $exam_question_type->chapters ?: [];
                foreach ($request->chapters as $c) {
                    if (!in_array($c['value'], $chapters)){
                        array_push($chapters, $c['value']);
                    }
                }
                $exam_question_type->chapters = $chapters;
                $exam_question_type->save();
                array_push($exam_type_ids, $exam_question_type->id);

                exam_question::where('exam_question_type_id', $question_type['id'])->delete();
                $min = floor($question_type['total']/sizeof($request->chapters)); //min question per chapter
                $max = ceil($question_type['total']/sizeof($request->chapters)); //max question per chapter
                for ($i=0; $i < $request->sets ; $i++) {
                    $remain_total = $question_type['total'];
                    foreach ($request->chapters as $c_i => $chapter) {
                        // if random_chapter true, chapters contain general value, text; question quantity not specified
                        if ($request->random_chapter) {
                            //randomly taken question quantity from min-max
                            $chapter_question_quantity = rand($min, $max);  
                            if ($question_type['total'] == sizeof($request->chapters)) {$chapter_question_quantity = 1;}
                            if ($remain_total == 0) {$chapter_question_quantity = 0;}
                            if ($c_i == sizeof($request->chapters) - 1) {$chapter_question_quantity = $remain_total;}
                            $chapter_id = $chapter['value'];
                        }
                        // if random_chapter false, chapters contain chapter_id and question_quantity
                        else {
                            $chapter_question_quantity = $chapter['question_quantity'];
                            $chapter_id = $chapter['value'];
                        }
                        // break if no chapter question quantity
                        if ($chapter_question_quantity == 0) {continue 2;}
                        // priorize examble statuse
                        $examableClause  = "CASE WHEN question_status = 'examable' THEN 0 ELSE 1 END";
                        $questions = question::whereHas('chapters', function($query) use ($chapter_id){
                            $query->where('chapter_id', $chapter_id);
                        })->Has('chapters', 1)->where('question_type', $exam_question_type->question_type)->orderByRaw($examableClause);
                        if ($request->question_tag != 'none' && $request->question_tag != 'board_school') {
                            $questions = $questions->where('question_tag', 'LIKE', "%$request->question_tag%");
                        }
                        if ($request->question_tag == 'board_school') {
                            $questions = $questions->where('question_tag', 'LIKE', "board")->orWhere('question_tag', 'LIKE', 'school');
                        }
                        $questions = $questions->inRandomOrder()->whereNotIn('id', $ids)->limit($chapter_question_quantity)->get();
                        // check found question
                        // if (sizeof($questions)==0 && $c_i == sizeof($request->chapters) - 1) {
                        //     return response()->json('no_data', 422);
                        // }
                        if (sizeof($questions)==0) {
                            $min = floor($question_type['total']/sizeof($request->chapters)-($c_i + 1));
                            $max = ceil($question_type['total']/sizeof($request->chapters)-($c_i + 1));
                            continue 2;
                        }
                        foreach ($questions as $q_i => $question) {
                            $exam_question = new exam_question;
                            $exam_question->question_id = $question->id;
                             $exam_question->exam_question_type_id = $question_type['id'];
                            $exam_question->method = 'generate';
                            $exam_question->set = $i + 1;
                            $exam_question->save();
                            array_push($ids, $question->id);
                        }
                        if (sizeof($questions)<= $chapter_question_quantity) {
                            array_shift($ids);
                        }
                        $remain_total = $remain_total - sizeof($questions);
                    }
                }
            }
            DB::commit();
            return response()->json(['data'=>exam_question_type::whereIn('id', $exam_type_ids)->with('questions')->get()]);
        } catch (Exception $e) {
            DB::rollback();
        } 
    }

    public function question_print(Request $request)
    {
        $exam_question_list = exam_question_list::where('id', $request->id)->first();
        $exam_question_list->full_marks = $request->full_marks;
        $exam_question_list->full_time = $request->full_time;
        $exam_question_list->answer_instruction = $request->answer_instruction;
        $exam_question_list->layout = $request->layout;
        $exam_question_list->language = $request->language;
        $exam_question_list->chapter_text = $request->chapter_text;
        $exam_question_list->exam_type_id = $request->exam_type;
        $exam_question_list->status = 'printable';
        $exam_question_list->save();
        foreach ($request->question_data as $key => $question) {
            $type = exam_question_type::find($question['id']);
            $type->answer_instruction = $question['answer_instruction'];
            $type->title = $question['title'];
            $type->seperation = $question['seperation'];
            $type_data = [];
            if (!$question['relative_type_data']) {
                $question['relative_type_data'] = [];
            }
            foreach ($question['relative_type_data'] as $key => $item) {
                $arr = ['relative_question_type' => $item['relative_question_type'], 'total' => $item['total'], 'answer_instruction'=> $item['answer_instruction']];
                array_push($type_data, $arr);
                // save answer instruction
                $instruction = question_instruction::where('subject_id', $exam_question_list->subject_id)->where('question_type', $exam_question_list->question_type)->where('sub_type', $item['relative_question_type'])->first();
                if (!$instruction) {
                    $instruction = new question_instruction;
                    $instruction->subject_id = $exam_question_list->subject_id;
                    $instruction->question_type = $type->question_type;
                    $instruction->sub_type = $item['relative_question_type'];
                    $instruction->title = $question['title'];
                    $instruction->answer_instruction = $item['answer_instruction'];
                    $instruction->save();
                }
            }
            $type->relative_type_data = $type_data;
            $type->save(); 
            // answer instructions
            if ($type->question_type == 'general') {
                $instruction = question_instruction::where('subject_id', $exam_question_list->subject_id)->where('question_type', $exam_question_list->question_type)->first();
                if (!$instruction) {
                    $instruction = new question_instruction;
                    $instruction->subject_id = $exam_question_list->subject_id;
                    $instruction->question_type = $type->question_type;
                    $instruction->title = $question['title'];
                    $instruction->answer_instruction = $question['answer_instruction'];
                    $instruction->save();
                }
            }
        }
    }

    public function question_print_update(Request $request)
    {
        $question = exam_question_list::find($request->id);
        $question->title = $request->title;
        $question->class_title = $request->class_title;
        $question->page_size = $request->page_size;
        $question->page_height = $request->page_height;
        $question->font_size = $request->font_size;
        $question->line_height = $request->line_height;
        $question->save();
    }
}
