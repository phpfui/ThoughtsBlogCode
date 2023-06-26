<?php

namespace Tests\CSV;

use PHPUnit\Framework\Attributes\DataProvider;

class CSVTest extends \PHPUnit\Framework\TestCase
	{
	private static string $countryFileName = __DIR__ . '/data/countries.csv';

	private static string $stateFileName = __DIR__ . '/data/states.tsv';

	/**
	 * @return array<array<\CSV\Reader>>
	 */
	public static function countryReaders() : array
		{
		return [
			[new \CSV\FileReader(self::$countryFileName)],
			[new \CSV\StringReader(\file_get_contents(self::$countryFileName))],
			[new \CSV\StreamReader(\fopen(self::$countryFileName, 'r'))],
		];
		}

	/**
	 * @return array<array<\CSV\Reader>>
	 */
	public static function stateReaders() : array
		{
		$tab = "\t";

		return [
			[new \CSV\FileReader(self::$stateFileName, delimiter:$tab)],
			[new \CSV\StringReader(\file_get_contents(self::$stateFileName), delimiter:$tab)],
			[new \CSV\StreamReader(\fopen(self::$stateFileName, 'r'), delimiter:$tab)],
		];
		}

	#[DataProvider('countryReaders')]
	public function testCountries(\CSV\Reader $reader) : void
		{
		$count = 0;
		$row = [];

		foreach ($reader as $row)
			{
			$this->assertCount(3, $row, 'Row has wrong number of elements (not 3).');
			++$count;
			}
		$this->assertGreaterThan(194, $count, 'All 195 countries not found.');
		$this->assertLessThan(196, $count, 'Header row appears to be read.');
		$headers = ['Country', 'Population', 'Land Area'];
		$this->assertSame($headers, \array_keys($row), 'Headers are incorrect');
		}

	#[DataProvider('stateReaders')]
	public function testRewind(\CSV\Reader $reader) : void
		{
		$firstCount = 0;

		foreach ($reader as $row)
			{
			++$firstCount;
			}
		$reader->rewind();

		$secondCount = 0;

		foreach ($reader as $row)
			{
			++$secondCount;
			}
		$this->assertEquals($firstCount, $secondCount, 'rewind() failed.');
		}

	#[DataProvider('stateReaders')]
	public function testStates(\CSV\Reader $reader) : void
		{
		$count = 0;
		$nyFound = false;
		$row = [];

		foreach ($reader as $row)
			{
			$this->assertCount(4, $row, 'Row has wrong number of elements (not 4).');

			if ('NY' == $row['state'])
				{
				$nyFound = true;
				}
			$this->assertEquals($count, $reader->key(), 'key() is wrong.');
			++$count;
			}
		$this->assertGreaterThan(49, $count, 'All 50 states not found.');
		$this->assertLessThan(51, $count, 'Header row appears to be read.');
		$headers = ['state', 'latitude', 'longitude', 'name'];
		$this->assertSame($headers, \array_keys($row), 'Headers are incorrect');
		$this->assertTrue($nyFound, 'NY was not found.');
		$this->assertTrue($reader instanceof \CSV\Reader, 'Incorrect inheritance');
		}
	}
