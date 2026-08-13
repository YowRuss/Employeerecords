<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpWord\TemplateProcessor;

class SalnController extends Controller
{
    private const ROLE_EMPLOYEE = 1;

    /** Tables the generic delete endpoint is allowed to touch. */
    private const CHILD_TABLES = [
        'saln_unmarried_children',
        'saln_real_properties',
        'saln_personal_properties',
        'saln_liabilities',
        'saln_business_interests',
        'saln_relatives_gov',
    ];

    // =========================================================
    // GUARDS & HELPERS
    // =========================================================

    /**
     * FIX: only index() and exportDocx() checked the session. Every add/update/delete
     * method read Session::get('user_id') straight into a query, so an expired session
     * wrote rows with user_id = NULL (or updated nothing at all, silently).
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

    /** mb_ variant so Ñ/ñ in Filipino names uppercases correctly (strtoupper leaves ñ alone). */
    private function upper($value): string
    {
        return is_string($value) || is_numeric($value)
            ? mb_strtoupper(trim((string) $value), 'UTF-8')
            : '';
    }

    /**
     * FIX: peso amounts were inserted exactly as typed. "1,250,000.00" going into a
     * DECIMAL column is truncated to 1 by MySQL (or rejected in strict mode), which
     * quietly wrecked every subtotal. Normalise to a plain number first.
     */
    private function money($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        return (float) preg_replace('/[^0-9.\-]/', '', (string) $value);
    }

    private function peso($value): string
    {
        return number_format((float) $value, 2);
    }

    /** Split a single "SURNAME, FIRST MIDDLE" or "FIRST MIDDLE SURNAME" string. */
    private function splitName(?string $full): array
    {
        $full = trim((string) $full);
        if ($full === '') {
            return ['last' => '', 'first' => '', 'mi' => ''];
        }

        if (str_contains($full, ',')) {
            [$last, $rest] = array_pad(explode(',', $full, 2), 2, '');
            $parts = preg_split('/\s+/', trim($rest)) ?: [];
            $first = array_shift($parts) ?? '';

            return ['last' => trim($last), 'first' => $first, 'mi' => $this->initial(implode(' ', $parts))];
        }

        $parts = preg_split('/\s+/', $full) ?: [];
        $last = array_pop($parts) ?? '';
        $first = array_shift($parts) ?? '';

        return ['last' => $last, 'first' => $first, 'mi' => $this->initial(implode(' ', $parts))];
    }

    private function initial(?string $middle): string
    {
        $middle = trim((string) $middle);

        return $middle === '' ? '' : mb_substr($middle, 0, 1, 'UTF-8').'.';
    }

    // =========================================================
    // FORM
    // =========================================================

    public function index()
    {
        if (! Session::has('user_id') || Session::get('role_id') != self::ROLE_EMPLOYEE) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $user_id = $this->userId();

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

        // FIX: pds_personal_info was queried twice (as $pds and $pds_personal_info).
        $pds_personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $pds_spouse = DB::table('pds_spouses')->where('user_id', $user_id)->first();

        $auto_name = '';
        if ($pds_personal_info) {
            // Formats as "LASTNAME, FIRSTNAME MIDDLENAME EXTENSION"
            $auto_name = trim("{$pds_personal_info->last_name}, {$pds_personal_info->first_name} {$pds_personal_info->middle_name} {$pds_personal_info->name_extension}");
            $auto_name = trim($auto_name, ' ,');
        }

        $latest_sr = DB::table('service_records')
            ->where('user_id', $user_id)
            ->orderBy('date_from', 'desc')
            ->first();
        $auto_position = $latest_sr ? $latest_sr->designation : '';

        $user = User::with('position')->find($user_id);

        $auto_address = '';
        if ($pds_personal_info) {
            if (! empty($pds_personal_info->residential_address)) {
                $auto_address = $pds_personal_info->residential_address;
            } else {
                $parts = [];
                foreach (['res_house_no', 'res_street', 'res_subdivision'] as $field) {
                    if (! empty($pds_personal_info->$field)) {
                        $parts[] = $pds_personal_info->$field;
                    }
                }

                $lookups = [
                    'res_barangay' => ['ref_barangays', 'brgy_name'],
                    'res_city' => ['ref_cities', 'city_name'],
                    'res_province' => ['ref_provinces', 'province_name'],
                ];
                foreach ($lookups as $field => [$table, $column]) {
                    if (! empty($pds_personal_info->$field)) {
                        $value = DB::table($table)->where('id', $pds_personal_info->$field)->value($column);
                        if ($value) {
                            $parts[] = $value;
                        }
                    }
                }

                $zip = $pds_personal_info->res_zipcode ?? $pds_personal_info->res_zip ?? null;
                if (! empty($zip)) {
                    $parts[] = $zip;
                }

                $auto_address = implode(', ', $parts);
            }
        }

        $auto_spouse_name = '';
        $auto_spouse_position = '';
        $auto_spouse_agency = '';
        $auto_spouse_office_address = '';
        if ($pds_spouse) {
            $spouse_parts = [];
            if (! empty($pds_spouse->first_name)) {
                $spouse_parts[] = $pds_spouse->first_name;
            }
            if (! empty($pds_spouse->middle_name)) {
                $spouse_parts[] = $this->initial($pds_spouse->middle_name);
            }
            if (! empty($pds_spouse->surname)) {
                $spouse_parts[] = $pds_spouse->surname;
            }
            if (! empty($pds_spouse->name_extension)) {
                $spouse_parts[] = $pds_spouse->name_extension;
            }

            $auto_spouse_name = implode(' ', $spouse_parts);
            $auto_spouse_position = $pds_spouse->occupation ?? '';
            $auto_spouse_agency = $pds_spouse->employer_business_name ?? '';
            $auto_spouse_office_address = $pds_spouse->business_address ?? '';
        }

        return view('employee.saln', compact(
            'saln_info', 'total_assets', 'total_liabilities', 'net_worth',
            'children', 'real_properties', 'personal_properties',
            'liabilities', 'businesses', 'relatives',
            'auto_name', 'auto_position', 'user',
            'auto_address', 'auto_spouse_name', 'auto_spouse_position', 'auto_spouse_agency', 'auto_spouse_office_address'
        ));
    }

    // =========================================================
    // WRITES
    // =========================================================

    public function updateInfo(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        /**
         * FIX: this used to be `$request->except(['_token'])` — every field in the POST
         * body was written straight to the table. A crafted request could overwrite
         * user_id (hijacking another employee's SALN row) or id, and any stray input
         * name that isn't a column threw a 500. Keep only real columns, minus the
         * ones that must never come from the browser.
         */
        $columns = Schema::getColumnListing('saln_info');
        $blocked = ['id', 'user_id', 'created_at', 'updated_at'];
        $data = [];

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            if (! in_array($key, $columns, true) || in_array($key, $blocked, true)) {
                continue;
            }
            // FIX: strtoupper() on an array input (e.g. name="foo[]") is a TypeError in PHP 8.
            $data[$key] = ($key === 'as_of_date' || ! is_string($value)) ? $value : $this->upper($value);
        }

        if (empty($data)) {
            return back()->with('error', 'Nothing to save.')->with('active_tab', 'info');
        }

        $data['updated_at'] = now();

        try {
            DB::table('saln_info')->updateOrInsert(['user_id' => $this->userId()], $data);
        } catch (\Throwable $e) {
            Log::error('SALN info save failed: '.$e->getMessage());

            return back()->with('error', 'Could not save your information. Please try again.')->with('active_tab', 'info');
        }

        return back()->with('success', 'Basic Information saved!')->with('active_tab', 'assets');
    }

    public function addChild(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        // FIX: Carbon::parse('') on a blank/garbage date threw an uncaught exception (500).
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
        ]);

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age >= 18) {
            return back()->with('error', 'Only children STRICTLY BELOW 18 years of age can be declared in the SALN.')->with('active_tab', 'info');
        }

        DB::table('saln_unmarried_children')->insert([
            'user_id' => $this->userId(),
            'name' => $this->upper($validated['name']),
            'date_of_birth' => $validated['date_of_birth'],
            'age' => $age,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Child added successfully!')->with('active_tab', 'info');
    }

    public function addRealProperty(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->realPropertyRules());

        DB::table('saln_real_properties')->insert(
            $this->realPropertyPayload($request) + ['user_id' => $this->userId(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Real Property added!')->with('active_tab', 'assets');
    }

    public function addPersonalProperty(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->personalPropertyRules());

        DB::table('saln_personal_properties')->insert(
            $this->personalPropertyPayload($request) + ['user_id' => $this->userId(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Personal Property added!')->with('active_tab', 'assets');
    }

    public function addLiability(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->liabilityRules());

        DB::table('saln_liabilities')->insert(
            $this->liabilityPayload($request) + ['user_id' => $this->userId(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Liability added!')->with('active_tab', 'liabilities');
    }

    public function addBusiness(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->businessRules());

        DB::table('saln_business_interests')->insert(
            $this->businessPayload($request) + ['user_id' => $this->userId(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Business Interest added!')->with('active_tab', 'business');
    }

    public function addRelative(Request $request)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->relativeRules());

        DB::table('saln_relatives_gov')->insert(
            $this->relativePayload($request) + ['user_id' => $this->userId(), 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('success', 'Relative record added!')->with('active_tab', 'relatives');
    }

    public function updateChild(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
        ], [
            'date_of_birth.before_or_equal' => 'Date of birth cannot be in the future.',
        ]);

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age >= 18) {
            return back()->with('error', 'Only children STRICTLY BELOW 18 years of age can be declared in the SALN.')->with('active_tab', 'info');
        }

        DB::table('saln_unmarried_children')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update([
                'name' => $this->upper($validated['name']),
                'date_of_birth' => $validated['date_of_birth'],
                'age' => $age,
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Child updated!')->with('active_tab', 'info');
    }

    public function updateRealProperty(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->realPropertyRules());

        DB::table('saln_real_properties')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($this->realPropertyPayload($request) + ['updated_at' => now()]);

        return back()->with('success', 'Real Property updated!')->with('active_tab', 'assets');
    }

    public function updatePersonalProperty(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->personalPropertyRules());

        DB::table('saln_personal_properties')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($this->personalPropertyPayload($request) + ['updated_at' => now()]);

        return back()->with('success', 'Personal Property updated!')->with('active_tab', 'assets');
    }

    public function updateLiability(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->liabilityRules());

        DB::table('saln_liabilities')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($this->liabilityPayload($request) + ['updated_at' => now()]);

        return back()->with('success', 'Liability updated!')->with('active_tab', 'liabilities');
    }

    public function updateBusiness(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->businessRules());

        DB::table('saln_business_interests')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($this->businessPayload($request) + ['updated_at' => now()]);

        return back()->with('success', 'Business Interest updated!')->with('active_tab', 'business');
    }

    public function updateRelative(Request $request, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $request->validate($this->relativeRules());

        DB::table('saln_relatives_gov')
            ->where('id', $id)
            ->where('user_id', $this->userId())
            ->update($this->relativePayload($request) + ['updated_at' => now()]);

        return back()->with('success', 'Relative record updated!')->with('active_tab', 'relatives');
    }

    public function deleteRecord($table, $id)
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        if (! in_array($table, self::CHILD_TABLES, true)) {
            return back()->with('error', 'Invalid action.');
        }

        DB::table($table)->where('id', $id)->where('user_id', $this->userId())->delete();

        return back()->with('success', 'Record deleted successfully!');
    }

    // ---- rules / payloads (shared by add* and update*) -------------------

    private function realPropertyRules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'kind' => ['nullable', 'string', 'max:255'],
            'exact_location' => ['nullable', 'string', 'max:255'],
            'assessed_value' => ['nullable', 'string', 'max:30'],
            'fair_market_value' => ['nullable', 'string', 'max:30'],
            'acquisition_year' => ['nullable', 'digits:4'],
            'acquisition_mode' => ['nullable', 'string', 'max:255'],
            'acquisition_cost' => ['nullable', 'string', 'max:30'],
        ];
    }

    private function realPropertyPayload(Request $request): array
    {
        return [
            'description' => $this->upper($request->input('description')),
            'kind' => $this->upper($request->input('kind')),
            'exact_location' => $this->upper($request->input('exact_location')),
            'assessed_value' => $this->money($request->input('assessed_value')),
            'fair_market_value' => $this->money($request->input('fair_market_value')),
            'acquisition_year' => $request->input('acquisition_year'),
            'acquisition_mode' => $this->upper($request->input('acquisition_mode')),
            'acquisition_cost' => $this->money($request->input('acquisition_cost')),
        ];
    }

    private function personalPropertyRules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'year_acquired' => ['nullable', 'string', 'max:30'],
            'acquisition_cost' => ['nullable', 'string', 'max:30'],
        ];
    }

    private function personalPropertyPayload(Request $request): array
    {
        return [
            'description' => $this->upper($request->input('description')),
            'year_acquired' => $this->upper($request->input('year_acquired')),
            'acquisition_cost' => $this->money($request->input('acquisition_cost')),
        ];
    }

    private function liabilityRules(): array
    {
        return [
            'nature' => ['required', 'string', 'max:255'],
            'name_of_creditors' => ['nullable', 'string', 'max:255'],
            'outstanding_balance' => ['nullable', 'string', 'max:30'],
        ];
    }

    private function liabilityPayload(Request $request): array
    {
        return [
            'nature' => $this->upper($request->input('nature')),
            'name_of_creditors' => $this->upper($request->input('name_of_creditors')),
            'outstanding_balance' => $this->money($request->input('outstanding_balance')),
        ];
    }

    private function businessRules(): array
    {
        return [
            'business_name' => ['required', 'string', 'max:255'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'nature_of_business' => ['nullable', 'string', 'max:255'],
            'date_of_acquisition' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function businessPayload(Request $request): array
    {
        return [
            'business_name' => $this->upper($request->input('business_name')),
            'business_address' => $this->upper($request->input('business_address')),
            'nature_of_business' => $this->upper($request->input('nature_of_business')),
            'date_of_acquisition' => $this->upper($request->input('date_of_acquisition')),
        ];
    }

    private function relativeRules(): array
    {
        return [
            'relative_name' => ['required', 'string', 'max:255'],
            'relationship' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'agency_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function relativePayload(Request $request): array
    {
        return [
            'relative_name' => $this->upper($request->input('relative_name')),
            'relationship' => $this->upper($request->input('relationship')),
            'position' => $this->upper($request->input('position')),
            'agency_address' => $this->upper($request->input('agency_address')),
        ];
    }

    // =========================================================
    // DOCX EXPORT
    // =========================================================

    public function exportDocx()
    {
        if ($redirect = $this->requireAuth()) {
            return $redirect;
        }

        $user_id = $this->userId();
        $user = User::with('position')->find($user_id);

        if (! $user) {
            return back()->with('error', 'Your account could not be found.');
        }

        $saln_info = DB::table('saln_info')->where('user_id', $user_id)->first();
        $children = DB::table('saln_unmarried_children')->where('user_id', $user_id)->get();
        $real_properties = DB::table('saln_real_properties')->where('user_id', $user_id)->get();
        $personal_properties = DB::table('saln_personal_properties')->where('user_id', $user_id)->get();
        $liabilities = DB::table('saln_liabilities')->where('user_id', $user_id)->get();
        $businesses = DB::table('saln_business_interests')->where('user_id', $user_id)->get();
        $relatives = DB::table('saln_relatives_gov')->where('user_id', $user_id)->get();

        $total_real = $real_properties->sum('acquisition_cost');
        $total_personal = $personal_properties->sum('acquisition_cost');
        $total_assets = $total_real + $total_personal;
        $total_liabilities = $liabilities->sum('outstanding_balance');
        $net_worth = $total_assets - $total_liabilities;

        /**
         * FIX: this was `->where('id', $user->id)` — it looked the position table up by
         * the USER's id, so everyone got whatever position happened to share their row
         * number. Use the relation, falling back to the latest service record.
         */
        $position_name = $user->position->position_name ?? null;
        if (! $position_name) {
            $position_name = DB::table('service_records')
                ->where('user_id', $user_id)
                ->orderBy('date_from', 'desc')
                ->value('designation');
        }

        // FIX: the form pulled the declarant's name from pds_personal_info but the export
        // pulled it from users, so the printed SALN could disagree with the on-screen form.
        $pds = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $d_last = $pds->last_name ?? $user->last_name ?? '';
        $d_first = trim(($pds->first_name ?? $user->first_name ?? '').' '.($pds->name_extension ?? ''));
        $d_mi = $this->initial($pds->middle_name ?? $user->middle_name ?? '');

        // Spouse: prefer the structured PDS record, fall back to splitting saln_info.spouse_name
        $pds_spouse = DB::table('pds_spouses')->where('user_id', $user_id)->first();
        if ($pds_spouse && ! empty($pds_spouse->surname)) {
            $s_last = $pds_spouse->surname;
            $s_first = trim(($pds_spouse->first_name ?? '').' '.($pds_spouse->name_extension ?? ''));
            $s_mi = $this->initial($pds_spouse->middle_name ?? '');
        } else {
            $split = $this->splitName($saln_info->spouse_name ?? '');
            $s_last = $split['last'];
            $s_first = $split['first'];
            $s_mi = $split['mi'];
        }

        $templatePath = storage_path('app/templates/SALN.docx');

        if (! is_readable($templatePath)) {
            return back()->with('error', 'SALN template not found in storage/app/templates.');
        }

        $as_of = ($saln_info && ! empty($saln_info->as_of_date))
            ? Carbon::parse($saln_info->as_of_date)->year
            : Carbon::now()->year;

        try {
            $tp = new TemplateProcessor($templatePath);

            /**
             * FIX: the old $safeSet/$safeClone helpers swallowed EVERY exception without
             * logging, which is why a template containing no ${placeholders} at all still
             * "succeeded" and produced a blank form. Fail loudly instead.
             */
            if (empty($tp->getVariables())) {
                return back()->with('error', 'The SALN template has no ${placeholders} — it is still the blank CSC form. Replace storage/app/templates/SALN.docx with the templated version.');
            }

            $set = function (string $var, $value) use ($tp) {
                try {
                    $tp->setValue($var, (string) $value);
                } catch (\Throwable $e) {
                    Log::warning("SALN template: could not set \${{$var}} — ".$e->getMessage());
                }
            };

            /** Clone a table row per record, or blank the row's placeholders when there are none. */
            $rows = function (string $anchor, array $data, array $vars) use ($tp) {
                try {
                    if (! empty($data)) {
                        $tp->cloneRowAndSetValues($anchor, $data);
                    } else {
                        foreach ($vars as $var) {
                            $tp->setValue($var, '');
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning("SALN template: row \${{$anchor}} could not be filled — ".$e->getMessage());
                }
            };

            // ---- declarant / spouse block ----
            $set('d_last', $this->upper($d_last));
            $set('d_first', $this->upper($d_first));
            $set('d_mi', $this->upper($d_mi));
            $set('d_position', $this->upper($position_name ?: 'N/A'));
            $set('d_agency', $this->upper($saln_info->declarant_agency ?? 'CNHS-JHS'));
            $set('d_office_address', $this->upper($saln_info->declarant_office_address ?? ''));

            $set('s_last', $this->upper($s_last));
            $set('s_first', $this->upper($s_first));
            $set('s_mi', $this->upper($s_mi));
            $set('s_position', $this->upper($saln_info->spouse_position ?? ''));
            $set('s_agency', $this->upper($saln_info->spouse_agency ?? ''));
            $set('s_office_address', $this->upper($saln_info->spouse_office_address ?? ''));

            $set('filing_year', $as_of);
            $set('date_signed', Carbon::now()->format('F d, Y'));

            // ---- unmarried children ----
            // FIX: age was read from the stored column, which goes stale the moment the
            // child has a birthday. Recompute from date_of_birth at export time.
            // NOTE: the 2025 form has no date-of-birth column, so ${c_dob} no longer exists.
            $childrenData = [];
            foreach ($children as $child) {
                $childrenData[] = [
                    'c_name' => $child->name,
                    'c_age' => $child->date_of_birth ? Carbon::parse($child->date_of_birth)->age : $child->age,
                ];
            }
            $rows('c_name', $childrenData, ['c_name', 'c_age']);

            // ---- real properties ----
            $realData = [];
            foreach ($real_properties as $rp) {
                $realData[] = [
                    'rp_desc' => $rp->description,
                    'rp_kind' => $rp->kind,
                    'rp_loc' => $rp->exact_location,
                    'rp_assessed' => $this->peso($rp->assessed_value),
                    'rp_market' => $this->peso($rp->fair_market_value),
                    'rp_year' => $rp->acquisition_year,
                    'rp_mode' => $rp->acquisition_mode,
                    'rp_cost' => $this->peso($rp->acquisition_cost),
                ];
            }
            $rows('rp_desc', $realData, ['rp_desc', 'rp_kind', 'rp_loc', 'rp_assessed', 'rp_market', 'rp_year', 'rp_mode', 'rp_cost']);
            $set('rp_subtotal', $this->peso($total_real));

            // ---- personal properties ----
            $personalData = [];
            foreach ($personal_properties as $pp) {
                $personalData[] = [
                    'pp_desc' => $pp->description,
                    'pp_year' => $pp->year_acquired,
                    'pp_cost' => $this->peso($pp->acquisition_cost),
                ];
            }
            $rows('pp_desc', $personalData, ['pp_desc', 'pp_year', 'pp_cost']);
            $set('pp_subtotal', $this->peso($total_personal));
            $set('total_assets', $this->peso($total_assets));

            // ---- liabilities ----
            $liaData = [];
            foreach ($liabilities as $lia) {
                $liaData[] = [
                    'l_nature' => $lia->nature,
                    'l_creditor' => $lia->name_of_creditors,
                    'l_balance' => $this->peso($lia->outstanding_balance),
                ];
            }
            $rows('l_nature', $liaData, ['l_nature', 'l_creditor', 'l_balance']);
            $set('total_liabilities', $this->peso($total_liabilities));
            $set('net_worth', $this->peso($net_worth));

            // ---- business interests ----
            $busData = [];
            foreach ($businesses as $bus) {
                $busData[] = [
                    'b_name' => $bus->business_name,
                    'b_address' => $bus->business_address,
                    'b_nature' => $bus->nature_of_business,
                    'b_date' => $bus->date_of_acquisition,
                ];
            }
            $rows('b_name', $busData, ['b_name', 'b_address', 'b_nature', 'b_date']);

            // ---- relatives in government ----
            // FIX: the original ran this block twice — once through $safeClone and again
            // through a raw cloneRowAndSetValues(). The second call threw (the placeholder
            // was already consumed), and the outer catch turned the whole export into
            // "Error generating document".
            $relData = [];
            foreach ($relatives as $rel) {
                $relData[] = [
                    'r_name' => $rel->relative_name,
                    'r_relation' => $rel->relationship,
                    'r_position' => $rel->position,
                    'r_agency' => $rel->agency_address,
                ];
            }
            $rows('r_name', $relData, ['r_name', 'r_relation', 'r_position', 'r_agency']);

            if ($unresolved = $tp->getVariables()) {
                Log::info('SALN export: placeholders left unfilled — '.implode(', ', $unresolved));
            }

            // FIX: filename came straight from last_name, and two exports by the same user
            // collided on the same temp path. Sanitise and make it unique.
            $safeLast = preg_replace('/[^A-Za-z0-9]+/', '_', (string) ($d_last ?: $user->last_name)) ?: 'DECLARANT';
            $fileName = 'SALN_'.$as_of.'_'.trim($safeLast, '_').'.docx';

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir) && ! mkdir($tempDir, 0775, true) && ! is_dir($tempDir)) {
                throw new \RuntimeException('Could not create temp directory.');
            }

            $tempPath = $tempDir.'/'.uniqid('saln_', true).'.docx';
            $tp->saveAs($tempPath);

            return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('SALN export failed for user '.$user_id.': '.$e->getMessage());

            return back()->with('error', 'Error generating document: '.$e->getMessage());
        }
    }
}
