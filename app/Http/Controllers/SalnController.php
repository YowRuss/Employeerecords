<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SalnController extends Controller
{
    public function index()
    {
        if (!Session::has('user_id') || Session::get('role_id') != 1) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user_id = Session::get('user_id');

        // Fetch all SALN Data
        $saln_info = DB::table('saln_info')->where('user_id', $user_id)->first();
        $children = DB::table('saln_unmarried_children')->where('user_id', $user_id)->get();
        $real_properties = DB::table('saln_real_properties')->where('user_id', $user_id)->get();
        $personal_properties = DB::table('saln_personal_properties')->where('user_id', $user_id)->get();
        $liabilities = DB::table('saln_liabilities')->where('user_id', $user_id)->get();
        $businesses = DB::table('saln_business_interests')->where('user_id', $user_id)->get();
        $relatives = DB::table('saln_relatives_gov')->where('user_id', $user_id)->get();

        // Calculate Net Worth Automatically!
        $total_real = $real_properties->sum('acquisition_cost');
        $total_personal = $personal_properties->sum('acquisition_cost');
        $total_assets = $total_real + $total_personal;
        $total_liabilities = $liabilities->sum('outstanding_balance');
        $net_worth = $total_assets - $total_liabilities;

        // --- 1. GET AUTO-FILL NAME FROM PDS ---
        $pds = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $auto_name = '';
        if ($pds) {
            // Formats as "LASTNAME, FIRSTNAME MIDDLENAME EXTENSION"
            $auto_name = trim("{$pds->last_name}, {$pds->first_name} {$pds->middle_name} {$pds->name_extension}");
            $auto_name = trim($auto_name, " ,"); // Cleans up if some fields are empty
        }

        // --- 2. GET AUTO-FILL POSITION FROM LATEST SERVICE RECORD ---
        $latest_sr = DB::table('service_records')
            ->where('user_id', $user_id)
            ->orderBy('date_from', 'desc')
            ->first();
        $auto_position = $latest_sr ? $latest_sr->designation : '';

        // --- 3. RETURN TO VIEW ---
        return view('employee.saln', compact(
            'saln_info', 'total_assets', 'total_liabilities', 'net_worth', 
            'children', 'real_properties', 'personal_properties', 
            'liabilities', 'businesses', 'relatives', 
            'auto_name', 'auto_position'
        ));
    }

    public function updateInfo(Request $request)
    {
        $user_id = Session::get('user_id');
        $data = $request->except(['_token']);
        $data['updated_at'] = now();

        // Make inputs uppercase for official formatting
        foreach($data as $key => $value) {
            if($key != 'as_of_date' && $key != 'updated_at') {
                $data[$key] = strtoupper($value);
            }
        }

        DB::table('saln_info')->updateOrInsert(['user_id' => $user_id], $data);
        return back()->with('success', 'Basic Information saved!')->with('active_tab', 'assets');
    }

    public function addChild(Request $request)
    {
        DB::table('saln_unmarried_children')->insert([
            'user_id' => Session::get('user_id'),
            'name' => strtoupper($request->name),
            'date_of_birth' => $request->date_of_birth,
            'age' => \Carbon\Carbon::parse($request->date_of_birth)->age,
            'created_at' => now()
        ]);
        return back()->with('success', 'Child added successfully!')->with('active_tab', 'info');
    }

    public function addRealProperty(Request $request)
    {
        DB::table('saln_real_properties')->insert([
            'user_id' => Session::get('user_id'),
            'description' => strtoupper($request->description),
            'kind' => strtoupper($request->kind),
            'exact_location' => strtoupper($request->exact_location),
            'assessed_value' => $request->assessed_value,
            'fair_market_value' => $request->fair_market_value,
            'acquisition_year' => $request->acquisition_year,
            'acquisition_mode' => strtoupper($request->acquisition_mode),
            'acquisition_cost' => $request->acquisition_cost,
            'created_at' => now()
        ]);
        return back()->with('success', 'Real Property added!')->with('active_tab', 'assets');
    }

    public function addPersonalProperty(Request $request)
    {
        DB::table('saln_personal_properties')->insert([
            'user_id' => Session::get('user_id'),
            'description' => strtoupper($request->description),
            'year_acquired' => strtoupper($request->year_acquired),
            'acquisition_cost' => $request->acquisition_cost,
            'created_at' => now()
        ]);
        return back()->with('success', 'Personal Property added!')->with('active_tab', 'assets');
    }

    public function addLiability(Request $request)
    {
        DB::table('saln_liabilities')->insert([
            'user_id' => Session::get('user_id'),
            'nature' => strtoupper($request->nature),
            'name_of_creditors' => strtoupper($request->name_of_creditors),
            'outstanding_balance' => $request->outstanding_balance,
            'created_at' => now()
        ]);
        return back()->with('success', 'Liability added!')->with('active_tab', 'liabilities');
    }

    public function addBusiness(Request $request)
    {
        DB::table('saln_business_interests')->insert([
            'user_id' => Session::get('user_id'),
            'business_name' => strtoupper($request->business_name),
            'business_address' => strtoupper($request->business_address),
            'nature_of_business' => strtoupper($request->nature_of_business),
            'date_of_acquisition' => strtoupper($request->date_of_acquisition),
            'created_at' => now()
        ]);
        return back()->with('success', 'Business Interest added!')->with('active_tab', 'business');
    }

    public function addRelative(Request $request)
    {
        DB::table('saln_relatives_gov')->insert([
            'user_id' => Session::get('user_id'),
            'relative_name' => strtoupper($request->relative_name),
            'relationship' => strtoupper($request->relationship),
            'position' => strtoupper($request->position),
            'agency_address' => strtoupper($request->agency_address),
            'created_at' => now()
        ]);
        return back()->with('success', 'Relative record added!')->with('active_tab', 'relatives');
    }

    public function deleteRecord($table, $id)
    {
        $allowedTables = [
            'saln_unmarried_children', 'saln_real_properties', 'saln_personal_properties', 
            'saln_liabilities', 'saln_business_interests', 'saln_relatives_gov'
        ];

        if (in_array($table, $allowedTables)) {
            DB::table($table)->where('id', $id)->where('user_id', Session::get('user_id'))->delete();
            return back()->with('success', 'Record deleted successfully!');
        }

        return back()->with('error', 'Invalid action.');
    }
}