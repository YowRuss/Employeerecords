<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\LearningArea;
use App\Models\PdsPersonalInfo;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
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
        $education = DB::table('pds_education')
            ->leftJoin('schools', 'pds_education.school_id', '=', 'schools.school_id')
            ->select('pds_education.*', 'schools.school_name')
            ->where('pds_education.user_id', $id)
            ->get();
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
        $category = $request->query('category', '');

        // Self-heal: ensure active employees' employee_type matches their position category
        User::whereNotNull('position_id')
            ->where(function ($q) {
                $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
            })
            ->each(function (User $user) {
                $user->syncEmployeeType();
            });

        // 1. Independent Statistics (Standalone queries for absolute tab counts)
        $baseCountQuery = User::where('role_id', 1);

        $totalActiveCount = (clone $baseCountQuery)->where(function ($q) {
            $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
        })->count();

        $inactiveCount = (clone $baseCountQuery)->where('users.status', 'Inactive')->count();

        $teachingCount = (clone $baseCountQuery)->where(function ($q) {
            $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
        })->where(function ($q) {
            $q->where('users.employee_type', 1)
                ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
        })->count();

        $nonTeachingCount = (clone $baseCountQuery)->where(function ($q) {
            $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
        })->where(function ($q) {
            $q->where(function ($sub) {
                $sub->where('users.employee_type', 0)
                    ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
            })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
        })->count();

        $incompleteCount = (clone $baseCountQuery)->where(function ($q) {
            $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
        })->leftJoin('pds_personal_info', 'users.id', '=', 'pds_personal_info.user_id')
            ->whereNull('pds_personal_info.id')
            ->count('users.id');

        $unassignedCount = (clone $baseCountQuery)->where(function ($q) {
            $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
        })->whereNull('position_id')->count();

        // Gender stats for dashboard widget
        $genderStats = DB::table('users')
            ->join('pds_personal_info', 'users.id', '=', 'pds_personal_info.user_id')
            ->where('users.role_id', 1)
            ->where(function ($q) {
                $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
            })
            ->select('pds_personal_info.sex', DB::raw('count(*) as total'))
            ->groupBy('pds_personal_info.sex')
            ->pluck('total', 'sex');

        // Position Stats for dashboard widget
        $positionStats = collect([
            PositionCategory::Teaching->label() => $teachingCount,
            PositionCategory::NonTeaching->label() => $nonTeachingCount,
        ]);

        // 2. Fetch Paginated Employees
        $query = User::with(['learningArea', 'position'])
            ->leftJoin('pds_personal_info', 'users.id', '=', 'pds_personal_info.user_id')
            ->select(
                'users.*',
                'pds_personal_info.mobile_no',
                'pds_personal_info.email_address',
                'pds_personal_info.status as pds_status',
                'pds_personal_info.id as pds_id',
                'pds_personal_info.sex'
            )
            ->where('users.role_id', 1);

        // Apply Tab Status Logic
        if ($category === 'inactive') {
            $query->where('users.status', 'Inactive');
        } elseif ($category === 'incomplete') {
            $query->whereNull('pds_personal_info.id')
                ->where(function ($q) {
                    $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
                });
        } elseif ($category === 'unassigned') {
            $query->whereNull('position_id')
                ->where(function ($q) {
                    $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
                });
        } else {
            $query->where(function ($q) {
                $q->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
            });
        }

        // Apply search filter
        if ($search !== '') {
            $term = strtolower($search);
            $query->where(function ($q) use ($term) {
                $q->where(DB::raw('LOWER(users.last_name)'), 'like', '%'.$term.'%')
                    ->orWhere(DB::raw('LOWER(users.first_name)'), 'like', '%'.$term.'%')
                    ->orWhere(DB::raw('LOWER(users.username)'), 'like', '%'.$term.'%')
                    ->orWhereHas('position', function ($qPos) use ($term) {
                        $qPos->where(DB::raw('LOWER(position_name)'), 'like', '%'.$term.'%');
                    });
            });
        }

        // Apply Sex / Gender Filter
        $query->when($request->filled('sex'), function ($q) use ($request) {
            $q->where('pds_personal_info.sex', $request->sex);
        });

        // Apply Position Filter
        $query->when($request->filled('position_id'), function ($q) use ($request) {
            $q->where('position_id', $request->position_id);
        });

        // Apply Learning Area Filter (only when on teaching tab)
        $query->when($request->filled('learning_area_id') && $category === 'teaching', function ($q) use ($request) {
            $q->where('learning_area_id', $request->learning_area_id);
        });

        // Apply Tab Category Position Filter
        $query->when($request->filled('category') && in_array($category, ['teaching', 'non-teaching']), function ($q) use ($category) {
            if ($category === 'teaching') {
                $q->where(function ($sub) {
                    $sub->where('users.employee_type', 1)
                        ->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
                });
            } else {
                $q->where(function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('users.employee_type', 0)
                            ->whereDoesntHave('position', fn ($pos) => $pos->where('category', PositionCategory::Teaching->value));
                    })->orWhereHas('position', fn ($pos) => $pos->where('category', PositionCategory::NonTeaching->value));
                });
            }
        });

        $employees = $query->orderBy('users.last_name', 'asc')->paginate(10)->withQueryString();

        $learningAreas = LearningArea::orderBy('name', 'asc')->get();
        $positions = Position::orderBy('position_name', 'asc')->get();
        $filterPositions = Position::orderBy('position_name', 'asc')->get();

        return view('hr.employees.index', compact(
            'employees',
            'totalActiveCount',
            'inactiveCount',
            'teachingCount',
            'nonTeachingCount',
            'unassignedCount',
            'incompleteCount',
            'search',
            'genderStats',
            'positionStats',
            'learningAreas',
            'positions',
            'filterPositions'
        ));
    }

    public function viewProfile($id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $employee = User::with([
            'position',
            'learningArea',
            'serviceCredits' => fn ($query) => $query->orderByDesc('transaction_date')->orderByDesc('id'),
        ])->where('id', $id)->first();

        if (! $employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        $learningAreas = DB::table('learning_areas')->orderBy('name', 'asc')->get();
        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();

        return view('hr.employees.profile', compact('employee', 'learningAreas', 'positions'));
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

    public function updatePosition(Request $request, $id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'position_id' => 'required|exists:positions,id',
            'step_increment' => 'nullable|integer|min:1|max:8',
            'learning_area_id' => 'nullable|exists:learning_areas,id',
        ]);

        $employee = User::find($id);
        if (! $employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $employee->position_id = $request->position_id;
        if ($request->filled('step_increment')) {
            $employee->step_increment = (int) $request->step_increment;
        }
        if ($request->has('learning_area_id')) {
            $employee->learning_area_id = $request->learning_area_id ?: null;
        }
        $employee->save();
        $employee->syncEmployeeType();

        return redirect()->back()->with('success', 'Official position updated successfully.');
    }

    public function promoteEmployee(Request $request)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|string',
            'designation' => 'required|string',
            'status' => 'required|string',
            'salary' => 'required|string',
            'station_place' => 'required|string',
            'branch' => 'nullable|string',
            'leave_without_pay' => 'nullable|string',
            'separation_date' => 'nullable|string',
            'separation_cause' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::findOrFail($request->user_id);
                $position = Position::where('position_name', $request->designation)->first();
                if ($position) {
                    $user->position_id = $position->id;
                    $user->save();
                    $user->syncEmployeeType();
                }

                $effectiveDate = Carbon::parse($request->date_from);
                $endDate = $effectiveDate->copy()->subDay()->toDateString();

                // Close active record
                DB::table('service_records')
                    ->where('user_id', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('date_to')
                            ->orWhere('date_to', 'Present')
                            ->orWhere('date_to', 'PRESENT');
                    })
                    ->update([
                        'date_to' => $endDate,
                        'updated_at' => now(),
                    ]);

                // Create new record
                DB::table('service_records')->insert([
                    'user_id' => $user->id,
                    'designation' => $request->designation,
                    'branch' => $request->branch ?? 'NONE',
                    'date_from' => $request->date_from,
                    'date_to' => strtoupper($request->date_to),
                    'salary' => $request->salary,
                    'station_place' => $request->station_place,
                    'status' => $request->status,
                    'leave_without_pay' => $request->leave_without_pay ?? 'NONE',
                    'separation_date' => $request->separation_date ?? 'NONE',
                    'separation_cause' => $request->separation_cause ?? 'NONE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Promotion failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to promote employee. Please try again.');
        }

        return redirect()->back()->with('success', 'Employee promoted successfully.');
    }

    public function offboardEmployee(Request $request)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'date_from' => 'required|date',
            'date_to' => 'required|string',
            'designation' => 'required|string',
            'status' => 'required|string',
            'salary' => 'required|string',
            'station_place' => 'required|string',
            'branch' => 'nullable|string',
            'leave_without_pay' => 'nullable|string',
            'separation_date' => 'required|date',
            'separation_cause' => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $user = User::findOrFail($request->user_id);
                $user->status = 'Inactive';
                $user->separation_reason = $request->separation_cause;
                $user->separation_date = $request->separation_date;
                $user->save();

                // Close active record
                DB::table('service_records')
                    ->where('user_id', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('date_to')
                            ->orWhere('date_to', 'Present')
                            ->orWhere('date_to', 'PRESENT');
                    })
                    ->update([
                        'date_to' => strtoupper($request->date_to),
                        'separation_date' => $request->separation_date,
                        'separation_cause' => $request->separation_cause,
                        'updated_at' => now(),
                    ]);
            });
        } catch (\Exception $e) {
            \Log::error('Offboarding failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to offboard employee. Please try again.');
        }

        return redirect()->back()->with('success', 'Employee successfully offboarded and moved to Inactive tab.');
    }

    public function reassignEmployee(Request $request, $id)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|string',
            'designation' => 'required|string',
            'status' => 'required|string',
            'salary' => 'required|string',
            'station_place' => 'required|string',
            'branch' => 'required|string',
            'leave_without_pay' => 'nullable|string',
            'separation_date' => 'nullable|string',
            'separation_cause' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $user = User::findOrFail($id);

                $position = Position::where('position_name', $request->designation)->first();
                if ($position) {
                    $user->position_id = $position->id;
                }
                $learningArea = LearningArea::where('name', $request->branch)->first();
                if ($learningArea) {
                    $user->learning_area_id = $learningArea->id;
                }
                $user->save();
                $user->syncEmployeeType();

                $effectiveDate = Carbon::parse($request->date_from);
                $endDate = $effectiveDate->copy()->subDay()->toDateString();

                // Close active record
                DB::table('service_records')
                    ->where('user_id', $user->id)
                    ->where(function ($query) {
                        $query->whereNull('date_to')
                            ->orWhere('date_to', 'Present')
                            ->orWhere('date_to', 'PRESENT');
                    })
                    ->update([
                        'date_to' => $endDate,
                        'updated_at' => now(),
                    ]);

                // Create new record
                DB::table('service_records')->insert([
                    'user_id' => $user->id,
                    'designation' => $request->designation,
                    'branch' => $request->branch,
                    'date_from' => $request->date_from,
                    'date_to' => strtoupper($request->date_to),
                    'salary' => $request->salary,
                    'station_place' => $request->station_place,
                    'status' => $request->status,
                    'leave_without_pay' => $request->leave_without_pay ?? 'NONE',
                    'separation_date' => $request->separation_date ?? 'NONE',
                    'separation_cause' => $request->separation_cause ?? 'NONE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Reassignment failed: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to reassign employee. Please try again.');
        }

        return redirect()->back()->with('success', 'Employee successfully reassigned.');
    }
}
