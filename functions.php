<?php

include('Helper.class.php');
$helper = new Helper;

$key_array_freq_first_letters = array('t','a','s','h','w','i','o','b','m','f','c','l','d','p','n','e','g','r','y','u','v','j','k','q','z','x');
$key_array_freq_second_letters = array('h','o','e','i','a','u','n','r','t');
$key_array_freq_total_letters = array('e','t','a','o','i','n','s','h','u','d','r','l','c','m','f','g','y','p','w','b','v','k','x','j','q','z');

$base64_code = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','+','/');
$base16_code = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');

/**
 * String conversion from hex to base64
 *
 * @param string $str_to_conv
 * 
 * @author Luca Brognara
 * @date May 2015
 * @return string
 */
function hex_to_base64($str_to_conv) {

	$bin_cod = '';

	$bit = 4;

	for($i=0; $i<strlen($str_to_conv); $i++) {
		foreach ($base16_code as $key => $value) {
			if ($str_to_conv[$i]==$value) {
				$result = $key;
				$byte = '';
				for ($j=0; $j<$bit; $j++) {
					$byte = ($result%2).$byte;
					$result = floor($result/2);
				}
				$bin_cod .= $byte;
			}
		}
	}

	$str_ecoded = '';
	for ($p=1; $p<=strlen($bin_cod); $p++){
		if ($p%6==0) {
			$seibit = substr($bin_cod, $p-6, 6);
			$key_for_base64_code = 0;
			for ($q=0; $q<strlen($seibit); $q++){
				$key_for_base64_code = $key_for_base64_code + $seibit[$q]*pow(2,(5-$q));
			}
			$str_ecoded .= $base64_code[$key_for_base64_code];
		}
	}

	return $str_ecoded;
}

/*
 * String conversion from base64 to hex
 *
 * @param string $str_to_conv
 * 
 * @author Luca Brognara
 * @date May 2015
 * @return string
 */
function base64_to_hex($str_to_conv) {

	$bin_cod = '';

	$bin_string = '';
	for ($i=0; $i<strlen($str_to_conv); $i++) { 
		$base_64_key = array_search($str_to_conv[$i], $base64_code);
		$bin_string .= sprintf("%06d", decbin($base_64_key));
	}

	$hex_string = '';
	for ($i=0; $i < strlen($bin_string); $i += 4) { 
		$hex_string .= base_convert(substr($bin_string, $i, 4), 2, 16);
	}

	return $hex_string;
}

/*
 * Bit to bit XOR of given strings
 *
 * @param string $str1
 * @param string $str2
 * 
 * @author Luca Brognara
 * @date May 2015
 * @return string
 */
function fixed_xor($str1, $str2, $bit=4) {

	$str1 = strtolower($str1);
	$str2 = strtolower($str2);

	$xor_hex = '';
	for($i=0; $i<strlen($str1); $i++) {
		$char_str1_hex_to_bin = sprintf('%0'.$bit.'d', base_convert($str1[$i], 16, 2));
		$char_str2_hex_to_bin = sprintf('%0'.$bit.'d', base_convert($str2[$i], 16, 2));

		$xor_bin_partial = '';
		for($j=0; $j<$bit; $j++) {
			$xor_bin_partial .= ($char_str1_hex_to_bin[$j] xor $char_str2_hex_to_bin[$j]) ? '1' : '0';

			if ((($j+1)%4) == 0) {
				$xor_hex .= base_convert($xor_bin_partial, 2, 16);
			}
		}
	}

	return $xor_hex;
}

/**
 * Decodifica la stringa in $input con un singolo carattere
 *
 * @author Luca Brognara
 * @date Maggio 2015
 *
 * @param string $input Stringa da decodificare
 * @param int $base Base in cui è codificata la stringa
 * @param array $ascii_key (optional) Range di caratteri in cui cercare la chiave
 * @param array $ord (optional) Misura la frequenza dei caratteri nella stringa per poi ordinare i risultati
 * @param string $langage (optional) Lingua in cui dovrebbe essere scritto il messaggio decodificato
 * @param float $avarage_word_range_tollerance (optional) Tolleranza della media della lunghezza delle parole nel linguaggio specificato
 * 
 * @return array Ritorna le stringhe codificate più probabili e rispettive chiavi
 */
function single_byte_xor_cipher($input, 
								$base, 
								$ascii_key = array('min'=>0, 'max'=>255), /* range for ascii key*/
								$ord = array('total_letter'=>0,
											 'first_letter'=>0,
											 'second_letter'=>0),
								$language = 'EN',
								$avarage_word_range_tollerance = 0.5) {

	$bit = 8;

	$ascii_from = $ascii_key['min'];
	$ascii_to   = $ascii_key['max'];

	$array_freq_first_letters  = init_array_with($key_array_freq_first_letters);
	$array_freq_second_letters = init_array_with($key_array_freq_second_letters);
	$array_freq_letters_input  = init_array_with($key_array_freq_total_letters);

	$array_input_splitted = $helper->unique_split_to_array($input, $helper->bit_for_number_by_base(255, $base));

	// per ogni codice ascii si fa lo xor con la stringa
	$results = array();
	for ($ascii = $ascii_from; $ascii<$ascii_to+1; $ascii++) {
		$ascii_bin = sprintf('%0'.$bit.'d',decbin($ascii));
		$string_finale = $input;

		foreach ($array_input_splitted as $key => $item) {
			$item_bin = sprintf('%0'.$bit.'d', base_convert($item, $base, 2));

			$xor_hex_partial = '';
			for($j=0; $j<$bit; $j++) {
				$xor_hex_partial .= ($ascii_bin[$j] xor $item_bin[$j]) ? '1' : '0';
			}

			$xor_hex_partial_to_dec = bindec($xor_hex_partial);

			$character = chr($xor_hex_partial_to_dec);
			if (preg_match('/^[0-9a-zA-Z \r\n\'?*!+-]$/', $character)==1) {
				$string_finale = str_replace($item, $character, $string_finale);
			}
		} // end for byte hex

		$numeri = 0;
		for ($i=0; $i<strlen($string_finale); $i++) {
			// controllo quanti numeri ci sono nella stringa data
			if ((ord($string_finale[$i])>=48)&&(ord($string_finale[$i])<=57)) { 
				$numeri++;
			}
		}

		$split_words = explode(' ', $string_finale);

		$avarage_length_words = strlen($string_finale)/count($split_words);
		$margin_bottom_avarage = 5.1 - 5.1*(5/10);
		$margin_top_avarage = 5.1 + 5.1*(5/10);

		if (($numeri<(strlen($string_finale)*(1/2)))) {

			$results[] = array('string_decodificata' => htmlentities($string_finale),
							   'chiave' => $ascii." => '".htmlentities(chr($ascii))."'");
		}
	} // end for caratteri ascii

	/////////////////////////////////////////////////////////
	////////////////////ANALISI OPZIONALI////////////////////
	/////////////////////////////////////////////////////////

	// rilevamento frequenze lettere totali e ordinamento
	if ($ord['total_letter']&&(count($results)>1)) {
		$i = 0;
		foreach ($results as $record) {
			for($j=0; $j<strlen($record['string_decodificata']); $j++) {
				$character = $record['string_decodificata'][$j];
				if (in_array(strtolower($character), $this->key_array_freq_total_letters)) {
					$array_freq_letters_input[strtolower($character)]++;
				}
			}

			arsort($array_freq_letters_input);

			$results[$i]['frequenze_lettere_totali'] = $array_freq_letters_input;

			$frequenze_lettere_totali_keys = $helper->arraykeys_to_string($array_freq_letters_input);
			$results[$i]['brognara_distance'] = $helper->brognara_distance('etaoinshudrlcmfgypwbvkxjqz',$frequenze_lettere_totali_keys);

			$array_freq_letters_input = init_array_with($key_array_freq_total_letters);
			$i++;
		}
		usort($results, function($a, $b) {
			$el1 = $a['brognara_distance'];
			$el2 = $b['brognara_distance'];

			if ($el1 == $el2) return 0;

    		return ($el1 < $el2) ? -1 : 1;
		});
	}

	// rilevamento frequenze prime lettere e ordinamento
	else if ($ord['first_letter']&&(count($results)>1)) {
		$i = 0;
		foreach ($results as $record) {
			for($j=0; $j<strlen($record['string_decodificata']); $j++) {
				$character = $record['string_decodificata'][$j];
				if (in_array(strtolower($character), $this->key_array_freq_first_letters)) {
					$array_freq_first_letters[strtolower($character)]++;
				}
			}
			$results[$i]['frequenze_lettere_iniziali'] = $array_freq_first_letters;
			$array_freq_first_letters = init_array_with($this->key_array_freq_first_letters);
			$i++;
		}

		foreach ($this->key_array_freq_first_letters as $value) {
			usort($results, function($a, $b) use($value) {
				$el1 = $a['frequenze_lettere_iniziali'][$value];
				$el2 = $b['frequenze_lettere_iniziali'][$value];

				if ($el1 == $el2) return 0;

	    		return ($el1 > $el2) ? -1 : 1;
			});
		}
	}

	// rilevamento frequenze seconde lettere e ordinamento
	else if ($ord['second_letter']&&(count($results)>1)) {
		$i = 0;
		foreach ($results as $record) {
			for($j=0; $j<strlen($record['string_decodificata']); $j++) {
				$character = $record['string_decodificata'][$j];
				if (in_array(strtolower($character), $this->key_array_freq_second_letters)) {
					$array_freq_second_letters[strtolower($character)]++;
				}
			}
			$results[$i]['frequenze_lettere_seconda'] = $array_freq_second_letters;
			$array_freq_second_letters = init_array_with($this->key_array_freq_second_letters);
			$i++;
		}

		foreach ($this->key_array_freq_second_letters as $value) {
			usort($results, function($a, $b) use($value) {
				$el1 = $a['frequenze_lettere_seconda'][$value];
				$el2 = $b['frequenze_lettere_seconda'][$value];

				if ($el1 == $el2) return 0;

	    		return ($el1 > $el2) ? -1 : 1;
			});
		}
	}

	return $results;
}

/**
 * Codifica la stringa in $input con la chiave $key facendo lo xor di ogni byte di $input con
 * con il corrispondere di $key.
 * Esempio:
 *
 * Se
 * $input = "abcdefg"
 * $key = "hil"
 *
 * la codifica sarà:
 * a xor h, b xor i, c xor l, d xor h, 
 * e xor i, f xor l, g xor h
 *
 * @param string $input La stringa da codificare
 * @param string $key chiave di codifica
 * 
 * @author Luca Brognara
 * @date Maggio 2015
 * @return string Stringa codificata
 */

function repeating_key_XOR($input, $key) {
	$j = 0;
	$encoded_string = '';
	for ($i=0; $i < strlen($input); $i++) { 
		if ($j==3) {
			$j = 0;
		}
		$char_encoded = fixed_xor_string($input[$i], $key[$j]);
		$encoded_string .= $char_encoded;;

		$j++;
	}

	return $encoded_string;
}

/*
 * Decripta un repeating xor. WIP
 * <pre>
 * array (
 *    "file" => path file
 *    "string" => stringa da decodificare
 *    "kl_min" => keylenght minima possibile
 *    "kl_max" => keylenght massima possibile
 * )
 * </pre>
 *
 * @author Luca Brognara
 * @date Maggio 2015
 *
 * @param array $args
 * 
 * @return string
 */
function break_repeating_key_XOR($args = NULL) {


	if ($args==NULL) {
		return false;
	}

	// INIT PARAMETERS
	$kl_min = 2;
	$kl_max = 10;
	if (isset($args['kl_min'])) {
		$kl_min = $args['kl_min'];
	}
	if (isset($args['kl_max'])) {
		$kl_max = $args['kl_max'];
	}

	// Recupero il testo cifrato a seconda che venga passato direttamente
	// o sia passato l'url di un file
	$cypher = '';
	if ($args['file']!=NULL) {
		$lines = file($args['file'], FILE_IGNORE_NEW_LINES);

		set_time_limit(0);
		foreach($lines as $line) {
			$cypher .= $line;
		}
		set_time_limit(30);
	}
	else if ($args['string']) {
		$cypher = $args['string'];
	}

	//echo $cypher."<br><br>";

	$cypher = $this->base64_to_hex($cypher);
	$array_norm_keylegth = array();
	for ($keylength=2; $keylength <= 40; $keylength++) { 
		$sum = 0;
		$keylength_byte = $keylength*2;
		//echo $keylength."<br>";
		for ($i=0; $i < 4; $i++) { 
			$str1 = substr($cypher, ($keylength_byte*2)*$i, $keylength_byte);
			$str2 = substr($cypher, (($keylength_byte*2)*$i)+$keylength_byte, $keylength_byte);
			//echo "#### ".$str1."<br>";
			//echo "#### ".$str2."<br>";
			//echo "#### ".$this->helper->hamming_distance($str1, $str2)/$keylength."<br><br>";
			$sum = $sum + $this->helper->hamming_distance($str1, $str2)/$keylength_byte;
		}

		//echo "######## ".$sum."<br>";
		//echo "######## ".($sum/4)."<br>"."<br>";

		//echo "###################################<br><br>";
		
		$array_norm_keylegth[] = array('norm_keylength' => $sum/4, 'keylength' => $keylength_byte);
		
	}

	usort($array_norm_keylegth, function($a, $b) {
		$el1 = $a['norm_keylength'];
		$el2 = $b['norm_keylength'];

		if ($el1 == $el2) return 0;

		return ($el1 < $el2) ? -1 : 1;
	});

	//echo "<pre>";
	//print_r($array_norm_keylegth);
	//echo "</pre>";

	$keylength_candidata = $array_norm_keylegth[0]['keylength'];

	$array_blocchi_testo_keylength = $this->helper->unique_split_to_array($cypher, $keylength_candidata*2);

	//echo "<pre>";
	//print_r($array_blocchi_testo_keylength);
	//echo "</pre>";

	$array_string = array();
	for ($i=0; $i<(($keylength_candidata*2)); $i += 2) { 
		$partial_string = '';
		foreach ($array_blocchi_testo_keylength as $string) {
			$partial_string .= substr($string, $i, 2);
		}
		$array_string[] = $partial_string;

		echo "<pre>";
		print_r($this->single_byte_xor_cipher($partial_string, 
										   	  16, 
										   	  array('min'=>0, 'max'=>255),
										   	  array('total_letter'=>1,
													'first_letter'=>0,
													'second_letter'=>0)));
		echo "</pre>";

		echo "<br>################################################</br>";
	}/**/

	echo "<pre>";
	print_r($array_string);
	echo "</pre>";

	// Controllo le ripetizioni di sottostrighe
	/*set_time_limit(0);
	$array_text = array();

	$dim_cypher = strlen($cypher);
	$occurences = array();

	for ($i = floor($dim_cypher/2); $i>0; $i--) {
		$offset_sx = 0;
		$offset_dx = $dim_cypher-$i;
		$finito = false;
		while ((($i <= $offset_sx)||($i<=$offset_dx))
			   &&($offset_sx<=$dim_cypher)
			   &&($offset_dx>=0)) {

			$text = substr($cypher, $offset_sx, $i);

			$occurences = $this->helper->strpos_all($cypher, $text);
			if (count($occurences)>1) {
				$finito = true;
				break;
			}

			$offset_sx++;
			$offset_dx--;
		}

		if ($finito) {
			break;
		}
	}
	set_time_limit(30);

	/*echo $text."<br>";
	print_r($occurences);*/


	// Possibili keylengths e valutazione con distanza di hemming
	/*set_time_limit(0);
	$distance = $occurences[1] - $occurences[0];
	//echo "<br>distance: ".$distance."<br>";

	$keylengths = 0;
	$array_somme = array();
	$smaller_avarage_dist = 0;
	$guessed_keylength = 0;
	for ($i=1; $i <= $distance; $i++) { 
		//echo "<br>#### distance%i: ".($distance%$i)."<br>";
		if (($distance%$i)==0) {
			$keylength = $distance/$i; // sottomultipli $distance

			if ($keylength>2) {
				//echo "<br>######## keylengths: ".($keylengths)."<br>";
				$array_blocchi_testo = $this->helper->unique_split_to_array($cypher, $keylength);
				//print_r($array_blocchi_testo);
				$d_crypt = 0;
				$somma_d_crypt = 0;
				for ($j=0; $j < count($array_blocchi_testo)-1; $j += 2) { 
					$str1 = $array_blocchi_testo[$j];
					$str2 = $array_blocchi_testo[$j+1];

					if (strlen($str1) == strlen($str2)) {
						$d_crypt = $this->helper->hamming_distance($str1, $str2)/$keylength;
					}

					$somma_d_crypt = $somma_d_crypt + $d_crypt;

				}
				//echo "<br>######## j-2".($j-2)."<br>";
				$array_somme[] = array("avar_norm"=>$somma_d_crypt/($j-2), "keylength"=>$keylength);
			}
		}

	}
	set_time_limit(30);

	usort($array_somme, function($a, $b) {
		$el1 = $a['avar_norm'];
		$el2 = $b['avar_norm'];

		if ($el1 == $el2) return 0;

		return ($el1 < $el2) ? -1 : 1;
	});

	$keylength_candidata = $array_somme[0]['keylength'];

	$array_blocchi_testo_keylength = $this->helper->unique_split_to_array($cypher, $keylength_candidata);

	/*echo "<pre>";
	print_r($array_blocchi_testo_keylength);
	echo "</pre>";*/

	/*$array_string = array();
	for ($i=0; $i<$keylength_candidata; $i++) { 
		$partial_string = '';
		foreach ($array_blocchi_testo_keylength as $string) {
			$partial_string .= $string[$i];
		}
		$array_string[$i] = $this->base64_to_hex($partial_string);

		echo "<pre>";
		print_r($this->single_byte_xor_cipher($array_string[$i], 
										   	  16, 
										   	  array('min'=>0, 'max'=>255),
										   	  array('total_letter'=>1,
													'first_letter'=>0,
													'second_letter'=>0))[0]);
		echo "</pre>";

		echo "<br>################################################</br>";
	}

	/*echo "<pre>";
	print_r($array_string);
	echo "</pre>";*/

}
