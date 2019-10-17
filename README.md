# ITK Development - User bundle

User bundle overrides the templates in the [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) with styles
often used in projects developed in ITK Development.

## Getting started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes. See deployment for notes on how to deploy the project on a live system.

### Installing

There are multiple ways you could set up this project for development. Typically you would want to develop and test it in a Symfony project:

Install the ITK Development user-bundle:

```bash
# Clone the code to a place e.g. inside your Symfony 
git clone git@github.com:itk-dev/user-bundle.git bundles/user-bundle

# Install it with Composer
composer config repositories.itk-dev/user-bundle path bundles/user-bundle
composer require itk-dev/user-bundle:dev-develop
```

Configure the FOSUserBundle as described [here](https://github.com/FriendsOfSymfony/FOSUserBundle/blob/master/Resources/doc/index.rst).

## Running the tests

### Coding standards

```bash
composer check-coding-standards

# Most of the times the tools can fix it: (Twig templates excluded)
composer apply-coding-standards
```
## Deployment

```bash
composer config repositories.itk-dev/user-bundle vcs https://github.com/itk-dev/user-bundle
composer require itk-dev/user-bundle
```

## Contributing

### Pull Request Process

1. Update the README.md with details of changes that are relevant.
2. You may merge the Pull Request in once you have the sign-off of one other developer, or if you 
   do not have permission to do that, you may request the reviewer to merge it for you.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/itk-dev/user-bundle/tags). 

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
