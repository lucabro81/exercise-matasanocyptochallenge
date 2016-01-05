<?php
include('../functions.php');

//################################# Convert hex to base64 #################################

echo "<strong>########### Convert hex to base64 ###########</strong><br>";
echo "<strong>input:</strong><br>";
echo "49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d<br>";
echo "<strong>output:</strong><br>";
echo hex_to_base64('49276d206b696c6c696e6720796f757220627261696e206c696b65206120706f69736f6e6f7573206d757368726f6f6d');
echo "<br><br>";