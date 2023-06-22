<?php

namespace Tests;

class DieTest extends \PHPUnit\Framework\TestCase
  {
  public function testBadADie() : void
	{
	$this->expectException(\Exception::class);
	$aDie = new \ADie(1);
	}

  public function testDefaultADie() : void
	{
	$aDie = new \ADie();
	$this->assertIsInt($aDie->sides());
	$this->assertEquals(6, $aDie->sides());
	$this->assertIsInt($aDie->value());
	// make sure we exercise the roll method
	for ($i = 0; $i < 1000; ++$i)
	  {
	  $this->assertGreaterThan(0, $aDie->value());
	  $this->assertLessThanOrEqual(6, $aDie->value());
	  $aDie->roll();
	  }
	}

  public function testLargeADie() : void
	{
	$sides = 12;
	$aDie = new \ADie($sides);
	$this->assertIsInt($aDie->sides());
	$this->assertEquals($sides, $aDie->sides());
	$this->assertIsInt($aDie->value());
	// make sure we exercise the roll method
	for ($i = 0; $i < 1000; ++$i)
	  {
	  $this->assertGreaterThan(0, $aDie->value());
	  $this->assertLessThanOrEqual($sides, $aDie->value());
	  $aDie->roll();
	  }
	}
  }
