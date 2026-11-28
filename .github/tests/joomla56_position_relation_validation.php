<?php
/**
 * Regression coverage for malformed administrator position relations.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        /** Return the original translation key for assertions. */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel {
        public int $databaseCalls = 0;
        public string $error = '';

        /** Supply an authorised identity so input validation is exercised. */
        protected function administratorApplication(): object {
            return new class {
                /** Return an authenticated administrator fixture. */
                public function getIdentity(): object {
                    return new class {
                        public int $id = 7;

                        /** Permit component edits in this validation fixture. */
                        public function authorise(string $action, string $asset): bool {
                            return $action === 'core.edit' && $asset === 'com_sportsmanagement';
                        }
                    };
                }
            };
        }

        /** Record validation errors. */
        public function setError(string $message): void { $this->error = $message; }

        /** Fail if malformed input reaches the database boundary. */
        protected function getDatabase(): never {
            ++$this->databaseCalls;
            throw new \LogicException('Database boundary reached.');
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Model/PositioneventtypeModel.php';
    require_once $root . '/admin/src/Model/PositionstatisticModel.php';

    foreach ([
        \Diddipoeler\Component\SportsManagement\Administrator\Model\PositioneventtypeModel::class => 'position_eventslist',
        \Diddipoeler\Component\SportsManagement\Administrator\Model\PositionstatisticModel::class => 'position_statistic',
    ] as $class => $field) {
        foreach ([[], [$field => null], [$field => '7'], [$field => false],
            [$field => ['7junk']], [$field => [[7]]], [$field => [true]],
            [$field => [-7]], [$field => [1.5]],
            [$field => ['99999999999999999999999999']],
            [$field => [7, 'bad']],
        ] as $input) {
            $model = new $class();
            if ($model->store($input, 5) !== false || $model->databaseCalls !== 0
                || $model->error !== 'JLIB_APPLICATION_ERROR_SAVE_FAILED') {
                throw new \RuntimeException('Malformed relation reached persistence: ' . $class);
            }
        }

        foreach ([[], [''], [7, '0008', '7']] as $valid) {
            $model = new $class();
            try {
                $model->store([$field => $valid], 5);
                throw new \RuntimeException('Valid selection did not reach persistence.');
            } catch (\LogicException $expected) {
                if ($model->databaseCalls !== 1) {
                    throw new \RuntimeException('Unexpected database access count.');
                }
            }
        }
    }

    echo "Position relation validation boundaries: OK\n";
}
