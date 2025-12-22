<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use RockSymphony\Util\Finder;

require_once(__DIR__ . '/../../bootstrap/unit.php');

class my_lime_test extends lime_test
{
  public function arrays_are_equal(array $a, array $b, string $message): bool
  {
    sort($a);
    sort($b);

    return $this->is($a, $b, $message);
  }
}

$t = new my_lime_test(39);

$fixtureDir = __DIR__ . '/fixtures/finder';

$phpFiles                = [
  'dir1/dir2/file21.php',
  'dir1/file12.php',
];
$txtFiles                = [
  'FILE5.txt',
  'file2.txt',
];
$regexpFiles             = [
  'dir1/dir2/file21.php',
  'dir1/dir2/file22',
  'dir1/dir2/file23',
  'dir1/dir2/file24',
  'file2.txt',
];
$regexpWithModifierFiles = [
  'dir1/dir2/file21.php',
  'dir1/dir2/file22',
  'dir1/dir2/file23',
  'dir1/dir2/file24',
  'FILE5.txt',
  'file2.txt',
];
$allFiles                = [
  'dir1/dir2/dir3/file31',
  'dir1/dir2/dir4/file41',
  'dir1/dir2/file21.php',
  'dir1/dir2/file22',
  'dir1/dir2/file23',
  'dir1/dir2/file24',
  'dir1/file11',
  'dir1/file12.php',
  'dir1/file13',
  'file1',
  'FILE5.txt',
  'file2.txt',
];
$minDepth1Files          = [
  'dir1/dir2/dir3/file31',
  'dir1/dir2/dir4/file41',
  'dir1/dir2/file21.php',
  'dir1/dir2/file22',
  'dir1/dir2/file23',
  'dir1/dir2/file24',
  'dir1/file11',
  'dir1/file12.php',
  'dir1/file13',
];
$maxDepth2Files          = [
  'dir1/dir2/file21.php',
  'dir1/dir2/file22',
  'dir1/dir2/file23',
  'dir1/dir2/file24',
  'dir1/file11',
  'dir1/file12.php',
  'dir1/file13',
  'file1',
  'FILE5.txt',
  'file2.txt',
];
$anyWithoutDir2          = [
  'dir1',
  'dir1/dir2',
  'dir1/file11',
  'dir1/file12.php',
  'dir1/file13',
  'file1',
  'FILE5.txt',
  'file2.txt',
];

// ::type()
$t->diag('::files()');
$finder = Finder::files();
$t->ok($finder instanceof Finder, '::files() returns a Finder instance');

$t->diag('::dirs()');
$finder = Finder::dirs();
$t->ok($finder instanceof Finder, '::dirs() returns a Finder instance');

$t->diag('::any()');
$finder = Finder::any();
$t->ok($finder instanceof Finder, '::any() returns a Finder instance');

$t->diag('::type()');
$finder = Finder::files();
$t->is($finder->type(), 'file', '::type() returns the finder type');
$finder = Finder::dirs();
$t->is($finder->type(), 'directory', '::type() takes a file, dir, or any as its first argument');
$finder = Finder::any();
$t->is($finder->type(), 'any', '::type() takes a file, dir, or any as its first argument');

try {
  $finder = new Finder('somethingelse');
  $t->fail('It validates `type` when constructing a finder instance, and fails if unsupported.');
} catch (InvalidArgumentException $exception) {
  $t->pass('It validates `type` when constructing a finder instance, and fails if unsupported.');
}

// ->name()
$t->diag('->name()');
$finder = Finder::files();
$t->is($finder->name('*.php'), $finder, '->name() implements the fluent interface');

$t->diag('->name() file name support');
$finder = Finder::files()->name('file21.php')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), ['dir1/dir2/file21.php'], '->name() can take a file name as an argument');

$t->diag('->name() globs support');
$finder = Finder::files()->name('*.php')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), $phpFiles, '->name() can take a glob pattern as an argument');

$t->diag('->name() regexp support');
$finder = Finder::files()->name('/^file2.*$/')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), $regexpFiles, '->name() can take a regexp as an argument');

$t->diag('->name() regexp support with modifier');
$finder = Finder::files()->name('/^file(2|5).*$/i')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), $regexpWithModifierFiles, '->name() can take a regexp with a modifier as an argument');

$t->diag('->name() array / args / chaining');
$finder = Finder::files()->name(['*.php', '*.txt'])->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_merge($phpFiles, $txtFiles), '->name() can take an array of patterns');
$finder = Finder::files()->name(['*.php', '*.txt'])->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_merge($phpFiles, $txtFiles), '->name() can take patterns as arguments');
$finder = Finder::files()->name('*.php')->name('*.txt')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_merge($phpFiles, $txtFiles), '->name() can be called several times');

// ->not_name()
$t->diag('->notName()');
$finder = Finder::files();
$t->is($finder->notName('*.php'), $finder, '->notName() implements the fluent interface');

$t->diag('->notName() file name support');
$finder = Finder::files()->notName('file21.php')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, ['dir1/dir2/file21.php'])), '->notName() can take a file name as an argument');

$t->diag('->notName() globs support');
$finder = Finder::files()->notName('*.php')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, $phpFiles)), '->notName() can take a glob pattern as an argument');

$t->diag('->notName() regexp support');
$finder = Finder::files()->notName('/^file2.*$/')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, $regexpFiles)), '->notName() can take a regexp as an argument');

$t->diag('->notName() array / args / chaining');
$finder = Finder::files()->notName(['*.php', '*.txt'])->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, array_merge($phpFiles, $txtFiles))), '->notName() can take an array of patterns');
$finder = Finder::files()->notName(['*.php', '*.txt'])->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, array_merge($phpFiles, $txtFiles))), '->notName() can take patterns as arguments');
$finder = Finder::files()->notName('*.php')->notName('*.txt')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, array_merge($phpFiles, $txtFiles))), '->notName() can be called several times');

$t->diag('->name() ->notName() in the same query');
$finder = Finder::files()->notName('/^file2.*$/')->name('*.php')->relative();
$t->arrays_are_equal($finder->in($fixtureDir), ['dir1/file12.php'], '->not_name() and ->name() can be called in the same query');

// ->size()
$t->diag('->size()');
$finder = Finder::files();
$t->is($finder->size('> 2K'), $finder, '->size() implements the fluent interface');

$finder = Finder::files()->size('> 100K')->relative();
$t->is($finder->in($fixtureDir), [], '->size() takes a size comparison string as its argument');
$finder = Finder::files()->size('> 1K')->relative();
$t->is($finder->in($fixtureDir), ['file1'], '->size() takes a size comparison string as its argument');
$finder = Finder::files()->size('> 1K')->size('< 2K')->relative();
$t->is($finder->in($fixtureDir), [], '->size() takes a size comparison string as its argument');

// ->mindepth() ->maxdepth()
$t->diag('->minDepth() ->maxDepth()');
$finder = Finder::files();
$t->is($finder->minDepth(1), $finder, '->minDepth() implements the fluent interface');
$t->is($finder->maxDepth(1), $finder, '->maxDepth() implements the fluent interface');

$finder = Finder::files()->relative()->minDepth(1);
$t->arrays_are_equal($finder->in($fixtureDir), $minDepth1Files, '->minDepth() takes a minimum depth as its argument');
$finder = Finder::files()->relative()->maxDepth(2);
$t->arrays_are_equal($finder->in($fixtureDir), $maxDepth2Files, '->maxDepth() takes a maximum depth as its argument');
$finder = Finder::files()->relative()->minDepth(1)->maxDepth(2);
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_intersect($minDepth1Files, $maxDepth2Files)), '->maxDepth() and ->minDepth() can be called in the same query');

// ->discard()
$t->diag('->discard()');
$t->is($finder->discard('file2.txt'), $finder, '->discard() implements the fluent interface');

$t->diag('->discard() file name support');
$finder = Finder::files()->relative()->discard('file2.txt');
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, ['file2.txt'])), '->discard() can discard a file name');

$t->diag('->discard() glob support');
$finder = Finder::files()->relative()->discard('*.php');
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, $phpFiles)), '->discard() can discard a glob pattern');

$t->diag('->discard() regexp support');
$finder = Finder::files()->relative()->discard('/^file2.*$/');
$t->arrays_are_equal($finder->in($fixtureDir), array_values(array_diff($allFiles, $regexpFiles)), '->discard() can discard a regexp pattern');

// ->prune()
$t->diag('->prune()');
$t->is($finder->prune('dir2'), $finder, '->prune() implements the fluent interface');

$finder = Finder::any()->relative()->prune('dir2');
$t->arrays_are_equal($finder->in($fixtureDir), $anyWithoutDir2, '->prune() ignore all files/directories under the given directory');

// ->in() permissions
$t->diag('->in() permissions');
chmod($fixtureDir . '_permissions/secret', 0000);
$finder = Finder::files()->relative();
$t->arrays_are_equal($finder->in($fixtureDir . '_permissions'), [], '->in() ignores directories it cannot read');
chmod($fixtureDir . '_permissions/secret', 0755);
