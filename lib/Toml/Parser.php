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
		$lines = explode("\n", $this->raw);

		foreach($lines as $line) {
			$this->processLine($line);
		}

		return $this->doc;
	}

	protected function processLine($raw)
	{
		$line = trim($raw);
		$line = $this->stripComments($line);

		// Skip blank lines
		if(empty($line)) {
			return;
		}

		// Check for groups
		if(preg_match('/^\[([^\]]+)\]/', $line, $matches)) {
			$this->setGroup($matches[1]);
			return;
		}

		// Look for keys
		if(preg_match('/(\S+)\s*=\s*(.+)/u', $line, $matches)) {
			$this->group[$matches[1]] = $this->parseValue($matches[2]);
			return;
		}

		throw new \Exception(sprintf('Invalid TOML syntax `%s`', $raw));
	}

	protected function processKey($line)
	{
		
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
			}

			$this->group = &$pointer[$part];
		}
	}

	public function parseValue($value)
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
			return $this->parseString($matches[1]);
		}
		
	}

	protected function parseString($string)
	{
		return strtr($string, array(
			'\\0'  => "\0",
			'\\t'  => "\t",
			'\\n'  => "\n",
			'\\r'  => "\r",
			'\\"'  => '"',
			'\\\\' => '\\',
		));
	}
}