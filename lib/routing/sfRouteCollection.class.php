<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfRouteCollection represents a collection of routes.
 *
 * @package    symfony
 * @subpackage routing
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfRouteCollection implements IteratorAggregate
{
  /**
   * @var array<string,mixed>
   */
  protected array $options = [];
  /**
   * @var sfRoute[]
   */
  protected array $routes = [];

  /**
   * Constructor.
   *
   * @param array $options An array of options
   */
  public function __construct(array $options)
  {
    if (empty($options['name']))
    {
      throw new InvalidArgumentException('You must pass a "name" option to sfRouteCollection');
    }

    $this->options = $options;
  }

  /**
   * Returns the routes.
   *
   * @return sfRoute[] The routes
   */
  public function getRoutes(): array
  {
    return $this->routes;
  }

  /**
   * Returns the options.
   *
   * @return array The options
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  public function getIterator(): Traversable
  {
    yield from $this->routes;
  }
}
