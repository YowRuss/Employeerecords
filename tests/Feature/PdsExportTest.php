<?php

use App\Http\Controllers\PdsController;
use App\Models\Country;
use App\Models\PdsPersonalInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

uses(DatabaseTransactions::class);

function pdsExportSession(User $employee): array
{
    return [
        'user_id' => $employee->id,
        'role_id' => 1,
        'full_name' => trim(($employee->first_name ?? '').' '.($employee->last_name ?? '')),
    ];
}

test('hr can download another employees official pds workbook', function () {
    $info = PdsPersonalInfo::whereNotNull('last_name')->where('last_name', '!=', '')->first();

    if (! $info) {
        $this->markTestSkipped('No PDS personal info is available to export.');
    }

    $hr = User::where('role_id', 2)->firstOrFail();

    $response = $this->withSession([
        'user_id' => $hr->id,
        'role_id' => 2,
    ])->get(route('pds.export', $info->user_id))
        ->assertSuccessful();

    $path = tempnam(sys_get_temp_dir(), 'pds_hr_');
    copy($response->getFile()->getPathname(), $path);

    $sheet = IOFactory::load($path)->getSheetByName('C1');
    expect(mb_strtoupper((string) $sheet->getCell('D10')->getValue()))->toBe(mb_strtoupper($info->last_name));

    @unlink($path);
});

test('an employee cannot export another employees pds', function () {
    $employee = User::where('role_id', 1)->firstOrFail();
    $other = User::where('id', '!=', $employee->id)->firstOrFail();

    $this->withSession(pdsExportSession($employee))
        ->get(route('pds.export', $other->id))
        ->assertForbidden();
});

test('pds excel export ticks sex civil status and keeps the country dropdown', function () {
    $info = PdsPersonalInfo::whereNotNull('last_name')->where('last_name', '!=', '')->first();

    if (! $info) {
        $this->markTestSkipped('No PDS personal info is available to export.');
    }

    $country = Country::where('name', 'Australia')->first();

    $info->update([
        'sex' => 1,
        'civil_status' => 'Married',
        'citizenship' => $country ? 1 : 0,
        'citizenship_country_id' => $country?->id,
    ]);

    $employee = User::findOrFail($info->user_id);

    $response = $this->withSession(pdsExportSession($employee))
        ->get(route('pds.print'))
        ->assertSuccessful();

    $path = tempnam(sys_get_temp_dir(), 'pds_test_');
    copy($response->getFile()->getPathname(), $path);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    expect($zip->locateName('xl/drawings/vmlDrawing1.vml'))->not->toBeFalse();
    expect($zip->locateName('xl/ctrlProps/ctrlProp1.xml'))->not->toBeFalse();

    $vml = $zip->getFromName('xl/drawings/vmlDrawing1.vml');
    expect($vml)->toContain('ObjectType="Drop"')
        ->toContain('ObjectType="Checkbox"')
        ->toContain('<x:Checked/>');

    expect(preg_match('/Male<\/font>.*?<\/v:textbox>.*?<x:Checked\/>.*?<\/v:shape>/s', $vml))->toBe(1);
    expect(preg_match('/Married<\/font>.*?<\/v:textbox>.*?<x:Checked\/>.*?<\/v:shape>/s', $vml))->toBe(1);

    if ($country) {
        expect($vml)->toContain('Dual Citizenship');
        expect(preg_match('/Dual Citizenship<\/font>.*?<\/v:textbox>.*?<x:Checked\/>.*?<\/v:shape>/s', $vml))->toBe(1);
        expect($zip->getFromName('xl/ctrlProps/ctrlProp1.xml'))->toContain('val="11"');
    }

    $zip->close();

    $sheet = IOFactory::load($path)->getSheetByName('C1');
    expect(mb_strtoupper((string) $sheet->getCell('D10')->getValue()))->toBe(mb_strtoupper($info->last_name));
    expect((string) $sheet->getCell('C16')->getValue())->not->toStartWith('☑');
    expect((string) $sheet->getCell('D16')->getValue())->not->toBe('X')
        ->not->toBe('Male')
        ->not->toBe('Female');
    expect((string) $sheet->getCell('D17')->getValue())->not->toBe('Married')
        ->not->toBe('X');
    expect((string) $sheet->getCell('F17')->getValue())->not->toStartWith('☑');
    expect((string) $sheet->getCell('J13')->getValue())->not->toContain('Dual');
    expect((string) $sheet->getCell('L15')->getValue())->not->toContain('Australia');

    if ($country) {
        expect((string) $sheet->getCell('L16')->getValue())->toBe('Australia');
    }

    unlink($path);
});

test('find and replace checks the cell that already contains the label', function () {
    $sheet = (new Spreadsheet)->getActiveSheet();
    $sheet->setCellValue('G17', '☐ Single');
    $sheet->setCellValue('I17', '☐ Married');

    $method = new ReflectionMethod(PdsController::class, 'checkPdsBoxByText');
    $method->invoke(new PdsController, $sheet, '☐ Married', '☑ Married');

    expect($sheet->getCell('G17')->getValue())->toBe('☐ Single');
    expect($sheet->getCell('I17')->getValue())->toBe('☑ Married');
});

test('find and replace reads rich text and no-space ballot labels', function () {
    $sheet = (new Spreadsheet)->getActiveSheet();
    $rich = new RichText;
    $rich->createText('☐Female');
    $sheet->setCellValue('D16', $rich);

    $method = new ReflectionMethod(PdsController::class, 'checkPdsBoxByText');
    $method->invoke(new PdsController, $sheet, '☐ Female', '☑ Female');

    expect($sheet->getCell('D16')->getValue())->toBe('☑ Female');
});
