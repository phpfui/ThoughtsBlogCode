<?php

namespace Test;

class DiceTest extends \PHPUnit\Framework\TestCase
  {
  public function testDefaultDice() : void
    {
    $dice = new \Dice();
    $this->assertCount(0, $dice);
    $dice->add();
    $this->assertCount(1, $dice);
    $dice->add();
    $this->assertCount(2, $dice);
    $dice->add();
    $this->assertCount(3, $dice);
    $dice->add();
    $this->assertCount(4, $dice);
    $dice->add();
    $this->assertCount(5, $dice);
    $dice->roll();
    $values = $dice->values();
    $this->assertCount(5, $values);
    $dies = $dice->getDies();
    $this->assertCount(5, $dies);

    for($i = 0; $i < 5; ++$i)
      {
      $this->assertEquals($values[$i], $dies[$i]->value());
      }
    }

  public function testCustomDice() : void
    {
    $dice = new \Dice();
    $this->assertCount(0, $dice);
    $sixDie = new \ADie(6);
    $dice->add($sixDie);
    $this->assertCount(1, $dice);
    $eightDie = new \ADie(8);
    $dice->add($eightDie);
    $this->assertCount(2, $dice);
    $twelveDie = new \ADie(12);
    $dice->add($twelveDie);
    $this->assertCount(3, $dice);
    $sixteenDie = new \ADie(16);
    $dice->add($sixteenDie);
    $this->assertCount(4, $dice);
    $eightteenDie = new \ADie(18);
    $dice->add($eightteenDie);
    $this->assertCount(5, $dice);
    $dice->roll();
    $values = $dice->values();
    $this->assertCount(5, $values);
    $dies = $dice->getDies();
    $this->assertCount(5, $dies);
    $sideCount = [6, 8, 12, 16, 18];

    for($i = 0; $i < 5; ++$i)
      {
      $this->assertEquals($values[$i], $dies[$i]->value());
      $this->assertEquals($sideCount[$i], $dies[$i]->sides());
      }
    // roll our copies of the dies, then test to see if things have changed with the Dice copy
    $sixDie->roll();
    $eightDie->roll();
    $twelveDie->roll();
    $sixteenDie->roll();
    $eightteenDie->roll();
    $newValues = $dice->values();
    $this->assertCount(5, $newValues);
    $newDies = $dice->getDies();
    $this->assertCount(5, $newDies);

    for($i = 0; $i < 5; ++$i)
      {
      $this->assertEquals($values[$i], $newValues[$i]);
      $this->assertEquals($newDies[$i]->value(), $dies[$i]->value());
      $newDies[$i]->roll();    // roll and see if we can affect next test
      }
    $newValues = $dice->values();
    $this->assertCount(5, $newValues);
    $newDies = $dice->getDies();
    $this->assertCount(5, $newDies);

    for($i = 0; $i < 5; ++$i)
      {
      $this->assertEquals($values[$i], $newValues[$i]);
      $this->assertEquals($newDies[$i]->value(), $dies[$i]->value());
      }
    }
  }
