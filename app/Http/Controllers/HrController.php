<?php

namespace App\Http\Controllers;

use App\Models\LearningArea;
use App\Models\PdsPersonalInfo;
use App\Models\Position;
use App\Models\User;
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

        // Get gender statistics
        $genderStats = DB::table('users')
            ->join('pds_personal_info', 'users.id', '=', 'pds_personal_info.user_id')
            ->where('users.role_id', 1)
            ->select('pds_personal_info.sex', DB::raw('count(*) as total'))
            ->groupBy('pds_personal_info.sex')
            ->pluck('total', 'sex');

        // Fetch all employees
        $employees = User::with('learningArea')
            ->leftJoin('pds_personal_info', 'users.id', '=', 'pds_personal_info.user_id')
            ->select(
                'users.*',
                'pds_personal_info.mobile_no',
                'pds_personal_info.email_address',
                'pds_personal_info.status as pds_status',
                'pds_personal_info.id as pds_id',
                'pds_personal_info.sex'
            )
            ->where('users.role_id', 1)
            ->orderBy('users.last_name', 'asc')
            ->get();

        $learningAreas = LearningArea::orderBy('name', 'asc')->get();

        // Fetch all positions and service records to avoid N+1 queries
        $positionsByName = DB::table('positions')->get()->keyBy(function ($item) {
            return strtoupper($item->position_name);
        });
        $positionsById = DB::table('positions')->get()->keyBy('id');
        $allServiceRecords = DB::table('service_records')
            ->orderBy('date_from', 'desc')
            ->get()
            ->groupBy('user_id');

        // Map the correct position and category to each employee based on their Service Record
        foreach ($employees as $emp) {
            $emp->position_name = null;
            $emp->category = null;

            if ($allServiceRecords->has($emp->id)) {
                $latestSr = $allServiceRecords->get($emp->id)->first();
                $lookupName = strtoupper($latestSr->designation);
                $emp->position_name = $latestSr->designation;

                if ($positionsByName->has($lookupName)) {
                    $pos = $positionsByName->get($lookupName);
                    $emp->category = $pos->category;
                    $emp->position_name = $pos->position_name; // Use the nicely cased name from DB
                }
            } elseif ($emp->position_id && $positionsById->has($emp->position_id)) {
                // Fallback to their user account position_id if they have no service records
                $pos = $positionsById->get($emp->position_id);
                $emp->position_name = $pos->position_name;
                $emp->category = $pos->category;
            }
        }

        // Apply search filter in memory
        if ($search !== '') {
            $term = strtolower($search);
            $employees = $employees->filter(function ($emp) use ($term) {
                return str_contains(strtolower($emp->last_name), $term)
                    || str_contains(strtolower($emp->first_name), $term)
                    || str_contains(strtolower($emp->username), $term)
                    || str_contains(strtolower($emp->position_name ?? ''), $term);
            });
        }

        $allEmployees = $employees;
        $teachingStaff = $employees->where('category', 'Teaching');
        $nonTeachingStaff = $employees->where('category', 'Non-Teaching');
        $incompletePds = $employees->whereNull('pds_id');

        $maleEmployees = $teachingStaff->where('sex', 'Male');
        $femaleEmployees = $teachingStaff->where('sex', 'Female');
        $maleCount = $maleEmployees->count();
        $femaleCount = $femaleEmployees->count();

        $nonTeachingMaleEmployees = $nonTeachingStaff->where('sex', 'Male');
        $nonTeachingFemaleEmployees = $nonTeachingStaff->where('sex', 'Female');
        $nonTeachingMaleCount = $nonTeachingMaleEmployees->count();
        $nonTeachingFemaleCount = $nonTeachingFemaleEmployees->count();

        // Get position category statistics
        $positionStats = collect([
            'Teaching' => $teachingStaff->count(),
            'Non-Teaching' => $nonTeachingStaff->count(),
        ]);

        return view('hr.employees.index', compact('allEmployees', 'teachingStaff', 'nonTeachingStaff', 'incompletePds', 'maleEmployees', 'femaleEmployees', 'maleCount', 'femaleCount', 'nonTeachingMaleEmployees', 'nonTeachingFemaleEmployees', 'nonTeachingMaleCount', 'nonTeachingFemaleCount', 'search', 'genderStats', 'positionStats', 'learningAreas'));
    }

    public function viewProfile($id)
    {
        $employee = User::with(['position', 'learningArea'])->where('id', $id)->first();

        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        // FETCH THE ACTUAL SERVICE RECORD POSITION
        $latestServiceRecord = DB::table('service_records')
            ->where('user_id', $id)
            ->orderBy('date_from', 'desc')
            ->first();

        $serviceRecordPosition = null;
        if ($latestServiceRecord) {
            $serviceRecordPosition = Position::where('position_name', $latestServiceRecord->designation)->first();
        }

        // Fallback to the user's position_id if no service record exists
        if (! $serviceRecordPosition && $employee->position) {
            $serviceRecordPosition = $employee->position;
        }

        $learningAreas = DB::table('learning_areas')->orderBy('name', 'asc')->get();

        return view('hr.employees.profile', compact('employee', 'learningAreas', 'serviceRecordPosition'));
    }

    public function updateLearningArea(Request $request, $id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'learning_area_id' => 'required|exists:learning_areas,id',
        ]);

        $employee = User::find($id);
        if (! $employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $employee->learning_area_id = $request->learning_area_id;
        $employee->save();

        return redirect()->back()->with('success', 'Area of Specialization updated successfully.');
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
