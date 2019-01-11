<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      joomleagueimports.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage controllers
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
 
/**
 * sportsmanagementControllerjoomleagueimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementControllerjoomleagueimports extends JSMControllerAdmin
{

/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
        //$this->jsmdb = sportsmanagementHelper::getDBConnection();
        // Reference global application object
        $this->jsmapp = Factory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        //$this->jsmoption = $this->jsmjinput->getCmd('option');
//        $this->jsmdocument = Factory::getDocument();
//        $this->jsmuser = Factory::getUser();
//        $this->jsmdate = Factory::getDate();
////        $this->option = $this->jsmjinput->getCmd('option');
//        $this->club_id = $this->jsmapp->getUserState( "$this->option.club_id", '0' );
        
//        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($config,true).'</pre>'),'');
        
		// Map the apply task to the save method.
		//$this->registerTask('apply', 'save');
	}


/**
 * sportsmanagementControllerjoomleagueimports::joomleaguesetagegroup()
 * 
 * @return void
 */
function joomleaguesetagegroup()
{
$model = $this->getModel();    
$result = $model->joomleaguesetagegroup();   
$type = ''; 
$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_SETAGEGROUP');    
$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&jl_table_import_step=0&layout=infofield', false),$msg,$type);    
}


/**
 * sportsmanagementControllerjoomleagueimports::importjoomleaguenew()
 * 
 * @return void
 */
function importjoomleaguenew()
{
        //$app = Factory::getApplication();
        //$option = Factory::getApplication()->input->getCmd('option');
        
        //$jl_table_import_step = $app->getUserState( "$this->option.jl_table_import_step", 0 );
        $jl_table_import_step = $this->jsmjinput->get('jl_table_import_step',0);
        $sports_type_id = $this->jsmjinput->get('filter_sports_type', 0);
        
//        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' jl_table_import_step <br><pre>'.print_r($jl_table_import_step,true).'</pre>'),'');
//        $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' sports_type_id <br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
        
        if ( $jl_table_import_step != 'ENDE' )
        {
        $model = $this->getModel();
        $result = $model->importjoomleaguenew($jl_table_import_step,$sports_type_id);
        $jl_table_import_step = $this->jsmjinput->get('jl_table_import_step',0);
        //$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' ' .  ' model result <br><pre>'.print_r($result,true).'</pre>'),'');
        //$result = $model->importjoomleaguenewtest($jl_table_import_step);
        $this->jsmapp->setUserState( $this->option.".jl_table_import_success", $result );
        //sleep(3);
        //$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&task=joomleagueimports.importjoomleaguenew&layout=default&jl_table_import_step='.$jl_table_import_step, false));
        
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&layout=default&jl_table_import_step='.$jl_table_import_step.'&filter_sports_type='.$sports_type_id, false));
        
        }
        else
        {
//        $model::$_success = $result;
        //$this->jsmapp->setUserState( "$this->option.jl_table_import_step", 0 );
        $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&jl_table_import_step=0&layout=infofield', false));
        }

}


/**
 * sportsmanagementControllerjoomleagueimports::importjoomleagueagegroup()
 * 
 * @return void
 */
function importjoomleagueagegroup()
{
    
    $this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.'&layout=infofield', false));
    
}
  

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'joomleagueimports', $prefix = 'sportsmanagementModel', $config = Array() ) 
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}

?>