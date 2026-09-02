<?php
/**
 * @version    4.24.00
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

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
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
