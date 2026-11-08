<?php
/**
 * Test Joomla 5/6 frontend view errors never expose private SQL messages.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Provide the site's generic translated error instead of SQL details.
         *
         * @param string $key Joomla translation lookup key.
         * @return string Public-safe error text.
         */
        public static function _(string $key): string
        {
            return $key === 'JERROR_AN_ERROR_HAS_OCCURRED' ? 'A generic site error.' : $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record Joomla log details without displaying them in the view.
         *
         * @param string $message Private model diagnostics.
         * @param int $level Joomla message severity.
         * @param string $category Component log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\MVC\View {
    class HtmlView
    {
        public array $data = [];
        public bool $rendered = false;

        /**
         * Return requested values from the stub Joomla MVC model.
         *
         * @param string $name Model data key such as Item or Errors.
         * @return mixed Value provided to the test view.
         */
        public function get(string $name): mixed
        {
            return $this->data[$name] ?? null;
        }

        /**
         * Record normal Joomla frontend layout rendering in the test.
         *
         * @param string|null $tpl Optional view template suffix.
         * @return void
         */
        public function display($tpl = null)
        {
            $this->rendered = true;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver
    {
        public static array $styles = [];

        /**
         * Supply the Joomla site document and asset manager for JSON forms.
         *
         * @return object Fake site application.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Return a stand-in document with the standard asset manager.
                 *
                 * @return object Joomla-like document.
                 */
                public function getDocument(): object
                {
                    return new class {
                        /**
                         * Provide an asset manager recording stylesheet usage.
                         *
                         * @return object Fake Joomla web asset manager.
                         */
                        public function getWebAssetManager(): object
                        {
                            return new class {
                                /**
                                 * Record the component stylesheet registered by JSON views.
                                 *
                                 * @param string $name Joomla asset identifier.
                                 * @param string $uri Media URL to the component style.
                                 * @param array $options Versioned Joomla style options.
                                 * @return void
                                 */
                                public function registerAndUseStyle(
                                    string $name,
                                    string $uri,
                                    array $options = []
                                ): void {
                                    SportsManagementSiteApplicationResolver::$styles[] = [$name, $uri, $options];
                                }
                            };
                        }
                    };
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);

    $root = dirname(__DIR__, 2) . '/site/src/';
    require_once $root . 'Service/SportsManagementViewErrorReporter.php';
    require_once $root . 'View/Sportsmanagement/HtmlView.php';
    require_once $root . 'View/Json/HtmlView.php';

    $views = [
        \Diddipoeler\Component\SportsManagement\Site\View\Sportsmanagement\HtmlView::class,
        \Diddipoeler\Component\SportsManagement\Site\View\Json\HtmlView::class,
    ];

    foreach ($views as $viewClass) {
        \Joomla\CMS\Log\Log::$entries = [];
        \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::$styles = [];
        $view = new $viewClass();
        $view->data = [
            'Errors' => ['SQLSTATE connection_password=private', new \RuntimeException('Private DB hostname')],
            'Item' => (object) ['id' => 12],
            'Form' => (object) ['id' => 3],
            'State' => (object) ['filter' => 1],
        ];

        try {
            $view->display();
            throw new \RuntimeException('The frontend ignored its model error collection.');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 500
                || $error->getMessage() !== 'A generic site error.'
                || $view->rendered) {
                throw $error;
            }
        }

        $entries = \Joomla\CMS\Log\Log::$entries;
        if (count($entries) !== 2 || !str_contains($entries[0][0], 'password=private')
            || !str_contains($entries[1][0], 'Private DB hostname')
            || $entries[0][1] !== \Joomla\CMS\Log\Log::ERROR
            || $entries[0][2] !== 'com_sportsmanagement') {
            throw new \RuntimeException('Private Joomla MVC errors were not recorded correctly.');
        }

        $view->data['Errors'] = [];
        $view->display();
        if (!$view->rendered) {
            throw new \RuntimeException('The healthy Joomla view did not render.');
        }
    }

    $styles = \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::$styles;
    if (count($styles) !== 1
        || $styles[0][0] !== 'com_sportsmanagement.site'
        || $styles[0][1] !== 'media/com_sportsmanagement/css/site.stylesheet.css') {
        throw new \RuntimeException('Successful JSON view did not register its native Joomla CSS.');
    }

    echo "Joomla 5/6 root and JSON view model error privacy passed.\n";
}
