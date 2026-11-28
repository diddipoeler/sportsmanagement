<?php
/**
 * Joomla 5/6 clubs form field.
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

final class ClubsField extends SportsManagementListField
{
    protected $type = 'clubs';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Configure a Joomla XML field for ten-row multiple club selection.
     *
     * Applies the legacy multi-select attributes before delegating form
     * initialisation and value handling to Joomla's parent field.
     *
     * @param \SimpleXMLElement $element XML form-field definition.
     * @param mixed $value Selected club IDs from saved form data.
     * @param string|null $group Optional Joomla form group name.
     * @return bool Whether field setup succeeded.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['size'] = '10';
        $element['class'] = 'inputbox form-select';

        return parent::setup($element, $value, $group);
    }

    /**
     * Load ordered clubs from the configured SportsManagement table.
     *
     * Only an unmodified bounded identifier is accepted as a table prefix.
     * Corrupt or nested parameters fall back to the standard component
     * table, and Joomla's inherited options are preserved.
     *
     * @return array<int,object> Club IDs, names and inherited select options.
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
                $db->quoteName('c.id', 'value'),
                $db->quoteName('c.name', 'text'),
            ])
            ->from($db->quoteName('#__' . $databaseTable . '_club', 'c'))
            ->order($db->quoteName('c.name'));
        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => "\u{00A0}" . (string) $item->text . ' (' . (int) $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
