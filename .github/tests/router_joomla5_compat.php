<?php

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    define('JPATH_SITE', $root . '/site');
    define('JPATH_ADMINISTRATOR', $root . '/admin');
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\CMS\Categories {
    interface CategoryFactoryInterface
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\CMS\Menu {
    abstract class AbstractMenu
    {
        abstract public function getItem($id);
        abstract public function getItems($attributes, $values, $firstonly = false);
        abstract public function getActive();
    }
}

namespace Joomla\CMS\Component\Router {
    interface RouterInterface
    {
        public function preprocess($query);
        public function build(&$query);
        public function parse(&$segments);
    }

    abstract class RouterBase implements RouterInterface
    {
        public $app;
        public $menu;

        public function __construct($app = null, $menu = null)
        {
            $this->app = $app;
            $this->menu = $menu;
        }

        public function preprocess($query)
        {
            return $query;
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static function getComponent($option)
        {
            return (object) ['id' => 99, 'option' => $option];
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static $application;

        public static function getApplication()
        {
            return self::$application;
        }
    }
}

namespace SportsManagementRouterSmokeTest {
    use Joomla\CMS\Application\CMSApplicationInterface;
    use Joomla\CMS\Factory;
    use Joomla\CMS\Menu\AbstractMenu;

    final class Language
    {
        public function getTag(): string
        {
            return 'de-DE';
        }
    }

    final class Identity
    {
        public function getAuthorisedViewLevels(): array
        {
            return [1];
        }
    }

    final class Menu extends AbstractMenu
    {
        private array $items;
        private $active;

        public function __construct(array $items, $active)
        {
            $this->items = $items;
            $this->active = $active;
        }

        public function getItem($id)
        {
            return $this->items[(int) $id] ?? null;
        }

        public function getItems($attributes, $values, $firstonly = false)
        {
            $attributes = (array) $attributes;
            $values = (array) $values;
            $matches = [];

            foreach ($this->items as $item) {
                $match = true;

                foreach ($attributes as $index => $attribute) {
                    $expected = $values[$index] ?? null;

                    if (($item->{$attribute} ?? null) != $expected) {
                        $match = false;
                        break;
                    }
                }

                if (!$match) {
                    continue;
                }

                if ($firstonly) {
                    return $item;
                }

                $matches[] = $item;
            }

            return $matches;
        }

        public function getActive()
        {
            return $this->active;
        }
    }

    final class Application implements CMSApplicationInterface
    {
        private Menu $menu;

        public function __construct(Menu $menu)
        {
            $this->menu = $menu;
        }

        public function getMenu(): Menu
        {
            return $this->menu;
        }

        public function getLanguageFilter(): bool
        {
            return true;
        }

        public function getLanguage(): Language
        {
            return new Language();
        }

        public function getIdentity(): Identity
        {
            return new Identity();
        }
    }

    function menuItem(int $id, string $language, array $query): object
    {
        return (object) [
            'id' => $id,
            'component' => 'com_sportsmanagement',
            'component_id' => 99,
            'published' => 1,
            'access' => 1,
            'language' => $language,
            'type' => 'component',
            'query' => $query,
        ];
    }

    function assertSame($expected, $actual, string $message): void
    {
        if ($expected !== $actual) {
            fwrite(
                STDERR,
                $message . "\nExpected: " . var_export($expected, true)
                . "\nActual: " . var_export($actual, true) . "\n"
            );
            exit(1);
        }
    }

    $english = menuItem(10, 'en-GB', [
        'option' => 'com_sportsmanagement',
        'view' => 'teamplan',
        'p' => '7',
    ]);
    $german = menuItem(20, 'de-DE', [
        'option' => 'com_sportsmanagement',
        'view' => 'teamplan',
        'p' => '7',
        'division' => '3',
        'menu_context' => 'from-menu',
    ]);
    $neutral = menuItem(30, '*', [
        'option' => 'com_sportsmanagement',
        'view' => 'teamplan',
        'p' => '7',
    ]);

    $menu = new Menu([
        10 => $english,
        20 => $german,
        30 => $neutral,
    ], $german);
    $app = new Application($menu);
    Factory::$application = $app;

    $root = dirname(__DIR__, 2);
    require $root . '/site/src/Service/SiteRouteSchema.php';
    require $root . '/site/src/Service/Router.php';
    require $root . '/site/router.php';

    $router = new \SportsmanagementRouter($app, $menu);

    $query = [
        'option' => 'com_sportsmanagement',
        'view' => 'teamplan',
        'cfg_which_database' => '0',
        's' => '12',
        'p' => '7',
        'division' => '3',
        'Itemid' => 10,
    ];
    $query = $router->preprocess($query);

    assertSame(20, $query['Itemid'] ?? null, 'Joomla 5 must replace a foreign-language Itemid.');

    $segments = ['teamplan', '0', '12', '7', '44', '3', '0', '55', 'legacy-extra'];
    $vars = $router->parse($segments);

    assertSame([], $segments, 'Joomla 5 must consume recognised SportsManagement component segments.');
    assertSame('teamplan', $vars['view'] ?? null, 'Parsed view was not preserved.');
    assertSame('44', $vars['tid'] ?? null, 'Parsed team id was not preserved.');
    assertSame('3', $vars['division'] ?? null, 'Parsed division was not preserved.');
    assertSame('55', $vars['ptid'] ?? null, 'Parsed project team id was not preserved.');
    assertSame('from-menu', $vars['menu_context'] ?? null, 'Active SportsManagement menu query was not inherited.');

    echo "Joomla 5 SportsManagement router smoke test passed.\n";
}
