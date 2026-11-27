<?php
/**
 * Joomla 5/6 administrator team-season save and cleanup input regression.
 *
 * Exercises TeamModel::save() before parent persistence and independently
 * verifies that invalid season associations cannot reach SQL cleanup.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return an untranslated Joomla language token for model errors.
         *
         * @param string $key Joomla error translation key.
         * @return string Stable error token.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public int $saves = 0;
        public int $databaseCalls = 0;
        public array $savedData = [];
        public string $error = '';

        /**
         * Simulate successful Joomla persistence for a valid team selection.
         *
         * @param mixed $data Submitted native team form.
         * @return bool Successful parent save in this fixture.
         */
        public function save($data)
        {
            ++$this->saves;
            $this->savedData = $data;

            return true;
        }

        /**
         * Capture the error set before an invalid team save.
         *
         * @param string $message Joomla model error.
         * @return void
         */
        public function setError(string $message): void
        {
            $this->error = $message;
        }

        /**
         * Fail loudly if a malformed list reaches the database connection.
         *
         * @return never No SQL connection must be needed by invalid input.
         */
        protected function getDatabase(): never
        {
            ++$this->databaseCalls;
            throw new \RuntimeException('Malformed team season list reached SQL.');
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Model\TeamModel;

    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/TeamModel.php';

    /**
     * Compare model results with strict equality independently of assertions.
     *
     * @param mixed $expected Required result.
     * @param mixed $actual Observed result.
     * @param string $context Error details.
     * @return void
     */
    function teamSeasonAssert(mixed $expected, mixed $actual, string $context): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $context . ': expected ' . var_export($expected, true)
                . ', observed ' . var_export($actual, true)
            );
        }
    }

    $parse = new \ReflectionMethod(TeamModel::class, 'normaliseSeasonIds');
    foreach ([
        [[], []],
        [[''], []],
        [[null, 0, '0', '0000'], []],
        [[1, '0002', '2', 1, 3], [1, 2, 3]],
        [['0007', '0008'], [7, 8]],
    ] as [$raw, $expected]) {
        teamSeasonAssert($expected, $parse->invoke(null, $raw),
            'Valid legacy team season IDs were not preserved');

        $model = new TeamModel();
        teamSeasonAssert(true, $model->save(['id' => 14, 'season_ids' => $raw]),
            'Valid team season selection blocked parent save');
        teamSeasonAssert(1, $model->saves, 'Valid team season selection was not saved once');
        teamSeasonAssert($expected, $model->savedData['season_ids'] ?? null,
            'Team season IDs were not canonicalised before model persistence');
    }

    foreach ([
        '7', 7, false, true, ['7junk'], ['7.1'], [' 7'], ['7 '],
        [['7']], [(object) ['id' => 7]], [-3], ['-3'],
        ['+3'], ['1e2'], ['999999999999999999999999999'],
        array_fill(0, 501, 1),
    ] as $raw) {
        teamSeasonAssert(null, $parse->invoke(null, $raw),
            'Invalid team season selector was coerced into a valid ID');

        $model = new TeamModel();
        teamSeasonAssert(false, $model->save(['id' => 14, 'season_ids' => $raw]),
            'Malformed team season list was persisted');
        teamSeasonAssert(0, $model->saves,
            'Invalid season selection reached Joomla parent save');
        teamSeasonAssert(0, $model->databaseCalls,
            'Invalid season selection requested a SQL connection');
        teamSeasonAssert('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->error,
            'Invalid season list did not report a save error');

        // Also protect direct post-save hook calls from deleting associations.
        try {
            (new \ReflectionMethod($model, 'syncSeasons'))->invoke(
                $model, ['season_ids' => $raw], 14
            );
            // Non-arrays intentionally return without changing relationships.
            if (is_array($raw)) {
                throw new \RuntimeException('Malformed season list reached cleanup.');
            }
        } catch (\InvalidArgumentException $error) {
            teamSeasonAssert(400, $error->getCode(),
                'Malformed post-save list must fail before SQL');
        }
        teamSeasonAssert(0, $model->databaseCalls,
            'Malformed post-save list attempted a relation cleanup query');
    }

    // Missing checkbox field preserves historical no-op behaviour.
    $model = new TeamModel();
    teamSeasonAssert(true, $model->save(['id' => 14, 'name' => 'Example Team']),
        'Missing season checkbox prevented an ordinary team save');
    teamSeasonAssert(1, $model->saves, 'Missing season selection skipped parent save');

    // Missing owner context must fail before SQL or application resolution.
    $model = new TeamModel();
    teamSeasonAssert(false, $model->DeleteTrainigData(12),
        'Training deletion without owner context must fail');
    teamSeasonAssert(false, $model->UpdateTrainigData(['tdids' => [12]]),
        'Training update without owner context must fail');
    teamSeasonAssert(false, $model->DeleteTrainigData(12, 0),
        'Zero team owner must fail');
    teamSeasonAssert(false, $model->addNewTrainigData('12junk'),
        'Malformed training team ID must fail');
    teamSeasonAssert(0, $model->databaseCalls,
        'Invalid training context reached SQL');

    $clock = new \ReflectionMethod(TeamModel::class, 'timeToSeconds');
    foreach ([
        ['00:00', 0], ['9:30', 34200], ['18:30:45', 66645],
        ['23:59:59', 86399], ['25:00', 0], ['12:75', 0],
        ['12:30:60', 0], ['abc', 0],
    ] as [$raw, $expected]) {
        teamSeasonAssert($expected, $clock->invoke(null, $raw),
            'Training clock conversion failed');
    }

    echo "Joomla 5/6 team season and training input boundaries: OK\n";
}
