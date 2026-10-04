<?php

use App\Models\DeductionCategory;
use App\Models\DeductionType;
use App\Models\PayrollPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('the deductions modal only loads categories for the active profile', function () {
    $suffix = uniqid();

    $current = DeductionCategory::create([
        'name' => 'GSIS Loans '.$suffix,
        'slug' => 'gsis-loans-'.$suffix.'-v1',
        'sort_order' => 900,
        'is_active' => true,
        'profile_version' => 'v1',
    ]);
    $other = DeductionCategory::create([
        'name' => 'GSIS Loans '.$suffix,
        'slug' => 'gsis-loans-'.$suffix.'-v2',
        'sort_order' => 901,
        'is_active' => true,
        'profile_version' => 'v2',
    ]);

    DeductionType::create([
        'category_id' => $current->id,
        'name' => 'Consolidated '.$suffix,
        'code' => 'gsis_conso_'.$suffix,
        'is_active' => true,
    ]);
    DeductionType::create([
        'category_id' => $other->id,
        'name' => 'Consolidated '.$suffix,
        'code' => 'gsis_conso_v2_'.$suffix,
        'is_active' => true,
    ]);

    $period = PayrollPeriod::create([
        'fund_cluster' => '01',
        'period_month' => 3,
        'period_year' => 2034,
        'status' => 'DRAFT',
    ]);

    $hr = User::whereIn('role_id', [2, 3])->firstOrFail();

    $this->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
        'deduction_version' => 'v1',
    ])->get(route('hr.payroll.show', $period->id))
        ->assertSuccessful()
        ->assertViewHas('categories', function ($categories) use ($current, $other) {
            return $categories->contains('id', $current->id)
                && ! $categories->contains('id', $other->id)
                && $categories->every(fn ($category) => $category->profile_version === 'v1');
        });
});
