<?php

class Dice implements \Countable
  {
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

  public function roll() : void
    {
    foreach ($this->dice as $aDie)
      {
      $aDie->roll();
      }
    }

  public function values() : array
    {
    $values = [];

    foreach ($this->dice as $aDie)
      {
      $values[] = $aDie->value();
      }

    return $values;
    }

  public function getDies() : array
    {
    $values = [];

    foreach ($this->dice as $aDie)
      {
      $values[] = clone $aDie;
      }

    return $values;
    }
  }
