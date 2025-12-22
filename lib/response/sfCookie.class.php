<?php

class sfCookie
{
  /** @var string */
  private string $name;

  /** @var string|null */
  private string | null $value;

  /** @var \DateTimeInterface|null */
  private DateTimeInterface | null $expires;

  /** @var string|null */
  private string | null $domain;

  /** @var string */
  private string $path;

  /** @var bool */
  private bool $httpOnly;

  /** @var bool */
  private bool $secure;

  /** @var string */
  private string $sameSite;

  /**
   * @param string                            $name
   * @param string|null                       $value
   * @param DateTimeInterface|string|int|null $expires
   * @param string|null                       $domain
   * @param string                            $path
   * @param bool                              $httpOnly
   * @param bool                              $secure
   * @param string                            $sameSite
   */
  public function __construct(
    string                                  $name,
    ?string                                 $value,
    DateTimeInterface | string | int | null $expires = null,
    string                                  $path = '/',
    string | null                           $domain = null,
    bool                                    $httpOnly = false,
    bool                                    $secure = false,
    string                                  $sameSite = 'Lax'
  ) {
    $this->name     = $name;
    $this->value    = $value;
    $this->expires  = $this->parseExpires($expires);
    $this->path     = $path;
    $this->domain   = $domain;
    $this->httpOnly = $httpOnly;
    $this->secure   = $secure;
    $this->sameSite = $sameSite;
  }

  public static function create(string $name, ?string $value, array $options = []): self
  {
    $extra = array_diff(array_keys($options), ['expires', 'path', 'domain', 'httponly', 'secure', 'samesite']);

    if ($extra) {
      throw new InvalidArgumentException('Options array contains unsupported keys: ' . implode(', ', $extra) . '.');
    }

    return new self(
      $name,
      $value,
      $options['expires'] ?? null,
      $options['path'] ?? '/',
      $options['domain'] ?? null,
      $options['httponly'] ?? false,
      $options['secure'] ?? false,
      $options['samesite'] ?? 'Lax',
    );
  }

  /**
   * @param \DateTimeInterface|string|int|null $expires
   * @return \DateTimeInterface|null
   * @throws \InvalidArgumentException
   */
  private function parseExpires(DateTimeInterface | string | int | null $expires): ?DateTimeInterface
  {
    if ($expires === null) {
      return null;
    }

    if ($expires instanceof DateTimeInterface) {
      return $expires;
    }

    try {
      if (is_string($expires) && ! is_numeric($expires)) {
        return new DateTime($expires);
      }
      return DateTime::createFromFormat('U', $expires);
    } catch (Exception $exception) {
      throw new InvalidArgumentException('Your expire parameter is not valid.', 0, $exception);
    }
  }

  /**
   * @return string
   */
  public function getName(): string
  {
    return $this->name;
  }

  /**
   * @return string|null
   */
  public function getValue(): ?string
  {
    return $this->value;
  }

  /**
   * @return \DateTimeInterface|null
   */
  public function getExpires(): ?DateTimeInterface
  {
    return $this->expires;
  }

  /**
   * @return bool
   */
  public function isExpired(): bool
  {
    return $this->expires && $this->expires->getTimestamp() < time();
  }

  /**
   * @return string|null
   */
  public function getDomain(): ?string
  {
    return $this->domain;
  }

  /**
   * @return string
   */
  public function getPath(): string
  {
    return $this->path;
  }

  /**
   * @return bool
   */
  public function isHttpOnly(): bool
  {
    return $this->httpOnly;
  }

  /**
   * @return bool
   */
  public function isSecure(): bool
  {
    return $this->secure;
  }

  /**
   * @return string
   */
  public function getSameSite(): string
  {
    return $this->sameSite;
  }

  /**
   * Get cookie attributes.
   *
   * The returned array is 100% compatible with `setcookie()` and `setrawcookie()` functions
   * and thus can be used as-is.
   *
   * @return array
   * @see \setrawcookie()
   *
   * @see \setcookie()
   */
  public function getAttributes(): array
  {
    return [
      'expires'  => $this->expires ? (int)$this->expires->format('U') : null,
      'path'     => $this->path,
      'domain'   => $this->domain,
      'httponly' => $this->httpOnly,
      'secure'   => $this->secure,
      'samesite' => $this->sameSite,
    ];
  }

  /**
   * Get array representation for the cookie object.
   *
   * Mainly for debug/testing purposes.
   *
   * @return array
   */
  public function toArray(): array
  {
    return array_merge(['name' => $this->getName(), 'value' => $this->getValue()], $this->getAttributes());
  }
}
