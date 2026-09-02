<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class TeamlistField extends SportsManagementListField
{
    protected $type = 'Teamlist';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
