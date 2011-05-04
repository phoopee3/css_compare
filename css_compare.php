<?php
/**
 * CSS Compare
 */

if ($_POST['submit'] == 'Compare') {

	function breakCSS($css) {
		preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr);

		$result = array();
		foreach ($arr[0] as $i => $x) {
			$selector = trim($arr[1][$i]);
			$rules = explode(';', trim($arr[2][$i]));
			$result[$selector] = array();
			foreach ($rules as $strRule) {
				if (!empty($strRule)) {
					$rule = explode(":", $strRule);
					$result[$selector][][trim($rule[0])] = trim($rule[1]);
				}
			}
		}
		return $result;
	}

	$file_a = file_get_contents($_FILES['filea']['tmp_name']);
	$file_b = file_get_contents($_FILES['fileb']['tmp_name']);
//	echo "contents of file a:<br/><textarea style='height:300px;width:100%;'>$file_a</textarea><hr/>";
//	echo "contents of file b:<br/><textarea style='height:300px;width:100%;'>$file_b</textarea><hr/>";

	// strip new lines
	$file_a = str_replace(array("\r", "\r\n", "\n", "\t"), '', $file_a);
	$file_b = str_replace(array("\r", "\r\n", "\n", "\t"), '', $file_b);
	
	// remove comments
	$file_a = preg_replace("!/\*(.|[\r\n])*?\*/!", "", $file_a);
	$file_b = preg_replace("!/\*(.|[\r\n])*?\*/!", "", $file_b);

	echo "<pre>";

	$css_a = breakCSS($file_a);
	$css_b = breakCSS($file_b);

//	print_r($css_a);
//	print_r($css_b);

	// exists in both
	$selectors_in_both = array_intersect(array_keys($css_a), array_keys($css_b));

	// exists in a, but not in b
	$selectors_in_a = array_diff(array_keys($css_a), array_keys($css_b));

	// exists in b, but not a
	$selectors_in_b = array_diff(array_keys($css_b), array_keys($css_a));

//	echo "selectors in both\n";print_r($selectors_in_both);
//	echo "selectors in a\n";print_r($selectors_in_a);
//	echo "selectors in b\n";print_r($selectors_in_b);

//	echo "\n";
	// compare selectors in both and see if they have the same declarations
	foreach($selectors_in_both as $selector_in_both) {
//		echo "looking at cssa $selector_in_both\n";
		$all_same_declarations = 0;
		if (count($css_a[$selector_in_both]) != count($css_b[$selector_in_both])) {
//			echo "Different # of declarations\n";
			$all_same_declarations = 0;
		} else {
			foreach($css_a[$selector_in_both] as $declaration_a) {
				$property_a = key($declaration_a);
				$value_a = current($declaration_a);
//				echo "$property_a : $value_a\n";
//				echo "lookign in cssb for $property_a\n";
				foreach($css_b[$selector_in_both] as $declaration_b) {
	//				print_r($declaration_b);
					if (array_key_exists($property_a, $declaration_b)) {
						// see if they have the same value
						if ($value_a == $declaration_b[$property_a]) {
	//						echo "MATCH css_a[$selector_in_both][$property_a][$value_a] == {$declaration_b[$property_a]}\n";
							$all_same_declarations = $selector_in_both;
						} else {
	//						echo "NO MATCH css_a[$selector_in_both][$property_a][$value_a] != {$declaration_b[$property_a]}\n";
							$all_same_declarations = 0;
							break 2;
						}
					}
				}
//				echo "\n";
			}
		}
		// if everything is the same, the print out the name of the class
		if ($all_same_declarations) {
			echo "$all_same_declarations\n";
		}
	}

} else { ?>
<form enctype="multipart/form-data" method="post">
	File A: <input type="file" name="filea" />
	<br/><br/>
	File B: <input type="file" name="fileb" />
	<br/><br/>
	<input type="submit" name="submit" value="Compare" />
</form>
<?php }