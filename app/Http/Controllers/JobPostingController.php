<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class JobPostingController extends Controller
{
    public function index()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $postings = JobPosting::with('position')->orderBy('created_at', 'desc')->get();
        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();

        return view('hr.job_postings.index', compact('postings', 'positions'));
    }

    public function store(Request $request)
    {
        if (Session::get('role_id') != 2) {
            return back();
        }

        JobPosting::create($request->all());

        return back()->with('success', 'Job posting created successfully and is now live!');
    }

    public function update(Request $request, $id)
    {
        if (Session::get('role_id') != 2) {
            return back();
        }

        $posting = JobPosting::findOrFail($id);

        // Handle checkbox logic (if unchecked, it won't be sent in the request)
        $data = $request->all();
        $data['is_active'] = $request->has('is_active') ? 1 : 0;

        $posting->update($data);

        return back()->with('success', 'Job posting updated successfully!');
    }

    public function destroy($id)
    {
        if (Session::get('role_id') != 2) {
            return back();
        }

        JobPosting::findOrFail($id)->delete();

        return back()->with('success', 'Job posting deleted permanently.');
    }
}
