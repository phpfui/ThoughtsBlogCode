<?php

namespace Monopoly;

class ImageDie extends \ADie
  {
  public function getFace() : \PHPFUI\HTML5Element
    {
    $face = new \PHPFUI\HTML5Element('div');
    $face->addClass('face');
    $value = $this->value();

    while ($value--)
      {
      $face->add('<span class="pip"></span>');
      }

    return $face;
    }
  }
