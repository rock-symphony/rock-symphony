<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * HelperHelper.
 *
 * @param string[] $name
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 * @package    symfony
 * @subpackage helper
 */

function use_helper(string ...$names)
{
  $context = sfContext::getInstance();

  $context->getConfiguration()->loadHelpers($names, $context->getModuleName());
}
