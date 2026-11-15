<?php
/**
 * Verify Joomla 5/6 administrator quickicons handle missing identities.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        /**
         * Simulate the SportsManagement component being enabled for quickicons.
         *
         * @param string $option Component extension name.
         * @param bool $strict Whether Joomla should require an enabled extension.
         * @return bool True for this isolated module regression.
         */
        public static function isEnabled(string $option, bool $strict = false): bool
        {
            return true;
        }
    }
}

namespace Joomla\CMS\Dispatcher {
    class AbstractModuleDispatcher
    {
        protected object $application;

        /**
         * Inject a fake Joomla administrator application into the dispatcher.
         *
         * @param object $application Fake application with language and identity.
         */
        public function __construct(object $application)
        {
            $this->application = $application;
        }

        /**
         * Return base module template context, as the real framework would.
         *
         * @return array<string,mixed> Base module variables.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 1]];
        }

        /**
         * Supply the application to the real quickicon dispatcher.
         *
         * @return object Fake administrator application.
         */
        protected function getApplication(): object
        {
            return $this->application;
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Preserve a translation key to simplify assertions.
         *
         * @param string $key Joomla translation key.
         * @return string Original key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        /**
         * Return the module link without an installed Joomla router.
         *
         * @param string $url Original internal URL.
         * @param bool $xhtml Whether HTML escaping is enabled.
         * @return string Unmodified internal link.
         */
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}

namespace Joomla\CMS\Uri {
    class Uri
    {
        /**
         * Provide a deterministic administrator base URL for icon assets.
         *
         * @return string Test administrator base URL.
         */
        public static function base(): string
        {
            return 'https://example.invalid/administrator/';
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/admin/modules/mod_sportsmanagement_quickicon/src/Dispatcher/Dispatcher.php';

    $application = new class {
        public ?object $identity = null;

        /**
         * Return the active fake administrator identity or null.
         *
         * @return object|null Active fake identity.
         */
        public function getIdentity(): ?object
        {
            return $this->identity;
        }

        /**
         * Supply the minimal Joomla language service used by the module.
         *
         * @return object Language stub with load() and getTag().
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Return the test site's configured language tag.
                 *
                 * @return string Language tag.
                 */
                public function getTag(): string
                {
                    return 'en-GB';
                }

                /**
                 * Accept module and component translation requests.
                 *
                 * @param string $extension Joomla extension name.
                 * @param string $basePath Root directory for translation files.
                 * @param string $tag Requested language tag.
                 * @param bool $reload Whether to reload translation files.
                 * @return bool Always true in the isolated test.
                 */
                public function load(string $extension, string $basePath, string $tag, bool $reload): bool
                {
                    return true;
                }
            };
        }
    };

    define('JPATH_SITE', '/site');
    define('JPATH_ADMINISTRATOR', '/administrator');

    $dispatcher = new \Diddipoeler\Module\SportsManagementQuickIcon\Administrator\Dispatcher\Dispatcher($application);
    $render = new \ReflectionMethod($dispatcher, 'getLayoutData');

    $data = $render->invoke($dispatcher);

    if (!is_array($data) || $data['canManage'] !== false || count($data['links']) !== 5) {
        throw new \RuntimeException('Administrator quickicons must deny missing identities.');
    }

    $application->identity = new class {
        /**
         * Allow only the administrator component management capability.
         *
         * @param string $action Joomla permission name.
         * @param string $asset Component being managed.
         * @return bool Whether the request matches the allowed operation.
         */
        public function authorise(string $action, string $asset): bool
        {
            return $action === 'core.manage' && $asset === 'com_sportsmanagement';
        }
    };

    $data = $render->invoke($dispatcher);

    if (!is_array($data) || $data['canManage'] !== true) {
        throw new \RuntimeException('Authorised administrators must retain quickicons.');
    }

    echo "Joomla 5/6 administrator quickicon identity guards: OK\n";
}
