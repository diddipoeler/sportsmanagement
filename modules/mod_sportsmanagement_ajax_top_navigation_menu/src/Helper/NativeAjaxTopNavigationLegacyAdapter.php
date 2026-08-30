<?php
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Bridge the native module dispatcher to the legacy query/link implementation
 * without resolving the Joomla application or database through global Factory calls.
 */
final class NativeAjaxTopNavigationLegacyAdapter extends \modSportsmanagementAjaxTopNavigationMenuHelper
{
    public function __construct(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $database
    ) {
        $this->_params = $params;
        $this->_app = $app;

        $input = $app->getInput();
        $selector = $input->getInt(
            'cfg_which_database',
            (int) $params->get(
                'cfg_which_database',
                (int) ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_database', 0)
            )
        );
        $this->_db = SportsManagementDatabaseResolver::resolve($database, $selector);

        if (self::$_project_id) {
            $input->set('jlamtopseason', $this->getSeasonId());
            $input->set('jlamtopleague', $this->getLeagueId());
            $input->set('jlamtopproject', self::$_project_id);
            $input->set('jlamtopteam', $this->_team_id);
            $input->set('jlamtopdivisionid', $this->_division_id);
        }
    }
}
