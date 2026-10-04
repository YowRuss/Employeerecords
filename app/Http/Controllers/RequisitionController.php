<?php

namespace App\Http\Controllers;

use App\Models\LearningArea;
use App\Models\Position;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RequisitionController extends Controller
{
    /**
     * Shared guard: only HR (2) or Admin (3) may access requisition routes.
     */
    private function requireHrAccess(): ?RedirectResponse
    {
        $role_id = Session::get('role_id');
        if (! Session::has('user_id') || ! in_array($role_id, [2, 3])) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access. HR privileges required.');
        }

        return null;
    }

    /**
     * Page each requisition history independently and keep its tab selected.
     */
    private function paginateList(EloquentBuilder|QueryBuilder $query, string $pageName, string $tab): LengthAwarePaginator
    {
        return $query->paginate(5, ['*'], $pageName)
            ->withQueryString()
            ->appends(['tab' => $tab]);
    }

    /**
     * Display the Personnel Requisitions dashboard with all four lifecycle tabs.
     */
    public function index()
    {
        if ($redirect = $this->requireHrAccess()) {
            return $redirect;
        }

        // All active employees for Select2 dropdowns in modals
        $employees = User::with(['position', 'learningArea'])
            ->where('role_id', 1)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Inactive');
            })
            ->orderBy('last_name', 'asc')
            ->get();

        // Positions & learning areas for modal dropdowns
        $positions = Position::orderBy('position_name', 'asc')->get();
        $learningAreas = LearningArea::orderBy('name', 'asc')->get();

        // Recent Hires — employees created within last 30 days
        $recentHires = $this->paginateList(
            User::with(['position', 'learningArea'])
                ->where('role_id', 1)
                ->where('created_at', '>=', now()->subDays(30))
                ->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', '!=', 'Inactive');
                })
                ->orderBy('created_at', 'desc'),
            'hires_page',
            'newhires'
        );

        // Recent Promotions — service_records with status 'Promoted' in last 90 days
        $recentPromotions = $this->paginateList(
            DB::table('service_records')
                ->join('users', 'service_records.user_id', '=', 'users.id')
                ->where('service_records.status', 'Promoted')
                ->where('service_records.created_at', '>=', now()->subDays(90))
                ->select(
                    'service_records.*',
                    'users.first_name',
                    'users.last_name',
                    'users.middle_name'
                )
                ->orderBy('service_records.created_at', 'desc'),
            'promotions_page',
            'promotions'
        );

        // Transfer History — service_records with status 'Reassigned' in last 90 days
        $transferHistory = $this->paginateList(
            DB::table('service_records')
                ->join('users', 'service_records.user_id', '=', 'users.id')
                ->where('service_records.status', 'Reassigned')
                ->where('service_records.created_at', '>=', now()->subDays(90))
                ->select(
                    'service_records.*',
                    'users.first_name',
                    'users.last_name',
                    'users.middle_name'
                )
                ->orderBy('service_records.created_at', 'desc'),
            'transfers_page',
            'transfers'
        );

        // Separation History — inactive users
        $separationHistory = $this->paginateList(
            User::with('position')
                ->where('role_id', 1)
                ->where('status', 'Inactive')
                ->orderBy('separation_date', 'desc'),
            'separations_page',
            'terminations'
        );

        return view('hr.requisitions.index', compact(
            'employees',
            'positions',
            'learningAreas',
            'recentHires',
            'recentPromotions',
            'transferHistory',
            'separationHistory'
        ));
    }
}
