<?php

namespace Monopoly;

class Dice extends \Dice
  {
  public function __construct()
		{
		$this->add();
		$this->add();
		}

  public function doubles() : bool
		{
		$values = $this->values();

		return $values[0] === $values[1];
		}

  public function value() : int
		{
		$values = $this->values();

		return $values[0] + $values[1];
		}
  }
