<?php

namespace Toml;

class LexerTest extends \PHPUnit_Framework_TestCase
{
	public function testLexing()
	{
		$files = glob(__DIR__ . '/fixtures/lexer/*.toml');
		foreach($files as $file) {
			$toml     = file_get_contents($file);
			$expected = json_decode(file_get_contents(substr($file, 0, -5).'_expected.json'));

			$p = new Lexer($toml);
			$this->assertEquals($expected, $p->getTokens());
		}
	}

	/**
	* @expectedException Exception
	* @expectedExceptionMessage Unexpected character "." on line 1 at position 1
	*/
	public function testBadToken()
	{
		$p = new Lexer('.');
	}

	/**
	* @expectedException Exception
	* @expectedExceptionMessage Unexpected character "." on line 5 at position 6
	*/
	public function testLineNumbersAndPositionInErrors()
	{
		$p = new Lexer('this="is valid toml"
			and_this="
			is a multi
			line string"
			  .
			# That is a bad character above
		');
	}

	/**
	* @expectedException Exception
	* @expectedExceptionMessage Unexpected character "." on line 4 at position 27
	*/
	public function testLineNumberErrorsFollowingMultilineString()
	{
		$this->markTestIncomplete(
          'This test has not been implemented yet.'
        );

		$p = new Lexer('this="is valid toml"
			and_this="
			is a multi
			line string" .
			  .
			# That is a bad character above
		');
	}
}
