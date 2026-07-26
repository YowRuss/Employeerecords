<?php

namespace App\Http\Controllers;

use App\Models\PdsPersonalInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HrController extends Controller
{
    /**
     * Shared guard: only HR (2) or Admin (3) may access HR routes.
     */
    private function requireHrAccess()
    {
        $role_id = Session::get('role_id');
        if (! Session::has('user_id') || ! in_array($role_id, [2, 3])) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        return null;
    }

    public function viewPds($id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $employee = DB::table('users')->where('id', $id)->first();
        if (! $employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $personal_info = PdsPersonalInfo::with(['region', 'province', 'city', 'barangay', 'permRegion', 'permProvince', 'permCity', 'permBarangay', 'country'])->where('user_id', $id)->first();
        $children = DB::table('pds_children')->where('user_id', $id)->get();
        $education = DB::table('pds_education')->where('user_id', $id)->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $id)->orderBy('date_from', 'desc')->get();
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $id)->get();
        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $id)->first();
        $references = DB::table('pds_references')->where('user_id', $id)->get();
        $page4_details = DB::table('pds_page4_details')->where('user_id', $id)->first();

        return view('hr.employees.pds', compact(
            'employee', 'personal_info', 'children', 'education',
            'eligibilities', 'work_experiences', 'voluntary_works',
            'learnings', 'other_info', 'questionnaire', 'references', 'page4_details'
        ));
    }

    public function staffProfiling(Request $request)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $search = trim($request->query('search', ''));

        $latestDesignation = DB::table('service_records')
            ->select('designation')
            ->whereColumn('service_records.user_id', 'users.id')
            ->orderByDesc('date_from')
            ->limit(1);

        $query = DB::table('users')
            ->select('users.*')
            ->addSelect(['position_name' => $latestDesignation])
            ->where('users.role_id', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhereExists(function ($q2) use ($search) {
                        $q2->select(DB::raw(1))
                            ->from('service_records')
                            ->whereColumn('service_records.user_id', 'users.id')
                            ->where('designation', 'like', "%{$search}%");
                    });
            });
        }

        $employees = $query->orderBy('users.last_name', 'asc')->paginate(20)->withQueryString();

        return view('hr.employees.index', compact('employees', 'search'));
    }

    public function viewProfile($id)
    {
        $employee = DB::table('users')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.*', 'positions.position_name')
            ->where('users.id', $id)
            ->first();

        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        return view('hr.employees.profile', compact('employee'));
    }

    public function viewSaln($id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $employee = DB::table('users')->where('id', $id)->first();
        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        $current_year = date('Y');

        // Fetch all SALN Data
        $saln_info = DB::table('saln_info')->where('user_id', $id)->first();
        $children = DB::table('saln_unmarried_children')->where('user_id', $id)->get();
        $real_properties = DB::table('saln_real_properties')->where('user_id', $id)->get();
        $personal_properties = DB::table('saln_personal_properties')->where('user_id', $id)->get();
        $liabilities = DB::table('saln_liabilities')->where('user_id', $id)->get();
        $businesses = DB::table('saln_business_interests')->where('user_id', $id)->get();
        $relatives = DB::table('saln_relatives_gov')->where('user_id', $id)->get();

        // Calculate Net Worth
        $total_real = $real_properties->sum('assessed_value'); // Oh wait, SalnController uses acquisition_cost? Let me check what SalnController uses.
        $total_real = $real_properties->sum('acquisition_cost');
        $total_personal = $personal_properties->sum('acquisition_cost');
        $total_assets = $total_real + $total_personal;
        $total_liabilities = $liabilities->sum('outstanding_balance');
        $net_worth = $total_assets - $total_liabilities;

        $saln = $saln_info; // For compatibility with the basic view check, though we pass saln_info directly

        return view('hr.employees.saln', compact(
            'employee', 'current_year', 'saln', 'saln_info',
            'total_assets', 'total_liabilities', 'net_worth',
            'children', 'real_properties', 'personal_properties',
            'liabilities', 'businesses', 'relatives'
        ));
    }

    public function updateOfficialName(Request $request, $id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
        ]);

        $employee = DB::table('users')->where('id', $id)->first();
        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        // Update the users table
        DB::table('users')->where('id', $id)->update([
            'first_name' => strtoupper($request->first_name),
            'last_name' => strtoupper($request->last_name),
            'middle_name' => strtoupper($request->middle_name),
            'suffix' => strtoupper($request->suffix),
            'updated_at' => now(),
        ]);

        // Update the pds_personal_info table (to keep it synced)
        DB::table('pds_personal_info')->where('user_id', $id)->update([
            'first_name' => strtoupper($request->first_name),
            'last_name' => strtoupper($request->last_name),
            'middle_name' => strtoupper($request->middle_name),
            'name_extension' => strtoupper($request->suffix),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Employee official name has been updated successfully.');
    }
}
