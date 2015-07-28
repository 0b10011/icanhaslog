<?php

namespace bfrohs\ICanHasLog;

class InvalidErrorException extends \InvalidArgumentException {}
class EndOfErrorException extends \LogicException {}

use DateTime;

class Parser implements \Iterator {
	protected $reader;
	protected $errors = [
		0 => 0,
	];
	protected $error = 0;

	public function __construct($filename) {
		$this->reader = new FileReader($filename);
	}

	public function goToError($error) {
		// If error isn't indexed yet, find it
		if (!array_key_exists($error, $this->errors)) {
			$i = count($this->errors) - 1;
			while($i < $error) {
				$this->consumeError($i);
				$i += 1;
			}
		}

		if (!array_key_exists($error, $this->errors)) {
			throw new InvalidErrorException("End of file");
		}

		// Set current error and move to line in reader
		$this->error = $error;
		$this->reader->goToLine($this->errors[$error]);
	}

	/**
	 * Returns string on error,
	 * indexes next error,
	 * but does not move to next error.
	 */
	public function consumeError($error) {
		$this->goToError($error);

		$str = '';

		// Consume next error
		$error_array = $this->parseError($this->reader->current());
		// Check for stacktrace (next() will always return NULL)
		try {
			$eof = false;
			while ($this->reader->next() || $this->reader->valid()) {
				$trace = $this->parseTrace($this->reader->current());
				if (!$trace) {
					continue;
				}

				$error_array['stack'][] = $trace;
			}
			$eof = true;
		} catch(EndOfErrorException $ex) {
			// Don't need to do anything
		}

		// Index next error, if necessary
		if (!$eof) {
			$next_error = $error + 1;
			if (!array_key_exists($next_error, $this->errors)) {
				$this->errors[$next_error] = $this->reader->key();
			}
		}

		return $error_array;
	}

	protected function parseError($line) {
		$error_matched = preg_match(
			'#^(?:\[(?<date>[^\]]+)\] )?(?:PHP (?<type>[^:]+):  )?(?<message>.*?) (?:in (?<file>.+?)(?: on line |:)(?<line>\d+))?$#',
			$line,
			$matches
		);
		if (!$error_matched) {
			$matches['message'] = $line;
		}

		$error = [
			"date" => isset($matches['date'])
				? new DateTime($matches['date'])
				: null,
			"type" => isset($matches['type']) ? $matches['type'] : null,
			"message" => $matches['message'],
			"stack" => [],
		];

		if (isset($matches['file']) || isset($matches['line'])) {
			$error['stack'][] = [
				"file" => isset($matches['file'])
					? $matches['file']
					: null,
				"line" => isset($matches['line'])
					? $matches['line']
					: 0,
			];
		}

		return $error;
	}

	protected function parseTrace($line) {
		if ($line === '') {
			return;
		}

		if (preg_match("/Stack trace:$/", $line)) {
			return;
		}

		$error_trace = preg_match(
			'/^\[[^\]]+\] PHP   \d+\. (?<message>.*?)\(\) (?<file>.+?):(?<line>\d+)$/',
			$line,
			$matches
		);

		if ($error_trace) {
			return [
				"file" => $matches['file'],
				"line" => $matches['line'],
			];
		}

		$exception_trace = preg_match(
			'/^#\d+ (?:\{main\}|\[internal function\]:|(?<file>.+?)\((?<line>\d+)\):)/',
			$line,
			$matches
		);

		if ($exception_trace) {
			// If main/internal function, just skip
			if (!isset($matches['file'])) {
				return;
			}

			return [
				"file" => $matches['file'],
				"line" => $matches['line'],
			];
		}

		// Skip ending "thrown in" message
		if (preg_match('/^  thrown in /', $line)) {
			return;
		}

		throw new EndOfErrorException("Beginning of next error found");
	}

	public function rewind() {
		// Rewind to beginning
		// Reader position will be updated when consuming
		$this->error = 0;
	}

	public function current() {
		return $this->consumeError($this->error);
	}

	public function key() {
		return $this->error;
	}

	public function next() {
		// Increment error
		// Reader position will be updated when consuming
		$this->error += 1;
	}

	public function valid() {
		// If indexed, it's valid
		if (array_key_exists($this->error, $this->errors)) {
			return true;
		}

		// Try to go to error
		// If it exists, goToError() will be successfull and TRUE will be returned
		// Otherwise, InvalidErrorException will be thrown (and caught) and FALSE returned
		try {
			$this->goToError($this->error);
			return true;
		} catch(InvalidErrorException $ex) {
			return false;
		}
	}

}
