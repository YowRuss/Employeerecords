<?php

namespace App\Http\Controllers;

use App\Enums\RemittanceAgency;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RemittanceController extends Controller
{
    /**
     * Display the remittance dashboard with an empty filter card.
     */
    public function index()
    {
        return view('payroll.remittances.index', $this->baseViewData() + [
            'results' => null,
        ]);
    }

    /**
     * Run the remittance report for the selected month, year, and agency.
     */
    public function generateReport(Request $request)
    {
        $filters = $this->validateFilters($request);
        $agency = RemittanceAgency::from($filters['agency']);

        return view('payroll.remittances.index', $this->baseViewData($filters) + [
            'results' => $this->buildReport($filters['period_month'], $filters['period_year'], $agency),
        ]);
    }

    /**
     * Download the current filtered dataset as a CSV upload file.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->validateFilters($request);
        $agency = RemittanceAgency::from($filters['agency']);
        $month = $filters['period_month'];
        $year = $filters['period_year'];

        $rows = $this->buildReport($month, $year, $agency);
        $monthName = Carbon::create()->month($month)->format('F');
        $fileName = sprintf('%s_Remittance_%s_%d.csv', str_replace(['-', ' '], '', $agency->label()), $monthName, $year);

        return response()->streamDownload(function () use ($rows, $agency, $monthName, $year) {
            $handle = fopen('php://output', 'w');

            // Excel opens UTF-8 CSVs correctly only when a BOM is present.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [$agency->label().' Remittance Report']);
            fputcsv($handle, ['Period', $monthName.' '.$year]);
            fputcsv($handle, ['Generated', now()->format('Y-m-d H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                '#',
                'Employee No.',
                $agency->identifierLabel(),
                'Last Name',
                'First Name',
                'Middle Name',
                'Position',
                $agency->amountLabel(),
            ]);

            foreach ($rows as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row->employee_no,
                    $row->agency_identifier,
                    $row->last_name,
                    $row->first_name,
                    $row->middle_name,
                    $row->position_name,
                    number_format((float) $row->amount_withheld, 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTAL',
                $rows->count().' employees',
                '', '', '', '', '',
                number_format((float) $rows->sum('amount_withheld'), 2, '.', ''),
            ]);

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Aggregate every finalized withholding for the month into one row per
     * employee. A month can hold more than one finalized period — a regular
     * run plus a bonus run, for instance — and the agency expects a single
     * combined figure per member.
     *
     * @return Collection<int, object>
     */
    private function buildReport(int $month, int $year, RemittanceAgency $agency): Collection
    {
        // Column names come from the enum, never from request input.
        $amountColumn = 'payroll_records.'.$agency->deductionColumn();
        $identifierColumn = 'pds_personal_info.'.$agency->identifierColumn();

        return PayrollRecord::query()
            ->join('payroll_periods', 'payroll_records.payroll_period_id', '=', 'payroll_periods.id')
            ->join('users', 'payroll_records.user_id', '=', 'users.id')
            ->leftJoin('pds_personal_info', 'pds_personal_info.user_id', '=', 'users.id')
            ->leftJoin('positions', 'users.position_id', '=', 'positions.id')
            ->where('payroll_periods.period_month', $month)
            ->where('payroll_periods.period_year', $year)
            ->where('payroll_periods.status', 'FINALIZED')
            ->selectRaw("SUM({$amountColumn}) as amount_withheld")
            ->addSelect([
                'users.id as user_id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.suffix',
                'pds_personal_info.agency_employee_no as employee_no',
                "{$identifierColumn} as agency_identifier",
                'positions.position_name',
            ])
            ->groupBy(
                'users.id',
                'users.first_name',
                'users.middle_name',
                'users.last_name',
                'users.suffix',
                'pds_personal_info.agency_employee_no',
                $identifierColumn,
                'positions.position_name',
            )
            // Drops employees with a zero or null withholding for this agency.
            ->havingRaw("SUM({$amountColumn}) > 0")
            ->orderBy('users.last_name')
            ->orderBy('users.first_name')
            ->get();
    }

    /**
     * @return array{period_month: int, period_year: int, agency: string}
     */
    private function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2000|max:2100',
            'agency' => ['required', Rule::enum(RemittanceAgency::class)],
        ]);

        return [
            'period_month' => (int) $validated['period_month'],
            'period_year' => (int) $validated['period_year'],
            'agency' => $validated['agency'],
        ];
    }

    /**
     * Filter state shared by the empty dashboard and a generated report.
     *
     * @param  array{period_month: int, period_year: int, agency: string}|null  $filters
     * @return array<string, mixed>
     */
    private function baseViewData(?array $filters = null): array
    {
        return [
            'agencies' => RemittanceAgency::cases(),
            'selectedMonth' => $filters['period_month'] ?? (int) now()->month,
            'selectedYear' => $filters['period_year'] ?? (int) now()->year,
            'selectedAgency' => isset($filters['agency']) ? RemittanceAgency::from($filters['agency']) : null,
        ];
    }
}
