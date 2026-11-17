<?php
/**
 * Test that invalid Joomla administrator extra fields never reach database writes.
 *
 * Run: php .github/tests/joomla56-admin-extra-fields-invalid-input-runtime.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    /** Minimal database contract for an isolated administrator save test. */
    interface DatabaseInterface
    {
    }
}

namespace Joomla\CMS\Application {
    /** Minimal application contract for the form helper test. */
    interface CMSApplicationInterface
    {
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    use Joomla\CMS\Application\CMSApplicationInterface;
    use Joomla\Database\DatabaseInterface;

    /** Provide a standalone administrator application for save preflight checks. */
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Return the application with a stable non-copy task for the test.
         *
         * @return CMSApplicationInterface Minimal administrator application.
         */
        public static function resolve(): CMSApplicationInterface
        {
            return new class implements CMSApplicationInterface {
                /**
                 * Expose the form task as a Joomla-like input object.
                 *
                 * @return object Request input with a getCmd() method.
                 */
                public function getInput(): object
                {
                    return new class {
                        /**
                         * Report a regular save task instead of save-as-copy.
                         *
                         * @param string $name Requested input key.
                         * @return string Current form submission task.
                         */
                        public function getCmd(string $name): string
                        {
                            return 'save';
                        }
                    };
                }
            };
        }
    }

    /** Accept the explicitly provided test driver without service lookup. */
    final class SportsManagementDatabaseResolver
    {
        /**
         * Return the caller-supplied database without opening a connection.
         *
         * @param mixed $selector Unused legacy database selector.
         * @param DatabaseInterface|null $database Injected test database.
         * @return DatabaseInterface Database used for write attempts.
         */
        public function resolve(mixed $selector, ?DatabaseInterface $database): DatabaseInterface
        {
            if ($database === null) {
                throw new \RuntimeException('Missing injected database test fixture.');
            }

            return $database;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsSaveHelper;

    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Helper/ExtraFieldsSaveHelper.php';

    $database = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;

        /**
         * Track any attempted query, including a destructive DELETE.
         *
         * @return never No query should be constructed for malformed input.
         */
        public function createQuery(): never
        {
            ++$this->queries;
            throw new \RuntimeException('Invalid extra field unexpectedly reached SQL construction.');
        }
    };

    $helper = new ExtraFieldsSaveHelper();
    $badValues = [
        "invalid\0value",
        "broken\xC3\x28",
        str_repeat('x', 65536),
        ['unexpected', 'nested', 'value'],
    ];

    foreach ($badValues as $value) {
        $helper->save([
            'extraf' => [0 => $value],
            'extra_id' => [0 => '34'],
        ], 17, $database);
    }

    if ($database->queries !== 0) {
        throw new \RuntimeException('Malformed fields reached database write operations.');
    }

    echo "Joomla 5/6 administrator invalid extra-field preflight passed.\n";
}
