<?php
/**
 * Native Joomla 5/6 root federation selector for backend form fields.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class FederationField extends SportsManagementListField
{
    protected $type = 'Federation';

    /**
     * Query root-level federations for a Joomla administrator field.
     *
     * Only federations with parent ID zero are presented; this keeps
     * the historical top-level selector distinct from the hierarchical
     * FederationslistField and appends Joomla's inherited options.
     *
     * @return array<int,object> Root federation ID/name pairs.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->where($db->quoteName('parent_id') . ' = 0')
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
