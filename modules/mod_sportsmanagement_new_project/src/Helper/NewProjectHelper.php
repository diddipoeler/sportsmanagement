<?php
/**
 * Joomla 5/6 data and AJAX helper for mod_sportsmanagement_new_project.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNewProject\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class NewProjectHelper
{
    // Bound both the daily module render and its optional article-creation
    // action to avoid loading or creating an unlimited number of records.
    private const MAX_DAILY_PROJECTS = 100;

    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        ?DatabaseInterface $joomlaDatabase = null
    ): array {
        $databaseSelector = $this->databaseSelector($params->get('cfg_which_database', 0));

        // Invalid module settings must not silently select another database.
        if ($databaseSelector === null) {
            return [];
        }

        $joomlaDatabase = $this->joomlaDatabase($joomlaDatabase);
        $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $databaseSelector);
        [$start, $end] = $this->todayRange($app);

        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.picture', 'project_picture'),
                $db->quoteName('p.alias', 'project_alias'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('l.country'),
                $db->quoteName('l.picture', 'league_picture'),
                $db->quoteName('r.id', 'round_id'),
                $db->quoteName('r.alias', 'round_alias'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round') . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('p.modified') . ' >= :modifiedStart')
            ->where($db->quoteName('p.modified') . ' < :modifiedEnd')
            ->where($db->quoteName('p.published') . ' = 1')
            ->bind(':modifiedStart', $start, ParameterType::STRING)
            ->bind(':modifiedEnd', $end, ParameterType::STRING)
            ->order($db->quoteName('p.name') . ' ASC');
        $db->setQuery($query, 0, self::MAX_DAILY_PROJECTS);
        $rows = $db->loadObjectList() ?: [];

        $flags = $this->countryFlags($db, $rows);
        $placeholder = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');

        foreach ($rows as $row) {
            $row->project_slug = (int) ($row->id ?? 0) . ':' . (string) ($row->project_alias ?? '');
            $roundId = (int) ($row->round_id ?? 0);
            $row->round_slug = $roundId > 0
                ? $roundId . ':' . (string) ($row->round_alias ?? '')
                : '';
            $row->project_url = $this->resultsUrl($row, $databaseSelector);
            $row->flag_url = $flags[strtoupper(trim((string) ($row->country ?? '')))] ?? '';
            $row->project_picture = (string) ($row->project_picture ?: $placeholder);
            $row->league_picture = (string) ($row->league_picture ?: $placeholder);
        }

        return $rows;
    }

    public function canCreateArticles(Registry $params, CMSApplicationInterface $app): bool
    {
        if (!(int) $params->get('new_project_article', 0)) {
            return false;
        }

        $categoryId = (int) $params->get('mycategory', 0);

        if ($categoryId <= 0) {
            return false;
        }

        $identity = $app->getIdentity();

        return $identity !== null
            && $identity->authorise('core.manage', 'com_sportsmanagement')
            && $identity->authorise('core.create', 'com_content.category.' . $categoryId);
    }

    public function createArticlesAjax(?DatabaseInterface $joomlaDatabase = null): array
    {
        $app = self::siteApplication();

        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid CSRF token.', 403);
        }

        $moduleId = $app->getInput()->post->getInt('module_id', 0);

        if ($moduleId <= 0) {
            throw new \RuntimeException('Invalid module.', 400);
        }

        $joomlaDatabase = $this->joomlaDatabase($joomlaDatabase);

        $module = $this->loadPublishedModule($joomlaDatabase, $moduleId);

        if (!$module) {
            throw new \RuntimeException('New Project module is not published.', 404);
        }

        $params = new Registry((string) $module->params);

        if (!(int) $params->get('new_project_article', 0)) {
            throw new \RuntimeException('Article creation is disabled for this module.', 403);
        }

        $categoryId = (int) $params->get('mycategory', 0);

        if ($categoryId <= 0 || !$this->validContentCategory($joomlaDatabase, $categoryId)) {
            throw new \RuntimeException('The configured content category is invalid.', 409);
        }

        $identity = $app->getIdentity();

        if ($identity === null
            || !$identity->authorise('core.manage', 'com_sportsmanagement')
            || !$identity->authorise('core.create', 'com_content.category.' . $categoryId)) {
            throw new \RuntimeException('Not authorised to create project articles.', 403);
        }

        $projects = array_slice(
            $this->getData($params, $app, $joomlaDatabase),
            0,
            self::MAX_DAILY_PROJECTS
        );

        if (!$projects) {
            return ['created' => 0, 'skipped' => 0, 'errors' => [], 'module_id' => $moduleId];
        }

        $existing = $this->existingProjectReferences($joomlaDatabase, $categoryId, $projects);
        $content = $app->bootComponent('com_content');
        $mvcFactory = $content->getMVCFactory();
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($projects as $project) {
            $projectId = (int) $project->id;

            if (isset($existing[$projectId])) {
                $skipped++;
                continue;
            }

            $model = $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);

            if (!$model) {
                $errors[] = ['project_id' => $projectId, 'message' => 'Content article model unavailable.'];
                continue;
            }

            $data = [
                'id' => 0,
                'title' => (string) $project->name,
                'alias' => OutputFilter::stringURLSafe((string) $project->name . '-' . $projectId),
                'catid' => $categoryId,
                'state' => 1,
                'access' => 1,
                'featured' => 1,
                'language' => '*',
                'created_by' => (int) $identity->id,
                'xreference' => 'sportsmanagement-project:' . $projectId,
                'introtext' => $this->articleIntro($project),
            ];

            try {
                if (!$model->save($data)) {
                    $errors[] = [
                        'project_id' => $projectId,
                        'message' => 'Content article could not be saved.',
                    ];
                    continue;
                }

                $created++;
            } catch (\Throwable $e) {
                // Do not leak SQL errors or server paths in public AJAX output.
                Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
                $errors[] = [
                    'project_id' => $projectId,
                    'message' => 'Content article could not be saved.',
                ];
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'module_id' => $moduleId,
        ];
    }

    /**
     * Accept only non-negative decimal database selectors, including legacy
     * zero-padded parameters; do not turn invalid values into database zero.
     */
    private function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || !preg_match('/^[0-9]+$/D', $value)) {
            return null;
        }

        $digits = ltrim($value, '0');

        if ($digits === '') {
            return 0;
        }

        $maximum = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($maximum)
            || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    private function joomlaDatabase(?DatabaseInterface $database = null): DatabaseInterface
    {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement NewProject database service is unavailable.', 500);
        }

        return $database;
    }

    private function loadPublishedModule(DatabaseInterface $db, int $moduleId): ?object
    {
        $moduleName = 'mod_sportsmanagement_new_project';
        $query = $db->createQuery()
            ->select([
                $db->quoteName('params'),
                $db->quoteName('published'),
            ])
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('id') . ' = :moduleId')
            ->where($db->quoteName('module') . ' = :moduleName')
            ->where($db->quoteName('client_id') . ' = 0')
            ->bind(':moduleId', $moduleId, ParameterType::INTEGER)
            ->bind(':moduleName', $moduleName, ParameterType::STRING);
        $db->setQuery($query, 0, 1);
        $module = $db->loadObject();

        return $module && (int) $module->published === 1 ? $module : null;
    }

    private function validContentCategory(DatabaseInterface $db, int $categoryId): bool
    {
        $extension = 'com_content';
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__categories'))
            ->where($db->quoteName('id') . ' = :categoryId')
            ->where($db->quoteName('extension') . ' = :extension')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':categoryId', $categoryId, ParameterType::INTEGER)
            ->bind(':extension', $extension, ParameterType::STRING);
        $db->setQuery($query);

        return (int) $db->loadResult() === 1;
    }

    private function existingProjectReferences(DatabaseInterface $db, int $categoryId, array $projects): array
    {
        $projectIds = array_values(array_filter(array_map(static fn($row): int => (int) ($row->id ?? 0), $projects)));

        if (!$projectIds) {
            return [];
        }

        $references = [];

        foreach ($projectIds as $id) {
            $references[] = (string) $id;
            $references[] = 'sportsmanagement-project:' . $id;
        }

        $query = $db->createQuery()
            ->select($db->quoteName('xreference'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('catid') . ' = :categoryId')
            ->whereIn($db->quoteName('xreference'), $references, ParameterType::STRING)
            ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        $db->setQuery($query);

        $existing = [];

        foreach ($db->loadColumn() ?: [] as $reference) {
            if (preg_match('/(\d+)$/', (string) $reference, $match)) {
                $existing[(int) $match[1]] = true;
            }
        }

        return $existing;
    }

    private function articleIntro(object $project): string
    {
        $url = htmlspecialchars((string) $project->project_url, ENT_QUOTES, 'UTF-8');
        $name = htmlspecialchars((string) $project->name, ENT_QUOTES, 'UTF-8');
        $league = htmlspecialchars((string) $project->league_name, ENT_QUOTES, 'UTF-8');
        $leaguePicture = $this->mediaUrl((string) ($project->league_picture ?? ''));
        $projectPicture = $this->mediaUrl((string) ($project->project_picture ?? ''));

        // Omit image elements when no safe source is available.
        $leagueImage = $leaguePicture !== ''
            ? '<img src="' . htmlspecialchars($leaguePicture, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $league . '" style="float:left" width="100" /> '
            : '';
        $projectImage = $projectPicture !== ''
            ? '<img src="' . htmlspecialchars($projectPicture, ENT_QUOTES, 'UTF-8')
                . '" alt="' . $name . '" style="float:right" width="100" />'
            : '';

        return '<p><a href="' . $url . '">'
            . $leagueImage . $name . ' - ( ' . $league . ' )</a> neu angelegt/aktualisiert. '
            . $projectImage . '</p>';
    }

    private function resultsUrl(object $project, int $databaseSelector = 0): string
    {
        return SiteRouteHelper::view('resultsranking', [
            'cfg_which_database' => $databaseSelector === 1 ? 1 : 0,
            's' => 0,
            'p' => (string) $project->project_slug,
            'r' => (string) ($project->round_slug ?? ''),
            'division' => 0,
            'mode' => 0,
            'order' => 0,
            'layout' => 0,
        ]);
    }

    private function countryFlags(DatabaseInterface $db, array $rows): array
    {
        $countries = [];

        foreach ($rows as $row) {
            $country = strtoupper(trim((string) ($row->country ?? '')));

            if ($country !== '') {
                $countries[$country] = $country;
            }
        }

        if (!$countries) {
            return [];
        }

        $countryCodes = array_values($countries);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->whereIn($db->quoteName('alpha3'), $countryCodes, ParameterType::STRING);
        $db->setQuery($query);
        $result = [];

        foreach ($db->loadObjectList() ?: [] as $country) {
            $result[strtoupper((string) $country->alpha3)] = $this->mediaUrl((string) $country->picture);
        }

        return $result;
    }

    /**
     * Convert the site's current calendar day into a half-open UTC range.
     * SportsManagement stores project.modified in UTC. Calculating both
     * local midnights independently also handles 23- and 25-hour DST days.
     *
     * @return array{0: string, 1: string}
     */
    private function todayRange(CMSApplicationInterface $app, ?\DateTimeImmutable $now = null): array
    {
        try {
            $timezone = new \DateTimeZone((string) $app->get('offset', 'UTC'));
        } catch (\Throwable) {
            $timezone = new \DateTimeZone('UTC');
        }

        $today = ($now ?? new \DateTimeImmutable('now', $timezone))
            ->setTimezone($timezone)
            ->setTime(0, 0);
        $tomorrow = $today->modify('+1 day');
        $utc = new \DateTimeZone('UTC');

        return [
            $today->setTimezone($utc)->format('Y-m-d H:i:s'),
            $tomorrow->setTimezone($utc)->format('Y-m-d H:i:s'),
        ];
    }

    private function mediaUrl(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path)) {
            if (preg_match('/[\x00-\x20\x7F]/', $path)
                || str_contains($path, '\\')
                || filter_var($path, FILTER_VALIDATE_URL) === false
            ) {
                return '';
            }

            $parts = parse_url($path);

            return is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                ? $path
                : '';
        }

        // Data from historic media fields is not necessarily a safe path.
        // Do not permit URL schemes, protocol-relative paths or traversal.
        if (preg_match('/[\x00-\x1F\x7F]/', $path)
            || str_contains($path, '\\')
            || str_starts_with($path, '//')
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $path)
            || preg_match('~(?:^|/)\.\.?(?:/|$)~', $path)
            || str_contains($path, '%')
            || strpbrk($path, '?#') !== false
        ) {
            return '';
        }

        $relative = ltrim($path, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $relative)));

        return rtrim((string) Uri::root(), '/') . '/' . $encoded;
    }
}
