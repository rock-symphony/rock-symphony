<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Output escaping iterator decorator.
 *
 * This takes an object that implements the Traversable interface and turns it
 * into an iterator with each value escaped.
 *
 * Note: Prior to PHP 5.1, the IteratorIterator class was not implemented in the
 * core of PHP. This means that although it will still work with classes that
 * implement Iterator or IteratorAggregate, internal PHP classes that only
 * implement the Traversable interface will cause the constructor to throw an
 * exception.
 *
 * @see        sfOutputEscaper
 * @package    symfony
 * @subpackage view
 * @author     Mike Squire <mike@somosis.co.uk>
 * @version    SVN: $Id$
 */
class sfOutputEscaperIteratorDecorator extends sfOutputEscaperObjectDecorator implements IteratorAggregate, ArrayAccess
{
  /**
   * Constructs a new escaping iteratoror using the escaping method and value supplied.
   *
   * @param string      $escapingMethod  The escaping method to use
   * @param Traversable $value           The iterator to escape
   */
  public function __construct(string $escapingMethod, Traversable $value)
  {
    // Set the original value for __call(). Set our own iterator because passing
    // it to IteratorIterator will lose any other method calls.

    parent::__construct($escapingMethod, $value);
  }

  public function getIterator(): Traversable
  {
    foreach ($this->value as $key => $value) {
      yield $key => sfOutputEscaper::escape($this->escapingMethod, $value);
    }
  }

  /**
   * Returns true if the supplied offset isset in the array (as required by the ArrayAccess interface).
   *
   * @param  string $offset  The offset of the value to check existance of
   *
   * @return bool true if the offset isset; false otherwise
   */
  public function offsetExists($offset): bool
  {
    return isset($this->value[$offset]);
  }

  /**
   * Returns the element associated with the offset supplied (as required by the ArrayAccess interface).
   *
   * @param  string $offset  The offset of the value to get
   *
   * @return mixed The escaped value
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset)
  {
    return sfOutputEscaper::escape($this->escapingMethod, $this->value[$offset]);
  }

  /**
   * Throws an exception saying that values cannot be set (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @param  string $offset  (ignored)
   * @param  string $value   (ignored)
   *
   * @throws sfException
   */
  public function offsetSet($offset, $value): void
  {
    throw new sfException('Cannot set values.');
  }

  /**
   * Throws an exception saying that values cannot be unset (this method is
   * required for the ArrayAccess interface).
   *
   * This (and the other sfOutputEscaper classes) are designed to be read only
   * so this is an illegal operation.
   *
   * @param  string $offset  (ignored)
   *
   * @throws sfException
   */
  public function offsetUnset($offset): void
  {
    throw new sfException('Cannot unset values.');
  }
}
