<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class ClubField extends SportsManagementListField
{
    protected $type = 'club';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id', 'value'),
                $db->quoteName('c.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->order($db->quoteName('c.name') . ' ASC');

        $db->setQuery($query);

        $options = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')),
        ];

        foreach ($db->loadObjectList() ?: [] as $club) {
            $options[] = HTMLHelper::_(
                'select.option',
                $club->value,
                $club->text . ' (' . $club->value . ')'
            );
        }

        return array_merge($options, parent::getOptions());
    }
}
