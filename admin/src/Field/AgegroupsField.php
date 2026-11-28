<?php
/**
 * Joomla 5/6 age-group filter for an administrator team editor.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/** Age groups filtered by the sport type of the currently edited team. */
final class AgegroupsField extends SportsManagementListField
{
    protected $type = 'agegroups';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Restrict age-group choices to the edited team's configured sport.
     *
     * Finds the team's sport through a bound Joomla database query and then
     * loads age groups for that sport. Invalid team IDs or malformed driver
     * values leave only inherited options, preventing cross-sport matches
     * and PHP 8 type conversion warnings.
     *
     * @return array<int,object> Sport-matched age-group select choices.
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $teamId = $app->getInput()->getInt('id', 0);

        if ($teamId <= 0) {
            return $options;
        }

        $db = $this->getSportsManagementDatabase();
        $teamQuery = $db->createQuery()
            ->select($db->quoteName('sports_type_id'))
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $db->setQuery($teamQuery, 0, 1);
        $rawSportsTypeId = $db->loadResult();
        if (!is_int($rawSportsTypeId) && !is_string($rawSportsTypeId)) {
            return $options;
        }
        $sportsTypeId = filter_var(
            $rawSportsTypeId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        $sportsTypeId = $sportsTypeId === false ? 0 : $sportsTypeId;

        if ($sportsTypeId <= 0) {
            return $options;
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->where($db->quoteName('sportstype_id') . ' = :sportsTypeId')
            ->bind(':sportsTypeId', $sportsTypeId, ParameterType::INTEGER)
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => "\u{00A0}" . Text::_((string) $item->text) . ' (' . (int) $item->value . ')',
            ];
        }

        return $options;
    }
}
