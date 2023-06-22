<?php

class Dice implements \Countable
	{
	/**
	 * @var array<\ADie>
	 */
  private array $dice = [];

	public function add(\ADie $dice = new \ADie()) : int
		{
		$this->dice[] = clone $dice;

		return $this->count();
		}

	public function count() : int
		{
		return \count($this->dice);
		}

	/**
	 * @return array<\ADie>
	 */
	public function getDies() : array
		{
		$values = [];

		foreach ($this->dice as $aDie)
			{
			$values[] = clone $aDie;
			}

		return $values;
		}

	public function roll() : void
		{
		foreach ($this->dice as $aDie)
			{
			$aDie->roll();
			}
		}

	/**
	 * @return array<int>
	 */
	public function values() : array
		{
		$values = [];

		foreach ($this->dice as $aDie)
			{
			$values[] = $aDie->value();
			}

		return $values;
		}
	}
