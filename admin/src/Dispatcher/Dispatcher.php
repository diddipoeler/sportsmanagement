<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class Dispatcher extends ComponentDispatcher
{
    private const NATIVE_CRUD_CONTROLLERS = ['eventtype', 'eventtypes', 'extrafield', 'extrafields'];
    private const NATIVE_EDIT_VIEWS = ['club', 'clubname', 'eventtype', 'extrafield', 'league', 'match', 'player', 'playground', 'position', 'project', 'rosterposition', 'round', 'season', 'sportstype', 'team'];
    private const SAFE_STANDARD_CRUD_CONTROLLERS = ['club', 'clubname', 'clubnames', 'league', 'match', 'player', 'playground', 'position', 'project', 'rosterposition', 'round', 'season', 'seasons', 'sportstype', 'sportstypes', 'team'];
    private const NATIVE_LIST_CONTROLLERS = ['leagues', 'matches', 'players', 'playgrounds', 'positions', 'projectteams', 'rosterpositions', 'rounds', 'teams'];
    private const NATIVE_LIST_ACTIONS = ['publish', 'unpublish', 'archive', 'trash', 'checkin', 'saveorder', 'saveorderajax', 'reorder'];
    private const SAFE_STANDARD_CRUD_ACTIONS = ['add', 'edit', 'apply', 'save', 'save2new', 'save2copy', 'cancel', 'publish', 'unpublish', 'archive', 'trash', 'checkin', 'saveorder', 'saveorderajax', 'reorder'];
    private const NATIVE_SPECIAL_TASKS = [
        'leagues.saveshort', 'positions.saveshort', 'rosterpositions.addhome', 'rosterpositions.addaway', 'teams.saveshort', 'teams.copysave',
        'round.startpopulate', 'rounds.populate', 'rounds.massadd', 'rounds.saveshort', 'rounds.deleteroundmatches',
        'matches.saveshort', 'matches.count_result_yes', 'matches.count_result_no',
        'match.addmatch', 'match.copyfrom', 'match.remove', 'match.picture', 'match.insertgooglecalendar',
        'match.massadd', 'match.cancelmassadd', 'match.cancelmodal',
        'players.saveshort', 'players.assign', 'players.importupload', 'players.close', 'players.delete', 'player.import',
        'teamplayers.saveshort', 'teamplayers.publish', 'teamplayers.unpublish', 'teamplayers.archive', 'teamplayers.trash',
        'teamplayers.delete', 'teamplayers.assignplayerscountry',
        'projectteam.storechangeteams',
        'projectteams.saveshort', 'projectteams.addteam', 'projectteams.assign', 'projectteams.matchgroups', 'projectteams.setseasonid',
        'projectteams.delete', 'projectteams.copy', 'projectteams.storecopy', 'projectteams.set_playground', 'projectteams.set_playground_match',
        'projectteams.use_table_yes', 'projectteams.use_table_no', 'projectteams.use_table_points_yes', 'projectteams.use_table_points_no',
    ];
    private const LEGACY_DEFAULT_VIEWS = ['club', 'league', 'playground', 'position', 'rosterposition', 'team'];

    public function dispatch()
    {
        $identity = Factory::getApplication()->getIdentity();
        if (!$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorised to manage SportsManagement.', 403);
        }

        $task = strtolower($this->input->getCmd('task', 'display'));
        $view = strtolower($this->input->getCmd('view', 'cpanel'));
        $controller = strtolower($this->input->getCmd('controller', ''));
        $layout = strtolower($this->input->getCmd('layout', 'default'));
        $format = strtolower($this->input->getCmd('format', 'html'));

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

    private function isModernEditDisplay(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $layout !== 'edit' || $format !== 'html') return false;
        if ($controller !== '' && $controller !== 'display') return false;
        return in_array($view, self::NATIVE_EDIT_VIEWS, true);
    }

    private function isModernDisplayRequest(string $task, string $view, string $controller, string $layout, string $format): bool
    {
        if ($task !== 'display' || $format !== 'html') return false;
        if ($controller !== '' && $controller !== 'display') return false;

        if ($view === 'rounds') {
            $allowedLayouts = ['default', 'populate', 'massadd'];
        } elseif ($view === 'matches') {
            $allowedLayouts = ['default', 'massadd'];
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

    private function dispatchLegacy(): void
    {
        $legacyEntryPoint = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/sportsmanagement.php';
        if (!is_file($legacyEntryPoint)) throw new \RuntimeException('SportsManagement legacy administrator entry point not found.', 500);
        require $legacyEntryPoint;
    }
}
