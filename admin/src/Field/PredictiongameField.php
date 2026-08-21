<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

final class PredictiongameField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'predictiongame';

    protected function getInput(): string
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $value = (int) $item->id . ':' . (string) $item->name;
            $options[] = HTMLHelper::_(
                'select.option',
                $value,
                "\u{00A0}" . (string) $item->name . ' (' . $value . ')'
            );
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            'class="form-select" multiple="multiple" size="10"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
