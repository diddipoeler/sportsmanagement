<?php
/**
 * Joomla 5/6 single age-group administrator field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class AgegroupField extends SportsManagementListField
{
    protected $type = 'Agegroup';

    /**
     * Retrieve age group IDs and translated names for administrator forms.
     *
     * Queries age groups in display order, adds an optional country label
     * to each choice and combines the resulting options with those already
     * supplied by Joomla's list field.
     *
     * @return array<int,object> Country-labelled age-group options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $label = Text::_((string) $item->text);

            if (!empty($item->country)) {
                $label .= ' (' . (string) $item->country . ')';
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
