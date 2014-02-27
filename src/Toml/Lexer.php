<?php

namespace Toml;

class Lexer
{
    protected $toml;
    protected $tokens = array();
    protected $lineNo = 1;

    public function __construct($toml)
    {
        $this->toml = $toml;

        $this->tokens = $this->lex($toml, array(
            '~=~A'            => 'T_ASSIGNMENT',
            '~true|false~A'   => 'T_BOOLEAN',
            '~\d{4}\-\d{2}\-\d{2}T\d{2}:\d{2}:\d{2}Z~A'  => 'T_DATE',
            '~\-?\d+\.\d+~A'  => 'T_FLOAT',
            '~\-?\d+~A'       => 'T_INTEGER',
            '~[A-Za-z0-9\_\-]+~A' => 'T_KEY',
            '~\[[A-Za-z0-9\_\-\.]+\]~A'     => 'T_TABLE',
            '~\[\[[A-Za-z0-9\_\-\.]+\]\]~A' => 'T_TABLE_ARRAY',
            '~"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"~A'   => 'T_STRING',
            '~#[^\r\n]+~A'    => 'T_COMMENT',
            '~[^\S\r\n]+~A'   => 'T_WHITESPACE',
            '~\r\n?|\n~A'     => 'T_NEWLINE',
            '~\[~A'           => 'T_ARRAY_START',
            '~\]~A'           => 'T_ARRAY_END',
            '~\,~A'           => 'T_ARRAY_SEPERATOR',
        ));
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    function lex($string, array $tokenMap) {
        $tokens = array();

        $offset = 0; // current offset in string
        $lineNo = 1;
        $position = 1;
        while (isset($string[$offset])) { // loop as long as we aren't at the end of the string
            foreach ($tokenMap as $regex => $token) {
                if (preg_match($regex, $string, $matches, null, $offset)) {

                    $tokens[] = array(
                        $token,      // token ID      (e.g. T_FIELD_SEPARATOR)
                        $matches[0], // token content (e.g. ,)
                        $lineNo,
                        $position,
                        $offset,
                    );
                    $len = strlen($matches[0]);
                    $offset += $len;
                    $position += $len;
                    if($token === 'T_NEWLINE') {
                        $lineNo++;
                        $position = 1;
                    } if($token === 'T_STRING') {
                        $lineCount = preg_match_all('/\r\n?|\n/' , $matches[0], $m);
                        if($lineCount > 0) {
                           $lineNo += $lineCount;
                           $position = 1;
                        }
                    }

                    continue 2; // continue the outer while loop
                }
            }

            throw new \Exception(sprintf('Unexpected character "%s" on line %s at position %s', $string[$offset], $lineNo, $position));
        }

        return $tokens;
    }
}
