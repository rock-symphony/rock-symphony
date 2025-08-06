<?php

namespace RockSymphony\Util;

use InvalidArgumentException;
use SplFileInfo;
use Symfony\Component\Finder\Finder as BaseFinder;

/**
 * BC adapter between the Symfony/Finder component
 * and the project codebase written for the old sfFinder class.
 */
final class Finder
{
    private BaseFinder $finder;
    private bool       $relative = false;
    private string     $type;

    public function __construct(string | null $type = null)
    {
        $this->finder = new BaseFinder();

        $this->type = $type ?: 'any';

        match ($type) {
            'file'      => $this->finder->files(),
            'directory' => $this->finder->directories(),
            'any', null => null, // nothing
            default     => throw new InvalidArgumentException("Unsupported type given: `$type`."),
        };
    }

    public function type(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        match ($type) {
            'file'      => $this->finder->files(),
            'directory' => $this->finder->directories(),
            'any'       => null, // nothing
            default     => throw new InvalidArgumentException("Unsupported type given: `$type`."),
        };

        $this->type = $type;
    }

    public static function files(): self
    {
        return new self('file');
    }

    public static function dirs(): self
    {
        return new self('directory');
    }

    public static function any(): self
    {
        return new self();
    }

    public function relative(): self
    {
        $this->relative = true;

        return $this;
    }

    public function name(array | string $pattern): self
    {
        $this->finder->name($pattern);

        return $this;
    }


    public function notName(array | string $pattern): self
    {
        $this->finder->notName($pattern);

        return $this;
    }

    public function size(array | string | int $size): self
    {
        $this->finder->size($size);

        return $this;
    }

    public function discard(string $pattern): self
    {
        $this->finder->notName($pattern);

        return $this;
    }

    public function prune(string $name): self
    {
        $this->finder->filter(function (SplFileInfo $file) use ($name) {
            return $file->getPathname() !== $name
                && ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR)
                && ! str_starts_with($file->getPathname(), $name . DIRECTORY_SEPARATOR);
        });

        return $this;
    }

    public function minDepth(int $depth): self
    {
        $this->finder->depth(">= {$depth}");

        return $this;
    }

    public function maxDepth(int $depth): self
    {
        $this->finder->depth("<= {$depth}");

        return $this;
    }

    public function ignoreVersionControl(bool $ignore = true): self
    {
        $this->finder->ignoreVCS($ignore);

        return $this;
    }

    public function ignoreDotFiles(bool $ignore = true): self
    {
        $this->finder->ignoreDotFiles($ignore);

        return $this;
    }

    public function followLinks(): self
    {
        $this->finder->followLinks();

        return $this;
    }

    public function sortByName(): self
    {
        $this->finder->sortByName();

        return $this;
    }

    /**
     * @param string[]|string $directory
     * @return string[]
     */
    public function in(array | string $directory): array
    {
        $files = [];

        $this->finder->ignoreUnreadableDirs(true);

        $directories = is_array($directory) ? $directory : [$directory];

        $directories = array_filter($directories, 'is_dir');

        foreach ($this->finder->in($directories) as $file) {
            $files[] = $this->relative ? $file->getRelativePathname() : $file->getPathname();
        }

        return $files;
    }
}
