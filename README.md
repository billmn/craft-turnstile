<p align="center"><img width="100" src="./src/icon.svg"></p>

# Cloudflare Turnstile

Easily integrate Turnstile to validate your forms.

Turnstile is Cloudflare's **free**, **privacy-first**, smart **CAPTCHA replacement**. It automatically chooses from a rotating suite of non-intrusive browser challenges based on telemetry and client behavior exhibited during a session.

The plugin support [Sprig](https://github.com/putyourlightson/craft-sprig) requests out of the box.

If you want to know more about Turnstile, read the announcement [blog post](https://blog.cloudflare.com/turnstile-private-captcha-alternative) or the [official documentation](https://developers.cloudflare.com/turnstile).

## Requirements

This plugin requires Craft CMS 5.0.0 or later.

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

## Turnstile's keys

Insert [site and secret keys](https://developers.cloudflare.com/turnstile/get-started/#get-a-sitekey-and-secret-key) in the plugin settings page, `.env` variables are supported.

### Config file (optional)
You can create a `turnstile.php` file in the config folder of your project to override the settings specified in control panel:
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

## Verify form submissions

To validate the Turnstile response, you can use one of this methods:
```php
Turnstile::getInstance()->validator->verify(); // returns `true` if passes
Turnstile::getInstance()->validator->passes();
Turnstile::getInstance()->validator->fails();
```

This is an example on how to flag the message as spam using [Contact Form](https://plugins.craftcms.com/contact-form). Add the following code in your project module:
```php
use billmn\turnstile\Turnstile;
use craft\contactform\events\SendEvent;
use craft\contactform\Mailer;
use yii\base\Event;

Event::on(
    Mailer::class,
    Mailer::EVENT_BEFORE_SEND,
    function(SendEvent $e) {
        $e->isSpam = Turnstile::getInstance()->validator->fails();
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
Turnstile::getInstance()->validator->fails('custom-field');
```

## Config
You can customize the behavior of the widget using the `config` array.

| Option | Default | Description |
| --- | --- | --- |
| `registerJs` | `true` | Automatically register scripts
| `scriptAttr` | `{}` | Script tag HTML attributes

Like this:

```twig
{{ craft.turnstile.widget({
    config: {
        scriptAttr: {
            nonce: 'nonce'
        }
    }
}) }}
```

## Methods

| Method | Description |
| --- | --- |
| `widget` | Render the widget
| `siteKey` | Returns Turnstile's site key
| `scriptUrl` | Returns Turnstile's script url
| `initScript` | Returns initialization script

For example:
```twig
{{ craft.turnstile.widget({
    config: {
        registerJs: false,
    }
}) }}

<script src="{{ craft.turnstile.scriptUrl }}"></script>

<script>
    {{ craft.turnstile.initScript|raw }}
</script>
```
