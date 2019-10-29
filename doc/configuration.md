# Configuration

In order to use to the ITK Development User bundle you'll need to configure your Symfony project. This document will
show you what you have to do.

## ITKDevUserBundle

Create a configuration file with the following contents in your project:

```yaml
# config/packages/itk_dev_user.yaml

itk_dev_user:
    site_name: '%env(SITE_NAME)%'
    site_url: '%env(SITE_URL)%'

    sender:
        email: '%env(MAILER_FROM_EMAIL)%'
        name: '%env(MAILER_FROM_NAME)%'

    # Template for mails sent to new users
    user_created:
        subject: '{{ site_name }} – new user created'
        header: 'User created on {{ site_name }}'
        body: |
            <p style='margin: 0;'>
              You have been registered as user on {{ site_name }} with email
              address {{ user.email }}.
            </p>
            <p style='margin: 0;'>
              To get started, you have to choose a password.
            </p>
            <p style='margin: 0;'>
              After choosing a password, you can sign in with your email address
              ({{ user.email }}) and the choosen password.
            </p>
        button:
            text: 'Choose password'
        footer: '<p style="margin: 0;">Best regards,<br/> {{ site_name }}</p>'

    # Set to true to automatically notify users on creation.
    notify_user_on_create: false
```

Use Bootstrap 4 as default form theme:

```yaml
# config/packages/twig.yaml

twig:
    form_themes: ['bootstrap_4_layout.html.twig']
```

## FOSUserBundle

As this bundle is dependant on the FOSUserBundle you will need to follow the [instructions found here](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.rst) on how to configure that as well.

Use the ItkDev/UserBundle/Doctrine/UserManager as the default user manager:

```yaml
# config/packages/fos_user.yaml

fos_user:
    ...
    service:
        user_manager: ItkDev\UserBundle\Doctrine\UserManager
``` 

## Environment

Set variables in your environment file:

```ini
# E.g. .env or .env.local

SITE_NAME=Project
SITE_UTL=project.url
SITE_LOGO_URL=path/to/logo.file

MAILER_FROM_EMAIL=email@project.url
MAILER_FROM_NAME=project.url
```
