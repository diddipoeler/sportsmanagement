<?php
/**
 * Joomla 5/6 multi-event field for administrator forms.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

final class EventsField extends SportsManagementListField
{
    protected $type = 'events';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Configure the published events field as a searchable multiple-selection control.
     *
     * Preserves the legacy ten-row display but permits custom form XML
     * sizes only when they are valid integers between one and fifty.
     * Configure multiplicity before Joomla initialises the input name and value.
     *
     * @param \SimpleXMLElement $element Field XML definition.
     * @param mixed $value Current selection.
     * @param string|null $group Optional form group.
     * @return bool Whether Joomla initialised the field successfully.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $rawSize = $element['size'] ?? '10';
        $size = is_string($rawSize) || $rawSize instanceof \Stringable
            ? (string) $rawSize : '';
        $element['size'] = preg_match('/^[1-9][0-9]?$/D', $size) === 1
            && (int) $size <= 50 ? $size : '10';

        return parent::setup($element, $value, $group);
    }

    /**
     * Build the Joomla administrator multiple-event selector options.
     *
     * Uses a validated component table prefix rather than coercing nested
     * or malformed settings into an SQL table name. Includes published
     * event IDs and translated labels alongside the inherited options.
     *
     * @return array<int,object> Default and published event-type options.
     */
    protected function getOptions(): array
    {
        $rawPrefix = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database_table', 'sportsmanagement');
        $tablePrefix = is_string($rawPrefix) && strlen($rawPrefix) <= 64
            && preg_match('/^[A-Za-z0-9_]+$/D', $rawPrefix) === 1
                ? $rawPrefix : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('e.id', 'value'),
                $db->quoteName('e.name', 'text'),
            ])
            ->from($db->quoteName('#__' . $tablePrefix . '_eventtype', 'e'))
            ->where($db->quoteName('e.published') . ' = 1')
            ->order($db->quoteName('e.name') . ' ASC');

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
                'text' => Text::_((string) $item->text) . ' (' . $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
