<?php
/**
 * Joomla 5/6 native matchday list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Database\ParameterType;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;

final class MatchdaylistField extends SportsManagementListField
{
    protected $type = 'Matchdaylist';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Load published matchdays for the project selected by the configured request key.
     * Accepts a single legacy wrapped identifier, rejects malformed IDs and
     * binds the validated project filter before ordering by round code.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $varname = trim((string) ($this->element['varname'] ?? ''));

        if ($varname === '') {
            return parent::getOptions();
        }

        $projectId = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->get($varname, null, 'raw');

        if (is_array($projectId)) {
            $projectId = array_is_list($projectId) && count($projectId) === 1 ? $projectId[0] : null;
        }

        $projectId = (is_int($projectId) || is_string($projectId))
            ? filter_var($projectId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('roundcode') . ' ASC');
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
