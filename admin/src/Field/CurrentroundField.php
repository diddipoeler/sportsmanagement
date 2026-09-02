<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class CurrentroundField extends SportsManagementListField
{
    protected $type = 'Currentround';

    protected function getOptions(): array
    {
        $projectId = Factory::getApplication()->getInput()->getInt('id', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order([
                $db->quoteName('roundcode'),
                $db->quoteName('round_date_first'),
            ]);
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $name = trim((string) $item->name);
            $label = $name !== ''
                ? $name . ' (' . (string) $item->round_date_first . ')'
                : Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAY_NAME') . ' ' . (int) $item->id;
            $options[] = (object) [
                'value' => (string) $item->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
