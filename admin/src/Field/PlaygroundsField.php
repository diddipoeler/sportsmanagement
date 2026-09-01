<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

final class PlaygroundsField extends SportsManagementListField
{
    protected $type = 'playgrounds';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'inputbox form-select';

        return parent::setup($element, $value, $group);
    }

    protected function getOptions(): array
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

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $playground) {
            $options[] = (object) [
                'value' => (string) $playground->id,
                'text' => "\u{00A0}" . (string) $playground->name . ' (' . (int) $playground->id . ')',
            ];
        }

        return $options;
    }
}
