<?php
/**
 * Native Joomla 5/6 administrator cPanel view for SportsManagement.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\View\Cpanel;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\CpanelModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Native Joomla 5/6 administrator dashboard.
 *
 * The dashboard is intentionally read-only. Database maintenance and data
 * installation remain explicit actions in the database tools area.
 */
final class HtmlView extends BaseHtmlView
{
    public string $version = '';
    public int $countryCount = 0;
    public array $dashboardLinks = [];
    public $params;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof CpanelModel) {
            throw new \RuntimeException('Cpanel model could not be loaded.', 500);
        }

        $this->version = $model->getVersion();
        $this->countryCount = $model->checkcountry();
        $this->params = ComponentHelper::getParams('com_sportsmanagement');
        $this->dashboardLinks = $this->buildDashboardLinks();

        $this->addToolbar();
        $this->setLayout('native');

        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'home');

        if (Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }

    private function buildDashboardLinks(): array
    {
        return [
            [
                'title' => 'COM_SPORTSMANAGEMENT_MENU',
                'items' => [
                    $this->link('extensions', 'COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS', 'icon-puzzle'),
                    $this->link('projects', 'COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS', 'icon-list'),
                    $this->link('specialextensions', 'COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS', 'icon-puzzle'),
                    $this->link('predictiongames', 'COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS', 'icon-star'),
                    $this->link('currentseasons', 'COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS', 'icon-calendar'),
                    $this->link('jsmgcalendars', 'COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR', 'icon-calendar'),
                ],
            ],
            [
                'title' => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA',
                'items' => [
                    $this->link('sportstypes', 'COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES', 'icon-list'),
                    $this->link('seasons', 'COM_SPORTSMANAGEMENT_D_MENU_SEASONS', 'icon-calendar'),
                    $this->link('leagues', 'COM_SPORTSMANAGEMENT_D_MENU_LEAGUES', 'icon-list'),
                    $this->link('jlextfederations', 'COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS', 'icon-flag'),
                    $this->link('jlextcountries', 'COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES', 'icon-flag'),
                    $this->link('jlextassociations', 'COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS', 'icon-tree-2'),
                    $this->link('positions', 'COM_SPORTSMANAGEMENT_D_MENU_POSITIONS', 'icon-list'),
                    $this->link('eventtypes', 'COM_SPORTSMANAGEMENT_D_MENU_EVENTS', 'icon-list'),
                    $this->link('agegroups', 'COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS', 'icon-users'),
                ],
            ],
            [
                'title' => 'COM_SPORTSMANAGEMENT_D_HEADING_PERSONAL_DATA',
                'items' => [
                    $this->link('clubs', 'COM_SPORTSMANAGEMENT_D_MENU_CLUBS', 'icon-users'),
                    $this->link('teams', 'COM_SPORTSMANAGEMENT_D_MENU_TEAMS', 'icon-users'),
                    $this->link('jsmpersons', 'COM_SPORTSMANAGEMENT_D_MENU_PERSONS', 'icon-user'),
                    $this->link('playgrounds', 'COM_SPORTSMANAGEMENT_D_MENU_VENUES', 'icon-location'),
                    $this->link('rosterpositions', 'COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION', 'icon-list'),
                ],
            ],
            [
                'title' => 'COM_SPORTSMANAGEMENT_D_HEADING_SPECIAL_FUNCTION',
                'items' => [
                    $this->link('extrafields', 'COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS', 'icon-list'),
                    $this->link('statistics', 'COM_SPORTSMANAGEMENT_D_MENU_STATISTICS', 'icon-chart'),
                    $this->link('github', 'COM_SPORTSMANAGEMENT_D_MENU_GITHUB', 'icon-github'),
                ],
            ],
            [
                'title' => 'COM_SPORTSMANAGEMENT_D_HEADING_IMPORT_EXPORT_FUNCTION',
                'items' => [
                    $this->link('jlxmlimports', 'COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT', 'icon-upload', 'default'),
                    $this->link('smimageimports', 'COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT', 'icon-upload', 'default'),
                    $this->link('joomleagueimports', 'COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT', 'icon-upload', 'default'),
                ],
            ],
            [
                'title' => 'COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS',
                'items' => [
                    $this->link('updates', 'COM_SPORTSMANAGEMENT_D_MENU_UPDATES', 'icon-refresh'),
                    $this->link('databasetools', 'COM_SPORTSMANAGEMENT_D_MENU_TOOLS', 'icon-database'),
                    $this->link('smquotes', 'COM_SPORTSMANAGEMENT_D_MENU_QUOTES', 'icon-comment'),
                ],
            ],
        ];
    }

    private function link(string $view, string $label, string $icon, string $layout = ''): array
    {
        $url = 'index.php?option=com_sportsmanagement&view=' . rawurlencode($view);

        if ($layout !== '') {
            $url .= '&layout=' . rawurlencode($layout);
        }

        return [
            'url' => $url,
            'label' => $label,
            'icon' => $icon,
        ];
    }
}
