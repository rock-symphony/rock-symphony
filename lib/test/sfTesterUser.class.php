<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfTesterUser implements tests for the symfony user object.
 *
 * @package    symfony
 * @subpackage test
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfTesterUser extends sfTester
{
  protected $user;

  /**
   * Prepares the tester.
   */
  public function prepare()
  {
  }

  /**
   * Initializes the tester.
   */
  public function initialize()
  {
    $this->user = $this->browser->getUser();
  }

  /**
   * Tests a user attribute value.
   *
   * @param  string       $key
   * @param  string       $value
   * @param  string|null  $ns
   *
   * @return $this
   */
  public function isAttribute(string $key, string $value, string $ns = null): self
  {
    $this->tester->is($this->user->getAttribute($key, null, $ns), $value, sprintf('user attribute "%s" is "%s"', $key, $value));

    return $this;
  }

  /**
   * Tests a user flash value.
   *
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function isFlash(string $key, string $value): self
  {
    $this->tester->is($this->user->getFlash($key), $value, sprintf('user flash "%s" is "%s"', $key, $value));

    return $this;
  }

  /**
   * Tests the user culture.
   *
   * @param  string $culture  The user culture
   *
   * @return $this
   */
  public function isCulture(string $culture): self
  {
    $this->tester->is($this->user->getCulture(), $culture, sprintf('user culture is "%s"', $culture));

    return $this;
  }

  /**
   * Tests if the user is authenticated.
   *
   * @param  bool  $boolean  Whether to check if the user is authenticated or not
   *
   * @return $this
   */
  public function isAuthenticated(bool $boolean = true): self
  {
    $this->tester->is($this->user->isAuthenticated(), $boolean, sprintf('user is %sauthenticated', $boolean ? '' : 'not '));

    return $this;
  }

  /**
   * Tests if the user has some credentials.
   *
   * @param  mixed $credentials
   * @param  bool  $boolean      Whether to check if the user have some credentials or not
   * @param  bool  $useAnd       specify the mode, either AND or OR
   *
   * @return $this
   */
  public function hasCredential($credentials, bool $boolean = true, bool $useAnd = true): self
  {
    $this->tester->is($this->user->hasCredential($credentials, $useAnd), $boolean, sprintf('user has %sthe right credentials', $boolean ? '' : 'not '));

    return $this;
  }
}
