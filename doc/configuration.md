# Configuration

In order to use to the ITK Development User bundle you'll need to configure your Symfony project. This document will
show you what you have to do.

## Required

Use Bootstrap 4 as default form theme:

```yaml
# config/packages/twig.yaml

twig:
    form_themes: ['bootstrap_4_layout.html.twig']
```

As this bundle is dependant on the FOSUserBundle you will need to follow the [instructions found here](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.rst) on how to configure that as well.

## Optional

Setting variables in your environment file:

```ini
# E.g. .env or .env.local

SITE_NAME=Project
SITE_LOGO_URL=path/to/logo.file
```
