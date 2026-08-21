<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class PlaygroundsField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'playgrounds';

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
                $db->quoteName('pl.id'),
                $db->quoteName('pl.name'),
            ])
            ->from($db->quoteName('#__' . $databaseTable . '_playground', 'pl'))
            ->order($db->quoteName('pl.name'));
        $db->setQuery($query);

        $options = [HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'))];

        foreach ($db->loadObjectList() ?: [] as $playground) {
            $options[] = HTMLHelper::_(
                'select.option',
                $playground->id,
                "\u{00A0}" . $playground->name . ' (' . $playground->id . ')'
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
