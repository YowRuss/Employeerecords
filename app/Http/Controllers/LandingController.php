<?php

namespace App\Http\Controllers;

use App\Enums\PositionCategory;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    /**
     * Display the official landing page for CNHS-JHS HR & Employee Records System.
     */
    public function index()
    {
        $totalStaff = DB::table('users')->where('role_id', 1)->where(function ($query) {
            $query->whereNull('status')->orWhere('status', '!=', 'Inactive');
        })->count();

        $teachingCount = DB::table('users')
            ->join('positions', 'users.position_id', '=', 'positions.id')
            ->where('users.role_id', 1)
            ->where(function ($query) {
                $query->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
            })
            ->where('positions.category', PositionCategory::Teaching->value)
            ->count();

        $nonTeachingCount = DB::table('users')
            ->join('positions', 'users.position_id', '=', 'positions.id')
            ->where('users.role_id', 1)
            ->where(function ($query) {
                $query->whereNull('users.status')->orWhere('users.status', '!=', 'Inactive');
            })
            ->where('positions.category', PositionCategory::NonTeaching->value)
            ->count();

        return view('welcome', compact(
            'totalStaff',
            'teachingCount',
            'nonTeachingCount'
        ));
    }
}
