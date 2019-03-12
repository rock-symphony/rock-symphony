Upgrade guide 5.0 to 6.0
========================

1. Make sure you use *PDO* versions of DB-interacting classes.
   - `sfPDODataase` instead of `sfMySQLDatabase`, `sfMySQLiDatabase` and `sfPostgreSQLDatabase`
   - `sfPDOSessionStorage` instead of `sfMySQLSessionStorage`, `sfMySQLiSessionStorage` and `sfPostgreSQLSessionStorage`
   - Don't use `sfMessageSource_MySQL` 

2. Make sure you don't use `sfServiceContainerDumperGraphviz`.

3. Upgrade rock-symphony dependency to `6.0`

   ```bash
   composer require rock-symphony/rock-symphony:^6.0
   ```
