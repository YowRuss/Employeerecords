<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\Seminar;
use App\Services\LeaveCreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SeminarController extends Controller
{
    private function requireAuth()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        return null;
    }

    private function isManagement(): bool
    {
        $roleId = Session::get('role_id');

        return $roleId !== null && (int) $roleId !== 1;
    }

    public function store(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'hours' => 'required|numeric|min:0.5',
            'date_attended' => 'required|date',
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $userId = Session::get('user_id');
        $certificatePath = null;

        if ($request->hasFile('certificate')) {
            $certificatePath = $request->file('certificate')->store('seminar_certificates', 'public');
        }

        DB::table('seminars')->insert([
            'user_id' => $userId,
            'title' => $request->title,
            'hours' => $request->hours,
            'date_attended' => $request->date_attended,
            'certificate_path' => $certificatePath,
            'status' => 'PENDING',
            'rate_applied' => 0.00,
            'credits_earned' => 0.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Seminar recorded successfully and is pending approval.');
    }

    public function approve($id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $seminar = Seminar::find($id);

        if (! $seminar || $seminar->status !== 'PENDING') {
            return back()->with('error', 'Seminar not found or already processed.');
        }

        // Defense-in-depth: ensure the submitter is a Teaching employee
        $seminar->load('user.position');
        $submitterCategory = $seminar->user?->position?->category;

        if ($submitterCategory !== PositionCategory::Teaching) {
            return back()->with('error', 'Only seminars submitted by Teaching employees can earn credits.');
        }

        app(LeaveCreditService::class)->processSeminarApproval($seminar);

        return back()->with('success', 'Seminar approved successfully.');
    }

    public function reject($id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }
        if (! $this->isManagement()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $seminar = DB::table('seminars')->where('id', $id)->first();

        if (! $seminar || $seminar->status !== 'PENDING') {
            return back()->with('error', 'Seminar not found or already processed.');
        }

        DB::table('seminars')->where('id', $id)->update([
            'status' => 'REJECTED',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Seminar rejected.');
    }
}
