<?php
/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfInflectorTest extends \PHPUnit\Framework\TestCase
{
  /**
   * @test
   */
  public function it_should_camelize_strings()
  {
    $this->assertEquals(sfInflector::camelize('symfony'), 'Symfony', '::camelize() upper-case the first letter');
    $this->assertEquals(sfInflector::camelize('symfony_is_great'), 'SymfonyIsGreat', '::camelize() upper-case each letter after a _ and remove _');
  }

  /**
   * @test
   */
  public function it_should_underscore_strings()
  {
    $this->assertEquals(sfInflector::underscore('Symfony'), 'symfony', '::underscore() lower-case the first letter');
    $this->assertEquals(sfInflector::underscore('SymfonyIsGreat'), 'symfony_is_great', '::underscore() lower-case each upper-case letter and add a _ before');
    $this->assertEquals(sfInflector::underscore('HTMLTest'), 'html_test', '::underscore() lower-case all other letters');
  }

  /**
   * @test
   */
  public function it_should_humanize_strings()
  {
    $this->assertEquals(sfInflector::humanize('symfony'), 'Symfony', '::humanize() upper-case the first letter');
    $this->assertEquals(sfInflector::humanize('symfony_is_great'), 'Symfony is great', '::humanize() replaces _ by a space');
  }
}
