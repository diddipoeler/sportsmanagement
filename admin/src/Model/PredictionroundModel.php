<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

/**
 * Native Joomla 5/6 administrator form model for prediction rounds.
 */
final class PredictionroundModel extends SportsManagementAdminModel
{
    public function getTable($type = 'predictionround', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    public function saveshort(&$pks, &$post)
    {
        $pks = array_values(array_filter(array_map('intval', (array) $pks), static fn (int $id): bool => $id > 0));
        $post = (array) $post;
        $date = Factory::getDate()->toSql();
        $userId = (int) Factory::getApplication()->getIdentity()->id;

        foreach ($pks as $id) {
            $round = $this->getTable();

            if (!$round->load($id)) {
                return false;
            }

            $round->rien_ne_va_plus = (int) ($post['rien_ne_va_plus' . $id] ?? 0);
            $round->points_tipp = (int) ($post['points_tipp' . $id] ?? 0);
            $round->points_correct_result = (int) ($post['points_correct_result' . $id] ?? 0);
            $round->points_correct_diff = (int) ($post['points_correct_diff' . $id] ?? 0);
            $round->points_correct_draw = (int) ($post['points_correct_draw' . $id] ?? 0);
            $round->points_correct_tendence = (int) ($post['points_correct_tendence' . $id] ?? 0);
            $round->modified = $date;
            $round->modified_by = $userId;

            if (!$round->store()) {
                return false;
            }
        }

        return Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_SAVE');
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

        foreach ($roundIds as $roundId) {
            $round = $this->getTable();
            $round->prediction_id = $predictionId;
            $round->project_id = $projectId;
            $round->round_id = $roundId;
            $round->modified = $date;
            $round->modified_by = $userId;
            $round->published = 0;

            if (!$round->store()) {
                return false;
            }

            $count++;
        }

        return Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREDICITIONROUNDS_ADDED', $count);
    }
}
