<?php

$xml = file_get_contents('storage/app/templates/SALN_extracted/word/document.xml');
$text = strip_tags($xml);
echo substr($text, 0, 1000);
