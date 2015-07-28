<?php

namespace bfrohs\ICanHasLog;

class InvalidFileException extends \InvalidArgumentException {}
class EndOfFileException extends \LogicException {}

class FileReader implements \Iterator {
	protected $handle;
	protected $lines = [
		0 => 0,
	];
	protected $line = 0;

	public function __construct($filename) {
		if (!file_exists($filename)) {
			throw new InvalidFileException("File `$filename` does not exist");
		}
		$this->handle = fopen($filename, "r");
		if ($this->handle === false) {
			throw new InvalidFileException("File `$filename` could not be opened");
		}
	}

	public function __destruct() {
		flock($this->handle, LOCK_UN);
		fclose($this->handle);
	}

	public function goToLine($line) {
		// If line isn't indexed yet, find it
		if (!array_key_exists($line, $this->lines)) {
			$i = count($this->lines) - 1;
			while($i < $line) {
				$this->consumeLine($i);
				$i += 1;
			}
		}

		if (!array_key_exists($line, $this->lines)) {
			throw new EndOfFileException("End of file");
		}

		// Set current line and seek to position in file
		$this->line = $line;
		fseek($this->handle, $this->lines[$line]);
	}

	/**
	 * Returns string on line,
	 * indexes next line,
	 * but does not move to next line.
	 */
	public function consumeLine($line) {
		$this->goToLine($line);

		$str = '';

		// Consume characters until EOF or newline is found
		while (($ch = fgetc($this->handle)) !== false) {
			if ($ch === "\n") {
				break;
			}

			$str .= $ch;
		}

		// Index next line, if necessary
		$next_line = $line + 1;
		if (!feof($this->handle) && !array_key_exists($next_line, $this->lines)) {
			$this->lines[$next_line] = ftell($this->handle);
		}

		return $str;
	}

	public function rewind() {
		$this->line = 0;
	}

	public function current() {
		return $this->consumeLine($this->line);
	}

	public function key() {
		return $this->line;
	}

	/**
	 * Must always return NULL
	 * so `while(next() || valid())` works
	 */
	public function next() {
		$this->line += 1;
	}

	public function valid() {
		// If indexed, it's valid
		if (array_key_exists($this->line, $this->lines)) {
			return true;
		}

		// Try to go to line
		// If it exists, goToLine() will be successfull and TRUE will be returned
		// Otherwise, EndOfFileException will be thrown (and caught) and FALSE returned
		try {
			$this->goToLine($this->line);
			return true;
		} catch(EndOfFileException $ex) {
			return false;
		}
	}
}
