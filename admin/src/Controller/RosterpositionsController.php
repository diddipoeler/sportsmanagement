<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;
\defined('_JEXEC') or die;
use Joomla\CMS\Router\Route;
final class RosterpositionsController extends SportsManagementAdminController
{
    public function addhome(): void { $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=rosterposition&addposition=HOME_POS&layout=edit', false)); }
    public function addaway(): void { $this->setRedirect(Route::_('index.php?option=com_sportsmanagement&view=rosterposition&addposition=AWAY_POS&layout=edit', false)); }
    public function getModel($name='Rosterposition',$prefix='Administrator',$config=[]) { return parent::getModel($name,$prefix,['ignore_request'=>true]); }
}
