<?php
/**
 * Regression test for safe Joomla 5/6 sample-updater error reporting.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\CMS\Form {
    class Form
    {
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return a language key without requiring Joomla's translation files.
         *
         * @param string $key Translation key.
         * @return string Untranslated key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $entries = [];

        /**
         * Record diagnostic messages without writing to a production log.
         *
         * @param string $message Message under examination.
         * @param int $level Joomla log level.
         * @param string $category Component logging category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class FormModel
    {
        /**
         * Simulate a failed Joomla form factory containing a secret message.
         *
         * @param string $name Internal Joomla form name.
         * @param string $source XML form source.
         * @param array $options Joomla form loading options.
         * @return \Joomla\CMS\Form\Form Never reached.
         * @throws \RuntimeException Always, to test safe reporting.
         */
        protected function loadForm(string $name, string $source, array $options): \Joomla\CMS\Form\Form
        {
            throw new \RuntimeException('PRIVATE_FORM_PATH');
        }

        /**
         * Return a database stub that fails while storing a sample record.
         *
         * @return object Fake database service.
         */
        protected function getDatabase(): object
        {
            return new class {
                /**
                 * Simulate a failed write with sensitive connection details.
                 *
                 * @param string $table Target database table.
                 * @param object $record Sample model record.
                 * @param string $key Primary key field.
                 * @return bool Never reached.
                 * @throws \RuntimeException Always, to exercise safe reporting.
                 */
                public function updateObject(string $table, object $record, string $key): bool
                {
                    throw new \RuntimeException('PRIVATE_DATABASE_PASSWORD');
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver
    {
        public static ?\Joomla\CMS\Application\CMSApplicationInterface $application = null;

        /**
         * Supply a fake site application for the updater model.
         *
         * @return \Joomla\CMS\Application\CMSApplicationInterface Fake site.
         */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return self::$application;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/UpdsportsmanagementModel.php';

    $application = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];

        /**
         * Record messages shown to site visitors.
         *
         * @param string $message Public Joomla message.
         * @param string $type Message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = $message;
        }

        /**
         * Provide a user authorised to edit the sample record.
         *
         * @return object Fake Joomla identity.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Permit the SportsManagement component editing action.
                 *
                 * @param string $action Requested permission.
                 * @param string $asset Joomla component.
                 * @return bool Whether this user may edit.
                 */
                public function authorise(string $action, string $asset): bool
                {
                    return $action === 'core.edit' && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::$application = $application;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel();

    if ($model->getForm() !== false) {
        throw new \RuntimeException('A failed form load was reported as successful.');
    }

    if ($model->updItem(['id' => 1, 'greeting' => 'Demo']) !== false) {
        throw new \RuntimeException('A failed database write was reported as successful.');
    }

    $display = implode(' ', $application->messages);
    $logs = implode(' ', array_column(\Joomla\CMS\Log\Log::$entries, 0));

    if (count($application->messages) !== 2
        || !str_contains($display, 'JERROR_AN_ERROR_HAS_OCCURRED')
        || str_contains($display, 'PRIVATE_')
        || str_contains($logs, 'PRIVATE_')
        || !str_contains($logs, 'RuntimeException')) {
        throw new \RuntimeException('Sample updater disclosed private form or database details.');
    }

    echo "Joomla 5/6 sample updater safe exceptions: OK\n";
}
