<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class TeamsField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'teams';

    protected function getInput(): string
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->order($db->quoteName('t.name'));
        $db->setQuery($query);

        $options = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'))];

        foreach ($db->loadObjectList() ?: [] as $team) {
            $options[] = HTMLHelper::_(
                'select.option',
                $team->id,
                "\u{00A0}" . $team->name . ' (' . $team->id . ')'
            );
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name . '[]',
            'class="inputbox form-select" multiple="multiple" size="10"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
