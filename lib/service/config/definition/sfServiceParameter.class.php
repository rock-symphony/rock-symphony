<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfServiceParameter represents a parameter reference.
 *
 * @package    symfony
 * @subpackage service
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfServiceReference.php 267 2009-03-26 19:56:18Z fabien $
 */
class sfServiceParameter
{
  protected
    $id = null;

  /**
   * Constructor.
   *
   * @param string $id The parameter key
   */
  public function __construct($id)
  {
    $this->id = $id;
  }

  /**
   * @return string
   */
  public function getParameterName()
  {
    return $this->id;
  }

  /**
   * Stringify parameter reference back to %...% form.
   *
   * @return string
   */
  public function __toString()
  {
    return '%' . $this->id . '%';
  }
}
