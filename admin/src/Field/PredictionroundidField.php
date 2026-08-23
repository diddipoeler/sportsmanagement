<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/** Rounds belonging to projects assigned to the active prediction game. */
final class PredictionroundidField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'predictionroundid';

    protected function getInput(): string
    {
        $predictionId = (int) Factory::getApplication()->getUserState('com_sportsmanagement.prediction_id', 0);
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('r.id'),
                    $db->quoteName('r.name', 'roundname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('r.project_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = ' . $predictionId)
                ->group([$db->quoteName('r.id'), $db->quoteName('r.name')])
                ->order($db->quoteName('r.id'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $round) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    (int) $round->id,
                    "\u{00A0} ( " . (string) $round->roundname . ' ) '
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
