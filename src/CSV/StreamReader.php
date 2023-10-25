<?php

namespace CSV;

/**
 * A simple CSV reader based on an already open stream
 *
 * @inheritDoc
 */
class StreamReader extends \CSV\Reader
	{
	/**
	 * @param ?resource $stream
	 */
	public function __construct(protected $stream, bool $headerRow = true, string $separator = ',', string $enclosure = '"', string $escape = '\\')
		{
		parent::__construct($stream, $headerRow, $separator, $enclosure, $escape);
		$this->rewind();
		}
	}
