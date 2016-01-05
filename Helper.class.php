<?php

abstract class Types
{
    const RETURN_ARRAY  = 0;
    const RETURN_STRING = 1;
}

class Helper {

	public function construct() {

	}
	
	/*
	 * Fornisce il numero di bit necessari a rappresentare il numero in ingresso in funzione dalla base fornita
	 *
	 * @param int $number Numero da rappresentare
	 * @param int $base (optionale) Base in cui è rappresentato il numero passato
	 * @param int $n_bits (optionale) NON UTILIZZABILE, serve solo alla funzione essendo ricorsiva
	 * 
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return int Bit necessari a rappresentare il numero specificato in ingresso
	 */
	public function bit_for_number_by_base($number, $base, $n_bits = 0) {

		$number = floor($number/$base);
		$n_bits++;

		if ($number == 0) {
			return $n_bits;
		}

		return $this->bit_for_number_by_base($number, $base, $n_bits);
	}

	/*
	 * Spezza una stringa in elementi di lunghezza $offset, ripetendoli o meno
	 *
	 * @param string $input Stringa da spezzare
	 * @param int $offset (optionale) Lunghezza elementi
	 * @param boolean $unique (optionale) Se effettivamente si vogliono elementi unici o tutti quelli costituenti la stringa iniziale
	 * @param string $return (optionale) se ritornare un array o una stringa
	 * @param array $wrapper (optionale) Lunghezza elementi
	 * @param array $array_char (optionale) NON UTILIZZABILE, serve solo alla funzione, essendo ricorsiva, passa con questo parametro il resto della stringa da elaborare
	 * 
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return array Array con gli elementi ricavati dalla stringa
	 */
	public function unique_split_to_array($input,
										   $offset = 2, 
										   $unique = true,
										   $return = Types::RETURN_ARRAY,
										   $wrapper = array('left'=>NULL, 'right'=>NULL),   
										   $array_char = array()) {

		
		if (strlen($input)<$offset) { 
			$character = substr($input, 0, strlen($input));
			if ($character!='') {
				$array_char[] = $character;
			}
			if ($return==1) {
				$string = '';
				foreach ($array_char as $key => $value) {
					$string .= $wrapper['left'].$value.$wrapper['right'];
				}
				return $string;
			}
			return $array_char; 
		}

		$character = substr($input, 0, $offset);
		
		if ($unique) {
			$input = str_replace($character, '', $input);
		}
		else {
			$input = substr($input, 1);
		}

		$array_char[] = $character;
		return $this->unique_split_to_array($input, $offset, $unique, $return, $wrapper, $array_char);
	}

	public function string_to_array($str) {
		return $this->unique_split_to_array($str, 1, false);
	}

	/*
	 * Converte una stringa nel suo corrispondente ascii binario, di default a 8 bit
	 *
	 * @param string $string La stringa da convertire
	 * @param string $bit quanti bit per la codifica
	 * 
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string Stringa convertita in binario
	 */
	public function string_to_bin($string, $bit=8) {
		$splitted_string = $this->unique_split_to_array($string, 1, false);

		$string = '';
		foreach ($splitted_string as $value) {
			$char_to_bin = sprintf("%0".$bit."d", decbin(ord($value)));
			$string .= $char_to_bin;
		}

		return $string;
	}

	/*
	 * Come strpos ma tira fuori tutte le posizioni della sottostringa cercata, ritorna un array di posizioni
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 *
   	 * @param string $mystring 
   	 * @param string $findme 
	 *
	 * @return array
	 */
	public function strpos_all($mystring, $findme) {
		$position = array();
		$offset = 0;
		while(strpos($mystring, $findme) !== false) {
			$first_pos = $offset + strpos($mystring, $findme);
			$position[] = $first_pos;
			$offset = $first_pos+(strlen($findme));
			$mystring = substr($mystring, $offset);
		}
		return $position;
	}

	/*
	 * Determina se la stringa passata è in binario oppure no
   	 *
   	 * @param string $string 
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return boolean
	 */
	public function is_binary($string) {
		if (!preg_match("/[^01]/", $string)) {
			return true;
		}
		return false;
	}

	/*
	 * Ritorna la Hamming distance tra due stringhe uguali. La distanza di Hamming misura il numero 
	 * di sostituzioni necessarie per convertire una stringa nell'altra.
	 * Esempio:
	 * La distanza di Hamming tra 10[1]1[1]01 e 10[0]1[0]01 è 2.
   	 * La distanza di Hamming tra 2[1][4]3[8]96 e 2[2][3]3[7]96 è 3.
   	 *
   	 * @param string $str1 
	 * @param string $str2 
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return int|boolean
	 */
	public function hamming_distance($str1, $str2) {
		if (strlen($str1)!=strlen($str2)) {
			return false;
		}

		// binarizziamo le stringhe
		if (!$this->is_binary($str1)) {
			$str1 = $this->string_to_bin($str1, 8);
		}

		if (!$this->is_binary($str2)) {
			$str2 = $this->string_to_bin($str2, 8);
		}

		// calcolo la distanza
		$distanza = 0;
		for ($i=0; $i<strlen($str1); $i++) { 
			if ($str1[$i]!=$str2[$i]) {
				$distanza++;
			}
		}

		return $distanza;
	}

	/*
	 * NON ANCORA UTLIZZABILE
	 * Misura quanto disordinata è una stringa
   	 *
   	 * @param string $campione 
	 * @param string $str 
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string
	 */
	public function brognara_distance($campione, $str) {

		if (strlen($campione)!=strlen($str)) {
			return false;
		}

		$caos = 0; 
		for ($i=0; $i<strlen($campione); $i++) { 

			$positions = $this->strpos_all($str, $campione[$i]);
			$peso = (strlen($campione)-$i);
			$valuable_distance = 0;

			for ($j=0; $j<count($positions); $j++) {
				$offset_pos = abs($positions[$j]-$i);
				if (($offset_pos<$valuable_distance)||($j==0)) {
					$valuable_distance = $offset_pos;
				}
			}

			$caos += $valuable_distance*$peso;
		}

		return $caos;
	}

	/*
	 * Scambia di posto due caratteri in una stringa
   	 *
   	 * @param string $str 
	 * @param integer $pos_start 
	 * @param integer $pos_finish 
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string
	 */
	public function swap_char_position($str, $pos_start, $pos_finish) {

		if ($pos_start>=$pos_finish){
			$tmp = $pos_start;
			$pos_start = $pos_finish;
			$pos_finish = $tmp;
		}

		$char1  = substr($str, $pos_start, 1);
		$char2  = substr($str, $pos_finish, 1);

		$str = substr_replace($str, $char2, $pos_start, 1);
		$str = substr_replace($str, $char1, $pos_finish, 1);

		return $str;
	}

	/*
	 * Scambia di posto due caratteri in una stringa
   	 *
   	 * @param string $str 
	 * @param integer $i 
	 * @param integer $j 
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return void
	 */
	public function swap_char_position_ref(&$str,$i,$j) {
	    $temp = $str[$i];
	    $str[$i] = $str[$j];
	    $str[$j] = $temp;
	}  

	/*
	 * Calcolo fattoriale ricorsivo
   	 *
   	 * @param integer $num
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return integer
	 */
	public function fattoriale($num) {
		if ($num==1) {
			return 1;
		}

		return $num*($this->fattoriale($num-1));
	}

	/*
	 * Calcolo fattoriale con algoritmo tail recoursive
   	 *
   	 * @param integer $num
   	 * @param integer $running_fact (optionale) NON UTILIZZABILE, serve solo alla funzione
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return integer
	 */
	public function fattoriale_tail_rec($num, $running_fact=1) {
		if ($num==1) {
			return $running_fact;
		}

		return $this->fattoriale_tail_rec(($num-1), $running_fact*$num);
	}

	/*
	 * Calcolo permutazioni di una string
   	 *
   	 * @param integer $str
   	 * @param integer $i (optionale) NON UTILIZZABILE, serve solo alla funzione
   	 * @param integer $n (optionale) NON UTILIZZABILE, serve solo alla funzione
	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string
	 */
	public function permute_1($str,$i,$n) {
	   	if ($i == $n) {
	   		return $str."<br>";
	    }

        for ($j = $i; $j < $n; $j++) {

          	$this->swap_char_position_ref($str,$i,$j);

          	$this->permute($str, $i+1, $n);
       	}
	} 

	/*
	 * Calcolo permutazioni di una string
   	 *
   	 * @param string|array $arg
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return array
	 */
	public function permute_2($arg) {
		$array = is_string($arg) ? str_split($arg) : $arg;

		$func = function($array) use (&$func) {
			if (1 === count($array)) {
		        return $array;
		    }

		    $result = array();
		    foreach($array as $key => $item) {
		        foreach($func(array_diff_key($array, array($key => $item))) as $p) {
		            $result[] = $item . $p;
		        }
		    }

		    return array_unique($result);
		};

		return $func($array);
	}

	/*
	 * Calcolo permutazioni di una string
   	 *
   	 * @param string|array $arg
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return array
	 */
	public function permute_3($str) {

		$array = is_string($str) ? str_split($str) : $str;

		$func = function($array, &$perm = array(), $fixed = NULL) use(&$func) {
			$strlen = count($array);

			if ($strlen==1) {
				reset($array);
				$perm[] = $array[key($array)].$fixed;
				return $perm;
			}

			for ($i=0; $i < ($strlen); $i++) {
				$char = array_pop($array);
				$func($array, $perm, $fixed.$char);
				reset($array);
				$array =  array((key($array)-1) => $char) + $array;
			}
		};
		
		$func($array, $result);
		return array_unique($result);
	}

	/*
	 * Una array shift normale.
   	 *
   	 * @param array $array
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return array
	 */
	public function array_shift_custom(&$array) {
		$item = $array[0];
		unset($array[0]);
		return $item;
	}

	/*
	 * Come array shift ma con le stringhe
   	 *
   	 * @param string $str
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return array
	 */
	public function string_shift(&$str) {
		$char = $str[0];
		$str = substr($str, 1);
		return $char;
	}

	/*
	 * Genera una stringa dagli indici di un array
   	 *
   	 * @param array $array
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string
	 */
	public function arraykeys_to_string($array) {
		$string = '';
		foreach ($array as $key => $value) {
			$string .= $key;
		}
		return $string;
	}

	/*
	 * Genera una stringa dai valori di un array
   	 *
   	 * @param array $array
   	 *
	 * @author Luca Brognara
	 * @date Maggio 2015
	 * @return string
	 */
	public function array_to_string($array) {
		$string = '';
		foreach ($array as $value) {
			$string .= $value;
		}
		return $string;
	}

	/**
	 * Return initialized array full of 0 with keys the given array
	 *
	 * @param array $key_array array of keys
	 * 
	 * @author Luca Brognara
	 * @date May 2015
	 * @return array
	 */
	private function init_array_with($key_array) {
		$array = array();
		foreach ($key_array as $key => $value) {
			$array[$value] = 0;
		}

		return ($array);
	}

}
