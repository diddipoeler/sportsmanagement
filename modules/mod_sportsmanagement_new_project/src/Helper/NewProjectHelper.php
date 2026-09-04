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
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class NewProjectHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        [$start, $end] = $this->todayRange();

        $query = $db->createQuery()
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('p.name'),
                $db->quoteName('p.picture', 'project_picture'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('l.country'),
                $db->quoteName('l.picture', 'league_picture'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round') . ' AND ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('p.modified') . ' BETWEEN ' . $db->quote($start) . ' AND ' . $db->quote($end))
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('p.name') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        $flags = $this->countryFlags($db, $rows);
        $placeholder = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_project', '');

        foreach ($rows as $row) {
            $row->project_url = $this->resultsUrl($row);
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

        return $identity->authorise('core.manage', 'com_sportsmanagement')
            && $identity->authorise('core.create', 'com_content.category.' . $categoryId);
    }

    public function createArticlesAjax(): array
    {
        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);

        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid CSRF token.', 403);
        }

        $moduleId = $app->getInput()->post->getInt('module_id', 0);

        if ($moduleId <= 0) {
            throw new \RuntimeException('Invalid module.', 400);
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $module = $this->loadPublishedModule($db, $moduleId);

        if (!$module) {
            throw new \RuntimeException('New Project module is not published.', 404);
        }

        $params = new Registry((string) $module->params);

        if (!(int) $params->get('new_project_article', 0)) {
            throw new \RuntimeException('Article creation is disabled for this module.', 403);
        }

        $categoryId = (int) $params->get('mycategory', 0);

        if ($categoryId <= 0 || !$this->validContentCategory($db, $categoryId)) {
            throw new \RuntimeException('The configured content category is invalid.', 409);
        }

        $identity = $app->getIdentity();

        if (!$identity->authorise('core.manage', 'com_sportsmanagement')
            || !$identity->authorise('core.create', 'com_content.category.' . $categoryId)) {
            throw new \RuntimeException('Not authorised to create project articles.', 403);
        }

        $projects = $this->getData($params, $app);

        if (!$projects) {
            return ['created' => 0, 'skipped' => 0, 'errors' => [], 'module_id' => $moduleId];
        }

        $existing = $this->existingProjectReferences($db, $categoryId, $projects);
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
                    $errors[] = ['project_id' => $projectId, 'message' => (string) $model->getError()];
                    continue;
                }

                $created++;
            } catch (\Throwable $e) {
                $errors[] = ['project_id' => $projectId, 'message' => $e->getMessage()];
            }
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => $errors,
            'module_id' => $moduleId,
        ];
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
            ->bind(':moduleName', $moduleName);
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
            ->bind(':extension', $extension);
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
            $references[] = $db->quote((string) $id);
            $references[] = $db->quote('sportsmanagement-project:' . $id);
        }

        $query = $db->createQuery()
            ->select($db->quoteName('xreference'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('catid') . ' = :categoryId')
            ->where($db->quoteName('xreference') . ' IN (' . implode(',', $references) . ')')
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
        $leaguePicture = htmlspecialchars($this->mediaUrl((string) $project->league_picture), ENT_QUOTES, 'UTF-8');
        $projectPicture = htmlspecialchars($this->mediaUrl((string) $project->project_picture), ENT_QUOTES, 'UTF-8');

        return '<p><a href="' . $url . '">'
            . '<img src="' . $leaguePicture . '" alt="' . $league . '" style="float:left" width="100" /> '
            . $name . ' - ( ' . $league . ' )</a> neu angelegt/aktualisiert. '
            . '<img src="' . $projectPicture . '" alt="' . $name . '" style="float:right" width="100" />'
            . '</p>';
    }

    private function resultsUrl(object $project): string
    {
        return SiteRouteHelper::view('resultsranking', [
            'cfg_which_database' => 0,
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

        $quoted = array_map([$db, 'quote'], array_values($countries));
        $query = $db->createQuery()
            ->select([
                $db->quoteName('alpha3'),
                $db->quoteName('picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' IN (' . implode(',', $quoted) . ')');
        $db->setQuery($query);
        $result = [];

        foreach ($db->loadObjectList() ?: [] as $country) {
            $result[strtoupper((string) $country->alpha3)] = $this->mediaUrl((string) $country->picture);
        }

        return $result;
    }

    private function todayRange(): array
    {
        $date = Factory::getDate();
        $day = $date->format('Y-m-d');

        return [$day . ' 00:00:00', $day . ' 23:59:59'];
    }

    private function mediaUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/');
    }
}
