<?php

namespace bfrohs\ICanHasLog;

class ParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException bfrohs\ICanHasLog\InvalidFileException
	 */
	public function testMissingFile() {
		$filename = bin2hex(openssl_random_pseudo_bytes(10)).'.missing.php';
		$parser = new Parser($filename);
	}

	public function testError() {
		$actual_errors = new Parser(__DIR__.'/data/error.log');
		$expected_errors = require(__DIR__.'/data/error.php');

		$this->assertTrue($actual_errors instanceof \Traversable, "Parser must be Traversable");

		$key = -1;
		foreach($actual_errors as $key => $actual){
			if (!array_key_exists($key, $expected_errors)) {
				throw new \Exception("No key `$key` in expected errors. Actual: ".print_r($actual, true));
			}

			$this->assertEquals($expected_errors[$key], $actual);
		}
		$this->assertEquals(count($expected_errors), $key + 1);
	}

	/**
	 * @depends testError
	 */
	public function testValid() {
		$actual_errors = new Parser(__DIR__.'/data/error.log');
		$expected_errors = require(__DIR__.'/data/error.php');

		$actual_errors->next();
		$this->assertTrue($actual_errors->valid());

		$this->assertEquals($expected_errors[1], $actual_errors->current());
	}

}