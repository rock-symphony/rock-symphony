Upgrade guide 5.0 to 6.0
========================

1. Stop using `generator.yml` functionality (auto-generated modules)
   - [#24](https://github.com/rock-symphony/rock-symphony/pull/24).

2. Stop using view cache and `sfViewCacheManager`, `sfFunctionBase`
   - [#28](https://github.com/rock-symphony/rock-symphony/pull/28).
   Better rely on external HTTP cache service (like Varnish).
   
   - Do not use `sfFunctionBase`.
   - Drop `cache` and `etag` sections from `settings.yml`.
   - Drop `cache` section from `filters.yml`. 
   - Drop `Cache` helper usages (including `standard_helpers` from `settings.yml`).
   
3. Drop all `cache.yml` in your codebase. Do not use `sfCacheConfigHandler`. 
  - ([#35](https://github.com/rock-symphony/rock-symphony/pull/35))

4. Stop using dynamic method calls dispatching (`*.method_not_found` events)
   - [#31](https://github.com/rock-symphony/rock-symphony/pull/31).

5. Do not use `sfData` class
   - [#32](https://github.com/rock-symphony/rock-symphony/pull/32).
   
6. Stop using `i18n:extract`, `i18n:find` and all related code.

7. Upgrade rock-symphony dependency to `6.0`

   ```bash
   composer require rock-symphony/rock-symphony:^6.0
   ```
