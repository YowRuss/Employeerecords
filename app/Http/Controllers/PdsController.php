<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PdsController extends Controller
{
    // =========================================================
    // 1. VIEW PDS (Loads all tabs)
    // =========================================================
    public function editPds()
    {
        if (! Session::has('user_id') || Session::get('role_id') != 1) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user_id = Session::get('user_id');

        $personal_info = \App\Models\PdsPersonalInfo::with('country')->where('user_id', $user_id)->first();
        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')->where('user_id', $user_id)->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();

        // PAGE 3 DATA
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();

        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $user_id)->first();
        $references = DB::table('pds_references')->where('user_id', $user_id)->get();
        $page4_details = DB::table('pds_page4_details')->where('user_id', $user_id)->first();

        $countries = DB::table('countries')->orderBy('name', 'asc')->get();
        $regions = DB::table('ref_regions')->orderBy('id', 'asc')->get();

        return view('employee.pds', compact(
            'countries',
            'regions',
            'personal_info',
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
        $user_id = Session::get('user_id');

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

        // Exclude the raw dropdown '_code' fields so MySQL column matching stays clean
        $data = $request->except([
            '_token',
            'res_region_code',
            'res_province_code',
            'res_city_code',
            'res_barangay_code',
            'perm_region_code',
            'perm_province_code',
            'perm_city_code',
            'perm_barangay_code',
        ]);

        // Explicitly map location codes and zipcode into data array
        if ($resRegion !== null) {
            $data['res_region'] = $resRegion;
        }
        if ($resProvince !== null) {
            $data['res_province'] = $resProvince;
        }
        if ($resCity !== null) {
            $data['res_city'] = $resCity;
        }
        if ($resBarangay !== null) {
            $data['res_barangay'] = $resBarangay;
        }
        if ($resZip !== null) {
            $data['res_zip'] = $resZip;
            $data['res_zipcode'] = $resZip;
        }

        if ($permRegion !== null) {
            $data['perm_region'] = $permRegion;
        }
        if ($permProvince !== null) {
            $data['perm_province'] = $permProvince;
        }
        if ($permCity !== null) {
            $data['perm_city'] = $permCity;
        }
        if ($permBarangay !== null) {
            $data['perm_barangay'] = $permBarangay;
        }

        $data['updated_at'] = now();

        $existing = DB::table('pds_personal_info')->where('user_id', $user_id)->first();

        if ($existing) {
            DB::table('pds_personal_info')->where('user_id', $user_id)->update($data);
        } else {
            $data['user_id'] = $user_id;
            $data['created_at'] = now();
            DB::table('pds_personal_info')->insert($data);
        }

        return back()->with('success', 'Personal Information saved!')->with('active_tab', 'family');
    }

    public function edit()
    {
        $user_id = Session::get('user_id');

        // Fetch data here...
        $personal_info = \App\Models\PdsPersonalInfo::with('country')->where('user_id', $user_id)->first();
        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')->where('user_id', $user_id)->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();
        $questionnaire = DB::table('pds_questionnaire')->where('user_id', $user_id)->first();
        $references = DB::table('pds_references')->where('user_id', $user_id)->get();
        $page4_details = DB::table('pds_page_4_details')->where('user_id', $user_id)->first();

        // Fetch all countries alphabetically directly from the table
        $countries = DB::table('countries')->orderBy('name', 'asc')->get();

        // Pass it to your PDS view
        return view('employee.pds.pds-view', compact(
            'countries',
            'personal_info',
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

    public function addChild(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_children')->insert([
            'user_id' => $user_id,
            'child_name' => strtoupper($request->child_name),
            'date_of_birth' => $request->child_dob,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Child added successfully!')->with('active_tab', 'family');
    }

    public function updateFamilyBackground(Request $request)
    {
        $user_id = Session::get('user_id');
        $data = $request->except(['_token']);
        $data['updated_at'] = now();

        DB::table('pds_personal_info')->updateOrInsert(['user_id' => $user_id], $data);

        return back()->with('success', 'Spouse and Parents information saved!')->with('active_tab', 'family');
    }

    // =========================================================
    // 3. SAVE SECTION C: Education & Signature
    // =========================================================
    public function addEducation(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_education')->insert([
            'user_id' => $user_id,
            'level' => $request->level,
            'school_name' => strtoupper($request->school_name),
            'degree_course' => strtoupper($request->degree),
            'period_from' => $request->period_from,
            'period_to' => $request->period_to,
            'year_graduated' => $request->year_graduated,
            'highest_level_earned' => strtoupper($request->highest_level),
            'scholarship_honors' => strtoupper($request->honors),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Education record added!')->with('active_tab', 'education');
    }

    public function saveSignature(Request $request)
    {
        $user_id = Session::get('user_id');
        $request->validate([
            'e_signature' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'signature_date' => 'required|date',
            'active_tab' => 'nullable|string',
        ]);

        $data = ['signature_date' => $request->signature_date, 'updated_at' => now()];

        if ($request->hasFile('e_signature')) {
            $file = $request->file('e_signature');
            $data['e_signature'] = file_get_contents($file->getRealPath());
        }

        DB::table('pds_personal_info')->where('user_id', $user_id)->update($data);

        $tab = $request->active_tab ?? 'education';

        return back()->with('success', 'E-Signature (BLOB) and Date saved successfully!')->with('active_tab', $tab);
    }

    // =========================================================
    // 4. SAVE SECTION IV & V: Eligibility & Work
    // =========================================================
    public function addEligibility(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_eligibility')->insert([
            'user_id' => $user_id,
            'eligibility_name' => strtoupper($request->eligibility_name),
            'rating' => $request->rating,
            'exam_date' => $request->exam_date,
            'exam_place' => strtoupper($request->exam_place),
            'license_number' => strtoupper($request->license_number),
            'license_validity' => $request->license_validity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Eligibility record added successfully!')->with('active_tab', 'eligibility');
    }

    public function addWorkExperience(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_work_experience')->insert([
            'user_id' => $user_id,
            'date_from' => $request->date_from,
            'date_to' => strtoupper($request->date_to),
            'position_title' => strtoupper($request->position_title),
            'agency_company' => strtoupper($request->agency_company),
            'status_appointment' => strtoupper($request->status_appointment),
            'govt_service' => $request->govt_service,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Work experience record added successfully!')->with('active_tab', 'work');
    }

    // =========================================================
    // 5. SAVE SECTION VI, VII, VIII: Page 3 Info
    // =========================================================
    public function addVoluntaryWork(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_voluntary_work')->insert([
            'user_id' => $user_id,
            'organization_name' => strtoupper($request->organization_name),
            'date_from' => $request->date_from,
            'date_to' => strtoupper($request->date_to),
            'number_of_hours' => $request->number_of_hours,
            'position_nature_of_work' => strtoupper($request->position_nature_of_work),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Voluntary work added!')->with('active_tab', 'voluntary');
    }

    public function addLearning(Request $request)
    {
        $user_id = Session::get('user_id');

        // Extract the raw binary data if the files were uploaded
        $proofCompletion = $request->hasFile('proof_of_completion')
            ? file_get_contents($request->file('proof_of_completion')->getRealPath())
            : null;

        $proofInvitation = $request->hasFile('proof_of_invitation')
            ? file_get_contents($request->file('proof_of_invitation')->getRealPath())
            : null;

        DB::table('pds_learning_development')->insert([
            'user_id' => $user_id,
            'training_title' => strtoupper($request->training_title),
            'date_from' => $request->date_from,
            'date_to' => strtoupper($request->date_to),
            'number_of_hours' => $request->number_of_hours,
            'ld_type' => strtoupper($request->ld_type),
            'sponsored_by' => strtoupper($request->sponsored_by),
            'proof_of_completion' => $proofCompletion, // LONGBLOB
            'proof_of_invitation' => $proofInvitation, // LONGBLOB
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Learning & Development record added!')->with('active_tab', 'learning');
    }

    public function addOtherInfo(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_other_information')->insert([
            'user_id' => $user_id,
            'info_type' => $request->info_type, // 'skill', 'recognition', or 'membership'
            'details' => strtoupper($request->details),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Information added!')->with('active_tab', 'other');
    }

    public function downloadDocument($id, $column)
    {
        $record = DB::table('pds_learning_development')->where('id', $id)->first();

        // Safety check to ensure they are only requesting allowed columns
        if (! $record || ! in_array($column, ['proof_of_completion', 'proof_of_invitation']) || empty($record->$column)) {
            abort(404);
        }

        // Determine mime type based on magic bytes (PDF vs Image)
        $blob = $record->$column;
        $mimeType = 'application/pdf'; // Default fallback
        if (strpos($blob, '%PDF') === 0) {
            $mimeType = 'application/pdf';
        } elseif (strpos($blob, "\xFF\xD8\xFF") === 0) {
            $mimeType = 'image/jpeg';
        } elseif (strpos($blob, "\x89PNG\x0D\x0A\x1A\x0A") === 0) {
            $mimeType = 'image/png';
        }

        // Return the file stream
        return response($blob)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="'.$column.'_'.$id.'"');
    }

    // =========================================================
    // 6. SAVE SECTION IX: Page 4 (Questionnaire & Final Details)
    // =========================================================
    public function updateQuestionnaire(Request $request)
    {
        $user_id = Session::get('user_id');
        $data = $request->except(['_token']);
        $data['updated_at'] = now();

        DB::table('pds_questionnaire')->updateOrInsert(['user_id' => $user_id], $data);

        return back()->with('success', 'Questionnaire saved!')->with('active_tab', 'page4');
    }

    public function addReference(Request $request)
    {
        $user_id = Session::get('user_id');
        DB::table('pds_references')->insert([
            'user_id' => $user_id,
            'name' => strtoupper($request->name),
            'address' => strtoupper($request->address),
            'contact_no' => $request->contact_no,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Reference added!')->with('active_tab', 'page4');
    }

    public function updatePage4Details(Request $request)
    {
        $user_id = Session::get('user_id');
        $data = $request->except(['_token', 'passport_photo', 'right_thumbmark']);
        $data['updated_at'] = now();

        // Process Passport Photo (LONGBLOB)
        if ($request->hasFile('passport_photo')) {
            $data['passport_photo'] = file_get_contents($request->file('passport_photo')->getRealPath());
        }

        // Process Thumbmark (LONGBLOB)
        if ($request->hasFile('right_thumbmark')) {
            $data['right_thumbmark'] = file_get_contents($request->file('right_thumbmark')->getRealPath());
        }

        DB::table('pds_page4_details')->updateOrInsert(['user_id' => $user_id], $data);

        return back()->with('success', 'Gov ID and Images saved!')->with('active_tab', 'page4');
    }

    // =========================================================
    // 7. UNIVERSAL DELETE FUNCTION
    // =========================================================
    public function deleteRecord($table, $id)
    {
        $user_id = Session::get('user_id');

        // Security check: Only allow deletion from these specific PDS tables
        $allowedTables = [
            'pds_children',
            'pds_education',
            'pds_eligibility',
            'pds_work_experience',
            'pds_voluntary_work',
            'pds_learning_development',
            'pds_other_information',
            'pds_references',
        ];

        if (in_array($table, $allowedTables)) {
            DB::table($table)->where('id', $id)->where('user_id', $user_id)->delete();

            // Map table to the correct active tab so the user stays on the same page
            $tabMapping = [
                'pds_children' => 'family',
                'pds_education' => 'education',
                'pds_eligibility' => 'eligibility',
                'pds_work_experience' => 'work',
                'pds_voluntary_work' => 'voluntary',
                'pds_learning_development' => 'learning',
                'pds_other_information' => 'other',
                'pds_references' => 'page4',
            ];

            $activeTab = $tabMapping[$table] ?? 'personal';

            return back()->with('success', 'Record deleted successfully!')->with('active_tab', $activeTab);
        }

        return back()->with('error', 'Invalid table reference.');
    }

    // =========================================================
    // 8. AUTO-FILL AND PRINT EXCEL PDS
    // =========================================================
    public function printPds()
    {
        $user_id = Session::get('user_id');

        // 1. Load User Data
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $children = DB::table('pds_children')->where('user_id', $user_id)->get();
        $education = DB::table('pds_education')->where('user_id', $user_id)->get();
        $eligibilities = DB::table('pds_eligibility')->where('user_id', $user_id)->get();
        $work_experiences = DB::table('pds_work_experience')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $voluntary_works = DB::table('pds_voluntary_work')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $learnings = DB::table('pds_learning_development')->where('user_id', $user_id)->orderBy('date_from', 'desc')->get();
        $other_info = DB::table('pds_other_information')->where('user_id', $user_id)->get();

        // 2. Validate Data (Trigger Condition)
        if (! $personal_info || empty($personal_info->last_name)) {
            return back()->with('error', 'Please complete your profile information before printing.');
        }

        // 3. Load the Official Excel Template
        $templatePath = storage_path('app/templates/ANNEX-H-1-CS-Form-No.-212-Revised-2025-Personal-Data-Sheet.xlsx');

        if (! file_exists($templatePath)) {
            return back()->with('error', 'Template file not found on the server.');
        }

        $spreadsheet = IOFactory::load($templatePath);

        // ==========================================================
        // PAGE 1: PERSONAL INFO
        // ==========================================================
        $sheet1 = $spreadsheet->getSheet(0); // Assuming Page 1 is the first sheet

        // Map Personal Information (Replace coordinates with your exact Excel cells)
        $sheet1->setCellValue('D10', $personal_info->last_name ?? 'N/A');
        $sheet1->setCellValue('D11', $personal_info->first_name ?? 'N/A');
        $sheet1->setCellValue('L11', $personal_info->name_extension ?? 'N/A');
        $sheet1->setCellValue('D12', $personal_info->middle_name ?? 'N/A');
        $sheet1->setCellValue('D13', $personal_info->date_of_birth ?? 'N/A');
        $sheet1->setCellValue('D15', $personal_info->place_of_birth ?? 'N/A');

        // Translate Integer Sex ID (1 = Male, 0 = Female)
        $sexText = 'N/A';
        if (isset($personal_info->sex)) {
            if ($personal_info->sex == 1) {
                $sexText = 'Male';
            } elseif ($personal_info->sex == 0) {
                $sexText = 'Female';
            }
        }
        $sheet1->setCellValue('D16', $sexText);

        $sheet1->setCellValue('D17', $personal_info->civil_status ?? 'N/A');
        $sheet1->setCellValue('D19', $personal_info->height ?? 'N/A');
        $sheet1->setCellValue('D20', $personal_info->weight ?? 'N/A');
        $sheet1->setCellValue('D21', $personal_info->blood_type ?? 'N/A');

        // IDs
        $sheet1->setCellValue('D22', $personal_info->gsis_no ?? 'N/A');
        $sheet1->setCellValue('D24', $personal_info->pagibig_no ?? 'N/A');
        $sheet1->setCellValue('D25', $personal_info->philhealth_no ?? 'N/A');

        // Map the new PhilSys Number (psn_no)
        $sheet1->setCellValue('D26', $personal_info->psn_no ?? 'N/A');

        $sheet1->setCellValue('D27', $personal_info->tin_no ?? 'N/A');
        $sheet1->setCellValue('D28', $personal_info->agency_employee_no ?? 'N/A');

        // Residential Address
        $sheet1->setCellValue('I17', $personal_info->res_house_no ?? 'N/A');
        $sheet1->setCellValue('L17', $personal_info->res_street ?? 'N/A');
        $sheet1->setCellValue('I19', $personal_info->res_subdivision ?? 'N/A');
        $sheet1->setCellValue('L19', $personal_info->res_barangay ?? 'N/A');
        $sheet1->setCellValue('I22', $personal_info->res_city ?? 'N/A');
        $sheet1->setCellValue('L22', $personal_info->res_province ?? 'N/A');
        $sheet1->setCellValue('I24', $personal_info->res_zip ?? 'N/A');

        // Permanent Address
        $sheet1->setCellValue('I25', $personal_info->perm_house_no ?? 'N/A');
        $sheet1->setCellValue('L25', $personal_info->perm_street ?? 'N/A');
        $sheet1->setCellValue('I27', $personal_info->perm_subdivision ?? 'N/A');
        $sheet1->setCellValue('L27', $personal_info->perm_barangay ?? 'N/A');
        $sheet1->setCellValue('I29', $personal_info->perm_city ?? 'N/A');
        $sheet1->setCellValue('L29', $personal_info->perm_province ?? 'N/A');
        $sheet1->setCellValue('I31', $personal_info->perm_zip ?? 'N/A');

        // Contact Info
        $sheet1->setCellValue('I32', $personal_info->telephone_no ?? 'N/A');
        $sheet1->setCellValue('I33', $personal_info->mobile_no ?? 'N/A');
        $sheet1->setCellValue('I34', $personal_info->email_address ?? 'N/A');

        // Citizenship
        $citizenship = $personal_info->citizenship ?? 'N/A';
        if ($citizenship == 'Dual Citizenship') {
            $citizenship .= ' - '.($personal_info->country->name ?? '');
        }
        $sheet1->setCellValue('J13', $citizenship);

        // Family Background (Spouse)
        $sheet1->setCellValue('D36', $personal_info->spouse_last_name ?? 'N/A');
        $sheet1->setCellValue('D37', $personal_info->spouse_first_name ?? 'N/A');
        $sheet1->setCellValue('L37', $personal_info->spouse_name_extension ?? 'N/A');
        $sheet1->setCellValue('D38', $personal_info->spouse_middle_name ?? 'N/A');
        $sheet1->setCellValue('D39', $personal_info->spouse_occupation ?? 'N/A');
        $sheet1->setCellValue('D40', $personal_info->spouse_employer ?? 'N/A');
        $sheet1->setCellValue('D41', $personal_info->spouse_business_address ?? 'N/A');
        $sheet1->setCellValue('D42', $personal_info->spouse_telephone ?? 'N/A');

        // Family Background (Father)
        $sheet1->setCellValue('D43', $personal_info->father_last_name ?? 'N/A');
        $sheet1->setCellValue('D44', $personal_info->father_first_name ?? 'N/A');
        $sheet1->setCellValue('L44', $personal_info->father_name_extension ?? 'N/A');
        $sheet1->setCellValue('D45', $personal_info->father_middle_name ?? 'N/A');

        // Family Background (Mother)
        $sheet1->setCellValue('D47', $personal_info->mother_maiden_last_name ?? 'N/A');
        $sheet1->setCellValue('D48', $personal_info->mother_maiden_first_name ?? 'N/A');
        $sheet1->setCellValue('D49', $personal_info->mother_maiden_middle_name ?? 'N/A');

        // Children
        $childRow = 37;
        foreach ($children as $child) {
            if ($childRow > 48) {
                break;
            } // Maximum rows for children on this page
            $sheet1->setCellValue('I'.$childRow, $child->child_name);
            $sheet1->setCellValue('M'.$childRow, $child->date_of_birth);
            $childRow++;
        }

        // ==========================================================
        // DYNAMIC DATA ARRAYS (Education, Work, etc.)
        // ==========================================================
        // Example: Mapping Education records starting at row 54
        $eduRow = 54;
        foreach ($education as $edu) {
            $sheet1->setCellValue('A'.$eduRow, $edu->level);
            $sheet1->setCellValue('D'.$eduRow, $edu->school_name);
            $sheet1->setCellValue('G'.$eduRow, $edu->degree_course);
            $sheet1->setCellValue('J'.$eduRow, $edu->period_from);
            $sheet1->setCellValue('K'.$eduRow, $edu->period_to);
            $sheet1->setCellValue('L'.$eduRow, $edu->highest_level_earned);
            $sheet1->setCellValue('M'.$eduRow, $edu->year_graduated);
            $sheet1->setCellValue('N'.$eduRow, $edu->scholarship_honors);
            $eduRow++;
        }

        // ==========================================================
        // SIGNATURES (Left blank per requirement #6)
        // ==========================================================
        // We do not map the LONGBLOB signature to ensure the document
        // remains blank for manual signing after printing.

        // 4. Force Download as Excel File
        $fileName = 'PDS_'.strtoupper($personal_info->last_name).'_'.date('Ymd').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
