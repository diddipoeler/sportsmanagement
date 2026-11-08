<?php
/**
 * Joomla 5/6 season relation ACL and prediction group creation regression.
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
        public static ?object $identity = null;
        public static bool $parentDecision = false;
        public static int $parentCalls = 0;
        public static array $messages = [];

        /**
         * Return a minimal Joomla administrator application and identity.
         *
         * @return object Stand-in application with current identity and message queue.
         */
        protected function administratorApplication(): object
        {
            return new class {
                /**
                 * Return the simulated identity for authorisation tests.
                 *
                 * @return object|null Authenticated identity or no user.
                 */
                public function getIdentity(): ?object
                {
                    return SportsManagementAdminModel::$identity;
                }

                /**
                 * Capture Joomla creation messages for pluralisation checks.
                 *
                 * @param string $text Translated success message.
                 * @param string $type Joomla message severity.
                 * @return void
                 */
                public function enqueueMessage(string $text, string $type): void
                {
                    SportsManagementAdminModel::$messages[] = [$text, $type];
                }
            };
        }

        /**
         * Simulate the authoritative Joomla AdminModel record ACL decision.
         *
         * @param array $data Submitted relation record.
         * @param string $key Field storing the relation ID.
         * @return bool Configurable Joomla permission decision.
         */
        protected function allowEdit($data = [], $key = 'id')
        {
            ++self::$parentCalls;
            return self::$parentDecision;
        }

        /**
         * Provide a fallback form table for unrelated prediction group types.
         *
         * @param string $type Table type requested by Joomla.
         * @param string $prefix Legacy table prefix.
         * @param array $config Table configuration.
         * @return object Fallback table stand-in.
         */
        public function getTable($type = '', $prefix = '', $config = [])
        {
            return new \stdClass();
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Format the number of created Joomla administrator records.
         *
         * @param string $key Plural translation key.
         * @param int $count Number of records actually created.
         * @return string Simulated translated creation message.
         */
        public static function plural(string $key, int $count): string
        {
            return $key . ':' . $count;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    $root = dirname(__DIR__, 2) . '/admin/src/Model/';
    foreach (['SeasonteamModel.php', 'SeasonpersonModel.php',
        'SeasonteampersonModel.php', 'PredictiongroupModel.php',
        'SportsmanagementModel.php'] as $name) {
        require_once $root . $name;
    }

    $base = \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::class;
    $classes = [
        \Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonteamModel::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonpersonModel::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonteampersonModel::class,
    ];

    $identity = new class {
        public int $checks = 0;

        /**
         * Expose a legacy permission that must never bypass Joomla model ACL.
         *
         * @param string $action Required Joomla permission.
         * @param string $asset Asset being authorised.
         * @return bool Grant only the unrelated legacy message permission.
         */
        public function authorise(string $action, string $asset): bool
        {
            ++$this->checks;
            return $action === 'core.edit' && $asset === 'com_sportsmanagement.message.42';
        }
    };

    foreach ($classes as $class) {
        $model = new $class();
        $method = new \ReflectionMethod($model, 'allowEdit');

        $base::$identity = null;
        $base::$parentDecision = true;
        $base::$parentCalls = 0;
        if ($method->invoke($model, ['id' => 42]) !== false || $base::$parentCalls !== 0) {
            throw new \RuntimeException('Unauthenticated relation edit reached Joomla parent model.');
        }

        $base::$identity = $identity;
        $base::$parentDecision = false;
        if ($method->invoke($model, ['id' => 42]) !== false || $base::$parentCalls !== 1) {
            throw new \RuntimeException('Legacy message asset incorrectly granted a relation edit.');
        }

        $base::$parentDecision = true;
        if ($method->invoke($model, ['id' => 42]) !== true || $base::$parentCalls !== 2) {
            throw new \RuntimeException('Native Joomla parent permission was not honoured.');
        }
    }

    if ($identity->checks !== 0) {
        throw new \RuntimeException('Relation models still query unrelated message ACL assets.');
    }

    // The legacy sample table really defines message.<id> as its record
    // ACL asset. Preserve that permission while fixing null identities.
    $legacy = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsmanagementModel();
    $legacyEdit = new \ReflectionMethod($legacy, 'allowEdit');

    $base::$identity = null;
    $base::$parentDecision = true;
    if ($legacyEdit->invoke($legacy, ['id' => 42]) !== false) {
        throw new \RuntimeException('Legacy sample record accepted a missing administrator identity.');
    }

    $base::$identity = $identity;
    $base::$parentDecision = false;
    if ($legacyEdit->invoke($legacy, ['id' => 42]) !== true) {
        throw new \RuntimeException('Legacy sample record lost its real message asset ACL.');
    }

    $group = new \Diddipoeler\Component\SportsManagement\Administrator\Model\PredictiongroupModel();
    $afterSave = new \ReflectionMethod($group, 'afterSportsManagementSave');
    $base::$messages = [];
    $afterSave->invoke($group, ['name' => 'Test'], 927, true);
    $afterSave->invoke($group, ['name' => 'Test'], 927, false);

    if ($base::$messages !== [['COM_SPORTSMANAGEMENT_N_ITEMS_CREATED:1', 'message']]) {
        throw new \RuntimeException('Prediction group creation used its row ID as a plural count.');
    }

    echo "Joomla 5/6 season relation ACL and prediction group creation checks passed.\n";
}
