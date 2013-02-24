<?php

namespace Toml;

class ParserTest extends \PHPUnit_Framework_TestCase
{
	public function testParsingStrings()
	{
		$p = Parser::fromString('title = "TOML example"');
		$this->assertEquals($p, array('title' => 'TOML example'));
	}

	public function testParsingStringsWithLineBreaks()
	{
		$p = Parser::fromString('bio = "PHP Developer\nLives in Brighton, England."');
		$this->assertEquals($p, array('bio' => "PHP Developer\nLives in Brighton, England."));
	}

	public function testParsingIntegers()
	{
		$p = Parser::fromString('age = 27');
		$this->assertEquals($p, array('age' => 27));
	}

	public function testParsingFloats()
	{
		$p = Parser::fromString('pi = 3.14');
		$this->assertEquals($p, array('pi' => 3.14));
	}

	public function testParsingDates()
	{
		$p = Parser::fromString('dob = 1985-10-10T07:00:00Z');
		$this->assertEquals($p, array('dob' => new \Datetime('1985-10-10T07:00:00Z')));
	}

	public function testParsingBoolean()
	{
		$p = Parser::fromString('enabled = true');
		$this->assertEquals($p, array('enabled' => true));

		$p = Parser::fromString('enabled = false');
		$this->assertEquals($p, array('enabled' => false));
	}

	public function testParsingArray()
	{
		$p = Parser::fromString('ports = [9001, 9002, 9003]');
		$this->assertEquals($p, array('ports' => array(9001, 9002, 9003)));
	}

	public function testParsingMultiArray()
	{
		$p = Parser::fromString('data = [ [ 1, 2 ], ["a", "b" , "c" ] ]');
		$this->assertEquals($p, array('data' => array(array(1, 2), array('a', 'b', 'c'))));
	}

	/**
	* @expectedException Exception
	*/
	public function testParsingBadValue()
	{
		$p = Parser::fromString('profit = maybe');
	}

	public function testParsingComments()
	{
		$p = Parser::fromString("# This is a comment\ntitle=\"TOML Example\"");
		$this->assertEquals($p, array('title' => 'TOML Example'));
	}

	public function testLoadingFromFile()
	{
		$p = Parser::fromFile(__DIR__.'/example.toml');
		$this->assertEquals($p['title'], 'TOML Example');
	}

	public function testKeyGroup()
	{
		$p = Parser::fromString("[main]\nip = \"192.168.1.1\"");
		$this->assertEquals($p, array('main' => array('ip' => '192.168.1.1')));
	}

	public function testNestedKeyGroup()
	{
		$p = Parser::fromString("[main.beta]\nip = \"192.168.1.1\"");
		$this->assertEquals($p, array('main' => array('beta' => array('ip' => '192.168.1.1'))));
	}
}