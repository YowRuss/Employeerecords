<?php

use PhpOffice\PhpWord\TemplateProcessor;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$tp = new TemplateProcessor(storage_path('app/templates/SALN.docx'));
print_r($tp->getVariables());
