<?php

$content = file_get_contents('xl_extract/xl/drawings/vmlDrawing1.vml');

preg_match_all('/<v:shape[^>]*>.*?<x:ClientData ObjectType="Checkbox">.*?<x:Anchor>\s*(.*?)\s*<\/x:Anchor>.*?<\/x:ClientData>.*?<\/v:shape>/s', $content, $matches);

foreach ($matches[1] as $idx => $anchorStr) {
    $parts = explode(',', $anchorStr);
    $col = trim($parts[0]);
    $row = trim($parts[2]);

    $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $colLetter = $col < 26 ? $letters[$col] : $letters[floor($col / 26) - 1].$letters[$col % 26];
    $row1 = $row + 1;

    echo 'Checkbox '.($idx + 1)." visually at $colLetter$row1 (Anchor: $anchorStr)\n";
}
