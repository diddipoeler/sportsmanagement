<?php
/**
 * Exercise AJAX navigation dispatcher without an HTML document.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Dispatcher {
    abstract class AbstractModuleDispatcher
    {
        protected function getLayoutData(): array|false
        {
            return ['params' => new \TestParams(), 'module' => (object) ['id' => 42]];
        }

        protected function getApplication(): object
        {
            return new \TestApplication();
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument {}
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface {}

    trait HelperFactoryAwareTrait
    {
        protected function getHelperFactory(): object
        {
            return new \TestHelperFactory();
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/not-installed');

    class TestParams
    {
        private array $values = ['layout' => 'default'];

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }

        public function set(string $key, mixed $value): void
        {
            $this->values[$key] = $value;
        }
    }

    class TestApplication
    {
        public function isClient(string $client): bool { return $client === 'site'; }
        public function getLanguage(): object { return new class {
            public function load(mixed ...$args): bool { return true; }
        }; }
        public function getDocument(): object { return new \stdClass(); }
    }

    class TestHelperFactory
    {
        public function getHelper(string $name): object
        {
            return new class {
                public function getData(mixed ...$args): array { return ['clientConfig' => []]; }
            };
        }
    }

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_ajax_top_navigation_menu/src/Dispatcher/Dispatcher.php';

    $dispatcher = new \Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Dispatcher\Dispatcher();
    $result = (new \ReflectionMethod($dispatcher, 'getLayoutData'))->invoke($dispatcher);

    if (!is_array($result)
        || $result['legacyLayout'] !== 'default'
        || $result['params']->get('layout') !== 'native') {
        throw new \RuntimeException('AJAX navigation lost its Joomla 5/6 layout data.');
    }

    echo "AJAX navigation non-HTML document guard OK.\n";
}
