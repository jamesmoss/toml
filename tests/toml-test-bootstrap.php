#!/usr/bin/php
<?php

// Provides a wrapper around Toml/Parser so that the toml-test (https://github.com/BurntSushi/toml-test) lib can use it.

// Use Composers autoloader
require __DIR__.'/../vendor/autoload.php';

// Read STDIN
$stdin = '';
$f = fopen( 'php://stdin', 'r' );
while( $line = fgets( $f ) ) {
  $stdin.= $line;
}
fclose($f);

// Parse it up
try {
	$toml = Toml\Parser::fromString($stdin);

	// Turn the array into a format expected by toml-test
	function convertToTomlTestFormat($elements, &$result) {

		foreach($elements as $key => $value) {

			$type = is_object($value) ? get_class($value) : gettype($value);
			$type = strtolower($type);
			$type = ($type == 'boolean' ? 'bool' : $type);
			$type = ($type == 'double' ? 'float' : $type);

			if($type == 'array') {
				$result2 = array();
				convertToTomlTestFormat($value, $result2);
				$value = $result2;
			}

			$result[$key] = array(
				'type'  => $type,
				'value' => $value,
			);
		}
	}

	$result = array();
	convertToTomlTestFormat($toml, $result);

	echo json_encode($result);

	exit(0); 
} catch(Exception $e) {
	exit(1); // non 0 status code if there's an error
}