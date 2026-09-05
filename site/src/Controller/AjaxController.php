<?php
/**
 * Native JSON endpoints used by the Joomla 5/6 frontend.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\DatabaseInterface;

/** Native JSON endpoints used by the Joomla 5/6 frontend. */
final class AjaxController extends BaseController
{
    public function getLink(): void
    {
        $input = $this->getApplication()->getInput();
        $link = $this->ajaxModel()->getLink(
            $input->getCmd('view', 'ranking'),
            max(0, $input->getInt('project_id', $input->getInt('p', 0))),
            max(0, $input->getInt('team_id', $input->getInt('tid', 0))),
            max(0, $input->getInt('division_id', $input->getInt('division', 0))),
            $input->getString('points', '3,1,0'),
            max(0, $input->getInt('tnid', 0))
        );

        $this->sendJson([
            'linktext' => $input->getString('linktext', ''),
            'link' => $link,
        ]);
    }

    public function getProjectTeams(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectTeams(max(0, $input->getInt('project_id', 0))));
    }

    public function getProjectSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectSelect(max(0, $input->getInt('league_id', 0))));
    }

    public function getAssocLeagueSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getAssocLeagueSelect(
            $input->getString('country', ''),
            max(0, $input->getInt('assoc_id', 0))
        ));
    }

    public function getCountrySubSubAssocSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $parentId = max(0, $input->getInt('subassoc_id', 0));
        $options = [];

        try {
            $options = $this->ajaxModel()->getCountrySubSubAssocSelect($parentId);
        } catch (\Throwable) {
            // Older external SportsManagement databases may not have all modern publication columns.
        }

        if ($parentId > 0 && count($options) <= 1) {
            $fallback = $this->legacyAssociationOptions(
                '',
                $parentId,
                '-- Unterverbände 2 -- ',
                '-- keine Unterverbände 2 -- '
            );

            if (count($fallback) > 1 || $options === []) {
                $options = $fallback;
            }
        }

        $this->sendJson($options);
    }

    public function getCountrySubAssocSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $parentId = max(0, $input->getInt('assoc_id', 0));
        $options = [];

        try {
            $options = $this->ajaxModel()->getCountrySubAssocSelect($parentId);
        } catch (\Throwable) {
            // Older external SportsManagement databases may not have all modern publication columns.
        }

        if ($parentId > 0 && count($options) <= 1) {
            $fallback = $this->legacyAssociationOptions(
                '',
                $parentId,
                '-- Unterverbände -- ',
                '-- keine Unterverbände -- '
            );

            if (count($fallback) > 1 || $options === []) {
                $options = $fallback;
            }
        }

        $this->sendJson($options);
    }

    public function getcountryassoc(): void
    {
        $input = $this->getApplication()->getInput();
        $country = trim($input->getString('country', ''));

        if ($country === '' || $country === '0') {
            $this->sendJson([(object) ['value' => 0, 'text' => '-- keine Regionalverbände -- ']]);
            return;
        }

        $options = [];

        try {
            $options = $this->ajaxModel()->getCountryAssocSelect($country);
        } catch (\Throwable) {
            // Older external SportsManagement databases may not have all modern publication columns.
        }

        if (count($options) <= 1) {
            $fallback = $this->legacyAssociationOptions(
                $country,
                0,
                '-- Regionalverbände -- ',
                '-- keine Regionalverbände -- '
            );

            if (count($fallback) > 1 || $options === []) {
                $options = $fallback;
            }
        }

        $this->sendJson($options);
    }

    public function getroute(): void
    {
        $input = $this->getApplication()->getInput();
        $view = strtolower($input->getCmd('view', 'ranking'));

        if ($view === 'calendar') {
            $view = 'teamplan';
        }

        $link = $this->ajaxModel()->getLink(
            $view,
            max(0, $input->getInt('p', 0)),
            max(0, $input->getInt('tid', 0)),
            max(0, $input->getInt('division', 0)),
            $input->getString('points', '3,1,0'),
            max(0, $input->getInt('tnid', 0))
        );

        $this->sendJson($link);
    }

    public function getprojectsoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectsOptions(
            max(0, $input->getInt('s', 0)),
            max(0, $input->getInt('l', 0)),
            max(0, $input->getInt('o', 0))
        ));
    }

    // AjaxModel::getLink() owns route construction via SiteRouteHelper::view().
    private function ajaxModel(): AjaxModel
    {
        $model = $this->getModel('Ajax');

        if (!$model instanceof AjaxModel) {
            throw new \RuntimeException('Ajax controller requires AjaxModel.', 500);
        }

        return $model;
    }

    private function legacyAssociationOptions(
        string $country,
        int $parentId,
        string $prompt,
        string $nonePrompt
    ): array {
        $container = Factory::getContainer();
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = $container->get(DatabaseInterface::class);
        $input = $this->getApplication()->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
        );
        $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 's'))
            ->where($db->quoteName('s.parent_id') . ' = ' . $parentId)
            ->order($db->quoteName('s.name') . ' ASC');

        if ($country !== '') {
            $query->where($db->quoteName('s.country') . ' = ' . $db->quote($country));
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        return array_merge([
            (object) [
                'value' => 0,
                'text' => $rows === [] ? $nonePrompt : $prompt,
            ],
        ], $rows);
    }

    private function sendJson(mixed $payload): void
    {
        $app = $this->getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
            | JSON_THROW_ON_ERROR
        );
        $app->close();
    }
}
