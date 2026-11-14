<?php
/**
 * Joomla 5/6 administrator component dispatcher with controlled legacy fallback.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class Dispatcher extends ComponentDispatcher
{
    private const NATIVE_CRUD_CONTROLLERS = ['eventtype', 'eventtypes', 'extrafield', 'extrafields'];
    private const NATIVE_EDIT_VIEWS = [
        'club', 'clubname', 'eventtype', 'extrafield',
        'jlextassociation', 'jlextcountry', 'jlextfederation',
        'league', 'match', 'player', 'playground', 'position', 'project',
        'rosterposition', 'round', 'season', 'smquote', 'sportstype', 'team',
    ];
    private const SAFE_STANDARD_CRUD_CONTROLLERS = [
        'club', 'clubname', 'clubnames',
        'jlextassociation', 'jlextcountry', 'jlextfederation',
        'league', 'player', 'playground', 'position', 'project', 'rosterposition',
        'round', 'season', 'seasons', 'smquote', 'sportstype', 'sportstypes', 'team',
    ];
    private const NATIVE_LIST_CONTROLLERS = [
        'jlextassociations', 'jlextcountries', 'jlextfederations',
        'leagues', 'matches', 'players', 'playgrounds', 'positions', 'projectteams',
        'rosterpositions', 'rounds', 'smquotes', 'teams',
    ];
    private const NATIVE_LIST_ACTIONS = ['publish', 'unpublish', 'archive', 'trash', 'checkin', 'saveorder', 'saveorderajax', 'reorder'];
    private const SAFE_STANDARD_CRUD_ACTIONS = ['add', 'edit', 'apply', 'save', 'save2new', 'save2copy', 'cancel', 'publish', 'unpublish', 'archive', 'trash', 'checkin', 'saveorder', 'saveorderajax', 'reorder'];
    private const NATIVE_SPECIAL_TASKS = [
        'leagues.saveshort', 'positions.saveshort', 'rosterpositions.addhome', 'rosterpositions.addaway', 'teams.saveshort', 'teams.copysave',
        'round.startpopulate', 'rounds.populate', 'rounds.massadd', 'rounds.saveshort', 'rounds.deleteroundmatches',
        'matches.saveshort', 'matches.count_result_yes', 'matches.count_result_no', 'matches.savereferees',
        'match.edit', 'match.apply', 'match.save', 'match.cancel', 'match.cancelmodal',
        'match.addmatch', 'match.copyfrom', 'match.remove', 'match.picture', 'match.insertgooglecalendar',
        'match.massadd', 'match.cancelmassadd',
        'players.saveshort', 'players.assign', 'players.importupload', 'players.close', 'players.delete', 'player.import',
        'teamplayers.saveshort', 'teamplayers.publish', 'teamplayers.unpublish', 'teamplayers.archive', 'teamplayers.trash',
        'teamplayers.delete', 'teamplayers.assignplayerscountry',
        'projectteam.storechangeteams',
        'projectteams.saveshort', 'projectteams.addteam', 'projectteams.assign', 'projectteams.matchgroups', 'projectteams.setseasonid',
        'projectteams.delete', 'projectteams.copy', 'projectteams.storecopy', 'projectteams.set_playground', 'projectteams.set_playground_match',
        'projectteams.use_table_yes', 'projectteams.use_table_no', 'projectteams.use_table_points_yes', 'projectteams.use_table_points_no',
        'smquote.import', 'smquote.export', 'smquotes.edittxt',
        'jlextassociation.export', 'jlextassociations.import',
        'jlextcountry.import', 'jlextcountry.export', 'jlextcountries.importplz',
        'jlextfederation.import', 'jlextfederation.export',
    ];
    private const LEGACY_DEFAULT_VIEWS = ['club', 'league', 'playground', 'position', 'rosterposition', 'team'];

    /**
     * Dispatch an administrator request through Joomla 5/6 or the legacy bridge.
     *
     * Enforces the administrator client and component management permission,
     * validates route selectors, then delegates migrated CRUD tasks and views
     * to the native MVC stack. Adds the control-panel back link on native list
     * pages; unported operations remain available through the legacy entry.
     *
     * @return void
     * @throws \RuntimeException If the client, permission or legacy entry is unavailable.
     * @throws \InvalidArgumentException If a route identifier is malformed.
     */
    public function dispatch()
    {
        if (!$this->app instanceof AdministratorApplication || !$this->app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement requires the Joomla administrator application.', 500);
        }

        $identity = $this->app->getIdentity();
        if (!$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorised to manage SportsManagement.', 403);
        }

        $task = $this->validatedIdentifier('task', 'display');
        $view = $this->validatedIdentifier('view', 'cpanel');
        $controller = $this->validatedIdentifier('controller', '');
        $layout = $this->validatedIdentifier('layout', 'default');
        $format = $this->validatedIdentifier('format', 'html');

        $modernDisplay = $this->isModernDisplayRequest($task, $view, $controller, $layout, $format);

        if ($this->isModernCrudTask($task, $format) || $this->isModernEditDisplay($task, $view, $controller, $layout, $format) || $modernDisplay) {
            $this->input->set('view', $view);
            parent::dispatch();

            if ($modernDisplay && $layout === 'default' && !in_array($view, ['cpanel', 'sportsmanagement'], true)) {
                ToolbarHelper::back(
                    'JSM Panel',
                    Route::_('index.php?option=com_sportsmanagement&view=cpanel', false)
                );
            }

            return;
        }

        $this->dispatchLegacy();
    }


    /**
     * Reject arrays and filtered request identifiers before MVC or legacy routing.
     *
     * Joomla's getCmd() silently removes invalid characters; accepting the
     * transformed value could select a different controller, view or layout.
     *
     * @param string $key Joomla input key to inspect.
     * @param string $default Default command identifier.
     * @return string Lowercase, validated command identifier.
     * @throws \InvalidArgumentException If the selector is not a valid string.
     */
    private function validatedIdentifier(string $key, string $default): string
    {
        $raw = $this->input->get($key, $default, 'raw');

        if (!is_string($raw)) {
            throw new \InvalidArgumentException('Invalid SportsManagement ' . $key . '.', 400);
        }

        $filtered = $this->input->getCmd($key, $default);

        if (strtolower($filtered) !== strtolower($raw)) {
            throw new \InvalidArgumentException('Invalid SportsManagement ' . $key . '.', 400);
        }

        return strtolower($filtered);
    }

    /**
     * Classify administrator write actions eligible for native controller routing.
     *
     * Accepts migrated specialist operations, dedicated CRUD controllers, or
     * explicitly permitted actions on standard and list controllers. Other
     * requests and non-HTML formats are left to the compatibility dispatcher.
     *
     * @param string $task Normalised controller.action identifier.
     * @param string $format Requested response format.
     * @return bool True only for supported native administrator actions.
     */
    private function isModernCrudTask(string $task, string $format): bool
    {
        if ($format !== 'html' || $task === 'display') return false;
        if (in_array($task, self::NATIVE_SPECIAL_TASKS, true)) return true;
        $controller = strtolower((string) strtok($task, '.'));
        if (in_array($controller, self::NATIVE_CRUD_CONTROLLERS, true)) return true;
        $action = strtolower((string) substr($task, strlen($controller) + 1));
        if (in_array($controller, self::SAFE_STANDARD_CRUD_CONTROLLERS, true)) return in_array($action, self::SAFE_STANDARD_CRUD_ACTIONS, true);
        if (in_array($controller, self::NATIVE_LIST_CONTROLLERS, true)) return in_array($action, self::NATIVE_LIST_ACTIONS, true);
        return false;
    }

    /**
     * Detect migrated administrator edit-form display requests.
     *
     * Restricts native edit rendering to the expected display controller,
     * the HTML format, the edit layout and a named allowlist of forms.
     *
     * @param string $task Normalised Joomla task.
     * @param string $view Requested edit view.
     * @param string $controller Explicit controller name, when present.
     * @param string $layout Requested layout identifier.
     * @param string $format Output format.
     * @return bool True when the request targets a migrated edit form.
     */
    private function isModernEditDisplay(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $layout !== 'edit' || $format !== 'html') return false;
        if ($controller !== '' && $controller !== 'display') return false;
        return in_array($view, self::NATIVE_EDIT_VIEWS, true);
    }

    /**
     * Decide whether Joomla's native dispatcher can render an administrator view.
     *
     * Applies view-specific layout allowlists for match, round, project and
     * player screens, excludes views deliberately handled by legacy controllers,
     * and requires either the installed compatibility view or a native view
     * class before enabling the MVC factory's migration bridge.
     *
     * @param string $task Normalised Joomla task.
     * @param string $view Requested administrator view.
     * @param string $controller Explicit controller selector.
     * @param string $layout Requested layout name.
     * @param string $format Output format.
     * @return bool True if the native/compatibility MVC stack can serve the view.
     */
    private function isModernDisplayRequest(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $format !== 'html') return false;
        if ($controller !== '' && $controller !== 'display') return false;

        if ($view === 'rounds') {
            $allowedLayouts = ['default', 'populate', 'massadd'];
        } elseif ($view === 'matches') {
            $allowedLayouts = ['default', 'massadd'];
        } elseif ($view === 'match') {
            $allowedLayouts = ['editreferees', 'editreferees_3', 'editreferees_4'];
        } elseif ($view === 'project') {
            $allowedLayouts = ['panel', 'panel_3', 'panel_4'];
        } elseif ($view === 'projectteams') {
            $allowedLayouts = ['default', 'editlist', 'editlist_3', 'editlist_4', 'changeteams', 'changeteams_3', 'changeteams_4', 'copy'];
        } elseif ($view === 'players') {
            $allowedLayouts = [
                'default',
                'assignpersons', 'assignpersons_3', 'assignpersons_4',
                'assignpersonsclub', 'assignpersonsclub_3', 'assignpersonsclub_4',
                'players_upload', 'assignconfirm',
            ];
        } else {
            $allowedLayouts = ['default'];
        }

        if (!in_array($layout, $allowedLayouts, true)) return false;
        if (in_array($view, self::LEGACY_DEFAULT_VIEWS, true)) return false;

        $legacyView = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/' . $view . '/view.html.php';
        $nativeView = 'Diddipoeler\\Component\\SportsManagement\\Administrator\\View\\' . ucfirst($view) . '\\HtmlView';

        return is_file($legacyView) || class_exists($nativeView);
    }

    /**
     * Invoke the retained administrator component entry point for legacy tasks.
     *
     * Uses Joomla's fixed administrator component directory and refuses to
     * include a missing file, keeping older management screens reachable while
     * individual controllers are migrated.
     *
     * @return void
     * @throws \RuntimeException If the legacy entry point is not installed.
     */
    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sportsmanagement.php';
        if (!is_file($legacyEntryPoint)) throw new \RuntimeException('SportsManagement legacy administrator entry point not found.', 500);
        require $legacyEntryPoint;
    }
}
