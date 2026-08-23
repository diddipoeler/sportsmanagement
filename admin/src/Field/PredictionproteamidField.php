<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/** Project teams belonging to projects assigned to the active prediction game. */
final class PredictionproteamidField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'predictionproteamid';

    protected function getInput(): string
    {
        $predictionId = (int) Factory::getApplication()->getUserState('com_sportsmanagement.prediction_id', 0);
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('tl.id'),
                    $db->quoteName('t.name', 'teamname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'tl'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('tl.team_id') . ' = ' . $db->quoteName('st.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('tl.project_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = ' . $predictionId)
                ->group([$db->quoteName('tl.id'), $db->quoteName('t.name')])
                ->order($db->quoteName('t.name'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $team) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    (int) $team->id,
                    "\u{00A0} ( " . (string) $team->teamname . ' ) '
                );
            }
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name . '[]',
            'class="form-select" multiple="multiple" size="' . max(1, count($options)) . '"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
