<?php
/**
 * Joomla 5/6 club list form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

final class ClublistField extends SportsManagementListField
{
    protected $type = 'clublist';

    /**
     * Query selectable clubs and optional sport/club filters in the backend.
     *
     * Reads the target sport from form XML, the current club from request or
     * user state, and binds both optional filters through Joomla's query
     * builder. Invalid nested or control-character values fail closed rather
     * than being coerced into unfiltered SQL under PHP 8.
     *
     * @return array<int,object> Filtered club choices and inherited options.
     */
    protected function getOptions(): array
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $input = $app->getInput();
        $rawSport = $this->element['target'] ?? '';
        $sportType = is_string($rawSport) || $rawSport instanceof \Stringable
            ? (string) $rawSport : '';

        if (strlen($sportType) > 100
            || preg_match('/[\x00-\x1F\x7F]/', $sportType) === 1
            || ($sportType === '' && $rawSport !== '')) {
            return parent::getOptions();
        }

        $sportType = trim($sportType);
        $clubId = $input->getInt('club_id', 0);

        if ($clubId <= 0) {
            $savedClubId = $app->getUserState('com_sportsmanagement.club_id', 0);
            $clubId = filter_var(
                $savedClubId,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            $clubId = $clubId === false ? 0 : $clubId;
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('c.id', 'value'),
                $db->quoteName('c.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            );

        if ($clubId > 0) {
            $query->where($db->quoteName('c.id') . ' = :clubId')
                ->bind(':clubId', $clubId, ParameterType::INTEGER);
        }

        if ($sportType !== '') {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('t.sports_type_id')
            );
            $query->where($db->quoteName('st.name') . ' = :sportType')
                ->bind(':sportType', $sportType, ParameterType::STRING);
        }

        $query->group([
            $db->quoteName('c.id'),
            $db->quoteName('c.name'),
        ])->order($db->quoteName('c.name'));
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
