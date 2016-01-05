<?php
include('../functions.php');

//################################# Implement repeating-key XOR #################################

echo "<strong>########### Implement repeating-key XOR ###########</strong><br>";
echo "<strong>input:</strong><br>";
echo "<pre>";
echo "Burning 'em, if you ain't quick and nimble\nI go crazy when I hear a cymbal";
echo "</pre>";
echo "<strong>output:</strong><br>";
echo "<pre>";
echo repeating_key_XOR("Burning 'em, if you ain't quick and nimble\nI go crazy when I hear a cymbal","ICE");
echo "</pre>";
echo "<br><br>";