Upgrade guide 13.0 to 14.0
==========================

1. Remove the `mailer` factory from your `factories.yml` files.

2. Replace any use of the built-in mailer classes and tasks with an external mailer library.
   Version 14.0 removes `sfMailer`, `sfNoMailer`, `sfMailerMessageLoggerPlugin`,
   `sfTesterMailer`, `sfWebDebugPanelMailer`, and `sfProjectSendEmailsTask`.

3. Upgrade the Rock Symphony dependency:

   ```bash
   composer require rock-symphony/rock-symphony:^14.0
   ```
