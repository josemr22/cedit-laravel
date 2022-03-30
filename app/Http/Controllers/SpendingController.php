<?php

namespace App\Http\Controllers;

use App\Models\Spending;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SpendingController extends Controller
{
    //
    public function index()
    {
        $spendings = Spending::query()
            ->with('user')
            ->where('date', '>=', date(request('from')))
            ->where('date', '<=', date(request('to')))
            ->orderByDesc('created_at')
            ->get();

        return response()->json($spendings);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required',
            'amount' => 'required',
            'user_id' => 'required',
            'date' => 'required',
        ]);

        $data['date'] = new \Carbon\Carbon($data['date']);

        $spending = Spending::create($data);
        return response()->json($spending);
    }

    public function update(Spending $spending, Request $request)
    {
        $data = $request->validate([
            'description' => 'required',
            'amount' => 'required',
            'user_id' => 'required',
            'date' => 'required',
        ]);

        $data['date'] = new \Carbon\Carbon($data['date']);

        $spending->update($data);
        return response()->json($spending);
    }

    public function delete(Spending $spending)
    {
        $spending->delete();
        return response()->json(true);
    }
}
