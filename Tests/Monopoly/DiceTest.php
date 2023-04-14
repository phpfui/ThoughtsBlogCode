<?php

namespace Test\Monopoly;

class DiceTest extends \PHPUnit\Framework\TestCase
  {
  public function testDoublesDice() : void
    {
    $dice = new \Monopoly\Dice();
    $doubleCount = 0;

    for($i = 0; $i < 100; ++$i)
      {
      $dice->roll();
      $values = $dice->values();
      $value = $values[0] + $values[1];
      $this->assertEquals($value, $dice->value());

      if ($dice->doubles())
        {
        $this->assertEquals($values[0], $values[1]);
        ++$doubleCount;
        }
      else
        {
        $this->assertNotEquals($values[0], $values[1]);
        }
      }
    $this->assertGreaterThan(0, $doubleCount);
    }
  }
