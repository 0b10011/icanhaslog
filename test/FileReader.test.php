<?php

namespace bfrohs\ICanHasLog;

class FileReaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @expectedException bfrohs\ICanHasLog\InvalidFileException
	 */
	public function testMissingFile() {
		$filename = bin2hex(openssl_random_pseudo_bytes(10)).'.missing.php';
		$parser = new FileReader($filename);
	}

	public function testReader() {
		$reader = new FileReader(__DIR__.'/data/error.log');

		$reader->next();
		$this->assertTrue($reader->valid());

		$this->assertEquals('Stack trace:', $reader->consumeLine(10));
	}

	public function testForeach() {
		$reader = new FileReader(__DIR__.'/data/error.log');

		foreach($reader as $line){
			$this->assertInternalType('string', $line);
		}
	}

}