<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       predictionresults.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage prediction
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementControllerPredictionResults
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerPredictionResults extends BaseController
{

    /**
         * sportsmanagementControllerPredictionResults::display()
         * 
         * @param  bool $cachable
         * @param  bool $urlparams
         * @return void
         */
    function display($cachable = false, $urlparams = false)
    {

        parent::display($cachable, $urlparams = false);
    }

    /**
     * sportsmanagementControllerPredictionResults::selectprojectround()
     * 
     * @return void
     */
    function selectprojectround()
    {
        JSession::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $pID = $jinput->getVar('prediction_id', '0');
        $pggroup = $jinput->getVar('pggroup', '0');
        $pggrouprank = $jinput->getVar('pggrouprank', '0');
        $pjID = $jinput->getVar('pj', '0');
        $rID = $jinput->getVar('r', '0');
        $set_pj = $jinput->getVar('set_pj', '0');
        $set_r = $jinput->getVar('set_r', '0');
        $cfg_which_database = $jinput->getVar('cfg_which_database', '0');

        $link = JSMPredictionHelperRoute::getPredictionResultsRoute($pID, $rID, $pjID, null, '', $pggroup, $cfg_which_database);
        $this->setRedirect($link);
    }

}
?>
