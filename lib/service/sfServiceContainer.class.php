<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\ServiceContainer\ServiceContainer as RockSymfonyContainer;

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
class sfServiceContainer extends RockSymfonyContainer implements sfServiceContainerInterface
{
  public function __construct()
  {
    // auto-assign self to service_container
    $this->set('service_container', $this);
  }
}
