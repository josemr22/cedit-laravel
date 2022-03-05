<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    //
    public function index()
    {
        $courses = Course::orderByDesc('created_at')->get();
        return response()->json($courses);
    }

    public function show(Course $course)
    {
        return response()->json($course);
    }

    public function store(Request $request)
    {
        $course = new Course();
        $data = $request->validate([
            'name' => 'required',
            'days' => 'required',
        ]);
        $course->name = $data['name'];
        $course->days = $data['days'];
        $course->save();

        return response()->json($course);
    }

    public function update(Course $course, Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'days' => 'required',
        ]);
        $course->name = $data['name'];
        $course->days = $data['days'];
        $course->save();

        return response()->json($course);
    }

    public function delete(Course $course)
    {
        $course->delete();

        return response()->json();
    }
}
