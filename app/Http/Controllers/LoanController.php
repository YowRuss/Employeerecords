<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /** Loan products offered to DepEd personnel. */
    private const LOAN_TYPES = [
        'GSIS Conso-Loan',
        'GSIS Emergency Loan',
        'GSIS Policy Loan',
        'Pag-IBIG Multi-Purpose Loan',
        'Pag-IBIG Calamity Loan',
        'LANDBANK Salary Loan',
        'CNHS Multi-Purpose Coop',
        'Other',
    ];

    /** Agencies offered as quick filters above the table. */
    private const QUICK_FILTERS = ['GSIS', 'Pag-IBIG'];

    /**
     * Display all employee loans alongside the summary of outstanding balances.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $type = (string) $request->input('type', 'all');
        $status = (string) $request->input('status', 'all');

        $query = Loan::with(['user.position', 'user.pdsPersonalInfo'])
            ->join('users', 'loans.user_id', '=', 'users.id')
            ->select('loans.*');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('loans.loan_type', 'like', "%{$search}%")
                    ->orWhereHas('user.pdsPersonalInfo', function ($pds) use ($search) {
                        $pds->where('agency_employee_no', 'like', "%{$search}%");
                    });
            });
        }

        if (in_array($type, self::QUICK_FILTERS, true)) {
            $query->where('loans.loan_type', 'like', "{$type}%");
        }

        if (in_array($status, ['Active', 'Paid', 'Suspended'], true)) {
            $query->where('loans.status', $status);
        }

        $loans = $query->orderBy('users.last_name')
            ->orderBy('users.first_name')
            ->orderByDesc('loans.id')
            ->paginate(5)
            ->withQueryString();

        $employees = User::where('role_id', 1)
            ->where('status', 'active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'suffix']);

        return view('payroll.loans.index', $this->summaryData() + [
            'loans' => $loans,
            'employees' => $employees,
            'loanTypes' => self::LOAN_TYPES,
            'quickFilters' => self::QUICK_FILTERS,
            'currentSearch' => $search,
            'currentType' => $type,
            'currentStatus' => $status,
        ]);
    }

    /**
     * Portfolio-wide totals for the summary cards and filter badges. These stay
     * unfiltered so the headline figures never shift as HR narrows the table.
     *
     * @return array<string, mixed>
     */
    private function summaryData(): array
    {
        $stats = Loan::selectRaw("
            COUNT(CASE WHEN status = 'Active' THEN 1 END) as active_count,
            COUNT(CASE WHEN status = 'Paid' THEN 1 END) as paid_count,
            COALESCE(SUM(CASE WHEN status = 'Active' THEN principal_amount END), 0) as total_principal,
            COALESCE(SUM(CASE WHEN status = 'Active' THEN running_balance END), 0) as total_outstanding,
            COALESCE(SUM(CASE WHEN status = 'Active' AND running_balance > 0 THEN monthly_amortization END), 0) as monthly_withholding
        ")->first();

        $typeCounts = ['all' => Loan::count()];

        foreach (self::QUICK_FILTERS as $agency) {
            $typeCounts[$agency] = Loan::where('loan_type', 'like', "{$agency}%")->count();
        }

        return [
            'activeCount' => (int) $stats->active_count,
            'paidCount' => (int) $stats->paid_count,
            'totalPrincipal' => (float) $stats->total_principal,
            'totalOutstanding' => (float) $stats->total_outstanding,
            'totalMonthlyAmortization' => (float) $stats->monthly_withholding,
            'typeCounts' => $typeCounts,
        ];
    }

    /**
     * Store a new employee loan. The running balance always starts at the full principal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $validated['running_balance'] = $validated['principal_amount'];
        $validated['status'] = 'Active';

        Loan::create($validated);

        return redirect()->route('payroll.loans.index')
            ->with('success', 'Loan recorded successfully. It will be deducted on the next generated payroll.');
    }

    /**
     * Update an existing loan record.
     */
    public function update(Request $request, Loan $loan)
    {
        $validated = $request->validate($this->rules() + [
            'running_balance' => 'required|numeric|min:0',
            'status' => 'required|string|in:Active,Paid,Suspended',
        ]);

        if ($validated['running_balance'] > $validated['principal_amount']) {
            return back()->withInput()->with('error', 'Running balance cannot exceed the principal amount.');
        }

        // Keep the status consistent with the balance so payroll never deducts a settled loan.
        if ($validated['running_balance'] <= 0) {
            $validated['status'] = 'Paid';
        }

        $loan->update($validated);

        return redirect()->route('payroll.loans.index')
            ->with('success', 'Loan updated successfully.');
    }

    /**
     * Remove a loan record.
     */
    public function destroy(Loan $loan)
    {
        $loan->delete();

        return redirect()->route('payroll.loans.index')
            ->with('success', 'Loan deleted successfully.');
    }

    /**
     * Shared validation rules for creating and updating a loan.
     *
     * @return array<string, string>
     */
    private function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'loan_type' => 'required|string|max:255',
            'principal_amount' => 'required|numeric|min:0.01|max:99999999.99',
            'monthly_amortization' => 'required|numeric|min:0.01|lte:principal_amount',
        ];
    }
}
