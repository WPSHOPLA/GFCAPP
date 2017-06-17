<?php
$unlock_pdf = exec('"C:\Program Files\gs\gs9.20\bin\gswin64c" -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=C:\Users\Andrei\Desktop\UniServerZ\www\pdfs\data_unlocked.pdf -c .setpdfwrite -f C:\Users\Andrei\Desktop\UniServerZ\www\pdfs\data_locked.pdf'); 


include "pdfparser/vendor/autoload.php";
$parser = new \Smalot\PdfParser\Parser();
$pdf = $parser->parseFile('pdfs/data_unlocked.pdf');
$pdf_parsed = $pdf->getText();

echo $pdf_parsed;
?>

