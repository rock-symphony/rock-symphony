Upgrade guide 8.0 to 9.0
========================

1. Upgrade rock-symphony dependency to `9.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^9.0
   ```
   
2. (Optional) If you're using `rock-symphony/propel-orm-plugin`, upgrade it to `v4.0`.

   ```bash
   composer require rock-symphony/propel-orm-plugin:^4.0
   ```

3. Make sure that all classes that extend `sfForm` have `void` return types
   declared for all overriden `configure()` and `setup()` methods.
