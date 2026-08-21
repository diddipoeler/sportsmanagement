<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class ClubsField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'clubs';

    protected function getInput(): string
    {
        $databaseTable = preg_replace(
            '/[^A-Za-z0-9_]/',
            '',
            (string) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database_table', 'sportsmanagement')
        );
        $databaseTable = $databaseTable !== '' ? $databaseTable : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.name'),
            ])
            ->from($db->quoteName('#__' . $databaseTable . '_club', 'c'))
            ->order($db->quoteName('c.name'));
        $db->setQuery($query);

        $options = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'))];

        foreach ($db->loadObjectList() ?: [] as $club) {
            $options[] = HTMLHelper::_(
                'select.option',
                $club->id,
                "\u{00A0}" . $club->name . ' (' . $club->id . ')'
            );
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            'class="inputbox form-select" multiple="multiple" size="10"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
