<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ServiceRecordController extends Controller
{
    // =========================================================
    // EMPLOYEE VIEW (READ-ONLY)
    // =========================================================
    public function index()
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user_id = Session::get('user_id');

        $user = DB::table('users')->where('id', $user_id)->first();
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $records = DB::table('service_records')->where('user_id', $user_id)->orderBy('date_from', 'asc')->get();

        return view('employee.service_record', compact('user', 'personal_info', 'records'));
    }

    // =========================================================
    // HR VIEW & MANAGEMENT
    // =========================================================
    public function hrIndex($user_id)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        $user = DB::table('users')->where('id', $user_id)->first();
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $records = DB::table('service_records')->where('user_id', $user_id)->orderBy('date_from', 'asc')->get();

        // ADD THIS: Fetch all available positions from the database
        $positions = DB::table('positions')->orderBy('position_name', 'asc')->get();

        // Pass $positions to the view
        return view('hr.service_records.show', compact('user', 'personal_info', 'records', 'positions'));
    }

    public function hrStore(Request $request, $user_id)
    {
        DB::table('service_records')->insert([
            'user_id' => $user_id, // Attached to the specific employee
            'date_from' => $request->date_from,
            'date_to' => strtoupper($request->date_to),
            'designation' => strtoupper($request->designation),
            'status' => strtoupper($request->status),
            'salary' => strtoupper($request->salary),
            'station_place' => strtoupper($request->station_place),
            'branch' => strtoupper($request->branch),
            'leave_without_pay' => strtoupper($request->leave_without_pay ?? 'NONE'),
            'separation_date' => strtoupper($request->separation_date),
            'separation_cause' => strtoupper($request->separation_cause ?? 'NONE'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Service record entry added successfully!');
    }

    public function hrDestroy($id)
    {
        DB::table('service_records')->where('id', $id)->delete();

        return back()->with('success', 'Service record deleted.');
    }

    // =========================================================
    // HR SERVICE RECORD DIRECTORY
    // =========================================================
    public function hrDirectory(Request $request)
    {
        if (! Session::has('user_id') || Session::get('role_id') == 1) { // Block standard employees
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $filter = $request->query('filter', 'all');

        $query = User::with('position')->where('role_id', 1);

        if ($filter === 'teaching') {
            $query->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::Teaching->value);
            });
        } elseif ($filter === 'non-teaching') {
            $query->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::NonTeaching->value);
            });
        }

        $employees = $query->orderBy('last_name', 'asc')->paginate(10)->withQueryString();

        $allCount = User::where('role_id', 1)->count();
        $teachingCount = User::where('role_id', 1)
            ->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::Teaching->value);
            })->count();
        $nonTeachingCount = User::where('role_id', 1)
            ->whereHas('position', function ($q) {
                $q->where('category', PositionCategory::NonTeaching->value);
            })->count();

        return view('hr.service_records.index', compact('employees', 'filter', 'allCount', 'teachingCount', 'nonTeachingCount'));
    }

    // =========================================================
    // PRINT / EXPORT SERVICE RECORD
    // =========================================================
    public function printToExcel($user_id)
    {
        if (! Session::has('user_id')) {
            return redirect()->route('login');
        }

        // Fetch employee data
        $user = DB::table('users')->where('id', $user_id)->first();
        $personal_info = DB::table('pds_personal_info')->where('user_id', $user_id)->first();
        $records = DB::table('service_records')->where('user_id', $user_id)->orderBy('date_from', 'asc')->get();

        // Load the verbatim template
        $templatePath = storage_path('app/templates/Service Record - Blank Template.xls');
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Populate Header Information (Mapped precisely to your template's layout)
        // Row 12 contains the blanks above (Surname), (Given Name), and (M.I)
        $sheet->setCellValue('B12', strtoupper($user->last_name));
        $sheet->setCellValue('C12', strtoupper($user->first_name));

        // Optional: If you have middle name in your DB, you can place it in E12
        if (isset($user->middle_name)) {
            $sheet->setCellValue('E12', strtoupper(substr($user->middle_name, 0, 1)).'.');
        }

        // Row 15 contains the blanks above (Date of Birth) and (Place of Birth)
        $sheet->setCellValue('B15', $personal_info->date_of_birth ?? 'N/A');

        // Optional: If you track place of birth in your pds_personal_info table
        if (isset($personal_info->place_of_birth)) {
            $sheet->setCellValue('D15', strtoupper($personal_info->place_of_birth));
        }

        // 2. Populate Service Records
        $row = 25; // The actual table rows in your template start exactly at Row 25

        foreach ($records as $record) {
            $sheet->setCellValue('A'.$row, $record->date_from);
            $sheet->setCellValue('B'.$row, $record->date_to);
            $sheet->setCellValue('C'.$row, $record->designation);
            $sheet->setCellValue('D'.$row, $record->status);
            $sheet->setCellValue('E'.$row, $record->salary);
            $sheet->setCellValue('F'.$row, $record->station_place);
            $sheet->setCellValue('G'.$row, $record->branch);
            $sheet->setCellValue('H'.$row, $record->leave_without_pay);

            // Separation Date & Cause are usually placed at the end columns (I and J)
            $sheet->setCellValue('I'.$row, $record->separation_date);
            $sheet->setCellValue('J'.$row, $record->separation_cause);

            // Apply simple styling to borders to keep the grid intact
            $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $row++;
        }

        // 3. Save as a temporary file and return as download
        $fileName = 'Service_Record_'.strtoupper($user->last_name).'.xls';
        $writer = IOFactory::createWriter($spreadsheet, 'Xls');

        $tempFile = tempnam(sys_get_temp_dir(), 'sr_export');
        $writer->save($tempFile);

        return Response::download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ])->deleteFileAfterSend(true);
    }
}
