<?php
/**
 * Regression for destructive Joomla 5/6 administrator match and league IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        /**
         * Abort when an invalid selection triggers database activity.
         *
         * @return never This test intentionally has no DB service.
         */
        protected function getDatabase(): never
        {
            throw new \RuntimeException('Malformed match/league selection reached SQL.');
        }

        /**
         * Supply the posted league IDs for inline-save safety tests.
         *
         * @return object Application stub with Joomla POST input.
         */
        protected function administratorApplication(): object
        {
            return new class {
                /**
                 * Provide test input and posted league selectors.
                 *
                 * @return object Simulated Joomla input.
                 */
                public function getInput(): object
                {
                    return new class {
                        public object $post;
                        /**
                         * Set only invalid IDs to verify no table is loaded.
                         *
                         * @return void
                         */
                        public function __construct()
                        {
                            $this->post = new class {
                                /**
                                 * Provide legacy list selections.
                                 *
                                 * @param string $name Field name.
                                 * @param mixed $default Default list.
                                 * @param string $type Requested input filter.
                                 * @return array Invalid ID selections.
                                 */
                                public function get(string $name, mixed $default = null, string $type = ''): array
                                {
                                    return ['12junk', '-3', true, '1e2', [], '99999999999999999999999'];
                                }

                                /**
                                 * Provide empty inline league edit values.
                                 *
                                 * @return array Empty form fields.
                                 */
                                public function getArray(): array { return []; }
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
    require_once dirname(__DIR__, 2) . '/admin/src/Model/LeagueModel.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Model/MatchModel.php';

    foreach ([
        \Diddipoeler\Component\SportsManagement\Administrator\Model\LeagueModel::class,
        \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchModel::class,
    ] as $class) {
        $model = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($model, 'normaliseSelectionIds');
        $valid = $method->invoke(null, ['007:alias', '7', 12, '12junk',
            '1e2', -1, true, [], '999999999999999999999999999', ' 18 ']);

        if ($valid !== [7, 12, 18]) {
            throw new \RuntimeException($class . ' accepted a destructive malformed selection.');
        }
    }

    $matchType = \Diddipoeler\Component\SportsManagement\Administrator\Model\MatchModel::class;
    $match = (new \ReflectionClass($matchType))->newInstanceWithoutConstructor();
    $invalid = ['12junk', '1e2', true, -1, [], '999999999999999999999'];
    if ($match->delete($invalid) !== true) {
        throw new \RuntimeException('Malformed-only match delete should not attempt persistence.');
    }

    $leagueType = \Diddipoeler\Component\SportsManagement\Administrator\Model\LeagueModel::class;
    $league = (new \ReflectionClass($leagueType))->newInstanceWithoutConstructor();
    if ($league->saveshort() !== true) {
        throw new \RuntimeException('Malformed-only league inline save must avoid all writes.');
    }

    echo "Match and league destructive selection validation OK\n";
}
