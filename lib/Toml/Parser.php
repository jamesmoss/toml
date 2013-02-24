<?php

namespace Toml;

/**
* A TOML parser for PHP
*/
class Parser
{
	protected $raw;
	protected $doc = array();
	protected $group;
	protected $lineNum = 0;

	public function __construct($raw)
	{
		$this->raw = $raw;
		$this->group = &$this->doc;
	}

	static public function fromString($s)
	{
		$parser = new self($s);

		return $parser->parse();
	}

	static public function fromFile($path)
	{
		if(!is_file($path) || !is_readable($path)) {
			throw new \RuntimeException(sprintf('`%s` does not exist or cannot be read.', $path));
		}

		return self::fromString(file_get_contents($path));
	}

	public function parse()
	{
		$inString   = false;
		$arrayDepth = 0;
		$inComment  = false;
		$buffer     = '';

		// Loop over each character in the file, each line gets built up in $buffer
		// We can't simple explode on newlines because arrays can be declared
		// over multiple lines.
		for($i = 0; $i < strlen($this->raw); $i++) {
			$this->lineNum++;
			$char = $this->raw[$i];

			// Detect start / end of string boundries
			if($char === '"' && $this->raw[$i-1] !== '\\') {
				$inString = !$inString;
			}

			// Detect start of comments
			if($char === '#' && !$inString) {
				$inComment = true;
			}

			if($char === '[' && !$inString) {
				$arrayDepth++;
			}

			if($char === ']' && !$inString) {
				$arrayDepth--;
			}

			// At a line break or the end of the document see whats going on
			if($char === "\n") {
				// Line breaks arent allowed inside strings
				if($inString) {
					throw new \Exception('Multiline strings are not supported.');	
				}

				if($arrayDepth === 0) {
					$this->processLine($buffer);
					$inComment = false;
					$buffer = '';
					continue;
				}
			}

			// Don't append to the buffer if we're inside a comment
			if($inComment) {
				continue;
			}

			$buffer.= $char;
		}

		if($arrayDepth > 0) {
			throw new \Exception(sprintf('Unclosed array on line %s', $this->lineNum));
		}

		// Process any straggling content left in the buffer
		$this->processLine($buffer);

		return $this->doc;
	}

	protected function processLine($raw)
	{
		// replace new lines with a space to make parsing easier down the line.
		$line = str_replace("\n", ' ', $raw);
		$line = trim($line);
		
		// Skip blank lines
		if(empty($line)) {
			return;
		}

		// Check for groups
		if(preg_match('/^\[([^\]]+)\]$/', $line, $matches)) {
			$this->setGroup($matches[1]);
			return;
		}

		// Look for keys
		if(preg_match('/^(\S+)\s*=\s*(.+)/u', $line, $matches)) {
			$this->group[$matches[1]] = $this->parseValue($matches[2]);
			return;
		}

		throw new \Exception(sprintf('Invalid TOML syntax `%s` on line %s.', $raw, $this->lineNum));
	}

	protected function stripComments($line)
	{
		$output = explode('#', $line);

		return trim($output[0]);
	}

	protected function setGroup($keyGroup)
	{
		$parts = explode('.', $keyGroup);

		$this->group = &$this->doc;
		foreach($parts as $part) {
			if(!isset($this->group[$part])) {
				$this->group[$part] = array();
			} elseif(!is_array($this->group[$part])) {
				throw new \Exception(sprintf('%s has already been defined.', $keyGroup));
			}

			$this->group = &$this->group[$part];
		}
	}

	protected function parseValue($value)
	{
		// Detect bools
		if($value === 'true' || $value === 'false') {
			return $value === 'true';
		}

		// Detect floats
		if(preg_match('/^\-?\d*?\.\d+$/', $value)) {
			return (float)$value;
		}

		// Detect integers
		if(preg_match('/^\-?\d*?$/', $value)) {
			return (int)$value;
		}

		// Detect string
		if(preg_match('/^"(.*)"$/u', $value, $matches)) {
			return $this->parseString($value);
		}
		
		// Detect datetime
		if(preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})Z$/', $value)) {
			return new \Datetime($value);
		}

		// Detect arrays
		if(preg_match('/^\[(.*)\]$/u', $value)) {
			return $this->parseArray($value);
		}
		
		throw new \Exception(sprintf('Unknown primative for `%s` on line %s.', $value, $this->lineNum));
	}

	protected function parseString($string)
	{
		$string = trim($string, '"');

		return strtr($string, array(
			'\\0'  => "\0",
			'\\t'  => "\t",
			'\\n'  => "\n",
			'\\r'  => "\r",
			'\\"'  => '"',
			'\\\\' => '\\',
		));
	}

	protected function parseArray($array)
	{
		// strips the outer wrapping [ and ] characters and and whitespace from the strip
		$array = preg_replace('/^\s*\[\s*(.*)\s*\]\s*$/um', "$1", $array);

		$depth            = 0;
		$buffer           = '';
		$result           = array();
		$searchEndOfArray = false;
		$insideString     = false;
		// TODO: This is a duplicate of the logic in the parse() method.
		for($i = 0; $i < strlen($array); $i++) {
			
			if($array[$i] === '[') {
				$depth++;
				$searchEndOfArray = $depth;
			}

			if($array[$i] === ']') {
				if($searchEndOfArray === $depth) {
					$searchEndOfArray = false;
				}
				$depth--;
			}

			if($array[$i] === '"' && $array[$i-1] !== '\\') {
				$insideString = !$insideString;
			}

			if(!$insideString && $array[$i] === ',' && false === $searchEndOfArray ) {
				$result[] = $this->parseValue(trim($buffer));
				$buffer = '';
				continue;
			}

			$buffer.= $array[$i];
		}

		// Array hasnt been closed properly
		if($searchEndOfArray !== false) {
			throw new \Exception(sprintf('Unclosed array on line %s', $this->lineNum));
		}

		// whatever meaningful text left in the buffer should be the last element
		if($buffer = trim($buffer)) {
			$result[] = $this->parseValue($buffer);
		}

		return $result;
	}
}