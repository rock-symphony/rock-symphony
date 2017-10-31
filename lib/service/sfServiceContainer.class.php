<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\ServiceContainer\Exceptions\BindingNotFoundException;
use RockSymphony\ServiceContainer\ServiceContainer;

/**
 * sfServiceContainer is a dependency injection container.
 *
 * It gives access to object instances services.
 *
 * Services are stored as key/pair values. Mostly with deferred resolution.
 *
 * A service id can contain lowercased letters, digits, underscores, and dots.
 * Underscores are used to separate words, and dots to group services
 * under namespaces:
 *
 * <ul>
 *   <li>request</li>
 *   <li>mysql_session_storage</li>
 *   <li>symfony.mysql_session_storage</li>
 * </ul>
 *
 * @package    symfony
 * @subpackage service
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfServiceContainer implements sfServiceContainerInterface
{
  /** @var ServiceContainer */
  protected $container;

  public function __construct()
  {
    $this->container = new ServiceContainer();
    $this->container->set('service_container', $this);
  }

  /**
   * Sets a service.
   *
   * @param string $id      The service identifier
   * @param object $service The service instance
   */
  public function setService($id, $service)
  {
    $this->container->set($id, $service);
  }

  /**
   * Returns true if the given service is defined.
   *
   * @param  string  $id      The service identifier
   *
   * @return Boolean true if the service is defined, false otherwise
   */
  public function hasService($id)
  {
    return $this->container->has($id);
  }

  /**
   * Gets a service.
   *
   * @param  string $id The service identifier
   *
   * @return object The associated service
   *
   * @throws InvalidArgumentException if the service is not defined
   * @throws \RockSymphony\ServiceContainer\Exceptions\BindingResolutionException if an error occurred during resolution
   */
  public function getService($id)
  {
    try {
      return $this->container->get($id);
    } catch (BindingNotFoundException $exception) {
      throw new InvalidArgumentException(sprintf('The service "%s" does not exist.', $id), 0, $exception);
    }
  }
}
