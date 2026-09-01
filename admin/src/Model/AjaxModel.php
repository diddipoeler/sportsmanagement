<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 option-provider model for administrator AJAX endpoints.
 *
 * The public method names intentionally retain the historic API because many
 * custom form fields and JavaScript endpoints call them directly.
 */
final class AjaxModel extends BaseDatabaseModel
{
    public static function getPredictionId($dabse = false, $required = false, $slug = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('id', 'value'))
            ->select($db->quoteName('name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name') . ' DESC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function addGlobalSelectElement($elements, $required = false): array
    {
        $rows = is_array($elements) ? $elements : [];

        if (!$rows && (bool) $required) {
            return [];
        }

        array_unshift($rows, (object) [
            'value' => '0',
            'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
        ]);

        return $rows;
    }

    public static function getPredictionPj($prediction_id = 0, $required = false, $slug = false, $dabse = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('p.id', 'value'))
            ->select($db->quoteName('p.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_prediction_project', 'prpro')
                . ' ON ' . $db->quoteName('prpro.project_id') . ' = ' . $db->quoteName('p.id')
            )
            ->where($db->quoteName('prpro.prediction_id') . ' = ' . (int) $prediction_id)
            ->where($db->quoteName('prpro.published') . ' = 1')
            ->order($db->quoteName('p.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getPredictionGroups($prediction_id = 0, $required = false, $slug = false, $dabse = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('p.id', 'value'))
            ->select($db->quoteName('p.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_prediction_groups', 'p'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getpersoncontactid($show_user_profile = 0, $required = false): array
    {
        $profileType = (int) $show_user_profile;

        if (!in_array($profileType, [1, 2], true)) {
            return self::addGlobalSelectElement([], (bool) $required);
        }

        $db = self::database(false);
        $query = $db->getQuery(true);

        if ($profileType === 1) {
            $query->select($db->quoteName('a.id', 'value'))
                ->select("CONCAT(" . $db->quoteName('a.name') . ", ' - ', " . $db->quoteName('a.username') . ') AS ' . $db->quoteName('text'))
                ->from($db->quoteName('#__users', 'a'))
                ->order($db->quoteName('a.name') . ' ASC');
        } else {
            $query->select($db->quoteName('a.id', 'value'))
                ->select("CONCAT(" . $db->quoteName('a.firstname') . ", ' - ', " . $db->quoteName('a.lastname') . ') AS ' . $db->quoteName('text'))
                ->from($db->quoteName('#__comprofiler', 'a'))
                ->order($db->quoteName('a.lastname') . ' ASC');
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getcountryleagueoptions($country = '', $required = false, $slug = false, $dbase = false): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $country = trim((string) $country);

        if ($country === '') {
            $country = $input->getCmd('projects_search_nation', $input->getCmd('search_nation', ''));
        }

        $association = $input->getInt('projects_search_associations_leagues');
        $db = self::database((bool) $dbase);

        if ($country === '') {
            $row = new \stdClass();
            $row->value = 0;
            $row->text = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUES_FILTER');

            return self::addGlobalSelectElement([$row], (bool) $required);
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('l.id', 'value'))
            ->select("CONCAT(" . $db->quoteName('l.name') . ", ' (', " . $db->quoteName('l.id') . ", ')') AS " . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_league', 'l'))
            ->where($db->quoteName('l.country') . ' = ' . $db->quote($country))
            ->order($db->quoteName('l.name') . ' ASC');

        if ($association > 0) {
            $query->where($db->quoteName('l.associations') . ' = ' . $association);
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getcountryclubagegroupoptions($club_id = 0, $required = false, $slug = false, $dbase = false): array
    {
        $clubId = (int) $club_id;

        if ($clubId <= 0) {
            $row = new \stdClass();
            $row->value = 0;
            $row->text = Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_CLUB');

            return self::addGlobalSelectElement([$row], (bool) $required);
        }

        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.id', 'value'))
            ->select("CONCAT(" . $db->quoteName('a.name') . ", ' - ', " . $db->quoteName('a.country') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.country') . ' = ' . $db->quoteName('a.country')
            )
            ->where($db->quoteName('c.id') . ' = ' . $clubId)
            ->order($db->quoteName('a.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getassociationsoptions($country = null, $required = false, $slug = false, $dabse = false): array
    {
        $app = Factory::getApplication();
        $db = $app->getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 't'))
            ->where($db->quoteName('t.country') . ' = ' . $db->quote((string) $country))
            ->order($db->quoteName('t.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getseasons($dabse = false, $required = false, $slug = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('id', 'value'))
            ->select($db->quoteName('name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_season'))
            ->order($db->quoteName('name') . ' DESC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getsportstypes($dabse = false, $required = false, $slug = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->order($db->quoteName('name') . ' DESC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getlocationzipcodeoptions($zipcode, $required = false, $slug = false, $dabse = false, $country = null): array
    {
        $zipcode = trim((string) $zipcode);
        $country = trim((string) $country);
        $db = self::geoDatabase((bool) $dabse);

        if ($zipcode === '' && $country === '') {
            return self::addGlobalSelectElement([], (bool) $required);
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('a.place_name', 'value'))
            ->select("CONCAT(" . $db->quoteName('a.place_name') . ", ' ( ', " . $db->quoteName('a.country_code') . ", ' ) ( ', " . $db->quoteName('a.postal_code') . ", ' ) ', " . $db->quoteName('a.admin_name1') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_countries_plz', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_countries', 'c')
                . ' ON ' . $db->quoteName('c.alpha2') . ' = ' . $db->quoteName('a.country_code')
            );

        if ($zipcode !== '') {
            $query->where($db->quoteName('a.postal_code') . ' = ' . $db->quote($zipcode))
                ->order($db->quoteName('a.postal_code') . ' ASC');
        }

        if ($country !== '') {
            $query->where($db->quoteName('c.alpha3') . ' = ' . $db->quote($country))
                ->order($db->quoteName('a.place_name') . ' ASC');
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getCcountryName($country): array
    {
        $db = self::database(false);
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_countries', 'c'))
            ->where($db->quoteName('c.alpha3') . ' = ' . $db->quote((string) $country));
        $rows = self::loadRows($db, $query);

        foreach ($rows as $row) {
            $row->text = Text::_((string) $row->text);
        }

        return $rows;
    }

    public static function getCcountryAlpha2($country): array
    {
        $db = self::database(false);
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.alpha2', 'text'))
            ->from($db->quoteName('#__sportsmanagement_countries', 'c'))
            ->where($db->quoteName('c.alpha3') . ' = ' . $db->quote((string) $country));

        return self::loadRows($db, $query);
    }

    public static function getcountryzipcodeoptions($country, $required = false, $slug = false, $dabse = false, $project_id = 0): array
    {
        $country = trim((string) $country);
        $db = self::geoDatabase((bool) $dabse);

        if ($country === '') {
            return self::addGlobalSelectElement([], (bool) $required);
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('a.postal_code', 'value'))
            ->select("CONCAT(" . $db->quoteName('a.postal_code') . ", ' ( ', " . $db->quoteName('a.country_code') . ", ' )  ', " . $db->quoteName('a.admin_name1') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_countries_plz', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_countries', 'c')
                . ' ON ' . $db->quoteName('c.alpha2') . ' = ' . $db->quoteName('a.country_code')
            )
            ->where($db->quoteName('c.alpha3') . ' = ' . $db->quote($country))
            ->group([
                $db->quoteName('a.postal_code'),
                $db->quoteName('a.country_code'),
                $db->quoteName('a.admin_name1'),
            ])
            ->order($db->quoteName('a.postal_code') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectRoundOptions($project_id, $required = false, $slug = false, $ordering = 'ASC', $round_ids = null, $dabse = false): array
    {
        $db = self::database((bool) $dabse);
        $direction = strtoupper((string) $ordering) === 'DESC' ? 'DESC' : 'ASC';
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('id') . ', ' . $db->quoteName('alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('id', 'value'))
            ->select([
                $db->quoteName('name', 'text'),
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('round_date_first'),
                $db->quoteName('round_date_last'),
                $db->quoteName('roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . (int) $project_id)
            ->where($db->quoteName('published') . ' = 1');

        $roundIds = self::ids($round_ids);

        if ($roundIds) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $roundIds) . ')');
        }

        $query->order($db->quoteName('roundcode') . ' ' . $direction);

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getpersonpositionoptions($sports_type_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->order($db->quoteName('pos.name') . ' ASC');

        if ((int) $sports_type_id > 0) {
            $query->where($db->quoteName('pos.sports_type_id') . ' = ' . (int) $sports_type_id);
        }

        $rows = self::loadRows($db, $query);

        foreach ($rows as $row) {
            $row->text = Text::_((string) $row->text);
        }

        return self::addGlobalSelectElement($rows, (bool) $required);
    }

    public static function getpersonagegroupoptions($sports_type_id = 0, $required = false, $slug = false, $dabse = false, $project_id = 0, $country = ''): array
    {
        $db = self::geoDatabase((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.id', 'value'))
            ->select("CONCAT(" . $db->quoteName('a.country') . ", '-', " . $db->quoteName('a.name') . ", ' von: ', " . $db->quoteName('a.age_from') . ", ' bis: ', " . $db->quoteName('a.age_to') . ", ' Stichtag: ', " . $db->quoteName('a.deadline_day') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'a'))
            ->order($db->quoteName('a.name') . ' ASC');

        if ((int) $project_id > 0) {
            $query->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_league', 'l')
                . ' ON ' . $db->quoteName('l.country') . ' = ' . $db->quoteName('a.country')
            )->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )->where($db->quoteName('p.id') . ' = ' . (int) $project_id);
        }

        if ((int) $sports_type_id > 0) {
            $query->where($db->quoteName('a.sportstype_id') . ' = ' . (int) $sports_type_id);
        }

        if ((string) $country !== '') {
            $query->where($db->quoteName('a.country') . ' = ' . $db->quote((string) $country));
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getpredictionmembersoptions($prgame_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.user_id', 'value'))
            ->select("CONCAT(" . $db->quoteName('u.name') . ", ' ( ', " . $db->quoteName('u.username') . ", ' ) ') AS " . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id')
            )
            ->where($db->quoteName('a.prediction_id') . ' = ' . (int) $prgame_id)
            ->order($db->quoteName('u.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getpersonlistoptions($person_art, $required = false, $slug = false, $dbase = false): array
    {
        if ((int) $person_art !== 2) {
            return self::addGlobalSelectElement([], (bool) $required);
        }

        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('id', 'value'))
            ->select("CONCAT(" . $db->quoteName('lastname') . ", ' - ', " . $db->quoteName('firstname') . ") AS " . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_person'))
            ->order($db->quoteName('lastname') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getpersonlistoptionsprojectteam($person_art = 0, $required = false, $slug = false, $dbase = false): array
    {
        $app = Factory::getApplication();
        $projectId = (int) $app->getUserState('teamplayer.pid', 0);
        $seasonId = (int) $app->getUserState('teamplayer.season_id', 0);
        $teamId = (int) $app->getUserState('teamplayer.team_id', 0);
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('stp.id', 'value'))
            ->select("CONCAT(" . $db->quoteName('p.lastname') . ", ', ', " . $db->quoteName('p.firstname') . ", ' (', " . $db->quoteName('p.birthday') . ", ')') AS " . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp')
                . ' ON ' . $db->quoteName('stp.person_id') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('st.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('stp.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('st.team_id') . ' = ' . $teamId)
            ->where($db->quoteName('stp.team_id') . ' = ' . $teamId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('stp.persontype') . ' = 1')
            ->where($db->quoteName('stp.person_art') . ' = 1')
            ->group([
                $db->quoteName('stp.id'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.birthday'),
            ])
            ->order($db->quoteName('text') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectDivisionsOptions($project_id, $required = false, $slug = false, $dabse = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('d.id') . ', ' . $db->quoteName('d.alias') . ') AS ' . $db->quoteName('value')
                : $db->quoteName('d.id', 'value'))
            ->select($db->quoteName('d.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_division', 'd'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('d.project_id')
            )
            ->order($db->quoteName('d.name') . ' ASC');

        self::applyIdFilter($query, $db->quoteName('d.project_id'), $project_id);

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectTeamsByDivisionOptions($project_id, $required = false, $slug = false, $dbase = false, $division_id = 0): array
    {
        $db = self::database((bool) $dbase);
        $query = self::projectTeamBaseQuery($db, true)
            ->where($db->quoteName('pt.project_id') . ' = ' . (int) $project_id);

        if ((int) $division_id > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . (int) $division_id);
        }

        $query->order($db->quoteName('t.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectsByClubOptions($club_id, $required = false, $slug = false, $dbase = false): array
    {
        $clubId = (int) $club_id;

        if ($clubId <= 0) {
            return self::getProjects(0, $required, $slug, $dbase);
        }

        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value'))
            ->select($db->quoteName('p.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . $clubId)
            ->group([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.name'),
            ])
            ->order($db->quoteName('p.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjects($season_id = 0, $required = false, $slug = false, $dabse = false): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value'))
                : $db->quoteName('p.id', 'value'))
            ->select("CONCAT_WS(' - ', " . $db->quoteName('p.name') . ', ' . $db->quoteName('l.country') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->order($db->quoteName('p.name') . ' ASC');

        $ids = self::ids($season_id);

        if ($ids) {
            $query->where($db->quoteName('p.season_id') . ' IN (' . implode(',', $ids) . ')');
        } else {
            $query->where($db->quoteName('p.season_id') . ' = 0');
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectTeamOptions($project_id, $required = false, $slug = false, $dabse = false, $club_id = null): array
    {
        $db = self::database((bool) $dabse);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('value'))
                : $db->quoteName('t.id', 'value'))
            ->select($db->quoteName('t.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->group([
                $db->quoteName('t.id'),
                $db->quoteName('t.alias'),
                $db->quoteName('t.name'),
            ])
            ->order($db->quoteName('t.name') . ' ASC');

        self::applyIdFilter($query, $db->quoteName('pt.project_id'), $project_id);

        $clubIds = self::ids($club_id);

        if ($clubIds) {
            $query->where($db->quoteName('c.id') . ' IN (' . implode(',', $clubIds) . ')');
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectPlayerOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        return self::getProjectPersonsOptions($project_id, 1, $required, $dbase);
    }

    public static function getProjectStaffOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        return self::getProjectPersonsOptions($project_id, 2, $required, $dbase);
    }

    public static function getProjectClubOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('c.id') . ', ' . $db->quoteName('c.alias') . ') AS ' . $db->quoteName('value'))
                : $db->quoteName('c.id', 'value'))
            ->select($db->quoteName('c.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'));

        self::applyIdFilter($query, $db->quoteName('pt.project_id'), $project_id);
        $query->group([
            $db->quoteName('c.id'),
            $db->quoteName('c.alias'),
            $db->quoteName('c.name'),
        ])->order($db->quoteName('c.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectEventsOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('et.id') . ', ' . $db->quoteName('et.alias') . ') AS ' . $db->quoteName('value'))
            ->select($db->quoteName('et.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_event', 'me') . ' ON ' . $db->quoteName('me.event_type_id') . ' = ' . $db->quoteName('et.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . (int) $project_id)
            ->group([
                $db->quoteName('et.id'),
                $db->quoteName('et.alias'),
                $db->quoteName('et.name'),
                $db->quoteName('et.ordering'),
            ])
            ->order($db->quoteName('et.ordering') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectStatsOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('s.id') . ', ' . $db->quoteName('s.alias') . ') AS ' . $db->quoteName('value'))
            ->select($db->quoteName('s.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ' . $db->quoteName('ps.position_id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_statistic', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('ps.statistic_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('ppos.project_id'));

        self::applyIdFilter($query, $db->quoteName('ppos.project_id'), $project_id);
        $query->group([
            $db->quoteName('s.id'),
            $db->quoteName('s.alias'),
            $db->quoteName('s.name'),
        ])->order($db->quoteName('s.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    /** Backward-compatible singular method used by older controller code. */
    public static function getProjectStatOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        return self::getProjectStatsOptions($project_id, $required, $slug, $dbase);
    }

    public static function getMatchesOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('m.id', 'value'))
            ->select("CONCAT('(', " . $db->quoteName('m.match_date') . ", ') - ', " . $db->quoteName('t1.middle_name') . ", ' - ', " . $db->quoteName('t2.middle_name') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->order($db->quoteName('m.match_date') . ' ASC')
            ->order($db->quoteName('t1.short_name') . ' ASC');

        self::applyIdFilter($query, $db->quoteName('pt1.project_id'), $project_id);

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectTreenodeOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tt.id', 'value'),
                $db->quoteName('tt.id', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_treeto', 'tt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tt.project_id'))
            ->where($db->quoteName('tt.project_id') . ' = ' . (int) $project_id)
            ->order($db->quoteName('tt.id') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value'))
            ->select($db->quoteName('p.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . (int) $sports_type_id)
            ->order($db->quoteName('p.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getAgeGroupsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('a.id') . ', ' . $db->quoteName('a.alias') . ') AS ' . $db->quoteName('value'))
            ->select($db->quoteName('a.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'a'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('a.sportstype_id'))
            ->order($db->quoteName('a.name') . ' ASC');

        if ((int) $sports_type_id > 0) {
            $query->where($db->quoteName('a.sportstype_id') . ' = ' . (int) $sports_type_id);
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getProjectTeamPtidOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('pt.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('value'))
                : $db->quoteName('pt.id', 'value'))
            ->select($db->quoteName('t.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . (int) $project_id)
            ->order($db->quoteName('t.name') . ' ASC');

        return self::loadOptions($db, $query, (bool) $required);
    }

    public static function getRefereesOptions($project_id, $required = false, $slug = false, $dbase = false): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select($db->quoteName('p.id', 'value'))
            ->select("CONCAT(" . $db->quoteName('p.firstname') . ", ' ', " . $db->quoteName('p.lastname') . ') AS ' . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'sp') . ' ON ' . $db->quoteName('sp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON ' . $db->quoteName('pr.person_id') . ' = ' . $db->quoteName('sp.id'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('text') . ' ASC');

        $projectIds = self::ids($project_id);

        if ($projectIds) {
            $query->where($db->quoteName('pr.project_id') . ' IN (' . implode(',', $projectIds) . ')');
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    private static function getProjectPersonsOptions($project_id, int $personType, $required, $dbase): array
    {
        $db = self::database((bool) $dbase);
        $query = $db->getQuery(true)
            ->select("CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('value'))
            ->select("CONCAT(" . $db->quoteName('p.lastname') . ", ', ', " . $db->quoteName('p.firstname') . ", ' (', " . $db->quoteName('p.birthday') . ", ')') AS " . $db->quoteName('text'))
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('stp.persontype') . ' = ' . $personType)
            ->group([
                $db->quoteName('p.id'),
                $db->quoteName('p.alias'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.birthday'),
            ])
            ->order($db->quoteName('text') . ' ASC');

        if ((int) $project_id > 0) {
            $query->where($db->quoteName('pt.project_id') . ' = ' . (int) $project_id);
        }

        return self::loadOptions($db, $query, (bool) $required);
    }

    private static function projectTeamBaseQuery(DatabaseInterface $db, bool $slug)
    {
        return $db->getQuery(true)
            ->select($slug
                ? "CONCAT_WS(':', " . $db->quoteName('t.id') . ', ' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('value'))
                : $db->quoteName('t.id', 'value'))
            ->select($db->quoteName('t.name', 'text'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'));
    }

    private static function loadOptions(DatabaseInterface $db, $query, bool $required): array
    {
        return self::addGlobalSelectElement(self::loadRows($db, $query), $required);
    }

    private static function loadRows(DatabaseInterface $db, $query): array
    {
        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return [];
        }
    }

    private static function applyIdFilter($query, string $column, $value): void
    {
        $ids = self::ids($value);

        if ($ids) {
            $query->where($column . ' IN (' . implode(',', $ids) . ')');
        } else {
            $query->where($column . ' = 0');
        }
    }

    private static function ids($value): array
    {
        if ($value === null || $value === '' || $value === false) {
            return [];
        }

        if (!is_array($value)) {
            $value = explode(',', (string) $value);
        }

        $ids = array_values(array_unique(array_filter(
            array_map('intval', $value),
            static fn (int $id): bool => $id > 0
        )));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    private static function database(bool $external): DatabaseInterface
    {
        self::registerHelper();

        return $external
            ? \sportsmanagementHelper::getDBConnection(true, true)
            : \sportsmanagementHelper::getDBConnection();
    }

    /** Preserve the historic postal-code database selector semantics. */
    private static function geoDatabase(bool $componentDatabase): DatabaseInterface
    {
        self::registerHelper();

        return $componentDatabase
            ? \sportsmanagementHelper::getDBConnection()
            : \sportsmanagementHelper::getDBConnection(true, false);
    }

    private static function registerHelper(): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }
    }
}
