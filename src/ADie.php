<?php

class ADie
  {
  private int $value;

  public function __construct(private readonly int $sides = 6)
    {
    if ($sides < 2)
      {
      throw new \Exception(__CLASS__ . ' must have at least 2 sides');
      }
    $this->roll();
    }

  public function roll() : int
    {
    $this->value = \mt_rand(1, $this->sides);

    return $this->value;
    }

  public function value() : int
    {
    return $this->value;
    }

  public function sides() : int
    {
    return $this->sides;
    }
  }
