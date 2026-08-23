<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class TeamField extends SportsManagementListField
{
    protected $type = 'team';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->order($db->quoteName('t.name') . ' ASC');

        $db->setQuery($query);

        $options = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')),
        ];

        foreach ($db->loadObjectList() ?: [] as $team) {
            $options[] = HTMLHelper::_(
                'select.option',
                $team->value,
                $team->text . ' (' . $team->value . ')'
            );
        }

        return array_merge($options, parent::getOptions());
    }
}
