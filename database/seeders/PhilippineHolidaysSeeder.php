<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PhilippineHolidaysSeeder extends Seeder
{
    /**
     * Seed fixed-date Philippine holidays for the current year.
     *
     * Movable holidays such as Holy Week (Maundy Thursday and Good Friday),
     * Eid'l Fitr, and Eid'l Adha are excluded. Their dates change with the
     * lunar calendar and presidential proclamations, so HR must add them
     * manually each year.
     */
    public function run(): void
    {
        $year = Carbon::now()->year;

        $holidays = [
            ['title' => "New Year's Day", 'holiday_date' => "$year-01-01", 'type' => 'Regular'],
            ['title' => 'Araw ng Kagitingan', 'holiday_date' => "$year-04-09", 'type' => 'Regular'],
            ['title' => 'Labor Day', 'holiday_date' => "$year-05-01", 'type' => 'Regular'],
            ['title' => 'Independence Day', 'holiday_date' => "$year-06-12", 'type' => 'Regular'],
            ['title' => 'Bonifacio Day', 'holiday_date' => "$year-11-30", 'type' => 'Regular'],
            ['title' => 'Christmas Day', 'holiday_date' => "$year-12-25", 'type' => 'Regular'],
            ['title' => 'Rizal Day', 'holiday_date' => "$year-12-30", 'type' => 'Regular'],

            ['title' => 'Ninoy Aquino Day', 'holiday_date' => "$year-08-21", 'type' => 'Special Non-Working'],
            ['title' => "All Saints' Day", 'holiday_date' => "$year-11-01", 'type' => 'Special Non-Working'],
            ['title' => 'Feast of the Immaculate Conception', 'holiday_date' => "$year-12-08", 'type' => 'Special Non-Working'],
            ['title' => 'Last Day of the Year', 'holiday_date' => "$year-12-31", 'type' => 'Special Non-Working'],
        ];

        foreach ($holidays as $holiday) {
            Holiday::firstOrCreate(
                [
                    'holiday_date' => $holiday['holiday_date'],
                    'title' => $holiday['title'],
                ],
                ['type' => $holiday['type']]
            );
        }
    }
}
