<?php
/**
 * Joomla 5/6 compatibility view for the obsolete singular JoomLeague import route.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Joomleagueimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

/**
 * Compatibility endpoint for historic view=joomleagueimport URLs.
 *
 * The former singular workflow referenced model methods which never existed in
 * the component (newstructur() and getImportPositions()). The maintained import
 * workflow is the plural view/controller pair. Redirect old bookmarks and
 * third-party links there instead of failing with a model-method exception.
 */
final class HtmlView extends BaseHtmlView
{
    public function display($tpl = null)
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $app->redirect(
            Route::_('index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default', false)
        );

        return null;
    }
}
