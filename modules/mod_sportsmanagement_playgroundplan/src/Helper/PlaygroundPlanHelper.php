<?php
/**
 * Native Joomla 5/6 data helper for the SportsManagement Playground Plan module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundPlan\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class PlaygroundPlanHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app, object $module): array
    {
        try {
            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $db = $this->database($params, $joomlaDatabase);
            $projectIds = $this->ids($params->get('projects', []));
            $playgroundIds = $this->ids($params->get('playground', []));
            $teamField = $this->teamField((string) $params->get('teamformat', 'middle_name'));
            $logoField = $this->logoField((string) $params->get('show_picture', 'logo_big'));
            $limit = max(1, min(100, (int) $params->get('maxmatches', 7)));

            $query = $db->createQuery()
                ->select([
                    'm.match_date',
                    'p.id AS project_id',
                    'p.alias AS project_alias',
                    'p.name AS project_name',
                    'p.season_id',
                    'lg.name AS league_name',
                    'st1.team_id AS team1',
                    'st2.team_id AS team2',
                    't1.' . $teamField . ' AS team1_name',
                    't2.' . $teamField . ' AS team2_name',
                    'c1.' . $logoField . ' AS team1_logo',
                    'c2.' . $logoField . ' AS team2_logo',
                    'pl.id AS playground_id',
                    'pl.alias AS playground_alias',
                    'pl.name AS playground_name',
                    'pl.picture AS playground_picture',
                    'pltd.id AS team_playground_id',
                    'pltd.alias AS team_playground_alias',
                    'pltd.name AS team_playground_name',
                    'pltd.picture AS playground_team_picture',
                    'plcd.id AS club_playground_id',
                    'plcd.alias AS club_playground_alias',
                    'plcd.name AS club_playground_name',
                    'plcd.picture AS playground_club_picture',
                ])
                ->from('#__sportsmanagement_match AS m')
                ->join('INNER', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id')
                ->join('INNER', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id')
                ->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt1.project_id')
                ->join('INNER', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id')
                ->join('INNER', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id')
                ->join('INNER', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id')
                ->join('INNER', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id')
                ->join('INNER', '#__sportsmanagement_club AS c1 ON c1.id = t1.club_id')
                ->join('LEFT', '#__sportsmanagement_club AS c2 ON c2.id = t2.club_id')
                ->join('INNER', '#__sportsmanagement_league AS lg ON lg.id = p.league_id')
                ->join('LEFT', '#__sportsmanagement_playground AS plcd ON c1.standard_playground = plcd.id')
                ->join('LEFT', '#__sportsmanagement_playground AS pltd ON pt1.standard_playground = pltd.id')
                ->join('LEFT', '#__sportsmanagement_playground AS pl ON m.playground_id = pl.id')
                ->where('m.match_date > CURRENT_TIMESTAMP')
                ->where('m.published = 1')
                ->where('p.published = 1')
                ->order('m.match_date ASC');

            if ($projectIds) {
                $query->where('p.id IN (' . implode(',', $projectIds) . ')');
            }

            if ($playgroundIds) {
                $ids = implode(',', $playgroundIds);
                $query->where(
                    '(m.playground_id IN (' . $ids . ')'
                    . ' OR (pt1.standard_playground IN (' . $ids . ') AND m.playground_id IS NULL)'
                    . ' OR (c1.standard_playground IN (' . $ids . ') AND m.playground_id IS NULL AND pt1.standard_playground IS NULL))'
                );
            }

            $db->setQuery($query, 0, $limit);
            $rows = $db->loadObjectList() ?: [];
            $cfg = (int) $params->get('cfg_which_database', 0);
            $season = (int) $params->get('s', 0);
            $showLink = (int) $params->get('show_playground_link', 1) === 1;
            $showLogos = (int) $params->get('show_club_logo', 1) === 1;

            foreach ($rows as $row) {
                $row->project_slug = $this->slug((int) $row->project_id, (string) $row->project_alias);

                [$playgroundId, $playgroundAlias, $playgroundName, $playgroundPicture] = $this->playground($row);
                $row->display_playground_name = $playgroundName;
                $row->display_playground_picture = $playgroundPicture;
                $row->display_playground_slug = $this->slug($playgroundId, $playgroundAlias);
                $row->playground_link = '';

                if ($showLink && $playgroundId > 0) {
                    $row->playground_link = SiteRouteHelper::view('playground', [
                        'cfg_which_database' => $cfg,
                        's' => $season ?: (int) ($row->season_id ?? 0),
                        'p' => $row->project_slug,
                        'pgid' => $row->display_playground_slug,
                    ]);
                }

                if ($showLogos) {
                    $row->team1_logo = $this->logo((string) ($row->team1_logo ?? ''), $logoField);
                    $row->team2_logo = $this->logo((string) ($row->team2_logo ?? ''), $logoField);
                } else {
                    $row->team1_logo = '';
                    $row->team2_logo = '';
                }
            }

            return $rows;
        } catch (\Throwable $exception) {
            $app->enqueueMessage($exception->getMessage(), 'error');

            return [];
        }
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }

    private function logo(string $path, string $logoField): string
    {
        if ($path !== '') {
            return $path;
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        return (string) match ($logoField) {
            'logo_small' => $componentParams->get('ph_logo_small', ''),
            'logo_middle' => $componentParams->get('ph_logo_medium', ''),
            default => $componentParams->get('ph_logo_big', ''),
        };
    }

    private function playground(object $row): array
    {
        if ((int) ($row->playground_id ?? 0) > 0) {
            return [
                (int) $row->playground_id,
                (string) ($row->playground_alias ?? ''),
                (string) ($row->playground_name ?? ''),
                (string) ($row->playground_picture ?? ''),
            ];
        }

        if ((int) ($row->team_playground_id ?? 0) > 0) {
            return [
                (int) $row->team_playground_id,
                (string) ($row->team_playground_alias ?? ''),
                (string) ($row->team_playground_name ?? ''),
                (string) ($row->playground_team_picture ?? ''),
            ];
        }

        if ((int) ($row->club_playground_id ?? 0) > 0) {
            return [
                (int) $row->club_playground_id,
                (string) ($row->club_playground_alias ?? ''),
                (string) ($row->club_playground_name ?? ''),
                (string) ($row->playground_club_picture ?? ''),
            ];
        }

        return [0, '', '', ''];
    }

    private function teamField(string $value): string
    {
        return in_array($value, ['name', 'middle_name', 'short_name'], true)
            ? $value
            : 'middle_name';
    }

    private function logoField(string $value): string
    {
        return in_array($value, ['logo_small', 'logo_middle', 'logo_big'], true)
            ? $value
            : 'logo_big';
    }

    private function slug(int $id, string $alias): string
    {
        $alias = trim($alias);

        return $alias === '' ? (string) $id : $id . ':' . $alias;
    }

    private function ids(mixed $value): array
    {
        $values = is_array($value)
            ? $value
            : preg_split('/\s*,\s*/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];

        foreach ((array) $values as $item) {
            $id = (int) $item;

            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }
}
