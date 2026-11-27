<?php
/**
 * Joomla 5/6 native project rounds field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

final class ProjectroundsField extends SportsManagementListField
{
    protected $type = 'projectrounds';

    /**
     * Load rounds with an optional project filter from the current form.
     * Rejects malformed project selectors instead of broadening the query,
     * retains alphabetical ordering and appends inherited XML options.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'));

        $rawProjectId = $this->form->getValue('project');
        $projectId = ($rawProjectId === null || $rawProjectId === '') ? 0
            : ((is_int($rawProjectId) || is_string($rawProjectId))
                ? filter_var($rawProjectId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) : false);
        if ($projectId === false) {
            return parent::getOptions();
        }

        if ($projectId > 0) {
            $query->where($db->quoteName('project_id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
        }

        $query->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
