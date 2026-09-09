<?php
/**
 * Native Joomla 5/6 prediction round ID field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;

/** Rounds belonging to projects assigned to the active prediction game. */
final class PredictionroundidField extends SportsManagementListField
{
    protected $type = 'predictionroundid';

    private ?array $roundOptions = null;

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['class'] = 'form-select';
        $this->roundOptions = null;

        return parent::setup($element, $value, $group);
    }

    protected function getInput(): string
    {
        $options = $this->getOptions();
        $this->size = max(1, count($options));

        return parent::getInput();
    }

    protected function getOptions(): array
    {
        if ($this->roundOptions !== null) {
            return $this->roundOptions;
        }

        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $predictionId = (int) $app->getUserState('com_sportsmanagement.prediction_id', 0);
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('r.id', 'value'),
                    $db->quoteName('r.name', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('r.project_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = ' . $predictionId)
                ->group([$db->quoteName('r.id'), $db->quoteName('r.name')])
                ->order($db->quoteName('r.id'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $round) {
                $options[] = (object) [
                    'value' => (string) $round->value,
                    'text' => "\u{00A0} ( " . (string) $round->text . ' ) ',
                ];
            }
        }

        $this->roundOptions = $options;

        return $this->roundOptions;
    }
}
