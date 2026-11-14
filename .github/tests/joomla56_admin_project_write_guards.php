<?php
/**
 * Regression for strict Joomla 5/6 administrator project model permissions.
 *
 * Models can be called outside the protected list controller. Check that
 * anonymous and denied writes fail before reading form input or a database.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public object $testApp;
        public int $databaseRequests = 0;

        /**
         * Supply the current fake Joomla backend application.
         *
         * @return object Application fixture.
         */
        protected function administratorApplication(): object
        {
            return $this->testApp;
        }

        /**
         * Reject all database access during unauthorised model tests.
         *
         * @return never No database requests are permitted for denied calls.
         */
        protected function getDatabase(): never
        {
            ++$this->databaseRequests;
            throw new \RuntimeException('Denied project action requested a database.');
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once \dirname(__DIR__, 2) . '/admin/src/Model/ProjectModel.php';

    $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel::class;
    $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();
    $app = new class {
        public ?object $identity = null;

        /** @return object|null Current test identity. */
        public function getIdentity(): ?object { return $this->identity; }

        /** @return never No POST data is permitted for denied requests. */
        public function getInput(): never
        {
            throw new \RuntimeException('Denied project action requested POST data.');
        }
    };
    $model->testApp = $app;

    $normalise = new \ReflectionMethod($model, 'normaliseIds');
    if ($normalise->invoke($model, [
        '007', 7, ' 23 ', true, [8], '5junk', '9e1', -2, 3.2,
        '000', '92233720368547758080', '10',
    ]) !== [7, 23, 10]
        || $normalise->invoke($model, '0008, 9; 010') !== [8, 9, 10]
        || $normalise->invoke($model, ['9:slug', false, new \stdClass()]) !== []) {
        throw new \RuntimeException('Malformed project keys were accepted for bulk writes.');
    }

    foreach ([null, new class {
        public int $id = 0;

        /**
         * Simulate a user who appears authorised but is not logged in.
         *
         * @param string $action Permission being checked.
         * @param string $asset Joomla component asset.
         * @return bool True, to exercise the separate identity check.
         */
        public function authorise(string $action, string $asset): bool { return true; }
    }, new class {
        public int $id = 20;

        /**
         * Simulate a logged-in user without component write permission.
         *
         * @param string $action Permission being checked.
         * @param string $asset Joomla component asset.
         * @return bool Always false for denied writes.
         */
        public function authorise(string $action, string $asset): bool { return false; }
    }] as $identity) {
        $app->identity = $identity;

        foreach (['setleaguechampion', 'copy', 'saveshort'] as $method) {
            if ($model->{$method}() !== false) {
                throw new \RuntimeException('Unauthorised ' . $method . ' was allowed.');
            }
        }

        $ids = [3, 5];

        if ($model->delete($ids) !== false || $ids !== [3, 5]
            || $model->deleteProjectsData([3, 5]) !== false) {
            throw new \RuntimeException('Unauthorised project removal was allowed.');
        }
    }

    if ($model->databaseRequests !== 0) {
        throw new \RuntimeException('Denied write created a database connection.');
    }

    echo "Joomla 5/6 administrator project permissions and bulk IDs: OK\n";
}
