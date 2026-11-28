<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage fields
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class TeamsField extends SportsManagementListField
{
    protected $type = 'teams';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

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
     * Load the available teams for the multiple-selection control.
     * Preserves alphabetical ordering, the translated empty option and
     * the historical labels containing team names and identifiers.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->order($db->quoteName('t.name'));
        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $team) {
            if (!is_object($team)
                || (!is_int($team->value ?? null) && !is_string($team->value ?? null))
                || !is_string($team->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $team->value,
                'text' => "\u{00A0}" . (string) $team->text . ' (' . (int) $team->value . ')',
            ];
        }

        return $options;
    }
}
