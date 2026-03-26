Upgrade guide 12.0 to 13.0
=========================

1. Upgrade rock-symphony dependency to `13.0`:

   ```bash
   composer require rock-symphony/rock-symphony:^13.0
   ```

2. If you extend any of the Rock Symphony core classes, 
   make sure that your method overrides are compatible
   with the parent methods specifications.

   Check the complete changeset at [#76](https://github.com/rock-symphony/rock-symphony/pull/76).

