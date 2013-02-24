<?php

namespace Toml;

class ParserTest extends \PHPUnit_Framework_TestCase
{
	public function testParsingStrings()
	{
		$p = Parser::fromString('title = "TOML example"');
		$this->assertEquals(array('title' => 'TOML example'), $p);
	}

	/**
	* @expectedException Exception
	*/
	public function testParsingBadStrings()
	{
		$p = Parser::fromString('title = "TOML example'); // Missing closing quote
	}

	public function testParsingStringsWithLineBreaks()
	{
		$p = Parser::fromString('bio = "PHP Developer\nLives in Brighton, England."');
		$this->assertEquals(array('bio' => "PHP Developer\nLives in Brighton, England."), $p);
	}

	public function testParsingIntegers()
	{
		$p = Parser::fromString('age = 27');
		$this->assertEquals(array('age' => 27), $p);
	}

	public function testParsingFloats()
	{
		$p = Parser::fromString('pi = 3.14');
		$this->assertEquals(array('pi' => 3.14), $p);
	}

	public function testParsingDates()
	{
		$p = Parser::fromString('dob = 1985-10-10T07:00:00Z');
		$this->assertEquals(array('dob' => new \Datetime('1985-10-10T07:00:00Z')), $p);
	}

	public function testParsingBoolean()
	{
		$p = Parser::fromString('enabled = true');
		$this->assertEquals(array('enabled' => true), $p);

		$p = Parser::fromString('enabled = false');
		$this->assertEquals(array('enabled' => false), $p);
	}

	public function testParsingArray()
	{
		$p = Parser::fromString('ports = [9001, 9002, 9003]');
		$this->assertEquals(array('ports' => array(9001, 9002, 9003)), $p);
	}

	public function testParsingMultiArray()
	{
		$p = Parser::fromString('data = [ [ 1, 2 ], ["a", "b" , "c" ] ]');
		$this->assertEquals(array('data' => array(array(1, 2), array('a', 'b', 'c'))), $p);
	}

	public function testParsingMultiArrayOverMultipleLines()
	{
		$p = Parser::fromString("data = [ \n[ 1, 2 ], \n[true, \nfalse, true,]\n]");
		$this->assertEquals(array('data' => array(array(1, 2), array(true, false, true))), $p);
	}

	public function testParsingMultiArrayWithTrailingCommas()
	{
		$p = Parser::fromString('data = [ [ 1, 2, ], ["a", "b" , "c", ], ]');
		$this->assertEquals(array('data' => array(array(1, 2), array('a', 'b', 'c'))), $p);
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
		$this->assertEquals(array('title' => 'TOML Example'), $p);
	}

	public function testLoadingFromFile()
	{
		$p = Parser::fromFile(__DIR__.'/example.toml');
		$this->assertEquals('TOML Example', $p['title']);
	}

	public function testKeyGroup()
	{
		$p = Parser::fromString("[main]\nip = \"192.168.1.1\"");
		$this->assertEquals(array('main' => array('ip' => '192.168.1.1')), $p);
	}

	public function testNestedKeyGroup()
	{
		$p = Parser::fromString("[main.beta]\nip = \"192.168.1.1\"");
		$this->assertEquals(array('main' => array('beta' => array('ip' => '192.168.1.1'))), $p);
	}
}