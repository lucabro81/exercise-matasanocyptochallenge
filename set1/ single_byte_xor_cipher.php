<?php
include('../functions.php');

//################################# Single-byte XOR cipher #################################

echo "<strong>########### Single-byte XOR cipher ###########</strong><br>";
echo "<strong>input:</strong><br>";
echo "1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736<br>";
echo "<strong>output:</strong><br>";
$risposte = single_byte_xor_cipher('1b37373331363f78151b7f2b783431333d78397828372d363c78373e783a393b3736',
								   16,
								   array('min'=>0, 'max'=>255),
								   array('total_letter'=>1,
										 'first_letter'=>0,
										 'second_letter'=>0));
foreach ($risposte as $value) {
	echo "<pre>";
	printf("\t %s \t %s", $value['string_decodificata'], $value['chiave']); //però non fornisce la chiave
	echo "</pre>";
}
echo "<br><br>";