<?php

namespace Toml;

/**
* A TOML parser for PHP
*/
class Parser
{
	static public function fromString($s)
	{

	}

	static public function fromFile($path)
	{
		if(!is_file($path) || !is_readable($path)) {
			throw new \RuntimeException(sprintf('`%s` does not exist or cannot be read.', $path));
		}

		return self::fromString(file_get_contents($path));
	}
}