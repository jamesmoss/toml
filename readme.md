# TOML for PHP

An parser for [TOML](https://github.com/mojombo/toml) written in PHP. Currently supports 100% of the TOML spec.

## Requirements

- PHP 5.3
- Composer

## Installation

Use [Composer](http://getcomposer.org/) to install the Toml package. Package details [can be found on Packagist.org](https://packagist.org/packages/jamesmoss/toml).

You can use this lib without composer but you'll need to provide your own PSR-0 compatible autoloader. Really you should just use Composer.

## Use

`Toml\Parser` has two static methods `fromString` and `fromFile`, which are self explanatory. Both return an associative array. If your TOML doc can't be parsed an `Exception` will be thrown.

    use Toml\Parser;
    
    // Load directly from a string
    $toml = Parser::fromString('name = "James Moss"');
    
    // Load from a file instead
    $toml = Parser::fromFile(__DIR__ . '/config.toml');
    
## Running tests

There is 100% test coverage at the moment. If you'd like to run the tests use the following:

    $ composer update
    $ phpunit .

## Contributing

Fork this repo, create a new branch and submit a pull request. Make sure any features you add are covered by unit tests.  
   
## Todo

- Better documentation and docblocks
- More semantic exceptions to be thrown, standardise the error message format.