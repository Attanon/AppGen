# AppGen
🖨️ Model layer (Doctrine ORM) generator to speed up development

# Installation
```
composer require --dev archette/appgen
```

Or download binary files from `bin` folder.

# Usage

```
vendor/bin/appgen
```

```yaml
#################################################
~ Welcome to AppGen v0.1 created by Rick Strafy ~
#################################################

 Entity Name: Article
 Namespace: Test\Article

 Define Entity Properties? [yes]

 Property Name: slug
 Type (e.g. "?string|31 --unique") [string]: string|63 --unique
 Default Value:

 Define Another Property? [yes]

 Property Name: title
 Type (e.g. "?string|31 --unique") [string]: string|127
 Default Value:

 Define Another Property? [yes]

 Property Name: content
 Type (e.g. "?string|31 --unique") [string]: text
 Default Value: ""

 Define Another Property? [yes] no

 Create edit Method? [yes] y
 Create getAll Method? [yes] y
 Create delete Method? [yes] y

 Define Fields for getBy<Field> Methods (e.g. "email, slug"): slug
 Define Fields for getAllBy<Field> Methods (e.g. "author, type"):
 Define Events (for "created, updated, deleted" type "all"): all

 Use timestampable Trait? [yes]

 Files created:
 example/Article/Article.php
 example/Article/ArticleData.php
 example/Article/ArticleFactory.php
 example/Article/ArticleRepository.php
 example/Article/ArticleFacade.php
 example/Article/Exception/ArticleNotFoundException.php
 example/Article/Event/ArticleCreatedEvent.php
 example/Article/Event/ArticleUpdatedEvent.php
 example/Article/Event/ArticleDeletedEvent.php
```

# Example configuration

Default configuration will be created when `appgen` command is executed for the first time

```yaml
appDir: app
model:
    entity:
        idType: uuid_binary
        idComment: null
        createSetters: false
        defaultTraits:
            timestampable: \Your\TimestampableTrait
            removable: \Your\RemovableTrait

    symfonyEvents: true
```