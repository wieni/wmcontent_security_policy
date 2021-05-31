Content Security Policy
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmcontent_security_policy/v/stable)](https://packagist.org/packages/wieni/wmcontent_security_policy)
[![Total Downloads](https://poser.pugx.org/wieni/wmcontent_security_policy/downloads)](https://packagist.org/packages/wieni/wmcontent_security_policy)
[![License](https://poser.pugx.org/wieni/wmcontent_security_policy/license)](https://packagist.org/packages/wieni/wmcontent_security_policy)

> Secure your site using a Content Security Policy header

## Why?
- Content Security Policy adds a security layer to **detect and mitigate the risk of Cross Site Scripting (XSS), 
  data injection, and other vulnerabilities**.
- The [`csp` Drupal module](https://www.drupal.org/project/csp) is more feature-complete, but ours has a **simpler 
  interface** and **doesn't allow inline scripts on pages using Drupal AJAX**.

## Installation

This package requires PHP 7.2 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmcontent_security_policy drupal/multivalue_form_element:dev-3199142-drupal-9-compatibility"
```

### Patches
For this module to work, it is necessary to patch Drupal core and the 
[Multi-value form element module](https://www.drupal.org/project/multivalue_form_element). If you manage your 
installation with Composer, you should use the [cweagans/composer-patches](https://github.com/cweagans/composer-patches) 
package to manage and automatically apply patches. If not, please check the 
[documentation](https://www.drupal.org/patch/apply) for instructions on how to manually apply patches.

If the patches below don't apply to your package versions, you should visit the relevant issues and find older or newer
patches.

```json
// composer.json
{
    "extra": {
        "composer-exit-on-patch-failure": true,
        "patches": {
            "drupal/core": {
              "#2264739: Allow multiple field widgets to not use tabledrag": "https://www.drupal.org/files/issues/2021-01-23/2264739-105.patch"
            },
            "drupal/multivalue_form_element": {
              "#3199172 Handle default values of nested elements": "https://git.drupalcode.org/project/multivalue_form_element/-/merge_requests/2.patch",
              "#3199298 Don't add an empty element when the multivalue element is disabled": "https://git.drupalcode.org/project/multivalue_form_element/-/merge_requests/3.patch",
              "#3200306 Add support for non-orderable multivalue form elements": "https://git.drupalcode.org/project/multivalue_form_element/-/commit/ef4d01ae56a809fc2349d8fec8185ace3b63d15d.patch"
            }
        }
    }
}
```

### Composer repository
As a temporary workaround to make the 
[Multi-value form element module](https://www.drupal.org/project/multivalue_form_element) module work with Drupal 9, 
you should add the following repository to your composer.json. Make sure to add it before the 
`https://packages.drupal.org/8` repository, so this one takes precedence.

```json
// composer.json
{
    "repositories": [
      {
        "type": "vcs",
        "url": "https://git.drupalcode.org/issue/multivalue_form_element-3199142.git"
      }
    ]
}
```

## How does it work?
### Managing default sources
Using the form at `/admin/config/system/content-security-policy/default-sources`, you can set default sources for the 
different policy directives. These sources should be required for the website to function properly. 

Sources you add there are stored in configuration, so you can export them and add them to version control.

To manage these sources, you need the `administer default content security policy sources` permission.

### Managing custom sources
Using the form at `/admin/config/system/content-security-policy/custom-sources`, you can add custom sources to the 
different policy directives. 

Sources you add here are stored in the database and will not be exported with 
configuration. This is useful to allow content editors to add sources required for certain site content.

To manage these sources, you need the `administer custom content security policy sources` permission.

### Adding script hashes
If you want to include certain inline scripts (eg. a Google Analytics snippet) without allowing 
`script-src: 'unsafe-inline'`, you can add the hashes of these scripts to your `script-src` policy (more information 
[here](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Content-Security-Policy/script-src#unsafe_inline_script)).

You can add script hashes in Twig by using the `addCspHash` function:
```twig
{{ addCspHash("'sha256-n4MwUTyKKCBlMIFkvcS3dkmlRFEcqSm/V0IOhZelzA0='") }}
```

You can add script hashes in code by using `wmcontent_security_policy.content_security_policy:addScriptHash`:
```php
\Drupal::service('wmcontent_security_policy.content_security_policy')
    ->addScriptHash("'sha256-n4MwUTyKKCBlMIFkvcS3dkmlRFEcqSm/V0IOhZelzA0='");
```

Finally, it's also possible to add script hashes like any other source through the administration forms.

### Events
One event is provided, which allows you to dynamically add sources right before the header is built.

```php
<?php

use Drupal\wmcontent_security_policy\ContentSecurityPolicyEvents;
use Drupal\wmcontent_security_policy\Event\SourcesAlterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentSecurityPolicySourcesSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        $events[ContentSecurityPolicyEvents::SOURCES_ALTER][] = ['onSourcesAlter'];

        return $events;
    }

    public function onSourcesAlter(SourcesAlterEvent $event): void
    {
        if (!empty($_ENV['S3_CNAME'])) {
            $event->addSource('script-src', $_ENV['S3_CNAME']);
            $event->addSource('connect-src', $_ENV['S3_CNAME']);
            $event->addSource('style-src', $_ENV['S3_CNAME']);
        }
    }
}
```

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
