<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Psr\Container\ContainerInterface as PsrContainer;
use RockSymphony\ServiceContainer\Interfaces\ServiceContainerInterface as RockSymphonyContainer;

/**
 * sfServiceContainerInterface is the interface implemented by service container classes.
 *
 * @package    symfony
 * @subpackage service
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
interface sfServiceContainerInterface extends PsrContainer, RockSymphonyContainer
{

}
