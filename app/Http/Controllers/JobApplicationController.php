<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class JobApplicationController extends Controller
{
    // ==========================================
    // PUBLIC ROUTES (For Future Employees)
    // ==========================================
    public function index()
    {
        return redirect('/#open-positions');
    }

    public function showForm(Request $request)
    {
        $selectedPositionId = $request->query('position_id');

        // If they access the page without clicking a specific job, send them back to the welcome page
        if (! $selectedPositionId) {
            return redirect('/careers')->with('error', 'Please select a job opening to apply for.');
        }

        $selectedPosition = DB::table('positions')->where('id', $selectedPositionId)->first();

        return view('careers.index', compact('selectedPosition'));
    }

    public function apply(Request $request)
    {
        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'email' => 'required|email|max:255',
            'contact_number' => 'required|string|max:50',
            'resume' => 'required|mimes:pdf,doc,docx|max:5120', // Max 5MB
        ]);

        $resumeData = null;
        $resumeFilename = null;

        // Convert the uploaded file into BLOB data
        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $resumeFilename = time().'_'.preg_replace('/\s+/', '_', $request->first_name.'_'.$request->last_name).'.'.$file->getClientOriginalExtension();
            $resumeData = file_get_contents($file->getRealPath());
        }

        JobApplication::create([
            'position_id' => $request->position_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'suffix' => $request->suffix,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'cover_letter' => $request->cover_letter,
            'resume_filename' => $resumeFilename,
            'resume_data' => $resumeData,
        ]);

        return back()->with('success', 'Your application has been submitted successfully! Our HR team will contact you soon.');
    }

    // ==========================================
    // HR ROUTES (For HR Officer Role 2)
    // ==========================================
    public function hrIndex()
    {
        if (Session::get('role_id') != 2) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $applications = JobApplication::with('position')->orderBy('created_at', 'desc')->get();

        return view('hr.applications.index', compact('applications'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (Session::get('role_id') != 2) {
            return back();
        }

        $application = JobApplication::findOrFail($id);
        $application->status = $request->status;
        $application->save();

        return back()->with('success', 'Applicant status updated to: '.$request->status);
    }

    public function downloadResume($id)
    {
        // Ensure only HR can download
        if (Session::get('role_id') != 2) {
            abort(403, 'Unauthorized action.');
        }

        $application = JobApplication::findOrFail($id);

        if (! $application->resume_data) {
            return back()->with('error', 'No resume file found for this applicant.');
        }

        // Return the BLOB data as a downloadable file
        return response($application->resume_data)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-length', strlen($application->resume_data))
            ->header('Content-Disposition', 'attachment; filename="'.$application->resume_filename.'"')
            ->header('Content-Transfer-Encoding', 'binary');
    }
}
