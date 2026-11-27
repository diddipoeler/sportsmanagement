<?php
/**
 * Native Joomla 5/6 playgrounds field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

final class PlaygroundsField extends SportsManagementListField
{
    protected $type = 'playgrounds';

    /**
     * Initialise the native multiple-selection field for the administrator form.
     * Applies the historical ten-row display and Bootstrap styling before
     * delegating saved selection handling to Joomla's parent field.
     * @param \SimpleXMLElement $element Field definition.
     * @param mixed $value Saved selection.
     * @param string|null $group Optional form group.
     * @return bool Whether Joomla accepted field setup.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'inputbox form-select';

        return parent::setup($element, $value, $group);
    }

    /**
     * Load venues from the configured SportsManagement database table.
     * Validates the configured table prefix and retains alphabetical ordering,
     * the translated placeholder and the historical venue labels.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $rawPrefix = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database_table', 'sportsmanagement');
        $databaseTable = is_string($rawPrefix) && strlen($rawPrefix) <= 64
            && preg_match('/^[A-Za-z0-9_]+$/D', $rawPrefix) === 1
            ? $rawPrefix : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
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

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $playground) {
            if (!is_object($playground)
                || (!is_int($playground->id ?? null) && !is_string($playground->id ?? null))
                || !is_string($playground->name ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $playground->id,
                'text' => "\u{00A0}" . (string) $playground->name . ' (' . (int) $playground->id . ')',
            ];
        }

        return $options;
    }
}
