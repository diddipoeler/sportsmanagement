<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PredictionroundTable;
use Joomla\CMS\Factory;
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

    public function getTable($type = 'predictionround', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'predictionround') === 0) {
            return new PredictionroundTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function saveshort(&$pks, &$post)
    {
        $pks = array_values(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0));
        $post = (array) $post;
        $date = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;
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

    public function addPredRoundIds($projRoundsIdsToAdd, $prediction_id, $project_id)
    {
        $roundIds = array_values(array_unique(array_filter(
            array_map('intval', (array) $projRoundsIdsToAdd),
            static fn (int $id): bool => $id > 0
        )));
        $predictionId = (int) $prediction_id;
        $projectId = (int) $project_id;
        $date = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;
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
