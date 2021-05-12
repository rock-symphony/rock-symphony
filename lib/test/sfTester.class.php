<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTester is the base class for all tester classes.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 *
 * @mixin \sfTestFunctional
 */
abstract class sfTester
{
  /** @var \sfTestFunctional */
  protected $browser;
  /** @var \lime_test */
  protected $tester;

  /**
   * @param  sfTestFunctional  $browser  A browser
   * @param  lime_test         $tester   A tester object
   */
  public function __construct(sfTestFunctional $browser, lime_test $tester)
  {
    $this->browser = $browser;
    $this->tester = $tester;
  }

  /**
   * Prepares the tester.
   */
  abstract public function prepare();

  /**
   * Initializes the tester.
   */
  abstract public function initialize();

  public function __call($method, $arguments)
  {
    call_user_func_array([$this->browser, $method], $arguments);

    return $this;
  }
}
