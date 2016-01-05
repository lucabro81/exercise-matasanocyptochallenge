<?php
include('../functions.php');

//################################# Fixed XOR #################################

echo "<strong>########### Fixed XOR ###########</strong><br>";
echo "<strong>input:</strong><br>";
echo "1c0111001f010100061a024b53535009181c<br>";
echo "686974207468652062756c6c277320657965<br>";
echo "<strong>output:</strong><br>";
echo fixed_xor("1c0111001f010100061a024b53535009181c", "686974207468652062756c6c277320657965");
echo "<br><br>";