# Installation

You can install this bundle with `composer` using the `sulu/validation-bundle` package.

```bash
composer require sulu/validation-bundle
```

## 1. Add SuluValidation to your project:

In your Kernel:

```php
new Sulu\Bundle\ValidationBundle\SuluValidationBundle()
```

## 2. Prepend your config:

Prepend your bundles config as follows

```php
class YourBundleExtension extends Extension implements PrependExtensionInterface
{

    ...

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('sulu_validation')) {
            $container->prependExtensionConfig(
                'sulu_validation',
                [
                    'schemas' => [
                        'example_route_id' => '@YourBundle/Validation/ExampleActionSchema.json',
                    ],
                ]
            );
        }
    }
}
```

## 3. Define your schema:

For the example above create a file `Validation\ExampleActionSchema.json`.

Let's assume you would like to require a property `locale` in your api,
that must consist of 2 letters:

```json
{
    "title": "Example schema",
    "type": "object",
    "properties": {
        "locale": {
            "type": "string",
            "minLength": 2,
            "maxLength": 2
        }
    },
    "required": [
        "locale"
    ]
}
```

## Overwrite schema

Of course you can overwrite an existing schema. Just exchange
configuration parameter in your applications configuration file:

```yml
sulu_validation:
    schemas:
        example_route_id: '@AnotherBundle/Validation/anotherActionSchema.json'
```

## Overwrite default schema cache file

To overwrite the default path for the schema cache file set the following parameter in your applications configuration 
file:

```yml
sulu_validation:
    schema_cache: 'path/to/the/prefered/location/schemaCache.php'
```
