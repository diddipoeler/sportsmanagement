<?php
/**
 * Joomla 5/6 administrator form model for prediction rounds.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionroundTable;
use Joomla\CMS\Language\Text;

/**
 * Native Joomla 5/6 administrator form model for prediction rounds.
 */
final class PredictionroundModel extends SportsManagementAdminModel
{
    private const LOCK_MODES = [
        'FIRSTMATCH_OF_TIPPGAME',
        'FIRSTMATCH_OF_TIPPROUND',
        'BEGIN_OF_MATCH',
    ];

    /**
     * Resolve the native prediction round table.
     * @param string $type Requested Joomla table.
     * @param string $prefix Legacy table namespace.
     * @param array $config Optional table settings.
     * @return mixed PredictionroundTable or parent result.
     */
    public function getTable($type = 'predictionround', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictionround') === 0) {
            return new PredictionroundTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Save selected prediction-round scoring and lock settings.
     * Uses the supported lock modes and a database transaction so that
     * all selected round changes are committed or rolled back together.
     * @param array $pks Round IDs, normalised in place.
     * @param array $post Submitted scoring and lock options.
     * @return string|false Translated success message or false on failure.
     */
    public function saveshort(&$pks, &$post)
    {
        $pks = array_values(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0));
        $post = (array) $post;
        $date = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) ($this->administratorApplication()->getIdentity()?->id ?? 0);
        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            foreach ($pks as $id) {
                $round = $this->getTable();

                if (!$round->load($id)) {
                    throw new \RuntimeException((string) $round->getError());
                }

                $lockMode = (string) ($post['rien_ne_va_plus' . $id] ?? $round->rien_ne_va_plus ?? 'BEGIN_OF_MATCH');
                $round->rien_ne_va_plus = in_array($lockMode, self::LOCK_MODES, true)
                    ? $lockMode
                    : 'BEGIN_OF_MATCH';
                $round->points_tipp = (int) ($post['points_tipp' . $id] ?? 0);
                $round->points_correct_result = (int) ($post['points_correct_result' . $id] ?? 0);
                $round->points_correct_diff = (int) ($post['points_correct_diff' . $id] ?? 0);
                $round->points_correct_draw = (int) ($post['points_correct_draw' . $id] ?? 0);
                $round->points_correct_tendence = (int) ($post['points_correct_tendence' . $id] ?? 0);
                $round->modified = $date;
                $round->modified_by = $userId;

                if (!$round->store()) {
                    throw new \RuntimeException((string) $round->getError());
                }
            }

            $db->transactionCommit();

            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE');
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original save error.
                }
            }

            $this->setError($e->getMessage());

            return false;
        }
    }

    /**
     * Create missing prediction rounds for a project and game.
     * Initialises lock modes and audit fields in a single transaction.
     * @param array $projRoundsIdsToAdd Project round IDs to add.
     * @param int $prediction_id Prediction game ID.
     * @param int $project_id Project ID.
     * @return string|false Translated result or false on failure.
     */
    public function addPredRoundIds($projRoundsIdsToAdd, $prediction_id, $project_id)
    {
        $roundIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $projRoundsIdsToAdd),
            static fn (int $id): bool => $id > 0
        )));
        $predictionId = (int) $prediction_id;
        $projectId = (int) $project_id;
        $date = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $userId = (int) ($this->administratorApplication()->getIdentity()?->id ?? 0);
        $count = 0;
        $db = $this->getDatabase();
        $transactionStarted = false;

        try {
            $db->transactionStart();
            $transactionStarted = true;

            foreach ($roundIds as $roundId) {
                $round = $this->getTable();
                $round->prediction_id = $predictionId;
                $round->project_id = $projectId;
                $round->round_id = $roundId;
                $round->rien_ne_va_plus = 'BEGIN_OF_MATCH';
                $round->modified = $date;
                $round->modified_by = $userId;
                $round->published = 0;

                if (!$round->store()) {
                    throw new \RuntimeException((string) $round->getError());
                }

                $count++;
            }

            $db->transactionCommit();

            return Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ADDED', $count);
        } catch (\Throwable $e) {
            if ($transactionStarted) {
                try {
                    $db->transactionRollback();
                } catch (\Throwable) {
                    // Preserve the original insert error.
                }
            }

            $this->setError($e->getMessage());

            return false;
        }
    }
}
