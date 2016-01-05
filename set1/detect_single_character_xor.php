<?php
include('../functions.php');

//################################# Detect single-character XOR #################################

echo "<strong>########### Detect single-character XOR ###########</strong><br>";
echo "<strong>input:</strong><br>";
echo "file: 4.txt<br>";
echo "<strong>output:</strong><br>";
$lines = file("text/4.txt", FILE_IGNORE_NEW_LINES);

set_time_limit(0);
foreach($lines as $line) {
	$risposte = single_byte_xor_cipher($line,
									   16,
									   array('min'=>0, 'max'=>255),
									   array('total_letter'=>0,
											 'first_letter'=>0,
											 'second_letter'=>0));
	if (count($risposte)>0) {								 
		foreach ($risposte as $value) {
			echo "<pre>";
			printf("\t %s \t %s", $value['string_decodificata'], $value['chiave']); //però non fornisce la chiave
			echo "</pre>";
		}
		echo "<br>";
	}
}
set_time_limit(30);
echo "<br><br>";