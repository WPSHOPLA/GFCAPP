<?php
include "pdfparser/vendor/autoload.php";
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile('pdfs/data.pdf');

$pdf_parsed = $pdf->getText();
str_replace('<br />', '', $pdf_parsed);

//GET CHAPTERS AND TITLES
preg_match_all('/^(CHAPTER\s[A-Z0-9]+)\s+([A-Z0-9 ]+)$/m', $pdf_parsed, $chapters_titles, PREG_SET_ORDER);
foreach($chapters_titles as $chapter_title) { // 0 - full string, 1 - chapter, 2 - title
	echo '<b>' . $chapter_title[1] . '</b><br />';
	echo $chapter_title[2] . '<br />';
}

echo '<br><br>';

//GET SECTIONS AND THEIR NAME
preg_match_all('/(SECTION)\s([A-Z0-9]+\s?)([\sA-Z0-9,-]+\s)/', $pdf_parsed, $sections, PREG_SET_ORDER);

foreach($sections as $section) { // 0 - full string, 1 - section, 2 - sesction num, 3 - section name	
	echo '<b>' . $section[1] . ' ' . $section[2] . '</b><br />';
	echo $section[3] . '<br />';
	
	$section_num = preg_replace("/[^A-Za-z0-9]/", '', $section[2]);
	//echo '<br>""""""' . $section_num . '"""""<br>';
	$subsection_pattern = '/^(' . $section_num . ')(\.[0-9]+)+(\s)([A-Z][A-Z-a-z0-9\s]+\.)/m';

	preg_match_all($subsection_pattern, $pdf_parsed, $sub_sections, PREG_SET_ORDER);
	//var_dump($sub_sections);
	foreach($sub_sections as $sub_section) {
		echo $sub_section[0] . '<br />';
		
	}
	echo '<br />';
}

//chapters ^(CHAPTER\s[A-Z0-9\s]+)$
//^(CHAPTER\s[A-Z0-9]+)
?>