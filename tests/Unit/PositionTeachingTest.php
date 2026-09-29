<?php

use App\Enums\PositionCategory;
use App\Models\Position;
use App\Models\User;

test('position correctly identifies teaching category without converting enum to string', function () {
    $teachingPos = new Position([
        'position_name' => 'TEACHER I',
        'category' => PositionCategory::Teaching,
    ]);

    $nonTeachingPos = new Position([
        'position_name' => 'ADMINISTRATIVE AIDE I',
        'category' => PositionCategory::NonTeaching,
    ]);

    $masterTeacherPos = new Position([
        'position_name' => 'MASTER TEACHER II',
        'category' => PositionCategory::Teaching,
    ]);

    expect($teachingPos->isTeaching())->toBeTrue()
        ->and($nonTeachingPos->isTeaching())->toBeFalse()
        ->and($masterTeacherPos->isTeaching())->toBeTrue();
});

test('user model evaluates isTeaching correctly using position relation', function () {
    $user = new User([
        'employee_type' => 0,
    ]);

    $teachingPos = new Position([
        'position_name' => 'MASTER TEACHER II',
        'category' => PositionCategory::Teaching,
    ]);

    $user->setRelation('position', $teachingPos);
    $user->position_id = 999;

    expect($user->isTeaching())->toBeTrue()
        ->and($user->employee_type_label)->toBe('TEACHING');
});
