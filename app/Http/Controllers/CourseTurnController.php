<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTurn;
use Illuminate\Http\Request;

class CourseTurnController extends Controller
{
    public function index(Course $course)
    {
        $courseTurns = CourseTurn::where('course_id', $course->id)->with('turn')->get();
        return response()->json($courseTurns);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'course_id' => 'required',
            'turn_id' => 'required',
            'days' => 'required',
            'start_hour' => 'required',
            'end_hour' => 'required',
        ]);
        $course = Course::find($data['course_id']);

        $course->turns()->attach($data['turn_id'], ['days' => $data['days'], 'start_hour' => $data['start_hour'], 'end_hour' => $data['end_hour']]);

        return $this->index($course);
    }

    public function update(CourseTurn $courseTurn, Request $request)
    {
        $data = $request->validate([
            'turn_id' => 'required',
            'days' => 'required',
            'start_hour' => 'required',
            'end_hour' => 'required',
        ]);

        $courseTurn->turn_id = $data['turn_id'];
        $courseTurn->days = $data['days'];
        $courseTurn->start_hour = $data['start_hour'];
        $courseTurn->end_hour = $data['end_hour'];
        $courseTurn->save();

        $course = Course::find($courseTurn->course_id);

        return $this->index($course);
    }

    public function delete(CourseTurn $courseTurn)
    {
        $courseTurn->delete();

        return response()->json();
    }
}
