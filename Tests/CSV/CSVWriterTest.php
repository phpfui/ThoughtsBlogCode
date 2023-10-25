<?php

namespace Tests\CSV;

class CSVWriterTest extends \PHPUnit\Framework\TestCase
	{
	private static string $countryFileName = __DIR__ . '/data/countries.csv';

	private static string $stateFileName = __DIR__ . '/data/states.tsv';

	public function testFileWriteStates() : void
		{
		// read in file csv file and write to new file name, then diff files
		$reader = new \CSV\FileReader(self::$stateFileName, headerRow:true, separator:"\t");

		$tempFile = 'temp.tsv';
		$this->assertFileDoesNotExist($tempFile);
		$writer = new \CSV\FileWriter($tempFile, download:false, separator:"\t", eol:"\r\n");
		$writer->addHeaderRow();

		foreach ($reader as $row)
			{
			$writer->outputRow($row);
			}

		$this->assertFileExists($tempFile);
		$this->assertFileEquals(self::$stateFileName, $tempFile);
		unlink($tempFile);
		}

	public function testStringWriteCountries() : void
		{
		// read in file csv file as string, and write to string, then diff strings
		$originalString = \file_get_contents(self::$countryFileName);
		$reader = new \CSV\StringReader($originalString);

		$writer = new \CSV\StringWriter();
		$writer->addHeaderRow();

		foreach ($reader as $row)
			{
			$writer->outputRow($row);
			}

		$writtenString = "{$writer}";
		$this->assertEquals($originalString, $writtenString);
		}
	}
