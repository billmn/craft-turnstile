<p align="center"><img width="100" src="https://raw.githubusercontent.com/billmn/craft-turnstile/a4cbd7975f2cf5abca6014c7ff28a1fedb195376/src/icon.svg"></p>

# Cloudflare Turnstile

Integrate Cloudflare smart CAPTCHA alternative to validate your forms.

[Learn more on Turnstile docs](https://developers.cloudflare.com/turnstile).

## Requirements

This plugin requires Craft CMS 4.3.5 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Turnstile”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require billmn/craft-turnstile

# tell Craft to install the plugin
./craft plugin/install turnstile
```

## Turnstile's Keys

You can get site and secret keys following this [instructions](https://developers.cloudflare.com/turnstile/get-started/#get-a-sitekey-and-secret-key).

After that, insert keys in the plugin settings page (it supports `.env` variables).

You can also create a `turnstile.php` file in the config folder of your project to override the settings specified in control panel:
```php
<?php

return [
    'siteKey' => '',
    'secretKey' => '',
];
```

## Using Turnstile

In your template add the following code inside the `form` tag to render the widget:
```twig
{{ craft.turnstile.widget() }}
```

The `widget` method accept optional parameters in which you can provide HTML attributes to configure Turnstile.

A list of supported configurations is available on [Turnstile's docs](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configurations).

Here an example that provide the element ID attribute and set widget's theme and size:
```twig
{{ craft.turnstile.widget({
    id: 'contact-form',
    'data-size': 'compact',
    'data-theme': 'dark',
}) }}

{# or if you prefer #}

{{ craft.turnstile.widget({
    id: 'contact-form',
    data: {
        size: 'compact',
        theme: 'dark',
    }
}) }}
```

If you don't specify the ID, a random one will be created. 

### Script attributes

If you would provide HTML attribute to Cloudflare Turnstile's script, you can use the code as follow:
```twig
{{ craft.turnstile.widget({
    config: {
        scriptAttr: {
            nonce: 'nonce'
        }
    }
}) }}
```

## Verify form submissions

To validate the Turnstile response, use:
```php
// returns `true` if passes
Turnstile::getInstance()->validator->verify();
```

This is an example on how validate a [Contact Form](https://plugins.craftcms.com/contact-form) submission. Add the following code in your project module:
```php
Event::on(
    Submission::class,
    Submission::EVENT_AFTER_VALIDATE, function(Event $e) {
        /** @var Submission $submission */
        $submission = $e->sender;

        if (! Turnstile::getInstance()->validator->verify()) {
            $submission->addError('turnstile', __('Please, prove you are human.'));
        }
    }
);
```

If you use the [`response-field-name`](https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configurations) configuration, you can validate the submission by specifying the field name:

```twig
{{ craft.turnstile.widget({
    'data-response-field-name': 'custom-field',
}) }}
```

```php
if (! Turnstile::getInstance()->validator->verify('custom-field')) {
    // ...
}
```
