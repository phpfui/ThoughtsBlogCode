<?php

namespace CSV;

/**
 * A simple CSV reader based on an already open stream
 *
 * @inheritDoc
 */
class StreamReader extends \CSV\Reader
	{
	public function __construct($stream, bool $headerRow = true, string $delimiter = ',') // @phpstan-ignore-line
		{
		$this->stream = $stream;
		parent::__construct($headerRow, $delimiter);
		}
	}
