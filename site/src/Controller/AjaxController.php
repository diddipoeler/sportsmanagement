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
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Native JSON endpoints used by the Joomla 5/6 frontend. */
final class AjaxController extends BaseController
{
    /**
     * Return the canonical URL for the selected SportsManagement view.
     * Validates the view command and uses the native AJAX model to resolve
     * project, team and division inputs before emitting a JSON link response.
     * @return void
     */
    public function getLink(): void
    {
        $input = $this->app->getInput();
        $rawView = $input->get('view', 'ranking', 'raw');
        $view = $input->getCmd('view', 'ranking');

        if (!is_string($rawView)
            || ($rawView !== '' && (strtolower($view) !== strtolower($rawView) || trim($rawView) !== $rawView))) {
            throw new \InvalidArgumentException('Invalid SportsManagement AJAX view.', 400);
        }

        // Preserve the documented ranking default for an explicitly empty view.
        if (trim($view) === '') {
            $view = 'ranking';
        }

        // Keep route names canonical and preserve the calendar routing alias.
        $view = strtolower(trim($view));

        $link = $this->ajaxModel()->getLink(
            $view,
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

    /**
     * Return JSON options for teams assigned to the requested project.
     * Reads a nonnegative project identifier and delegates the list query to
     * the native AJAX model instead of producing HTML or a legacy response.
     * @return void
     */
    public function getProjectTeams(): void
    {
        $input = $this->app->getInput();
        $this->sendJson($this->ajaxModel()->getProjectTeams(
            max(0, $input->getInt('project_id', $input->getInt('p', 0)))
        ));
    }

    /**
     * Return project selection options for the requested league.
     * Normalises the league identifier before serialising the model result to JSON.
     * @return void
     */
    public function getProjectSelect(): void
    {
        $input = $this->app->getInput();
        $this->sendJson($this->ajaxModel()->getProjectSelect(
            max(0, $input->getInt('league_id', $input->getInt('l', 0)))
        ));
    }

    /**
     * Return league options filtered by country and association.
     * Reads the country and association request values and delegates the lookup
     * to the AJAX model before writing the JSON payload.
     * @return void
     */
    public function getAssocLeagueSelect(): void
    {
        $input = $this->app->getInput();
        $this->sendJson($this->ajaxModel()->getAssocLeagueSelect(
            trim($input->getString('country', '')),
            max(0, $input->getInt('assoc_id', 0))
        ));
    }

    /**
     * Load second-level subassociation options for an AJAX selector.
     * Tries the native model, uses the legacy association query only when needed,
     * and returns a structured JSON error if both lookups fail.
     * @return void
     */
    public function getCountrySubSubAssocSelect(): void
    {
        $input = $this->app->getInput();
        $parentId = max(0, $input->getInt('subassoc_id', 0));
        $options = [];
        $errors = [];

        try {
            $options = $this->ajaxModel()->getCountrySubSubAssocSelect($parentId);
        } catch (\Throwable $error) {
            $errors[] = $this->ajaxErrorMessage('native', $error);
        }

        if ($parentId > 0 && count($options) <= 1) {
            try {
                $fallback = $this->legacyAssociationOptions(
                    '',
                    $parentId,
                    '-- Unterverbände 2 -- ',
                    '-- keine Unterverbände 2 -- '
                );

                if (count($fallback) > 1 || $options === []) {
                    $options = $fallback;
                }
            } catch (\Throwable $error) {
                $errors[] = $this->ajaxErrorMessage('fallback', $error);
            }
        }

        if ($options === [] && $errors !== []) {
            $this->sendAjaxError('getCountrySubSubAssocSelect', $errors);
            return;
        }

        $this->sendJson($options);
    }

    /**
     * Load first-level subassociation options for an AJAX selector.
     * Tries the native lookup and a compatible fallback while preserving
     * its empty-selection placeholder and reporting unrecoverable failures.
     * @return void
     */
    public function getCountrySubAssocSelect(): void
    {
        $input = $this->app->getInput();
        $parentId = max(0, $input->getInt('assoc_id', 0));
        $options = [];
        $errors = [];

        try {
            $options = $this->ajaxModel()->getCountrySubAssocSelect($parentId);
        } catch (\Throwable $error) {
            $errors[] = $this->ajaxErrorMessage('native', $error);
        }

        if ($parentId > 0 && count($options) <= 1) {
            try {
                $fallback = $this->legacyAssociationOptions(
                    '',
                    $parentId,
                    '-- Unterverbände -- ',
                    '-- keine Unterverbände -- '
                );

                if (count($fallback) > 1 || $options === []) {
                    $options = $fallback;
                }
            } catch (\Throwable $error) {
                $errors[] = $this->ajaxErrorMessage('fallback', $error);
            }
        }

        if ($options === [] && $errors !== []) {
            $this->sendAjaxError('getCountrySubAssocSelect', $errors);
            return;
        }

        $this->sendJson($options);
    }

    /**
     * Return the regional associations available for a selected country.
     * Handles an unselected country explicitly, then tries native and legacy
     * lookups so existing association selectors receive usable JSON options.
     * @return void
     */
    public function getcountryassoc(): void
    {
        $input = $this->app->getInput();
        $country = trim($input->getString('country', ''));

        if ($country === '' || $country === '0') {
            $this->sendJson([(object) ['value' => 0, 'text' => '-- keine Regionalverbände -- ']]);
            return;
        }

        $options = [];
        $errors = [];

        try {
            $options = $this->ajaxModel()->getCountryAssocSelect($country);
        } catch (\Throwable $error) {
            $errors[] = $this->ajaxErrorMessage('native', $error);
        }

        if (count($options) <= 1) {
            try {
                $fallback = $this->legacyAssociationOptions(
                    $country,
                    0,
                    '-- Regionalverbände -- ',
                    '-- keine Regionalverbände -- '
                );

                if (count($fallback) > 1 || $options === []) {
                    $options = $fallback;
                }
            } catch (\Throwable $error) {
                $errors[] = $this->ajaxErrorMessage('fallback', $error);
            }
        }

        if ($options === [] && $errors !== []) {
            $this->sendAjaxError('getcountryassoc', $errors);
            return;
        }

        $this->sendJson($options);
    }

    /**
     * Return a route string for the requested SportsManagement view.
     * Rejects unsafe or malformed view names, normalises the supported alias
     * and passes project/team/division choices to the native route helper.
     * @return void
     */
    public function getroute(): void
    {
        $input = $this->app->getInput();
        $rawView = $input->get('view', 'ranking', 'raw');
        $view = strtolower(trim($input->getCmd('view', 'ranking')));

        if (!is_string($rawView)
            || ($rawView !== '' && ($view !== strtolower($rawView) || trim($rawView) !== $rawView))) {
            throw new \InvalidArgumentException('Invalid SportsManagement AJAX view.', 400);
        }

        if ($view === '') {
            $view = 'ranking';
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

    /**
     * Return a JSON list of projects filtered by sport, league and season.
     * Bounds the three numeric request values before delegating the lookup
     * to the native AJAX model.
     * @return void
     */
    public function getprojectsoptions(): void
    {
        $input = $this->app->getInput();
        $this->sendJson($this->ajaxModel()->getProjectsOptions(
            max(0, $input->getInt('s', 0)),
            max(0, $input->getInt('l', 0)),
            max(0, $input->getInt('o', 0))
        ));
    }

    // AjaxModel::getLink() owns route construction via SiteRouteHelper::view().
    /**
     * Retrieve and type-check the native AJAX model from MVCFactory.
     * Only an AjaxModel can handle the controller's asynchronous requests.
     * @return AjaxModel Model used for native AJAX lookups.
     * @throws \RuntimeException If the factory returns an unexpected model.
     */
    private function ajaxModel(): AjaxModel
    {
        $model = $this->getModel('Ajax');

        if (!$model instanceof AjaxModel) {
            throw new \RuntimeException('Ajax controller requires AjaxModel.', 500);
        }

        return $model;
    }

    /**
     * Query association options using the legacy-compatible database schema.
     * Resolves the selected database and filters the association hierarchy,
     * returning placeholder options expected by existing frontend selectors.
     * @param string $country Country code, or empty when filtering by parent.
     * @param int $parentId Parent association identifier.
     * @param string $prompt Label for the unselected option.
     * @param string $nonePrompt Label displayed when no associations are found.
     * @return array Association select options.
     */
    private function legacyAssociationOptions(
        string $country,
        int $parentId,
        string $prompt,
        string $nonePrompt
    ): array {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = $this->app->getContainer()->get(DatabaseInterface::class);
        $input = $this->app->getInput();
        $configuredSelector = ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0);
        $rawSelector = $input->get('cfg_which_database', $configuredSelector, 'raw');

        if (!is_int($rawSelector) && (!is_string($rawSelector) || !preg_match('/^[0-9]+$/D', $rawSelector))) {
            throw new \InvalidArgumentException('Invalid SportsManagement AJAX database selector.', 400);
        }

        $selector = filter_var($rawSelector, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        if ($selector === false) {
            throw new \InvalidArgumentException('Invalid SportsManagement AJAX database selector.', 400);
        }
        $db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 's'))
            ->where($db->quoteName('s.parent_id') . ' = :parentAssociationId')
            ->bind(':parentAssociationId', $parentId, \Joomla\Database\ParameterType::INTEGER)
            ->where($db->quoteName('s.published') . ' = 1')
            ->order($db->quoteName('s.name') . ' ASC');

        if ($country !== '') {
            $query->where($db->quoteName('s.country') . ' = :associationCountry')
                ->bind(':associationCountry', $country, \Joomla\Database\ParameterType::STRING);
        }

        $db->setQuery($query, 0, 500);
        $rows = $db->loadObjectList() ?: [];

        return array_merge([
            (object) [
                'value' => 0,
                'text' => $rows === [] ? $nonePrompt : $prompt,
            ],
        ], $rows);
    }

    /**
     * Log an AJAX lookup failure without exposing internal error details.
     * Records the exception for administrators while returning only the stage
     * identifier to the caller for safe error aggregation.
     * @param string $stage Name of the failed lookup stage.
     * @param \Throwable $error Failure raised by that stage.
     * @return string Safe failure-stage marker.
     */
    private function ajaxErrorMessage(string $stage, \Throwable $error): string
    {
        // Keep the existing native/fallback diagnostic stages on the server;
        // public AJAX responses must never contain database error messages.
        Log::add(
            __METHOD__ . ' (' . $stage . '): ' . $error->getMessage(),
            Log::ERROR,
            'jsmerror'
        );

        return $stage;
    }

    /**
     * Emit a generic JSON failure response for an AJAX lookup.
     * Keeps exception details out of public responses, using Joomla's
     * translated generic message instead.
     * @param string $task Failing AJAX operation.
     * @param array $errors Internal lookup failures already logged.
     * @return void
     */
    private function sendAjaxError(string $task, array $errors): void
    {
        // The endpoint is read-only and retains its existing JSON response
        // structure for legacy JavaScript, without exposing exception details.
        $this->sendJson([
            'success' => false,
            'message' => Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
        ]);
    }

    /**
     * Write a JSON response and terminate the Joomla request.
     * Sets JSON MIME and no-store headers and encodes the payload for
     * the site's AJAX consumers without rendering a Joomla template.
     * @param mixed $payload Data to serialise in the HTTP response.
     * @return void
     */
    private function sendJson(mixed $payload): void
    {
        $app = $this->app;
        $app->getDocument()->setMimeEncoding('application/json');
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->setHeader('Cache-Control', 'no-store, private', true);
        $app->setHeader('X-Content-Type-Options', 'nosniff', true);
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