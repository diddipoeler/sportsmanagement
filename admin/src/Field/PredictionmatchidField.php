<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/** Matches belonging to projects assigned to the active prediction game. */
final class PredictionmatchidField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'predictionmatchid';

    protected function getInput(): string
    {
        $predictionId = (int) Factory::getApplication()->getUserState('com_sportsmanagement.prediction_id', 0);
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.id'),
                    $db->quoteName('m.match_date'),
                    $db->quoteName('r.name', 'roundname'),
                    $db->quoteName('t1.name', 'home'),
                    $db->quoteName('t2.name', 'away'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('r.project_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'tt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('tt1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'tt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('tt2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('tt1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('tt2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = ' . $predictionId)
                ->order([$db->quoteName('m.match_date'), $db->quoteName('m.id')]);
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $match) {
                $label = (string) $match->match_date
                    . ' ( ' . (string) $match->roundname . ' )'
                    . ' -> [ ' . (string) $match->home . ' - ' . (string) $match->away . ' ]';
                $options[] = HTMLHelper::_('select.option', (int) $match->id, "\u{00A0}" . $label);
            }
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name . '[]',
            'class="form-select" multiple="multiple" size="10"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
