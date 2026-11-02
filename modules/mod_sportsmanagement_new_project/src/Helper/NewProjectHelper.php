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

    /**
     * Resolve the current Joomla site application for AJAX handling.
     *
     * Keeps the article writer independent of deprecated global application APIs.
     *
     * @return CMSApplicationInterface Active site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Load projects changed during the current site-local calendar day.
     *
     * Queries published projects and rejects malformed IDs, titles and
     * non-object rows before preparing safe routing, country flags and
     * image defaults for module rendering or authorised article creation.
     *
     * @param Registry $params Module configuration including database selector.
     * @param CMSApplicationInterface $app Active Joomla site application.
     * @param DatabaseInterface|null $joomlaDatabase Optional injected Joomla database.
     * @return array<int, object> Project presentation records.
     */
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
        $loadedRows = $db->loadObjectList();
        $rows = [];

        // Ignore malformed legacy records before using them in routes or
        // the authorised Joomla article writer.
        foreach (is_array($loadedRows) ? $loadedRows : [] as $row) {
            if (!is_object($row)) {
                continue;
            }

            $projectId = $this->databaseSelector($row->id ?? null);
            $name = $row->name ?? null;

            if ($projectId === null || $projectId <= 0
                || !is_string($name) || trim($name) === '') {
                continue;
            }

            $row->name = $name;
            $rows[] = $row;
        }

        $flags = $this->countryFlags($db, $rows);
        $rawPlaceholder = ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');
        $placeholder = is_string($rawPlaceholder) ? $rawPlaceholder : '';

        foreach ($rows as $row) {
            $projectId = $this->databaseSelector($row->id);
            $projectAlias = $row->project_alias ?? '';
            $row->project_slug = $projectId . ':'
                . (is_string($projectAlias) ? $projectAlias : '');
            $roundId = $this->databaseSelector($row->round_id ?? 0) ?? 0;
            $roundAlias = $row->round_alias ?? '';
            $row->round_slug = $roundId > 0
                ? $roundId . ':' . (is_string($roundAlias) ? $roundAlias : '')
                : '';
            $row->league_name = is_string($row->league_name ?? null) ? $row->league_name : '';
            $row->country = is_string($row->country ?? null) ? $row->country : '';
            $row->project_url = $this->resultsUrl($row, $databaseSelector);
            $row->flag_url = $flags[strtoupper(trim($row->country))] ?? '';

            foreach (['project_picture', 'league_picture'] as $pictureField) {
                $picture = $row->{$pictureField} ?? null;
                $row->{$pictureField} = is_string($picture) && $picture !== ''
                    ? $picture
                    : $placeholder;
            }
        }

        return $rows;
    }

    /**
     * Check whether the current identity may create project articles.
     *
     * Requires an explicitly enabled module setting, a validated content category, SportsManagement management permission and Joomla Content create permission.
     *
     * @param Registry $params Article-creation module settings.
     * @param CMSApplicationInterface $app Active Joomla site application.
     * @return bool Whether the user may use the article writer.
     */
    public function canCreateArticles(Registry $params, CMSApplicationInterface $app): bool
    {
        if (!self::articleCreationEnabled($params->get('new_project_article', 0))) {
            return false;
        }

        $categoryId = $this->contentCategoryId($params->get('mycategory', 0));

        if ($categoryId <= 0) {
            return false;
        }

        $identity = $app->getIdentity();

        return $identity !== null
            && $identity->authorise('core.manage', 'com_sportsmanagement')
            && $identity->authorise('core.create', 'com_content.category.' . $categoryId);
    }

    /**
     * Handle the protected AJAX request to create Joomla content articles.
     *
     * Validates the POST CSRF token, strict positive module identifier, published
     * module, configured category, valid author ID and current permissions.
     * Malformed project records are skipped rather than written as articles. Reuses Joomla's content model to save bounded project articles and avoids leaking internal errors.
     *
     * @param DatabaseInterface|null $joomlaDatabase Optional injected database service.
     * @return array<string, mixed> Created/skipped counts, errors and module ID.
     * @throws \RuntimeException When the request, module, category or access check is invalid.
     */
    public function createArticlesAjax(?DatabaseInterface $joomlaDatabase = null): array
    {
        $app = self::siteApplication();

        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid CSRF token.', 403);
        }

        $moduleId = $this->contentCategoryId($app->getInput()->post->get('module_id', null, 'RAW'));

        if ($moduleId <= 0) {
            throw new \RuntimeException('Invalid module.', 400);
        }

        $joomlaDatabase = $this->joomlaDatabase($joomlaDatabase);

        $module = $this->loadPublishedModule($joomlaDatabase, $moduleId);

        if (!$module) {
            throw new \RuntimeException('New Project module is not published.', 404);
        }

        if (!is_string($module->params ?? null)) {
            throw new \RuntimeException('Invalid New Project module settings.', 409);
        }

        $params = new Registry($module->params);

        if (!self::articleCreationEnabled($params->get('new_project_article', 0))) {
            throw new \RuntimeException('Article creation is disabled for this module.', 403);
        }

        $categoryId = $this->contentCategoryId($params->get('mycategory', 0));

        if ($categoryId <= 0 || !$this->validContentCategory($joomlaDatabase, $categoryId)) {
            throw new \RuntimeException('The configured content category is invalid.', 409);
        }

        $identity = $app->getIdentity();

        $authorId = is_object($identity) ? $this->contentCategoryId($identity->id ?? null) : 0;

        if ($authorId <= 0
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
            // Do not attempt article writes with malformed project data.
            if (!$this->validArticleProject($project)) {
                $skipped++;
                continue;
            }

            $projectId = $this->contentCategoryId($project->id);

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
                'created_by' => $authorId,
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
     * Verify a project record before creating an authorised Joomla article.
     *
     * Requires an object with a validated positive project ID and a
     * nonblank string title; rejects malformed legacy database rows.
     *
     * @param mixed $project Candidate project record.
     * @return bool Whether the record can be used for article creation.
     */
    private function validArticleProject(mixed $project): bool
    {
        return is_object($project)
            && $this->contentCategoryId($project->id ?? null) > 0
            && is_string($project->name ?? null)
            && trim($project->name) !== '';
    }

    /**
     * Decide whether a module explicitly enables article creation.
     *
     * Joomla module parameters may originate from JSON or legacy settings.
     * Only integer one and decimal string "1" enable this sensitive action;
     * arrays, booleans and partially numeric strings must not grant access.
     *
     * @param mixed $value Configured article-creation switch.
     * @return bool Whether creation was explicitly enabled.
     */
    private static function articleCreationEnabled(mixed $value): bool
    {
        return $value === 1 || $value === '1';
    }

    /**
     * Validate the configured Joomla content category for article writes.
     *
     * Reuses strict nonnegative selector validation but requires a positive
     * category ID. Invalid, overflowing and partial numeric values return 0.
     *
     * @param mixed $value Configured content category identifier.
     * @return int Positive category ID, or zero for invalid input.
     */
    private function contentCategoryId(mixed $value): int
    {
        $categoryId = $this->databaseSelector($value);

        return $categoryId !== null && $categoryId > 0 ? $categoryId : 0;
    }

    /**
     * Validate the database connection selector without unsafe PHP casts.
     *
     * Accepts nonnegative integers and zero-padded decimal strings within the platform integer range; rejects malformed and overflow values rather than silently selecting another database.
     *
     * @param mixed $value Configured database selector.
     * @return int|null Valid selector or null when invalid.
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

    /**
     * Resolve the Joomla database service for this module.
     *
     * Uses an explicitly injected database when provided; otherwise retrieves the registered Joomla DatabaseInterface service.
     *
     * @param DatabaseInterface|null $database Optional injected database.
     * @return DatabaseInterface Active Joomla database connection.
     * @throws \RuntimeException If the database service is unavailable.
     */
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

    /**
     * Load a published front-end New Project module by identifier.
     *
     * Restricts lookup to the New Project module type and Joomla site client.
     * Only explicitly published integer/string status values are accepted.
     *
     * @param DatabaseInterface $db Joomla database.
     * @param int $moduleId Positive module identifier from the request.
     * @return object|null Published module metadata or null.
     */
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

        return is_object($module) && in_array($module->published ?? null, [1, '1'], true)
            ? $module
            : null;
    }

    /**
     * Confirm that an article destination is a published Joomla content category.
     *
     * Checks the configured category ID, content extension and publication state before allowing a write operation.
     *
     * @param DatabaseInterface $db Joomla database.
     * @param int $categoryId Positive Joomla content category ID.
     * @return bool Whether the destination category is valid.
     */
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

    /**
     * Find project IDs already referenced by articles in a category.
     *
     * Searches both legacy numeric references and current prefixed references,
     * validates project IDs and matches only exact reference formats to avoid
     * duplicate articles or accidental matches to unrelated content.
     *
     * @param DatabaseInterface $db Joomla database.
     * @param int $categoryId Target Joomla content category ID.
     * @param array<int, object> $projects Projects being considered for article creation.
     * @return array<int, bool> Existing project IDs keyed by identifier.
     */
    private function existingProjectReferences(DatabaseInterface $db, int $categoryId, array $projects): array
    {
        $projectIds = [];

        foreach ($projects as $project) {
            if (!is_object($project)) {
                continue;
            }

            $id = $this->databaseSelector($project->id ?? null);

            if ($id !== null && $id > 0) {
                $projectIds[$id] = $id;
            }
        }

        if ($projectIds === []) {
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

        return self::matchedProjectReferences($db->loadColumn() ?: [], $projectIds);
    }

    /**
     * Match stored article references against the exact requested project IDs.
     *
     * Supports legacy numeric references and the namespaced new-project form
     * without accepting unrelated trailing digits or malformed database rows.
     *
     * @param array<int, mixed> $references Article xreference values.
     * @param array<int, int> $projectIds Allowed project identifiers keyed by ID.
     * @return array<int, bool> Existing requested projects keyed by identifier.
     */
    private static function matchedProjectReferences(array $references, array $projectIds): array
    {
        $existing = [];

        foreach ($references as $reference) {
            if (!is_string($reference) && !is_int($reference)) {
                continue;
            }

            $text = (string) $reference;

            if (!preg_match('/^(?:sportsmanagement-project:)?([1-9][0-9]*)$/D', $text, $match)) {
                continue;
            }

            $id = filter_var($match[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($id !== false && isset($projectIds[$id])) {
                $existing[$id] = true;
            }
        }

        return $existing;
    }

    /**
     * Build escaped introductory HTML for a generated project article.
     *
     * Escapes project and league names and URLs; adds images only for paths accepted by the module's media URL validator.
     *
     * @param object $project Prepared project data.
     * @return string Safe introductory article markup.
     */
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

    /**
     * Build the Joomla results-ranking link for a project.
     *
     * Passes the project and round slugs together with the validated SportsManagement
     * database selector to the native route helper. Preserves selectors greater
     * than one instead of silently redirecting to the default database.
     *
     * @param object $project Project record with prepared route slugs.
     * @param int $databaseSelector Selected SportsManagement database.
     * @return string Project results-ranking URL.
     */
    private function resultsUrl(object $project, int $databaseSelector = 0): string
    {
        return SiteRouteHelper::view('resultsranking', [
            'cfg_which_database' => $databaseSelector >= 0 ? $databaseSelector : 0,
            's' => 0,
            'p' => (string) $project->project_slug,
            'r' => (string) ($project->round_slug ?? ''),
            'division' => 0,
            'mode' => 0,
            'order' => 0,
            'layout' => 0,
        ]);
    }

    /**
     * Resolve URLs for the flags of the projects' league countries.
     *
     * Collects distinct configured country codes and loads their image paths
     * from the SportsManagement countries table. Malformed country rows and
     * non-string image fields are ignored.
     *
     * @param DatabaseInterface $db Selected SportsManagement database.
     * @param array<int, object> $rows Loaded project records.
     * @return array<string, string> Flag image URLs keyed by uppercase country code.
     */
    private function countryFlags(DatabaseInterface $db, array $rows): array
    {
        $countries = [];

        foreach ($rows as $row) {
            if (!is_object($row) || !is_string($row->country ?? null)) {
                continue;
            }

            $country = strtoupper(trim($row->country));

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

        $countryRows = $db->loadObjectList();

        foreach (is_array($countryRows) ? $countryRows : [] as $country) {
            if (!is_object($country) || !is_string($country->alpha3 ?? null)) {
                continue;
            }

            $code = strtoupper(trim($country->alpha3));
            $picture = $country->picture ?? null;

            if ($code !== '' && is_string($picture)) {
                $result[$code] = $this->mediaUrl($picture);
            }
        }

        return $result;
    }

    /**
     * Calculate today's half-open UTC range in the site's timezone.
     *
     * Determines consecutive local midnights independently to handle daylight-saving days of 23 or 25 hours, then converts the boundaries to database UTC timestamps.
     *
     * @param CMSApplicationInterface $app Joomla site application for timezone settings.
     * @param \DateTimeImmutable|null $now Optional reference instant for testing.
     * @return array{0: string, 1: string} Inclusive UTC start and exclusive UTC end.
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

    /**
     * Normalise a project or league picture to a safe URL.
     *
     * Permits absolute HTTP(S) URLs without credentials and encodes local paths under the Joomla site root; disallows unsafe schemes and traversal.
     *
     * @param string $path Relative or absolute image path.
     * @return string Validated image URL or an empty string.
     */
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
