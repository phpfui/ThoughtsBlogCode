<?php

namespace Tests\Monopoly;

class ImageDieTest extends \PHPFUI\HTMLUnitTester\Extensions
  {
  public function testImageDieFaces() : void
    {
    $page = new \PHPFUI\VanillaPage();
    $page->setPageName('ImageDie Test');
    $css = '.face {
      display: grid;
      grid-template-areas:
          "a . c"
          "e g f"
          "d . b";
      flex: 0 0 auto;
      margin: 16px;
      padding: 10px;
      width: 104px;
      height: 104px;
      background-color: #e7e7e7;
      box-shadow: inset 0 5px white, inset 0 -5px #bbb, inset 5px 0 #d7d7d7, inset -5px 0 #d7d7d7;
      border-radius: 10%;
    }
    .pip {
      display: block;
      align-self: center;
      justify-self: center;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background-color: #333;
      box-shadow: inset 0 3px #111, inset 0 -3px #555;
    }
    .pip:nth-child(2) {grid-area: b;}
    .pip:nth-child(3) {grid-area: c;}
    .pip:nth-child(4) {grid-area: d;}
    .pip:nth-child(5) {grid-area: e;}
    .pip:nth-child(6) {grid-area: f;}
    .pip:nth-child(odd):last-child {grid-area: g;}';

    $this->assertNotWarningCss($css);
    $this->assertValidCss($css);
    $page->addCSS($css);
    $faces = [];
    $imageDie = new \Monopoly\ImageDie();
    // make sure we have a face for every possible value
    while (\count($faces) < 6)
      {
      $face = (string)$imageDie->getFace();
      $this->assertValidHtml($face);
      $faces[$imageDie->value()] = $face;
      $imageDie->roll();
      }
    // add to page and display
    foreach ($faces as $face)
      {
      $page->add($face);
      }
    $html = (string)$page;
    $this->assertValidHtmlPage($html);
    }
  }
