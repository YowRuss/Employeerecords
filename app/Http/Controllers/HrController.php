<?php

namespace App\Http\Controllers;

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
        if (!Session::has('user_id') || !in_array($role_id, [2, 3])) {
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
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $personal_info = DB::table('pds_personal_info')->where('user_id', $id)->first();
        $children      = DB::table('pds_children')->where('user_id', $id)->get();
        $education     = DB::table('pds_education')->where('user_id', $id)->get();

        return view('hr.view_pds', compact('employee', 'personal_info', 'children', 'education'));
    }

    public function staffProfiling(Request $request)
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        $search = trim($request->query('search', ''));

        $query = DB::table('users')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.*', 'positions.position_name')
            ->where('users.role_id', 1);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('positions.position_name', 'like', "%{$search}%");
            });
        }

        $employees = $query->orderBy('users.last_name', 'asc')->paginate(20)->withQueryString();

        return view('hr.staff_profiling', compact('employees', 'search'));
    }

    public function viewProfile($id)
    {
        $employee = DB::table('users')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->select('users.*', 'positions.position_name')
            ->where('users.id', $id)
            ->first();

        if (!$employee) {
            return redirect()->route('hr.staff_profiling')->with('error', 'Employee not found.');
        }

        return view('hr.view_employee_profile', compact('employee'));
    }
}
