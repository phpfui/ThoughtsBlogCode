<?php

namespace CSV;

/**
 * A simple CSV reader based on a file containing CSV data
 *
 * @inheritDoc
 */
class FileReader extends \CSV\Reader
	{
	public function __construct(private readonly string $fileName, bool $headerRow = true, string $delimiter = ',')
		{
		parent::__construct($headerRow, $delimiter);
		}

	protected function open() : static
		{
		if (\file_exists($this->fileName))
			{
			if ($this->stream)
				{
				\fclose($this->stream);
				$this->stream = null;
				}
			$this->stream = @\fopen($this->fileName, 'r');
			}

		return $this;
		}
	}
