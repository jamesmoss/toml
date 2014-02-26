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
	public function testParsingMissingClosingQuoteString()
	{
		$p = Parser::fromString('title = "TOML example'); // Missing closing quote
	}

	/**
	* @expectedException Exception
	*/
	public function testInvalidEscapeSequenceStrings()
	{
		$s = 'This is A:\\invalid\\file\\path. This\\nShould\\tBe okay. This\\\\\\Should con\\\\fuse things';
		$p = Parser::fromString('title = "'.$s.'"'); // Missing closing quote
	}

	public function testUnicodeEscapeSequenceStrings()
	{
		$s = '\\u00C2wesom\\u0207!';
		$p = Parser::fromString('title = "'.$s.'"'); // Missing closing quote


		$this->assertEquals('Âwesomȇ!', $p['title']);
	}

	public function testParsingStringsWithLineBreakEscapeSequence()
	{
		$p = Parser::fromString('bio = "PHP Developer\nLives in Brighton, England."');
		$this->assertEquals(array('bio' => "PHP Developer\nLives in Brighton, England."), $p);
	}

	public function testParsingIntegers()
	{
		$p = Parser::fromString('age = 27');
		$this->assertEquals(array('age' => 27), $p);
	}

	public function testParsingGoodFloats()
	{
		$p = Parser::fromString('pi = 3.14');
		$this->assertEquals(array('pi' => 3.14), $p);
	}

	/**
	* @expectedException Exception
	*/
	public function testParsingBadFloats()
	{
		$p = Parser::fromString('size = .000001');
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

	/**
	* @expectedException Exception
	*/
	public function testParsingMixedTypeArray()
	{
		$p = Parser::fromString('data = [ [ 1, true ], ["a", 5.4 , "c" ],  1985-10-10T07:00:00Z]');
	}

	public function testParsingMultiArray()
	{
		$p = Parser::fromString('data = [ [ 1, 2 ], ["a", "b" , "c" ] ]');
		$this->assertEquals(array('data' => array(array(1, 2), array('a', 'b', 'c'))), $p);
	}

	public function testParsingMultiArrayOverMultipleLines()
	{
		$p = Parser::fromString('data = [
			[ 1, 2 ],
			[true,
				false, true,],
			["This is a # hash symbol"] # This comment makes it complex
		]');
		$this->assertEquals(array('data' => array(
			array(1, 2),
			array(true, false, true),
			array('This is a # hash symbol')
		)), $p);
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
		$p = Parser::fromString('
			# This is a comment
			title="TOML Example"
			stumped="This string contains a #hashtag" # But only this comment should be stripped
		');
		$this->assertEquals(array('title' => 'TOML Example', 'stumped' => 'This string contains a #hashtag'), $p);
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

	/**
	* @expectedException Exception
	*/
	public function testBadKeyGroup()
	{
		$p = Parser::fromString("[main] some chars after = true\nip = \"192.168.1.1\"");
	}

	public function testNestedKeyGroup()
	{
		$p = Parser::fromString("[main.beta]\nip = \"192.168.1.1\"");
		$this->assertEquals(array('main' => array('beta' => array('ip' => '192.168.1.1'))), $p);
	}

	/**
	* @expectedException Exception
	*/
	public function testKeyGroupsDontOverrideDeclaredKeys()
	{
		$p = Parser::fromString("[fruit] type = \"apple\"\n[fruit.type]\napple = \"yes\"");
	}

	public function testSimpleTable()
	{
		$p = Parser::fromString('
			[[products]]
			name = "Hammer"
			sku = 738594937

			[[products]]

			[[products]]
			name = "Nail"
			sku = 284758393
			color = "gray"
		');
		$this->assertEquals(array(
			'products' => array(
				array('name' => 'Hammer', 'sku' => 738594937),
				array(),
				array('name' => 'Nail', 'sku' => 738594937, 'color' => 'gray'),
			),
		), $p);
	}
}
