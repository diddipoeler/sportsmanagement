<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       predictionrules.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @subpackage predictionrules
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;



/**
 * sportsmanagementModelPredictionRules
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionRules extends BaseDatabaseModel
{
    /**
     * sportsmanagementModelPredictionRules::__construct()
     *
     * @return
     */
    function __construct()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
     
        $prediction = new sportsmanagementModelPrediction();
     
          sportsmanagementModelPrediction::$roundID = $jinput->getVar('r', '0');
          sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj', '0');
          sportsmanagementModelPrediction::$from = $jinput->getVar('from', $jinput->getVar('r', '0'));
          sportsmanagementModelPrediction::$to = $jinput->getVar('to', $jinput->getVar('r', '0'));
      
          sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id', '0');
     
        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid', 0);
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid', 0);
      
        sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup', 0);
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank', 0);
      
        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s', 0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok', 0);
      
        sportsmanagementModelPrediction::$type = $jinput->getInt('type', 0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page', 1);
      
        parent::__construct();
    }

}
?>
