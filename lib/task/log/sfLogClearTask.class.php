<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\Util\Finder;

/**
 * Clears log files.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfLogClearTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure(): void
  {
    $this->namespace = 'log';
    $this->name = 'clear';
    $this->briefDescription = 'Clears log files';

    $this->detailedDescription = <<<EOF
      The [log:clear|INFO] task clears all symfony log files:

        [./symfony log:clear|INFO]
      EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute(array $arguments = [], array $options = []): int
  {
    $logs = Finder::files()->in(sfConfig::get('sf_log_dir'));
    $this->getFilesystem()->remove($logs);

    return 0;
  }
}
