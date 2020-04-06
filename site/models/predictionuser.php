<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       predictionuser.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage predictionuser
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

// Include dependancy of the main model form
jimport('joomla.application.component.modelform');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');
// Include dependancy of the dispatcher
jimport('joomla.event.dispatcher');

/**
 * sportsmanagementModelPredictionUser
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionUser extends JModelForm
{
    var $predictionGameID = 0;
    var $predictionMemberID = 0;
    var $edit_modus = 0;
    var $cfg_which_database = 0;
    /**
     * sportsmanagementModelPredictionUser::__construct()
     * 
     * @return void
     */
    function __construct()
    {
          // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        $prediction = new sportsmanagementModelPrediction();  
         $this->edit_modus = $jinput->getInt('edit_modus', 0);
        sportsmanagementModelPrediction::$roundID = $jinput->getVar('r', '0');
          sportsmanagementModelPrediction::$pjID = $jinput->getVar('pj', '0');
          sportsmanagementModelPrediction::$from = $jinput->getVar('from', $jinput->getVar('r', '0'));
          sportsmanagementModelPrediction::$to = $jinput->getVar('to', $jinput->getVar('r', '0'));
          $this->cfg_which_database = $jinput->get('cfg_which_database', 0, '');
        sportsmanagementModelPrediction::$predictionGameID = $jinput->getVar('prediction_id', '0');
        
        sportsmanagementModelPrediction::$predictionMemberID = $jinput->getInt('uid', 0);
        sportsmanagementModelPrediction::$joomlaUserID = $jinput->getInt('juid', 0);
        
        sportsmanagementModelPrediction::$pggroup = $jinput->getInt('pggroup', 0);
        sportsmanagementModelPrediction::$pggrouprank = $jinput->getInt('pggrouprank', 0);
        
        sportsmanagementModelPrediction::$isNewMember = $jinput->getInt('s', 0);
        sportsmanagementModelPrediction::$tippEntryDone = $jinput->getInt('eok', 0);
        
        sportsmanagementModelPrediction::$type = $jinput->getInt('type', 0);
        sportsmanagementModelPrediction::$page = $jinput->getInt('page', 1);
       
        if ($this->edit_modus && !$jinput->getInt('uid', 0) ) {
            $user = Factory::getUser();
            sportsmanagementModelPrediction::$joomlaUserID = $user->id;
            $predictionMemberID = $this->getpredictionmemberid($user->id, $jinput->getVar('prediction_id', '0')); 
            $redirect = JSMPredictionHelperRoute::getPredictionMemberRoute((int)sportsmanagementModelPrediction::$predictionGameID, $predictionMemberID, 'edit', sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$pggroup, $roundID, $this->cfg_which_database);
            Factory::getApplication()->redirect($redirect);
        }
        parent::__construct();
    }


    function getpredictionmemberid($user_id=0,$prediction_id=0) 
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);  
        $query->select('pm.id');  
        $query->from('#__sportsmanagement_prediction_member AS pm');
        $query->where('pm.prediction_id = '.(int)$prediction_id);  
        $query->where('pm.user_id = '.$user_id);  
        $db->setQuery($query, 0, 1);  
        $result = $db->loadResult();  
 
        return $result;  
    }  
  
  
  
    /**
     * Method to get the record form.
     *
     * @param  array   $data     Data for the form.
     * @param  boolean $loadData True if the form is to load its own data (default case), false if not.
     * @return mixed    A JForm object on success, false on failure
     * @since  1.7
     */
    public function getForm($data = array(), $loadData = true)
    {
        $app = Factory::getApplication('site');
          // Get the form.
        $form = $this->loadForm(
            'com_sportsmanagement.'.$this->name, $this->name,
            array('load_data' => $loadData) 
        );
        if (empty($form)) {
            return false;
        }
        return $form;
    }
    

        
}
?>
