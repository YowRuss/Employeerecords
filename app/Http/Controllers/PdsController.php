<?php

namespace App\Http\Controllers;

use App\Models\PdsFather;
use App\Models\PdsMother;
use App\Models\PdsPersonalInfo;
use App\Models\PdsSpouse;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use ZipArchive;

class PdsController extends Controller
{
    private const ROLE_EMPLOYEE = 1;

    /** Tables the universal delete endpoint may touch, mapped to the tab to return to. */
    private const DELETABLE = [
        'pds_children' => 'family',
        'pds_education' => 'education',
        'pds_eligibility' => 'eligibility',
        'pds_work_experience' => 'work',
        'pds_voluntary_work' => 'voluntary',
        'pds_learning_development' => 'learning',
        'pds_other_information' => 'other',
        'pds_references' => 'page4',
    ];

    /** Template filenames to try, in order, under storage/app/templates. */
    private const TEMPLATE_CANDIDATES = [
        'PDS_CS-Form-212-Revised-2025_BLANK.xlsx',
        'ANNEX-H-1-CS-Form-No.-212-Revised-2025-Personal-Data-Sheet.xlsx',
        'Personal-Data-Sheet-CS-Form-No_-212-Revised-2025.xlsx',
    ];

    /**
     * Sheet C1 prints the five education levels in fixed rows 54-58, with the level
     * name already in column B. Records are matched to a row by keyword.
     */
    private const EDUCATION_LEVEL_ROWS = [
        'ELEM' => 54,
        'SECOND' => 55,
        'HIGH' => 55,
        'VOCATIONAL' => 56,
        'TRADE' => 56,
        'COLLEGE' => 57,
        'BACHELOR' => 57,
        'TERTIARY' => 57,
        'GRADUATE' => 58,
        'MASTER' => 58,
        'DOCTOR' => 58,
    ];

    /**
     * Sheet C4, items 34-40: "If YES, give details" cell only.
     * YES/NO is ticked on the form-control checkboxes, not written as cell text.
     */
    private const QUESTIONNAIRE_DETAIL_CELLS = [
        'q34_b' => 'G10',
        'q35_a' => 'G14',
        'q35_b' => 'G19',
        'q36' => 'G24',
        'q37' => 'G28',
        'q38_a' => 'G32',
        'q38_b' => 'G35',
        'q39' => 'G38',
        'q40_a' => 'G44',
        'q40_b' => 'G46',
        'q40_c' => 'G48',
    ];

    /** C4 YES/NO checkbox Excel row (1-based) => questionnaire field. */
    private const C4_QUESTION_BY_ROW = [
        5 => 'q34_a',
        7 => 'q34_b',
        8 => 'q34_b',
        9 => 'q34_b',
        13 => 'q35_a',
        17 => 'q35_b',
        18 => 'q35_b',
        23 => 'q36',
        27 => 'q37',
        31 => 'q38_a',
        33 => 'q38_b',
        34 => 'q38_b',
        37 => 'q39',
        43 => 'q40_a',
        45 => 'q40_b',
        47 => 'q40_c',
    ];

    private const CIVIL_STATUS_BOXES = ['Single', 'Married', 'Widowed', 'Separated'];

    private const CITIZENSHIP_COUNTRY_CELL = 'L16';

    /** 23. NAME of CHILDREN occupies rows 37-48 on sheet C1. */
    private const CHILD_ROW_START = 37;

    private const CHILD_ROW_END = 48;

    // =========================================================
    // GUARDS & HELPERS
    // =========================================================

    /**
     * FIX: only editPds() checked the session. Every write method, the document
     * download and the Excel export read Session::get('user_id') straight into a
     * query — an expired session inserted rows with user_id = NULL, or updated
     * nothing at all while still reporting "saved!".
     */
    private function requireAuth()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login')->with('error', 'Your session has expired. Please log in again.');
        }

        return null;
    }

    private function userId()
    {
        return Session::get('user_id');
    }

    /** mb_ variant: strtoupper() leaves ñ untouched, so PEÑA became PEñA. */
    private function upper($value): string
    {
        return is_string($value) || is_numeric($value)
            ? mb_strtoupper(trim((string) $value), 'UTF-8')
            : '';
    }

    private function na($value): string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? 'N/A' : $value;
    }

    /**
     * CS Form 212 (Revised 2025) labels every date field "(dd/mm/yyyy)", so the raw
     * yyyy-mm-dd out of the column has to be reordered. Non-dates such as the literal
     * "PRESENT" in a work-experience end date pass through untouched.
     */
    private function dmy($value): string
    {
        if (empty($value)) {
            return 'N/A';
        }

        if (! preg_match('/\d{4}|\d{1,2}[\/-]\d{1,2}/', (string) $value)) {
            return (string) $value;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    /** Like dmy() but returns '' rather than 'N/A' for an empty value. */
    private function dmyOrBlank($value): string
    {
        return empty($value) ? '' : $this->dmy($value);
    }

    /**
     * Resolve a location foreign key to its readable name.
     *
     * FIX: the export wrote res_barangay / res_city / res_province straight into the
     * sheet, but those columns hold reference IDs — the printed PDS showed numbers
     * where the place names belong. Falls back to the raw value if nothing matches,
     * so nothing is ever lost.
     */
    private function refName(string $table, string $nameColumn, string $ownCodeColumn, $value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $name = DB::table($table)->where('id', $value)->value($nameColumn);
            if ($name) {
                return $name;
            }

            // Some frontends post the PSGC code instead of the row id.
            if (Schema::hasColumn($table, $ownCodeColumn)) {
                $name = DB::table($table)->where($ownCodeColumn, $value)->value($nameColumn);
                if ($name) {
                    return $name;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("PDS: could not resolve {$table}.{$nameColumn} for '{$value}' — ".$e->getMessage());
        }

        return (string) $value;
    }

    private function barangayName($v): string
    {
        return $this->refName('ref_barangays', 'brgy_name', 'brgy_code', $v);
    }

    private function cityName($v): string
    {
        return $this->refName('ref_cities', 'city_name', 'city_code', $v);
    }

    private function provinceName($v): string
    {
        return $this->refName('ref_provinces', 'province_name', 'province_code', $v);
    }

    /**
     * Keep only real columns of $table from the request body.
     *
     * FIX: updatePersonalInfo/updateQuestionnaire/updatePage4Details each wrote
     * $request->except(['_token']) directly to the table. Any posted field became a
     * column write — including user_id, which let a crafted request retarget another
     * employee's row — and any input name that wasn't a column threw a 500.
     */
    private function columnsOnly(Request $request, string $table, array $extraExcept = []): array
    {
        $columns = Schema::getColumnListing($table);
        $blocked = array_merge(['id', 'user_id', 'created_at', 'updated_at'], $extraExcept);
        $data = [];

        foreach ($request->except(array_merge(['_token', '_method'], $extraExcept)) as $key => $value) {
            if (! in_array($key, $columns, true) || in_array($key, $blocked, true)) {
                continue;
            }
            // strtoupper()/string casts on an array input (name="foo[]") is a TypeError in PHP 8.
            $data[$key] = is_array($value) ? json_encode($value) : $value;
        }

        return $data;
    }

    // =========================================================
    // 1. VIEW PDS (Loads all tabs)
    // =========================================================
    public function editPds()
    {
        if (! Session::has('user_id') || Session::get('role_id') != self::ROLE_EMPLOYEE) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user_id = $this->userId();

        $personal_info = PdsPersonalInfo::with('country')->where('user_id', $user_id)->first();
        $spouse = PdsSpouse::where('user_id', $user_id)->first();
        $father = PdsFather::where('user_id', $user_id)->first();
        $mother = PdsMother::where('user_id', $user_id)->first();
        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')
            ->leftJoin('schools', 'pds_education.school_id', '=', 'schools.school_id')
            ->select('pds_education.*', 'schools.school_name')
            ->where('pds_education.user_id', $user_id)
            ->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();

        // PAGE 3 DATA
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();

        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $user_id)->first();
        $references = DB::table('pds_references')->where('user_id', $user_id)->get();
        $page4_details = DB::table('pds_page4_details')->where('user_id', $user_id)->first();

        $countries = DB::table('countries')->orderBy('name', 'asc')->get(['id', 'name']);
        $regions = DB::table('ref_regions')->orderBy('region_name', 'asc')->get(['id', 'region_name', 'region_code']);

        return view('employee.pds', compact(
            'countries',
            'regions',
            'personal_info',
            'spouse',
            'father',
            'mother',
            'children',
            'education',
            'eligibilities',
            'work_experiences',
            'voluntary_works',
            'learnings',
            'other_info',
            'questionnaire',
            'references',
            'page4_details'
        ));
    }

    /**
     * NOTE: this is a near-duplicate of editPds() pointing at a different view.
     * FIX: it read `pds_page_4_details` (extra underscore) — a table that doesn't
     * exist anywhere else in this controller, so it threw on every call. If nothing
     * routes here any more, delete the method rather than maintaining two copies.
     */
    public function edit()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = $this->userId();

        $personal_info = PdsPersonalInfo::with('country')->where('user_id', $user_id)->first();
        $spouse = PdsSpouse::where('user_id', $user_id)->first();
        $father = PdsFather::where('user_id', $user_id)->first();
        $mother = PdsMother::where('user_id', $user_id)->first();
        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')->where('user_id', $user_id)->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();
        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $user_id)->first();
        $references = DB::table('pds_references')->where('user_id', $user_id)->get();
        $page4_details = DB::table('pds_page4_details')->where('user_id', $user_id)->first();

        $countries = DB::table('countries')->orderBy('name', 'asc')->get();

        return view('employee.pds.pds-view', compact(
            'countries',
            'personal_info',
            'spouse',
            'father',
            'mother',
            'children',
            'education',
            'eligibilities',
            'work_experiences',
            'voluntary_works',
            'learnings',
            'other_info',
            'questionnaire',
            'references',
            'page4_details'
        ));
    }

    // =========================================================
    // 2. SAVE SECTION A & B: Personal & Family
    // =========================================================
    public function updatePersonalInfo(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'sex' => 'required|in:0,1',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'email_address' => 'nullable|email|max:255',
        ]);

        $user_id = $this->userId();

        // Capture location codes from frontend dropdowns
        $resRegion = $request->input('res_region_code') ?? $request->input('res_region');
        $resProvince = $request->input('res_province_code') ?? $request->input('res_province');
        $resCity = $request->input('res_city_code') ?? $request->input('res_city');
        $resBarangay = $request->input('res_barangay_code') ?? $request->input('res_barangay');

        $permRegion = $request->input('perm_region_code') ?? $request->input('perm_region');
        $permProvince = $request->input('perm_province_code') ?? $request->input('perm_province');
        $permCity = $request->input('perm_city_code') ?? $request->input('perm_city');
        $permBarangay = $request->input('perm_barangay_code') ?? $request->input('perm_barangay');

        $resZip = $request->input('res_zipcode') ?? $request->input('res_zip');

        // Whitelisted against the real column list; the raw '_code' dropdown fields and
        // the signature blob are never accepted from this form.
        $data = $this->columnsOnly($request, 'pds_personal_info', [
            'res_region_code', 'res_province_code', 'res_city_code', 'res_barangay_code',
            'perm_region_code', 'perm_province_code', 'perm_city_code', 'perm_barangay_code',
            'e_signature',
        ]);

        // Explicitly map location codes and zipcode into data array.
        // Each write is guarded by hasColumn so a schema that only has one of
        // res_zip / res_zipcode can't blow up the request.
        $mapped = [
            'res_region' => $resRegion, 'res_province' => $resProvince,
            'res_city' => $resCity, 'res_barangay' => $resBarangay,
            'perm_region' => $permRegion, 'perm_province' => $permProvince,
            'perm_city' => $permCity, 'perm_barangay' => $permBarangay,
            'res_zip' => $resZip, 'res_zipcode' => $resZip,
        ];

        foreach ($mapped as $column => $value) {
            if ($value !== null && Schema::hasColumn('pds_personal_info', $column)) {
                $data[$column] = $value;
            }
        }

        if (empty($data)) {
            return back()->with('error', 'Nothing to save.')->with('active_tab', 'personal');
        }

        $data['updated_at'] = now();

        try {
            $existing = DB::table('pds_personal_info')->where('user_id', $user_id)->exists();

            if ($existing) {
                DB::table('pds_personal_info')->where('user_id', $user_id)->update($data);
            } else {
                $data['user_id'] = $user_id;
                $data['created_at'] = now();
                DB::table('pds_personal_info')->insert($data);
            }
        } catch (\Throwable $e) {
            Log::error('PDS personal info save failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Could not save your information. Please try again.')->with('active_tab', 'personal');
        }

        return back()->with('success', 'Personal Information saved!')->with('active_tab', 'family');
    }

    public function addChild(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        // FIX: an empty child_dob was inserted as '' — rejected outright by MySQL in
        // strict mode, stored as 0000-00-00 otherwise.
        $validated = $request->validate([
            'child_name' => ['required', 'string', 'max:255'],
            'child_dob' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        DB::table('pds_children')->insert([
            'user_id' => $this->userId(),
            'child_name' => $this->upper($validated['child_name']),
            'date_of_birth' => $validated['child_dob'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Child added successfully!')->with('active_tab', 'family');
    }

    public function updateChild(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $validated = $request->validate([
            'child_name' => ['required', 'string', 'max:255'],
            'child_dob' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        DB::table('pds_children')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'child_name' => $this->upper($validated['child_name']),
                'date_of_birth' => $validated['child_dob'] ?? null,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Child updated successfully!')->with('active_tab', 'family');
    }

    public function updateFamilyBackground(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = $this->userId();

        // Spouse
        PdsSpouse::updateOrCreate(
            ['user_id' => $user_id],
            [
                'surname' => $this->upper($request->spouse_surname),
                'first_name' => $this->upper($request->spouse_first_name),
                'middle_name' => $this->upper($request->spouse_middle_name),
                'name_extension' => $this->upper($request->spouse_name_extension),
                'occupation' => $this->upper($request->spouse_occupation),
                'employer_business_name' => $this->upper($request->spouse_employer),
                'business_address' => $this->upper($request->spouse_business_address),
                'telephone_number' => $request->spouse_telephone,
            ]
        );

        // Father
        PdsFather::updateOrCreate(
            ['user_id' => $user_id],
            [
                'surname' => $this->upper($request->father_surname),
                'first_name' => $this->upper($request->father_first_name),
                'middle_name' => $this->upper($request->father_middle_name),
                'name_extension' => $this->upper($request->father_name_extension),
            ]
        );

        // Mother
        PdsMother::updateOrCreate(
            ['user_id' => $user_id],
            [
                'maiden_surname' => $this->upper($request->mother_maiden_surname),
                'first_name' => $this->upper($request->mother_first_name),
                'middle_name' => $this->upper($request->mother_middle_name),
            ]
        );

        return back()->with('success', 'Spouse and Parents information saved!')->with('active_tab', 'family');
    }

    // =========================================================
    // 3. SAVE SECTION C: Education & Signature
    // =========================================================
    public function addEducation(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'level' => ['required', 'string', 'max:100'],
            'school_id' => ['required', 'integer', 'exists:schools,school_id'],
            'degree' => ['nullable', 'string', 'max:255'],
            'year_graduated' => ['nullable', 'string', 'max:20'],
        ]);

        DB::table('pds_education')->insert([
            'user_id' => $this->userId(),
            'level' => $request->level,
            'school_id' => $request->school_id,
            'degree_course' => $this->upper($request->degree),
            'period_from' => $request->period_from,
            'period_to' => $request->period_to,
            'year_graduated' => $request->year_graduated,
            'highest_level_earned' => $this->upper($request->highest_level),
            'scholarship_honors' => $this->upper($request->honors),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Education record added!')->with('active_tab', 'education');
    }

    public function updateEducation(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,school_id'],
            'degree' => ['nullable', 'string', 'max:255'],
            'year_graduated' => ['nullable', 'string', 'max:20'],
        ]);

        DB::table('pds_education')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'school_id' => $request->school_id,
                'degree_course' => $this->upper($request->degree),
                'period_from' => $request->period_from,
                'period_to' => $request->period_to,
                'year_graduated' => $request->year_graduated,
                'highest_level_earned' => $this->upper($request->highest_level),
                'scholarship_honors' => $this->upper($request->honors),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Education record updated!')->with('active_tab', 'education');
    }

    public function saveSignature(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        // FIX: e_signature was 'required', so an employee could never correct just the
        // date without re-uploading the image.
        $request->validate([
            'e_signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'signature_date' => 'required|date',
            'active_tab' => 'nullable|string',
        ]);

        $user_id = $this->userId();
        $data = ['signature_date' => $request->signature_date, 'updated_at' => now()];

        if ($request->hasFile('e_signature')) {
            $data['e_signature'] = file_get_contents($request->file('e_signature')->getRealPath());
        }

        /**
         * FIX: this was a bare update(). If the employee saved a signature before ever
         * saving the personal info tab, no row existed, the update matched nothing, and
         * the page still said "saved successfully".
         */
        DB::table('pds_personal_info')->updateOrInsert(
            ['user_id' => $user_id],
            $data + ['created_at' => now()]
        );

        $tab = $request->active_tab ?? 'education';

        return back()->with('success', 'E-Signature (BLOB) and Date saved successfully!')->with('active_tab', $tab);
    }

    // =========================================================
    // 4. SAVE SECTION IV & V: Eligibility & Work
    // =========================================================
    public function addEligibility(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'eligibility_name' => ['required', 'string', 'max:255'],
            // varchar(100) columns, and the form allows free text such as "N/A"
            'exam_date' => ['nullable', 'string', 'max:100'],
            'license_validity' => ['nullable', 'string', 'max:100'],
        ]);

        DB::table('pds_eligibility')->insert([
            'user_id' => $this->userId(),
            'eligibility_name' => $this->upper($request->eligibility_name),
            'rating' => $request->rating,
            'exam_date' => $request->exam_date,
            'exam_place' => $this->upper($request->exam_place),
            'license_number' => $this->upper($request->license_number),
            'license_validity' => $request->license_validity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Eligibility record added successfully!')->with('active_tab', 'eligibility');
    }

    public function updateEligibility(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'eligibility_name' => ['required', 'string', 'max:255'],
            // varchar(100) columns, and the form allows free text such as "N/A"
            'exam_date' => ['nullable', 'string', 'max:100'],
            'license_validity' => ['nullable', 'string', 'max:100'],
        ]);

        DB::table('pds_eligibility')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'eligibility_name' => $this->upper($request->eligibility_name),
                'rating' => $request->rating,
                'exam_date' => $request->exam_date,
                'exam_place' => $this->upper($request->exam_place),
                'license_number' => $this->upper($request->license_number),
                'license_validity' => $request->license_validity,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Eligibility record updated successfully!')->with('active_tab', 'eligibility');
    }

    public function addWorkExperience(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'date_from' => ['required', 'date'],
            'position_title' => ['required', 'string', 'max:255'],
            'agency_company' => ['required', 'string', 'max:255'],
        ]);

        DB::table('pds_work_experience')->insert([
            'user_id' => $this->userId(),
            'date_from' => $request->date_from,
            'date_to' => $this->upper($request->date_to), // "PRESENT" is a valid value here
            'position_title' => $this->upper($request->position_title),
            'agency_company' => $this->upper($request->agency_company),
            'status_appointment' => $this->upper($request->status_appointment),
            'govt_service' => $request->govt_service,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Work experience record added successfully!')->with('active_tab', 'work');
    }

    public function updateWorkExperience(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'date_from' => ['required', 'date'],
            'position_title' => ['required', 'string', 'max:255'],
            'agency_company' => ['required', 'string', 'max:255'],
        ]);

        DB::table('pds_work_experience')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'date_from' => $request->date_from,
                'date_to' => $this->upper($request->date_to),
                'position_title' => $this->upper($request->position_title),
                'agency_company' => $this->upper($request->agency_company),
                'status_appointment' => $this->upper($request->status_appointment),
                'govt_service' => $request->govt_service,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Work experience record updated successfully!')->with('active_tab', 'work');
    }

    // =========================================================
    // 5. SAVE SECTION VI, VII, VIII: Page 3 Info
    // =========================================================
    public function addVoluntaryWork(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'number_of_hours' => ['nullable', 'string', 'max:50'], // varchar(50) column
        ]);

        DB::table('pds_voluntary_work')->insert([
            'user_id' => $this->userId(),
            'organization_name' => $this->upper($request->organization_name),
            'date_from' => $request->date_from,
            'date_to' => $this->upper($request->date_to),
            'number_of_hours' => $request->number_of_hours,
            'position_nature_of_work' => $this->upper($request->position_nature_of_work),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Voluntary work added!')->with('active_tab', 'voluntary');
    }

    public function updateVoluntaryWork(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'number_of_hours' => ['nullable', 'string', 'max:50'], // varchar(50) column
        ]);

        DB::table('pds_voluntary_work')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'organization_name' => $this->upper($request->organization_name),
                'date_from' => $request->date_from,
                'date_to' => $this->upper($request->date_to),
                'number_of_hours' => $request->number_of_hours,
                'position_nature_of_work' => $this->upper($request->position_nature_of_work),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Voluntary work updated!')->with('active_tab', 'voluntary');
    }

    public function addLearning(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        // FIX: proofs were accepted with no type or size check — any file of any size
        // went straight into a LONGBLOB.
        $request->validate([
            'training_title' => ['required', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'number_of_hours' => ['nullable', 'string', 'max:50'], // varchar(50) column
            'proof_of_completion' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'proof_of_invitation' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
        ]);

        $proofCompletion = $request->hasFile('proof_of_completion')
            ? file_get_contents($request->file('proof_of_completion')->getRealPath())
            : null;

        $proofInvitation = $request->hasFile('proof_of_invitation')
            ? file_get_contents($request->file('proof_of_invitation')->getRealPath())
            : null;

        DB::table('pds_learning_development')->insert([
            'user_id' => $this->userId(),
            'training_title' => $this->upper($request->training_title),
            'date_from' => $request->date_from,
            'date_to' => $this->upper($request->date_to),
            'number_of_hours' => $request->number_of_hours,
            'ld_type' => $this->upper($request->ld_type),
            'sponsored_by' => $this->upper($request->sponsored_by),
            'proof_of_completion' => $proofCompletion, // LONGBLOB
            'proof_of_invitation' => $proofInvitation, // LONGBLOB
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Learning & Development record added!')->with('active_tab', 'learning');
    }

    public function updateLearning(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'training_title' => ['required', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'number_of_hours' => ['nullable', 'string', 'max:50'], // varchar(50) column
            'proof_of_completion' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
            'proof_of_invitation' => ['nullable', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
        ]);

        $data = [
            'training_title' => $this->upper($request->training_title),
            'date_from' => $request->date_from,
            'date_to' => $this->upper($request->date_to),
            'number_of_hours' => $request->number_of_hours,
            'ld_type' => $this->upper($request->ld_type),
            'sponsored_by' => $this->upper($request->sponsored_by),
            'updated_at' => now(),
        ];

        if ($request->hasFile('proof_of_completion')) {
            $data['proof_of_completion'] = file_get_contents($request->file('proof_of_completion')->getRealPath());
        }
        if ($request->hasFile('proof_of_invitation')) {
            $data['proof_of_invitation'] = file_get_contents($request->file('proof_of_invitation')->getRealPath());
        }

        DB::table('pds_learning_development')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($data);

        return back()->with('success', 'Learning & Development record updated!')->with('active_tab', 'learning');
    }

    public function addOtherInfo(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $validated = $request->validate([
            'info_type' => ['required', 'in:skill,recognition,membership'],
            'details' => ['required', 'string', 'max:255'],
        ]);

        DB::table('pds_other_information')->insert([
            'user_id' => $this->userId(),
            'info_type' => $validated['info_type'], // 'skill', 'recognition', or 'membership'
            'details' => $this->upper($validated['details']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Information added!')->with('active_tab', 'other');
    }

    public function updateOtherInfo(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $validated = $request->validate([
            'info_type' => ['required', 'in:skill,recognition,membership'],
            'details' => ['required', 'string', 'max:255'],
        ]);

        DB::table('pds_other_information')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'info_type' => $validated['info_type'],
                'details' => $this->upper($validated['details']),
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Information updated!')->with('active_tab', 'other');
    }

    public function downloadDocument($id, $column)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! in_array($column, ['proof_of_completion', 'proof_of_invitation'], true)) {
            abort(404);
        }

        /**
         * FIX (serious): this looked the record up by id ALONE. Any logged-in employee
         * could walk the ids and pull down every other employee's uploaded training
         * certificates and invitations. Scoped to the owner now.
         */
        $record = DB::table('pds_learning_development')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->first();

        if (! $record || empty($record->$column)) {
            abort(404);
        }

        // Determine mime type based on magic bytes (PDF vs Image)
        $blob = $record->$column;
        $mimeType = 'application/octet-stream';
        $extension = 'bin';

        if (str_starts_with($blob, '%PDF')) {
            $mimeType = 'application/pdf';
            $extension = 'pdf';
        } elseif (str_starts_with($blob, "\xFF\xD8\xFF")) {
            $mimeType = 'image/jpeg';
            $extension = 'jpg';
        } elseif (str_starts_with($blob, "\x89PNG\x0D\x0A\x1A\x0A")) {
            $mimeType = 'image/png';
            $extension = 'png';
        }

        return response($blob)
            ->header('Content-Type', $mimeType)
            ->header('Content-Length', strlen($blob))
            // FIX: the filename had no extension, so browsers saved unusable files.
            ->header('Content-Disposition', 'inline; filename="'.$column.'_'.$id.'.'.$extension.'"');
    }

    // =========================================================
    // 6. SAVE SECTION IX: Page 4 (Questionnaire & Final Details)
    // =========================================================
    public function updateQuestionnaire(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $data = $this->columnsOnly($request, 'pds_questionnaire');

        if (empty($data)) {
            return back()->with('error', 'Nothing to save.')->with('active_tab', 'page4');
        }

        $data['updated_at'] = now();

        DB::table('pds_questionnaire')->updateOrInsert(['user_id' => $this->userId()], $data);

        return back()->with('success', 'Questionnaire saved!')->with('active_tab', 'page4');
    }

    public function addReference(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_no' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('pds_references')->insert([
            'user_id' => $this->userId(),
            'name' => $this->upper($request->name),
            'address' => $this->upper($request->address),
            'contact_no' => $request->contact_no,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Reference added!')->with('active_tab', 'page4');
    }

    public function updateReference(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'contact_no' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table('pds_references')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'name' => $this->upper($request->name),
                'address' => $this->upper($request->address),
                'contact_no' => $request->contact_no,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Reference updated!')->with('active_tab', 'page4');
    }

    public function updatePage4Details(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate([
            'passport_photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'right_thumbmark' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
        ]);

        $data = $this->columnsOnly($request, 'pds_page4_details', ['passport_photo', 'right_thumbmark']);

        // Process Passport Photo (LONGBLOB)
        if ($request->hasFile('passport_photo')) {
            $data['passport_photo'] = file_get_contents($request->file('passport_photo')->getRealPath());
        }

        // Process Thumbmark (LONGBLOB)
        if ($request->hasFile('right_thumbmark')) {
            $data['right_thumbmark'] = file_get_contents($request->file('right_thumbmark')->getRealPath());
        }

        if (empty($data)) {
            return back()->with('error', 'Nothing to save.')->with('active_tab', 'page4');
        }

        $data['updated_at'] = now();

        DB::table('pds_page4_details')->updateOrInsert(['user_id' => $this->userId()], $data);

        return back()->with('success', 'Gov ID and Images saved!')->with('active_tab', 'page4');
    }

    // =========================================================
    // 7. UNIVERSAL DELETE FUNCTION
    // =========================================================
    public function deleteRecord($table, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! array_key_exists($table, self::DELETABLE)) {
            return back()->with('error', 'Invalid table reference.');
        }

        DB::table($table)->where('id', $id)->where('user_id', $this->userId())->delete();

        return back()->with('success', 'Record deleted successfully!')
            ->with('active_tab', self::DELETABLE[$table]);
    }

    // =========================================================
    // 8. AUTO-FILL AND PRINT EXCEL PDS
    // =========================================================
    public function printPds()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = $this->userId();

        // FIX: loaded via the model so ->country resolves. The old code used
        // DB::table(), which returns a plain stdClass — $personal_info->country was
        // always null, so "Dual Citizenship - " printed with no country after it.
        $personal_info = PdsPersonalInfo::with('country')->where('user_id', $user_id)->first();

        if (! $personal_info || empty($personal_info->last_name)) {
            return back()->with('error', 'Please complete your profile information before printing.');
        }

        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')
            ->leftJoin('schools', 'pds_education.school_id', '=', 'schools.school_id')
            ->select('pds_education.*', 'schools.school_name')
            ->where('pds_education.user_id', $user_id)
            ->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();
        $references = DB::table('pds_references')->where('user_id', $user_id)->get();
        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $user_id)->first();
        $page4_details = DB::table('pds_page4_details')->where('user_id', $user_id)->first();

        $templatePath = $this->pdsTemplatePath();

        if (! $templatePath) {
            return back()->with('error', 'PDS template not found in storage/app/templates.');
        }

        try {
            $spreadsheet = IOFactory::load($templatePath);

            $c1 = $this->sheet($spreadsheet, 'C1', 0);
            $c2 = $this->sheet($spreadsheet, 'C2', 1);
            $c3 = $this->sheet($spreadsheet, 'C3', 2);
            $c4 = $this->sheet($spreadsheet, 'C4', 3);

            $text = function ($sheet, string $cell, $value) {
                if ($sheet) {
                    $sheet->setCellValueExplicit($cell, $this->na($value), DataType::TYPE_STRING);
                }
            };

            /** Writes only when there is something to write — no "N/A" filler. */
            $write = function ($sheet, string $cell, $value) {
                $value = trim((string) $value);
                if ($sheet && $value !== '') {
                    $sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING);
                }
            };

            /**
             * The form stores some labels and their answers in one cell, e.g.
             * "NAME EXTENSION (JR., SR)        N/A" or "If YES, give details: ______".
             * Writing the bare value would wipe the label, so cut the label at its last
             * ")" or ":" (or trim the trailing rule) and append after it.
             */
            $append = function ($sheet, string $cell, $value) {
                $value = trim((string) $value);
                if (! $sheet || $value === '') {
                    return;
                }
                $label = (string) $sheet->getCell($cell)->getValue();
                $label = preg_match('/^(.*[):])/', $label, $m)
                    ? $m[1]
                    : preg_replace('/[_\s]+$/', '', $label);

                $sheet->setCellValue($cell, trim($label) === '' ? $value : $label.'   '.$value);
            };

            // ==========================================================
            // C1 — I. PERSONAL INFORMATION
            // ==========================================================
            $text($c1, 'D10', $personal_info->last_name);
            $text($c1, 'D11', $personal_info->first_name);
            $append($c1, 'L11', $personal_info->name_extension);
            $text($c1, 'D12', $personal_info->middle_name);
            $text($c1, 'D13', $this->dmy($personal_info->date_of_birth));
            $text($c1, 'D15', $personal_info->place_of_birth);

            $sexLabel = null;
            if (isset($personal_info->sex)) {
                if ($personal_info->sex == 1 || $personal_info->sex === 'Male') {
                    $sexLabel = 'Male';
                } elseif ($personal_info->sex == 0 || $personal_info->sex === 'Female') {
                    $sexLabel = 'Female';
                }
            }

            $isDualCitizen = isset($personal_info->citizenship) && (int) $personal_info->citizenship === 1;
            $countryName = $isDualCitizen ? trim((string) ($personal_info->country->name ?? '')) : '';
            $countryIndex = $this->pdsCountryDropdownIndex($c1, $countryName);
            $this->markPdsChoiceBoxes(
                $c1,
                $sexLabel,
                trim((string) ($personal_info->civil_status ?? '')),
                $isDualCitizen,
                $countryName,
                trim((string) ($personal_info->citizenship_type ?? ''))
            );

            // FIX: rows 19/20/21 in the old mapping were blank spacer rows. Height,
            // weight and blood type actually sit at D22/D24/D25 on the 2025 form.
            $text($c1, 'D22', $personal_info->height);
            $text($c1, 'D24', $personal_info->weight);
            $text($c1, 'D25', $personal_info->blood_type);

            // FIX: every ID was one to six rows too high. NOTE: field 10 on the 2025
            // form is UMID ID NO., not GSIS — the gsis_no column feeds it for now.
            $text($c1, 'D27', $personal_info->gsis_no);          // 10. UMID ID NO.
            $text($c1, 'D29', $personal_info->pagibig_no);       // 11. PAG-IBIG ID NO.
            $text($c1, 'D31', $personal_info->philhealth_no);    // 12. PHILHEALTH NO.
            $text($c1, 'D32', $personal_info->psn_no);           // 13. PhilSys Number
            $text($c1, 'D33', $personal_info->tin_no);           // 14. TIN NO.
            $text($c1, 'D34', $personal_info->agency_employee_no); // 15. AGENCY EMPLOYEE NO.

            // 17. RESIDENTIAL ADDRESS — reference IDs resolved to names
            $text($c1, 'I17', $personal_info->res_house_no);
            $text($c1, 'L17', $personal_info->res_street);
            $text($c1, 'I19', $personal_info->res_subdivision);
            $text($c1, 'L19', $this->barangayName($personal_info->res_barangay));
            $text($c1, 'I22', $this->cityName($personal_info->res_city));
            $text($c1, 'L22', $this->provinceName($personal_info->res_province));
            $text($c1, 'I24', $personal_info->res_zip);

            // 18. PERMANENT ADDRESS
            $text($c1, 'I25', $personal_info->perm_house_no);
            $text($c1, 'L25', $personal_info->perm_street);
            $text($c1, 'I27', $personal_info->perm_subdivision);
            $text($c1, 'L27', $this->barangayName($personal_info->perm_barangay));
            $text($c1, 'I29', $this->cityName($personal_info->perm_city));
            $text($c1, 'L29', $this->provinceName($personal_info->perm_province));
            $text($c1, 'I31', $personal_info->perm_zip);

            // 19-21. CONTACT
            $text($c1, 'I32', $personal_info->telephone_no);
            $text($c1, 'I33', $personal_info->mobile_no);
            $text($c1, 'I34', $personal_info->email_address);

            // ==========================================================
            // C1 — II. FAMILY BACKGROUND
            // ==========================================================
            $spouse = PdsSpouse::where('user_id', $user_id)->first();
            $text($c1, 'D36', $spouse->surname ?? null);
            $text($c1, 'D37', $spouse->first_name ?? null);
            $append($c1, 'G37', $spouse->name_extension ?? null); // FIX: was L37
            $text($c1, 'D38', $spouse->middle_name ?? null);
            $text($c1, 'D39', $spouse->occupation ?? null);
            $text($c1, 'D40', $spouse->employer_business_name ?? null);
            $text($c1, 'D41', $spouse->business_address ?? null);
            $text($c1, 'D42', $spouse->telephone_number ?? null);

            $father = PdsFather::where('user_id', $user_id)->first();
            $text($c1, 'D43', $father->surname ?? null);
            $text($c1, 'D44', $father->first_name ?? null);
            $append($c1, 'G44', $father->name_extension ?? null); // FIX: was L44
            $text($c1, 'D45', $father->middle_name ?? null);

            $mother = PdsMother::where('user_id', $user_id)->first();
            $text($c1, 'D47', $mother->maiden_surname ?? null);
            $text($c1, 'D48', $mother->first_name ?? null);
            $text($c1, 'D49', $mother->middle_name ?? null);

            // 23. CHILDREN (rows 37-48, 12 slots)
            $this->fillRows($c1, $children, self::CHILD_ROW_START, self::CHILD_ROW_END, function ($child) {
                return [
                    'I' => $child->child_name,
                    'M' => $this->dmy($child->date_of_birth),
                ];
            }, 'children', $user_id);

            // ==========================================================
            // C1 — III. EDUCATIONAL BACKGROUND (rows 54-58)
            // ==========================================================
            // FIX: the old loop wrote the level into column A and appended rows with no
            // upper bound. The form has five FIXED rows whose level names are already
            // printed in column B — each record belongs on the row matching its level.
            $levelRows = self::EDUCATION_LEVEL_ROWS;
            $usedRows = [];

            foreach ($education as $edu) {
                $row = null;
                $needle = mb_strtoupper((string) $edu->level, 'UTF-8');

                foreach ($levelRows as $keyword => $candidate) {
                    if (str_contains($needle, $keyword) && ! in_array($candidate, $usedRows, true)) {
                        $row = $candidate;
                        break;
                    }
                }

                // Unrecognised level: drop it into the first still-empty row.
                if ($row === null) {
                    $free = array_diff(array_values(array_unique($levelRows)), $usedRows);
                    $row = reset($free) ?: null;
                }

                if ($row === null) {
                    Log::info("PDS export: no free education row for level '{$edu->level}' (user {$user_id}).");

                    continue;
                }

                $usedRows[] = $row;
                $text($c1, 'D'.$row, $edu->school_name);
                $text($c1, 'G'.$row, $edu->degree_course);
                $text($c1, 'J'.$row, $edu->period_from);
                $text($c1, 'K'.$row, $edu->period_to);
                $text($c1, 'L'.$row, $edu->highest_level_earned);
                $text($c1, 'M'.$row, $edu->year_graduated);
                $text($c1, 'N'.$row, $edu->scholarship_honors);
            }

            // ==========================================================
            // C2 — IV. CIVIL SERVICE ELIGIBILITY (rows 5-11)
            // ==========================================================
            $this->fillRows($c2, $eligibilities, 5, 11, function ($e) {
                return [
                    'A' => $e->eligibility_name,
                    'F' => $e->rating,
                    'G' => $this->dmy($e->exam_date),
                    'I' => $e->exam_place,
                    'J' => $e->license_number,
                    'K' => $this->dmy($e->license_validity),
                ];
            }, 'eligibility', $user_id);

            // ==========================================================
            // C2 — V. WORK EXPERIENCE (rows 18-40)
            // ==========================================================
            $this->fillRows($c2, $work_experiences, 18, 40, function ($w) {
                return [
                    'A' => $this->dmy($w->date_from),
                    'C' => $this->dmy($w->date_to),   // "PRESENT" passes through unchanged
                    'D' => $w->position_title,
                    'G' => $w->agency_company,
                    'J' => $w->status_appointment,
                    'K' => $w->govt_service,
                ];
            }, 'work experience', $user_id);

            // ==========================================================
            // C3 — VI. VOLUNTARY WORK (rows 6-12)
            // ==========================================================
            $this->fillRows($c3, $voluntary_works, 6, 12, function ($v) {
                return [
                    'A' => $v->organization_name,
                    'E' => $this->dmy($v->date_from),
                    'F' => $this->dmy($v->date_to),
                    'G' => $v->number_of_hours,
                    'H' => $v->position_nature_of_work,
                ];
            }, 'voluntary work', $user_id);

            // ==========================================================
            // C3 — VII. LEARNING AND DEVELOPMENT (rows 18-38)
            // ==========================================================
            $this->fillRows($c3, $learnings, 18, 38, function ($l) {
                return [
                    'A' => $l->training_title,
                    'E' => $this->dmy($l->date_from),
                    'F' => $this->dmy($l->date_to),
                    'G' => $l->number_of_hours,
                    'H' => $l->ld_type,
                    'I' => $l->sponsored_by,
                ];
            }, 'learning and development', $user_id);

            // ==========================================================
            // C3 — VIII. OTHER INFORMATION (rows 42-48, three parallel lists)
            // ==========================================================
            $buckets = [
                'skill' => 'A',        // 31. SPECIAL SKILLS and HOBBIES
                'recognition' => 'C',  // 32. NON-ACADEMIC DISTINCTIONS / RECOGNITION
                'membership' => 'I',   // 33. MEMBERSHIP IN ASSOCIATION/ORGANIZATION
            ];

            foreach ($buckets as $type => $column) {
                $row = 42;
                foreach ($other_info->where('info_type', $type) as $item) {
                    if ($row > 48) {
                        Log::info("PDS export: '{$type}' entries truncated for user {$user_id}.");
                        break;
                    }
                    $text($c3, $column.$row, $item->details);
                    $row++;
                }
            }

            // ==========================================================
            // C4 — 41. REFERENCES (rows 52-54, three slots)
            // ==========================================================
            $this->fillRows($c4, $references, 52, 54, function ($r) {
                return [
                    'A' => $r->name,
                    'F' => $r->address,
                    'G' => $r->contact_no,
                ];
            }, 'references', $user_id);

            // ==========================================================
            // C4 — 34-40 QUESTIONNAIRE
            // ==========================================================
            $questionnaireAnswers = [];
            if ($questionnaire) {
                foreach (array_unique(array_values(self::C4_QUESTION_BY_ROW)) as $field) {
                    $questionnaireAnswers[$field] = mb_strtoupper(trim((string) ($questionnaire->$field ?? '')), 'UTF-8');
                }

                foreach (self::QUESTIONNAIRE_DETAIL_CELLS as $field => $detailCell) {
                    $append($c4, $detailCell, $questionnaire->{$field.'_details'} ?? '');
                }

                $append($c4, 'H20', $this->dmyOrBlank($questionnaire->q35_b_date ?? ''));
                $append($c4, 'G21', $questionnaire->q35_b_status ?? '');
            }

            // ==========================================================
            // C4 — GOVERNMENT ISSUED ID
            // ==========================================================
            if ($page4_details) {
                $write($c4, 'D61', $page4_details->gov_id_type ?? '');
                $write($c4, 'D62', $page4_details->gov_id_no ?? '');
                $write($c4, 'D64', $page4_details->gov_id_issuance ?? '');
            }

            // ==========================================================
            // NOT MAPPED: the passport photo and right thumbmark boxes. Both are
            // LONGBLOBs and PhpSpreadsheet can place them, but the boxes need
            // measuring first — say the word and I'll wire them up.
            //
            // SIGNATURES are deliberately left blank for manual signing.
            // ==========================================================

            $spreadsheet->setActiveSheetIndex(0);

            $safeLast = preg_replace('/[^A-Za-z0-9]+/', '_', (string) $personal_info->last_name) ?: 'EMPLOYEE';
            $fileName = 'PDS_'.$this->upper(trim($safeLast, '_')).'_'.date('Ymd').'.xlsx';

            $tempFilled = tempnam(sys_get_temp_dir(), 'pds_');
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tempFilled);

            $this->applyPdsFormInputs($tempFilled, $templatePath, [
                'sex' => $sexLabel,
                'civil_status' => trim((string) ($personal_info->civil_status ?? '')),
                'dual' => $isDualCitizen,
                'country_index' => $countryIndex,
                'answers' => $questionnaireAnswers,
            ]);

            return response()->download($tempFilled, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'private, no-store, max-age=0',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('PDS export failed for user '.$user_id.': '.$e->getMessage());

            return back()->with('error', 'Could not generate the PDS. Please try again.');
        }
    }

    /** Locate the PDS template, tolerating the different names it ships under. */
    private function pdsTemplatePath(): ?string
    {
        foreach (self::TEMPLATE_CANDIDATES as $name) {
            $path = storage_path('app/templates/'.$name);
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /** Fetch a worksheet by name, falling back to its index if the tab was renamed. */
    private function sheet($spreadsheet, string $name, int $index)
    {
        $sheet = $spreadsheet->getSheetByName($name);

        if (! $sheet && $spreadsheet->getSheetCount() > $index) {
            $sheet = $spreadsheet->getSheet($index);
        }

        if (! $sheet) {
            Log::warning("PDS export: worksheet '{$name}' not found in the template.");
        }

        return $sheet;
    }

    /**
     * Write a collection into a fixed block of rows, stopping at the block's last row
     * instead of overwriting the section printed below it.
     */
    private function fillRows($sheet, $records, int $firstRow, int $lastRow, callable $mapper, string $label, $userId): void
    {
        if (! $sheet) {
            return;
        }

        $row = $firstRow;

        foreach ($records as $record) {
            if ($row > $lastRow) {
                Log::info("PDS export: {$label} truncated at ".($lastRow - $firstRow + 1)." rows for user {$userId}.");
                break;
            }

            foreach ($mapper($record) as $column => $value) {
                $sheet->setCellValueExplicit($column.$row, $this->na($value), DataType::TYPE_STRING);
            }

            $row++;
        }
    }

    private function checkPdsBoxByText($sheet, string $targetText, string $replacementText): void
    {
        if (! $sheet || $targetText === '') {
            return;
        }

        $needles = array_values(array_unique([
            $targetText,
            str_replace('☐ ', '☐', $targetText),
        ]));

        foreach ($sheet->getRowIterator(12, 20) as $row) {
            $cellIterator = $row->getCellIterator('C', 'O');
            $cellIterator->setIterateOnlyExistingCells(true);

            foreach ($cellIterator as $cell) {
                $currentValue = $cell->getValue();

                if ($currentValue instanceof RichText) {
                    $currentValue = $currentValue->getPlainText();
                }

                $currentValue = (string) $currentValue;

                foreach ($needles as $needle) {
                    if ($needle === '' || ! str_contains($currentValue, $needle)) {
                        continue;
                    }

                    $sheet->setCellValue($cell->getCoordinate(), str_replace($needle, $replacementText, $currentValue));
                    $sheet->getStyle($cell->getCoordinate())->getFont()->setName('Arial');

                    return;
                }
            }
        }
    }

    private function markPdsChoiceBoxes($sheet, ?string $sex, string $civilStatus, bool $dualCitizen, string $countryName, string $citizenshipType = ''): void
    {
        if (! $sheet) {
            return;
        }

        if ($sex === 'Male') {
            $this->checkPdsBoxByText($sheet, '☐ Male', '☑ Male');
        } elseif ($sex === 'Female') {
            $this->checkPdsBoxByText($sheet, '☐ Female', '☑ Female');
        }

        if ($civilStatus === 'Single') {
            $this->checkPdsBoxByText($sheet, '☐ Single', '☑ Single');
        } elseif ($civilStatus === 'Married') {
            $this->checkPdsBoxByText($sheet, '☐ Married', '☑ Married');
        } elseif ($civilStatus === 'Widowed') {
            $this->checkPdsBoxByText($sheet, '☐ Widowed', '☑ Widowed');
        } elseif ($civilStatus === 'Separated') {
            $this->checkPdsBoxByText($sheet, '☐ Separated', '☑ Separated');
        } elseif ($civilStatus !== '') {
            $this->checkPdsBoxByText($sheet, '☐ Other/s:', '☑ Other/s:');
        }

        if ($dualCitizen) {
            $this->checkPdsBoxByText($sheet, '☐ Dual Citizenship', '☑ Dual Citizenship');

            $type = mb_strtolower($citizenshipType);
            if (str_contains($type, 'natural')) {
                $this->checkPdsBoxByText($sheet, '☐ by naturalization', '☑ by naturalization');
            } else {
                $this->checkPdsBoxByText($sheet, '☐ by birth', '☑ by birth');
            }

            if ($countryName !== '') {
                $sheet->setCellValueExplicit(self::CITIZENSHIP_COUNTRY_CELL, $countryName, DataType::TYPE_STRING);
            }
        } else {
            $this->checkPdsBoxByText($sheet, '☐ Filipino', '☑ Filipino');
        }
    }

    /**
     * PhpSpreadsheet drops the CSC form-control checkboxes and country dropdown
     * on save. Copy them back from the official template and tick the matching inputs.
     */
    private function applyPdsFormInputs(string $filledPath, string $templatePath, array $inputs): void
    {
        $out = new ZipArchive;
        $tpl = new ZipArchive;

        if ($out->open($filledPath) !== true || $tpl->open($templatePath) !== true) {
            $out->close();
            $tpl->close();
            Log::warning('PDS export: could not reopen the workbook to restore form inputs.');

            return;
        }

        $parts = [
            'xl/drawings/drawing1.xml',
            'xl/drawings/drawing2.xml',
            'xl/drawings/vmlDrawing1.vml',
            'xl/drawings/vmlDrawing2.vml',
            'xl/worksheets/_rels/sheet1.xml.rels',
            'xl/worksheets/_rels/sheet4.xml.rels',
        ];

        for ($i = 1; $i <= 37; $i++) {
            $parts[] = 'xl/ctrlProps/ctrlProp'.$i.'.xml';
        }

        foreach ($parts as $name) {
            $data = $tpl->getFromName($name);
            if ($data !== false) {
                $out->addFromString($name, $data);
            }
        }

        $vml1 = $out->getFromName('xl/drawings/vmlDrawing1.vml');
        if ($vml1 !== false) {
            $out->addFromString('xl/drawings/vmlDrawing1.vml', $this->tickC1FormInputs($vml1, $inputs));
        }

        $vml2 = $out->getFromName('xl/drawings/vmlDrawing2.vml');
        if ($vml2 !== false) {
            $out->addFromString('xl/drawings/vmlDrawing2.vml', $this->tickC4FormInputs($vml2, $inputs['answers'] ?? []));
        }

        $countryIndex = (int) ($inputs['country_index'] ?? 0);
        $ctrl1 = $out->getFromName('xl/ctrlProps/ctrlProp1.xml');
        if ($ctrl1 !== false) {
            $ctrl1 = preg_replace('/\bval="\d+"/', 'val="'.$countryIndex.'"', $ctrl1);
            $ctrl1 = preg_replace('/\bsel="\d+"/', 'sel="'.($countryIndex + 1).'"', $ctrl1);
            $out->addFromString('xl/ctrlProps/ctrlProp1.xml', $ctrl1);
        }

        foreach (['sheet1', 'sheet4'] as $sheet) {
            $filledXml = $out->getFromName('xl/worksheets/'.$sheet.'.xml');
            $templateXml = $tpl->getFromName('xl/worksheets/'.$sheet.'.xml');
            if ($filledXml !== false && $templateXml !== false) {
                $out->addFromString('xl/worksheets/'.$sheet.'.xml', $this->restoreWorksheetControls($filledXml, $templateXml));
            }
        }

        $filledTypes = $out->getFromName('[Content_Types].xml');
        $templateTypes = $tpl->getFromName('[Content_Types].xml');
        if ($filledTypes !== false && $templateTypes !== false) {
            $out->addFromString('[Content_Types].xml', $this->mergeContentTypes($filledTypes, $templateTypes));
        }

        $tpl->close();
        $out->close();
    }

    private function pdsCountryDropdownIndex($sheet, string $countryName): int
    {
        if ($countryName === '' || ! $sheet) {
            return 0;
        }

        for ($row = 11; $row <= 216; $row++) {
            $label = trim((string) $sheet->getCell('Q'.$row)->getValue());
            if ($label === '' || str_starts_with(mb_strtolower($label), 'please indicate')) {
                continue;
            }
            if ($this->countryMatches($label, $countryName)) {
                return $row - 11;
            }
        }

        return 0;
    }

    private function countryMatches(string $listLabel, string $name): bool
    {
        $normalize = static fn (string $value): string => preg_replace('/[^A-Z0-9]/', '', mb_strtoupper($value, 'UTF-8')) ?? '';
        $left = $normalize($listLabel);
        $right = $normalize($name);

        if ($left === '' || $right === '') {
            return false;
        }

        return $left === $right || str_contains($left, $right) || str_contains($right, $left);
    }

    private function tickC1FormInputs(string $vml, array $inputs): string
    {
        $sex = $inputs['sex'] ?? null;
        $civil = $inputs['civil_status'] ?? '';
        $dual = (bool) ($inputs['dual'] ?? false);
        $countryIndex = (int) ($inputs['country_index'] ?? 0);
        $knownCivil = in_array($civil, self::CIVIL_STATUS_BOXES, true);

        $vml = $this->mapVmlShapes($vml, function (string $shape, string $type, string $caption) use ($sex, $civil, $dual, $knownCivil) {
            if ($type !== 'Checkbox') {
                return $shape;
            }

            $check = match ($caption) {
                'Male', 'Female' => $caption === $sex,
                'Filipino' => ! $dual,
                'Dual Citizenship' => $dual,
                'Single', 'Married', 'Widowed', 'Separated' => $caption === $civil,
                'Other/s:' => $civil !== '' && ! $knownCivil,
                default => null,
            };

            return $check === null ? $shape : $this->setVmlChecked($shape, $check);
        });

        return $this->mapVmlShapes($vml, function (string $shape, string $type) use ($countryIndex) {
            if ($type !== 'Drop') {
                return $shape;
            }

            $shape = preg_replace('/<x:Val>\d+<\/x:Val>/', '<x:Val>'.$countryIndex.'</x:Val>', $shape) ?? $shape;

            return preg_replace('/<x:Sel>\d+<\/x:Sel>/', '<x:Sel>'.($countryIndex + 1).'</x:Sel>', $shape) ?? $shape;
        });
    }

    private function tickC4FormInputs(string $vml, array $answers): string
    {
        return $this->mapVmlShapes($vml, function (string $shape, string $type, string $caption, int $row) use ($answers) {
            if ($type !== 'Checkbox' || ! in_array($caption, ['YES', 'NO'], true)) {
                return $shape;
            }

            $field = self::C4_QUESTION_BY_ROW[$row + 1] ?? null;
            if ($field === null || ! isset($answers[$field]) || $answers[$field] === '') {
                return $shape;
            }

            return $this->setVmlChecked($shape, $answers[$field] === $caption);
        });
    }

    private function mapVmlShapes(string $vml, callable $callback): string
    {
        return preg_replace_callback('/<v:shape\b[^>]*>.*?<\/v:shape>/s', function (array $match) use ($callback) {
            $shape = $match[0];
            preg_match('/ObjectType="([^"]+)"/', $shape, $type);
            preg_match('/<x:Anchor>\s*([^<]+)/', $shape, $anchor);
            preg_match('/>([^<]{0,80})<\/font>/', $shape, $text);
            $parts = array_map('intval', array_map('trim', explode(',', $anchor[1] ?? '')));
            $caption = trim(html_entity_decode(preg_replace('/\s+/', ' ', strip_tags($text[1] ?? '')), ENT_QUOTES | ENT_HTML5));
            $caption = ltrim($caption, "\xC2\xA0 \t");

            return $callback($shape, $type[1] ?? '', $caption, $parts[2] ?? 0);
        }, $vml) ?? $vml;
    }

    private function setVmlChecked(string $shape, bool $checked): string
    {
        $shape = preg_replace('/<x:Checked\s*\/>/', '', $shape) ?? $shape;

        if (! $checked) {
            return $shape;
        }

        return preg_replace('/(<x:ClientData\b[^>]*>)/', '$1<x:Checked/>', $shape, 1) ?? $shape;
    }

    private function restoreWorksheetControls(string $filledXml, string $templateXml): string
    {
        if (! preg_match('/(<drawing\b.*)$/s', $templateXml, $match) && ! preg_match('/(<legacyDrawing\b.*)$/s', $templateXml, $match)) {
            return $filledXml;
        }

        $filledXml = preg_replace('/<(drawing|legacyDrawing)\b[^>]*\/>\s*/', '', $filledXml) ?? $filledXml;
        $filledXml = preg_replace('/<\/worksheet>\s*$/', '', $filledXml) ?? $filledXml;

        return $filledXml.$match[1];
    }

    private function mergeContentTypes(string $filled, string $template): string
    {
        if (preg_match_all('/<Default\b[^>]*\/>/', $template, $defaults)) {
            foreach ($defaults[0] as $default) {
                if (! preg_match('/Extension="([^"]+)"/', $default, $ext)) {
                    continue;
                }
                if (! str_contains($filled, 'Extension="'.$ext[1].'"')) {
                    $filled = str_replace('</Types>', $default.'</Types>', $filled);
                }
            }
        }

        if (preg_match_all('/<Override\b[^>]*\/>/', $template, $overrides)) {
            foreach ($overrides[0] as $override) {
                if (! preg_match('/PartName="([^"]+)"/', $override, $part)) {
                    continue;
                }
                if (! str_contains($filled, 'PartName="'.$part[1].'"')) {
                    $filled = str_replace('</Types>', $override.'</Types>', $filled);
                }
            }
        }

        return $filled;
    }

    // =========================================================
    // API METHODS FOR LOCATION HIERARCHY
    // =========================================================
    public function getProvinces($region_code)
    {
        $provinces = DB::table('ref_provinces')->where('region_code', $region_code)->orderBy('province_name', 'asc')->get();

        return response()->json($provinces);
    }

    public function getCities($province_code)
    {
        $cities = DB::table('ref_cities')->where('province_code', $province_code)->orderBy('city_name', 'asc')->get();

        return response()->json($cities);
    }

    public function getBarangays($city_code)
    {
        $barangays = DB::table('ref_barangays')->where('city_code', $city_code)->orderBy('brgy_name', 'asc')->get();

        return response()->json($barangays);
    }

    /**
     * School names for the education dropdown. The full list is thousands of rows,
     * so the page searches instead of rendering every option up front.
     */
    public function searchSchools(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $term = trim((string) $request->query('q', ''));
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        $schools = School::query()
            ->where('school_name', 'like', $like)
            ->orderBy('school_name')
            ->limit(20)
            ->get(['school_id', 'school_name']);

        return response()->json($schools->map(fn (School $school) => [
            'id' => $school->school_id,
            'text' => $school->school_name,
        ])->values());
    }
}
