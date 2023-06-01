# Craft Twig View Composers

Allow composable PHP classes to be called before page view rendering, hence allowing organizing code à-la Laravel View Composers.

## Configuration

Install the plugin

```
composer require guimauve/craft-twig-view-composer
php craft plugin/install _twig-view-composers
```

Then, add the following path to your composer autoloader in composer.json. You can name the second part, which is the directory that psr-4 is going to look into, as you wish.

```
"autoload": {
"psr-4": {
  "guimauve\\composers\\": "composers/"
}
},
```

In that folder, you can now create a simple PHP class, named according to your twig template, with a "compose" method and you will receive the TemplateEvent as single argument. You can now add anything you want to the template data and thus isolate functionality, such as database calls, outside of the templates.

```php
<?php
/**
 * Basic view composer for the templates/index.twig file.
 * To create a view composer for a subfolder, simply replicate the structure here.
 * Ex.: templates/blog/_entry.twig -> composers/blog/Entry.php
 *
 * Element queries are made with PHP to
 * avoid using their twig counterparts
 * @see https://craftcms.com/docs/4.x/element-queries.html
 */

namespace guimauve\composers;

use craft\events\TemplateEvent;
use craft\elements\Entry;
use Craft;

class Index
{

    /**
     * @param      TemplateEvent $event Object passed by View::EVENT_BEFORE_RENDER_TEMPLATE
     * @see        https://github.com/craftcms/cms/blob/a1b232ea1888f131bb7626a5bdaff0f5fa2f4469/src/web/View.php#L1891
     * @see        https://github.com/craftcms/cms/blob/2eac9249964ccc553bf841c79b9ee44d58f16b61/src/events/TemplateEvent.php
     *
     * @return     void
     */
    public static function compose(TemplateEvent $event)
    {
         $locations = Entry::find()->section('shop')->relatedTo($selectedCity)->with([
            [
                'featuredImage', ['withTransforms' => ['x870']]
            ]
        ])->collect();
        $event->variables['locations'] = $locations;
    }
}

```

If you use a block based system and want to call the same code on multiple template or pages, there's sadly no way to bind one view composer to multiple templates (yet), but you can use a PHP Trait to make most of your code re-usable.

```php
namespace guimauve\composers\traits;

use Craft;
use craft\helpers\App;

trait blockableTrait {
    public static function getBlocksContent() {
        /**
         * Do your thing
         */
    }
}
```

And in your view composer

```php
namespace guimauve\composers;

use craft\events\TemplateEvent;
use craft\elements\Entry;
use Craft;

class Index
{
    use \guimauve\composers\traits\blockableTrait;

    /**
     * @param      TemplateEvent $event Object passed by View::EVENT_BEFORE_RENDER_TEMPLATE
     * @see        https://github.com/craftcms/cms/blob/a1b232ea1888f131bb7626a5bdaff0f5fa2f4469/src/web/View.php#L1891
     * @see        https://github.com/craftcms/cms/blob/2eac9249964ccc553bf841c79b9ee44d58f16b61/src/events/TemplateEvent.php
     *
     * @return     void
     */
    public static function compose(TemplateEvent $event)
    {
        $event->variables['blocks'] = self::getBlocksContent();
    }
}

```

## Requirements

This plugin requires Craft CMS 4.4.0 or later, and PHP 8.0.2 or later.

