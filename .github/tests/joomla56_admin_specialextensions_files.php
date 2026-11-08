<?php
/**
 * Test Joomla 5/6 administrator extension directories and empty list queries.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel
    {
        /**
         * Supply Joomla's minimal read-only list database service.
         *
         * @return object Fake database with a fluent empty query.
         */
        protected function getDatabase(): object
        {
            return new class {
                /**
                 * Create the deliberately empty Joomla list query.
                 *
                 * @return object Fluent SQL query fixture.
                 */
                public function createQuery(): object
                {
                    return new class {
                        public array $where = [];

                        /**
                         * Accept a list query column selector without fetching data.
                         *
                         * @param string $select SQL column expression.
                         * @return self Fluent query stub.
                         */
                        public function select(string $select): self { return $this; }

                        /**
                         * Capture the false filter expected from a file-backed list.
                         *
                         * @param string $condition SQL WHERE restriction.
                         * @return self Fluent query stub.
                         */
                        public function where(string $condition): self
                        {
                            $this->where[] = $condition;
                            return $this;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    $root = sys_get_temp_dir() . '/sm56-special-extensions-' . bin2hex(random_bytes(6));
    define('JPATH_SITE', $root);
    define('_JEXEC', 1);
    $extensionDir = $root . '/components/com_sportsmanagement/extensions';
    $externalDir = $root . '/external';

    if (!mkdir($extensionDir, 0700, true) || !mkdir($externalDir, 0700)) {
        throw new \RuntimeException('Failed to create temporary Joomla extension directory.');
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Model/SpecialextensionsModel.php';

    try {
        mkdir($extensionDir . '/Project10');
        mkdir($extensionDir . '/Project2');
        file_put_contents($extensionDir . '/readme.txt', 'ignore files');

        if (function_exists('symlink')) {
            @symlink($externalDir, $extensionDir . '/external-link');
        }

        $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SpecialextensionsModel();
        $result = $model->getSpecialExtensions();

        if ($result !== ['Project2', 'Project10']) {
            throw new \RuntimeException('Filesystem list followed a symlink or lost natural sorting.');
        }

        $listQuery = new \ReflectionMethod($model, 'getListQuery');
        $query = $listQuery->invoke($model);
        if ($query->where !== ['1 = 0']) {
            throw new \RuntimeException('Filesystem-backed list unexpectedly retrieves SQL records.');
        }
    } finally {
        if (is_link($extensionDir . '/external-link')) {
            unlink($extensionDir . '/external-link');
        }
        @unlink($extensionDir . '/readme.txt');
        @rmdir($extensionDir . '/Project10');
        @rmdir($extensionDir . '/Project2');
        @rmdir($extensionDir);
        @rmdir(dirname($extensionDir));
        @rmdir($root . '/components');
        @rmdir($externalDir);
        @rmdir($root);
    }

    echo "Joomla 5/6 special extension listing and symlink filtering passed.\n";
}
