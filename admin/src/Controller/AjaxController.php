<?php
/**
 * Native Joomla 5/6 read-only JSON controller for administrator option endpoints.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native read-only JSON controller for administrator option endpoints. */
final class AjaxController extends BaseController
{
    /**
     * Return a JSON list of configured prediction games for the selected SportsManagement database.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function predictionid(): void
    {
        $this->respondPredictionId();
    }

    /** Backward-compatible task name used by legacy administrator JavaScript. */
    /**
     * Provide the historical AJAX alias for retrieving prediction-game identifiers.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function getpredictionid(): void
    {
        $this->respondPredictionId();
    }

    /**
     * Return available groups belonging to the requested prediction game.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function predictiongroups(): void
    {
        $input = $this->input;
        $this->respondModel('getPredictionGroups', [
            max(0, $input->getInt('prediction_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return project choices associated with the selected prediction game.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function predictionpj(): void
    {
        $input = $this->input;
        $this->respondModel('getPredictionPj', [
            max(0, $input->getInt('prediction_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Load location choices matching the submitted postcode and country.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function locationzipcodeoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getlocationzipcodeoptions', [
            $input->getString('zipcode'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            $input->getCmd('country'),
        ]);
    }

    /**
     * Resolve the display name of a selected country code.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function getCcountryName(): void
    {
        $this->respondModel('getCcountryName', [
            $this->input->getCmd('country'),
        ]);
    }

    /**
     * Resolve the two-letter country code used by country selectors.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function getCcountryAlpha2(): void
    {
        $this->respondModel('getCcountryAlpha2', [
            $this->input->getCmd('country'),
        ]);
    }

    /**
     * Return league options filtered by the selected nation.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function countryleagueoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getcountryleagueoptions', [
            $input->getCmd('search_nation'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return postcode choices for the selected country and optional project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function countryzipcodeoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getcountryzipcodeoptions', [
            $input->getCmd('country'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            max(0, $input->getInt('p')),
        ]);
    }

    /**
     * Return a club's permitted age-group options.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function countryclubagegroupoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getcountryclubagegroupoptions', [
            max(0, $input->getInt('club_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return association options for the selected country.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function associationsoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getassociationsoptions', [
            $input->getCmd('country'),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Resolve person-contact options for the selected Joomla user profile.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function personcontactid(): void
    {
        $input = $this->input;
        $this->respondModel('getpersoncontactid', [
            max(0, $input->getInt('show_user_profile')),
            $input->getBool('required'),
        ]);
    }

    /**
     * List sports projects associated with the selected season.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projects(): void
    {
        $input = $this->input;
        $this->respondModel('getProjects', [
            max(0, $input->getInt('s', 0)),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * List season choices for the configured SportsManagement database.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function seasons(): void
    {
        $input = $this->input;
        $this->respondModel('getseasons', [
            max(0, $input->getInt('cfg_which_database')),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    /**
     * Return available people filtered by the selected person type.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function personlistoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getpersonlistoptions', [
            max(0, $input->getInt('person_art')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return people available for assignment to a project team.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function personlistoptionsprojectteam(): void
    {
        $input = $this->input;
        $this->respondModel('getpersonlistoptionsprojectteam', [
            max(0, $input->getInt('person_art')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return player or staff positions for the chosen sport type.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function personpositionoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getpersonpositionoptions', [
            max(0, $input->getInt('sports_type_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return person age groups for sport, project and country selections.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function personagegroupoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getpersonagegroupoptions', [
            max(0, $input->getInt('sports_type_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            max(0, $input->getInt('project')),
            $input->getCmd('country'),
        ]);
    }

    /**
     * List available members of the selected prediction game.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function predictionmembersoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getpredictionmembersoptions', [
            max(0, $input->getInt('prediction_id')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return division choices for the currently selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectdivisionsoptions(): void
    {
        $this->respondProjectOption('getProjectDivisionsOptions');
    }

    /**
     * Return event choices belonging to the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projecteventsoptions(): void
    {
        $this->respondProjectOption('getProjectEventsOptions');
    }

    /**
     * Return project-team choices restricted to a division.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectteamsbydivisionoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getProjectTeamsByDivisionOptions', [
            max(0, $input->getInt('p', 0)),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            max(0, $input->getInt('division')),
        ]);
    }

    /**
     * Return project choices filtered by a sport type.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectsbysportstypesoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getProjectsBySportsTypesOptions', [
            max(0, $input->getInt('sportstype')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return age-group choices filtered by sport type.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function agegroupsbysportstypesoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getAgeGroupsBySportsTypesOptions', [
            max(0, $input->getInt('sportstype')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return project choices associated with a selected club.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectsbycluboptions(): void
    {
        $input = $this->input;
        $this->respondModel('getProjectsByClubOptions', [
            max(0, $input->getInt('cid')),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * List season-team entries for the selected project and optional club.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectteamoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getProjectTeamOptions', [
            max(0, $input->getInt('p', 0)),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
            max(0, $input->getInt('club_id', 0)),
        ]);
    }

    /**
     * Return project-team options containing their relation identifiers.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectteamsptidoptions(): void
    {
        $this->respondProjectOption('getProjectTeamPtidOptions');
    }

    /**
     * List players belonging to the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectplayeroptions(): void
    {
        $this->respondProjectOption('getProjectPlayerOptions');
    }

    /**
     * List staff members belonging to the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectstaffoptions(): void
    {
        $this->respondProjectOption('getProjectStaffOptions');
    }

    /**
     * Return available clubs for the current project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectcluboptions(): void
    {
        $this->respondProjectOption('getProjectClubOptions');
    }

    /**
     * Return statistic choices for the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectstatsoptions(): void
    {
        $this->respondProjectOption('getProjectStatsOptions');
    }

    /** Backward-compatible singular task used by existing module XML files. */
    /**
     * Keep the legacy singular AJAX task for project statistics.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectstatoptions(): void
    {
        $this->respondProjectOption('getProjectStatsOptions');
    }

    /**
     * Return match choices belonging to the currently selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function matchesoptions(): void
    {
        $this->respondProjectOption('getMatchesOptions');
    }

    /**
     * Return referee choices associated with the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function refereesoptions(): void
    {
        $this->respondProjectOption('getRefereesOptions');
    }

    /**
     * Return the available rounds for the selected project in ascending order.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projectroundoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getProjectRoundOptions', [
            max(0, $input->getInt('p', 0)),
            $input->getBool('required'),
            $input->getBool('slug'),
            'ASC',
            null,
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Return bracket-tree nodes belonging to the selected project.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function projecttreenodeoptions(): void
    {
        $this->respondProjectOption('getProjectTreenodeOptions');
    }

    /**
     * List configured sport types for the selected SportsManagement database.
     *
     * Reads the relevant Joomla request filters, delegates the lookup to
     * the native administrator AJAX model and emits a JSON response.
     *
     * @return void
     */
    public function sportstypesoptions(): void
    {
        $input = $this->input;
        $this->respondModel('getsportstypes', [
            $input->getInt('cfg_which_database'),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    /**
     * Build a prediction-game query from database and option flags and send its JSON response.
     *
     * Reads nonnegative project or prediction identifiers and standard
     * required, slug and database flags from Joomla request input.
     *
     * @return void
     */
    private function respondPredictionId(): void
    {
        $input = $this->input;
        $this->respondModel('getPredictionId', [
            $input->getInt('cfg_which_database'),
            $input->getBool('required'),
            $input->getBool('slug'),
        ]);
    }

    /**
     * Build a common project-scoped option query and delegate it to the administrator AJAX model.
     *
     * Reads nonnegative project or prediction identifiers and standard
     * required, slug and database flags from Joomla request input.
     *
     * @param string $method Trusted model method used for this project selector.
     * @return void
     */
    private function respondProjectOption(string $method): void
    {
        $input = $this->input;
        $this->respondModel($method, [
            max(0, $input->getInt('p', 0)),
            $input->getBool('required'),
            $input->getBool('slug'),
            $input->getBool('dbase'),
        ]);
    }

    /**
     * Call an approved method of the administrator AJAX model and send a JSON response.
     *
     * Verifies the native model supports the requested method, substitutes
     * invalid UTF-8 during JSON encoding and closes the Joomla application.
     * Failed database calls return a generic JSON error without revealing
     * private exception messages to site visitors.
     *
     * @param string $method Requested administrator AJAX model method.
     * @param array<int,mixed> $arguments Validated selection and formatting arguments.
     * @return void
     */
    private function respondModel(string $method, array $arguments): void
    {
        $app = $this->app;

        try {
            $model = $this->getModel('Ajax', 'Administrator', ['ignore_request' => true]);

            if ($model === false || !is_callable([$model, $method])) {
                throw new \RuntimeException('Administrator AJAX model action is unavailable: ' . $method);
            }

            $result = $model->{$method}(...$arguments);

            // Legacy option labels may contain invalid UTF-8; always send valid JSON.
            $json = json_encode(
                $result,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
                | JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $error) {
            Log::add(__METHOD__ . ' (' . $method . ') failed (' . get_class($error) . ').', Log::ERROR, 'jsmerror');

            // Keep the AJAX response machine-readable without leaking database details.
            $json = json_encode(
                ['success' => false, 'message' => Text::_('JERROR_AN_ERROR_HAS_OCCURRED')],
                JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
            );
        }

        $app->getDocument()->setMimeEncoding('application/json');
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        $app->setHeader('Cache-Control', 'no-store, private', true);
        $app->setHeader('X-Content-Type-Options', 'nosniff', true);
        echo $json;
        $app->close();
    }
}
