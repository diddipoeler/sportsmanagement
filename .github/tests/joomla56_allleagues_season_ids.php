<?php
/**
 * Validate Joomla 5/6 public all-leagues season filter selectors.
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
        public static mixed $currentSeasons = null;

        /**
         * Provide configurable component settings for season parsing.
         *
         * @param string $name Joomla component identifier.
         * @return object Component parameters implementing get().
         */
        public static function getParams(string $name): object
        {
            if ($name !== 'com_sportsmanagement') {
                throw new \RuntimeException('Unexpected component for league list.');
            }
            return new class {
                /**
                 * Read the current test's configured seasons.
                 *
                 * @param string $key Component parameter name.
                 * @param mixed $default Value when no setting exists.
                 * @return mixed Requested simulated parameter.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return ComponentHelper::$currentSeasons ?? $default;
                }
            };
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementListModel {}
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/AllleaguesModel.php';
    $class = \Diddipoeler\Component\SportsManagement\Site\Model\AllleaguesModel::class;
    $model = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
    $method = new \ReflectionMethod($model, 'getCurrentSeasonIds');
    $settings = \Joomla\CMS\Component\ComponentHelper::class;
    $settings::$currentSeasons = ['007:season', '7', '8', '9bad', '1e2',
        '99999999999999999999999999999', [], false, -1, '10', '10'];
    if ($method->invoke($model) !== [7, 8, 10]) {
        throw new \RuntimeException('Malformed seasons reached the league-list filter.');
    }
    $settings::$currentSeasons = '12:one,13; 014|15:year';
    if ($method->invoke($model) !== [12, 13, 14, 15]) {
        throw new \RuntimeException('Valid legacy season options were not preserved.');
    }
    $settings::$currentSeasons = ['42bad', '-1', 0, true, '0'];
    if ($method->invoke($model) !== []) {
        throw new \RuntimeException('All-invalid season selections must remain empty.');
    }
    echo "Allleagues current season validation OK\n";
}
