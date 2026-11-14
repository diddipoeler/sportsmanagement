<?php
/**
 * Regression tests for Joomla 5/6 site extended forms and administrator/module guards.
 *
 * Usage: php .github/tests/joomla56_extended_form_extra_select_ranking_guards.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Application {
    /** Minimal site application fixture for frontend form tests. */
    class SiteApplication
    {
        public array $messages = [];

        /**
         * Capture the public-facing message produced by a failed form load.
         *
         * @param string $message Translated message shown to a visitor.
         * @param string $type Joomla message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type): void
        {
            $this->messages[] = [$message, $type];
        }
    }
}

namespace Joomla\CMS {
    /** Test-only service container exposing the Joomla site application. */
    class Factory
    {
        /**
         * Return a deterministic service container for the form helper test.
         *
         * @return object Service locator with a get method.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the requested test service without booting Joomla.
                 *
                 * @param string $service Service class name.
                 * @return object Corresponding test application.
                 */
                public function get(string $service): object
                {
                    return $GLOBALS['smFixtureApp'];
                }
            };
        }
    }
}

namespace Joomla\CMS\Form {
    /** Named contract required by the site's Joomla 5/6 form helper. */
    class Form
    {
    }

    /** Named form factory contract required by the site's use declaration. */
    interface FormFactoryInterface
    {
    }
}

namespace Joomla\CMS\Language {
    /** Minimal translation provider returning a generic error token. */
    class Text
    {
        /**
         * Translate a Joomla error key into deterministic safe test text.
         *
         * @param string $key Joomla translation key.
         * @return string Translated marker for assertions.
         */
        public static function _(string $key): string
        {
            return 'generic error: ' . $key;
        }
    }
}

namespace Joomla\CMS\Log {
    /** In-memory logger verifying that exceptions never reach public markup. */
    class Log
    {
        public const WARNING = 2;
        public static array $messages = [];

        /**
         * Store the private logging summary for test inspection.
         *
         * @param string $message Internal diagnostic summary.
         * @param int $priority Joomla log severity.
         * @param string $category Logger category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void
        {
            self::$messages[] = $message;
        }
    }
}

namespace Joomla\Registry {
    /** Test registry which throws a deliberately sensitive parsing exception. */
    class Registry
    {
        /**
         * Simulate an invalid serialized Registry configuration.
         *
         * @param string $data Stored form configuration.
         * @return void
         * @throws \RuntimeException Always, including a secret test marker.
         */
        public function loadString(string $data): void
        {
            throw new \RuntimeException('PRIVATE_CONFIGURATION_SECRET');
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);
    $tmp = \sys_get_temp_dir() . '/sm-form-regression-' . \bin2hex(\random_bytes(8));
    $formDir = $tmp . '/components/com_sportsmanagement/assets/extended';
    \mkdir($formDir, 0777, true);
    \file_put_contents($formDir . '/valid.xml', '<form/>');
    \file_put_contents($tmp . '/outside.xml', '<form/>');
    \define('JPATH_ADMINISTRATOR', $tmp);

    $GLOBALS['smFixtureApp'] = new \Joomla\CMS\Application\SiteApplication();
    require_once $root . '/site/src/Helper/ExtendedFormHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper::class;

    try {
        foreach (['../valid', 'folder/valid', 'valid.xml', ' invalid', \str_repeat('a', 101)] as $invalid) {
            if ($helper::load('', $invalid) !== false) {
                throw new \RuntimeException('Unsafe extended form filename was accepted.');
            }
        }

        // A syntactically valid filename must not follow an XML symlink
        // outside the SportsManagement form definition directory.
        if (function_exists('symlink')
            && @symlink($tmp . '/outside.xml', $formDir . '/escaped.xml')
            && $helper::load('', 'escaped') !== false) {
            throw new \RuntimeException('Site extended forms accepted an external XML symlink.');
        }

        if ($helper::load('bad serialized data', 'valid') !== false) {
            throw new \RuntimeException('Malformed extended form Registry data was accepted.');
        }

        $messages = $GLOBALS['smFixtureApp']->messages;

        if (\count($messages) !== 1
            || $messages[0][0] !== 'generic error: JERROR_AN_ERROR_HAS_OCCURRED'
            || \str_contains($messages[0][0], 'PRIVATE_CONFIGURATION_SECRET')) {
            throw new \RuntimeException('Extended form exception leaked to site visitors.');
        }

        foreach (\Joomla\CMS\Log\Log::$messages as $message) {
            if (\str_contains($message, 'PRIVATE_CONFIGURATION_SECRET')) {
                throw new \RuntimeException('Private error details leaked into log text.');
            }
        }

        $adminHelper = \file_get_contents($root . '/admin/src/Helper/ExtraSelectOptionsHelper.php');
        $rankingDispatcher = \file_get_contents(
            $root . '/modules/mod_sportsmanagement_ranking/src/Dispatcher/Dispatcher.php'
        );

        if ($adminHelper === false
            || !\str_contains($adminHelper, 'strlen($rawLabels) > 16384')
            || \str_contains($adminHelper, '$e->getMessage()')) {
            throw new \RuntimeException('Administrator extra-field bounds or error privacy regressed.');
        }

        if ($rankingDispatcher === false
            || !\preg_match('/if \(!\$app->isClient\(\x27site\x27\)\).*?\$data\[\x27params\x27\]->set\(\x27layout\x27, \x27native\x27\)/s', $rankingDispatcher)) {
            throw new \RuntimeException('Ranking dispatcher must validate the site client before changing layout.');
        }

        echo "Site extended-form, admin extra-field and ranking module guards: OK\n";
    } finally {
        if (is_link($formDir . '/escaped.xml')) {
            \unlink($formDir . '/escaped.xml');
        }
        \unlink($tmp . '/outside.xml');
        \unlink($formDir . '/valid.xml');
        \rmdir($formDir);
        \rmdir(\dirname($formDir));
        \rmdir(\dirname(\dirname($formDir)));
        \rmdir(\dirname(\dirname(\dirname($formDir))));
        \rmdir($tmp);
    }
}
