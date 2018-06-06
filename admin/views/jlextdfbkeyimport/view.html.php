<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage jlextdfbkeyimport
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementViewjlextdfbkeyimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementViewjlextdfbkeyimport extends sportsmanagementView {

    /**
     * sportsmanagementViewjlextdfbkeyimport::init()
     * 
     * @return
     */
    public function init() {
        $tpl = '';
        $this->division_id = $this->jinput->get('divisionid');
        if ( $this->getLayout() == 'default' || $this->getLayout() == 'default_3' ) {
            $this->setLayout('default');
            $this->_displayDefault($tpl);
            return;
        }

        if ( $this->getLayout() == 'default_createdays' || $this->getLayout() == 'default_createdays_3' ) {
            $this->setLayout('default_createdays');
            $this->_displayDefaultCreatedays($tpl);
            return;
        }


        if ( $this->getLayout() == 'default_firstmatchday' || $this->getLayout() == 'default_firstmatchday_3' ) {
            $this->setLayout('default_firstmatchday');
            $this->_displayDefaultFirstMatchday($tpl);
            return;
        }

        if ( $this->getLayout() == 'default_savematchdays' || $this->getLayout() == 'default_savematchdays_3' ) {
            $this->setLayout('default_savematchdays');
            $this->_displayDefaultSaveMatchdays($tpl);
            return;
        }
     
     if ( $this->getLayout() == 'default_getdivision' || $this->getLayout() == 'default_getdivision_3' ) {
            $this->setLayout('default_getdivision');
            $this->_displayDefaultGetDivision($tpl);
            return;
        }
     
    }

 /**
  * sportsmanagementViewjlextdfbkeyimport::_displayDefaultGetDivision()
  * 
  * @param mixed $tpl
  * @return void
  */
 function _displayDefaultGetDivision($tpl) {

$mdl_divisions = JModelLegacy::getInstance("Divisions", "sportsmanagementModel");  
$projectdivisions = $mdl_divisions->getDivisions($this->project_id);  
$this->division = 0;
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' projectdivisions <pre>'.print_r($projectdivisions,true).'</pre>', 'warning');
$divisionsList[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		
		if ($projectdivisions)
		{ 
			$projectdivisions = array_merge($divisionsList,$projectdivisions);
		}
		
		$lists['divisions'] = $projectdivisions;
        $this->lists = $lists;
JToolBarHelper::back('JPREV','index.php?option='.$this->option.'&view=projects');  
JToolbarHelper::save('jlextdfbkeyimport.getdivisionfirst', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_USE_DIVISION');

 }
 
    /**
     * sportsmanagementViewjlextdfbkeyimport::_displayDefault()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayDefault($tpl) {
        //$app = JFactory::getApplication();
//		$jinput = $app->input;
//		$option = $jinput->getCmd('option');

//        $db = sportsmanagementHelper::getDBConnection();
//		$uri 	= JFactory::getURI();
//		$user 	= JFactory::getUser();
//		$model	= $this->getModel();
        //get the project
//		$projectid = $model->getProject();
//		$this->assignRef( 'projectid',		$projectid );
/*
if (empty($this->project_id)) {
        $this->project_id = $this->app->getUserState("$this->option.pid", '0');
}
*/	    
$this->division_id = $this->jinput->get('divisionid');
$project_type = $this->model->getProjectType($this->project_id);    
//JError::raiseWarning(500, JText::_($project_type));
$this->app->enqueueMessage($project_type, 'notice');
if ( $project_type == 'DIVISIONS_LEAGUE' )
{
if ( !$this->division_id )
{
$this->app->redirect('index.php?option=' . $this->option . '&view=jlextdfbkeyimport&layout=default_getdivision'); 
}
}
        $istable = $this->model->checkTable();

        if (empty($this->project_id)) {
            JError::raiseWarning(500, JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_1'));
            $this->app->redirect('index.php?option=' . $this->option . '&view=projects');
        } else {
            // project selected. projectteams available ?
            //build the html options for projectteams
            if ( $res = $this->model->getProjectteams($this->project_id,$this->division_id) ) {
                $projectteams[] = JHtml::_('select.option', '0', '- ' . JText::_('Select projectteams') . ' -');
                $projectteams = array_merge($projectteams, $res);
                $lists['projectteams'] = $projectteams;


                $dfbteams = count($projectteams) - 1;

                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($dfbteams,true).'</pre>'),'');

                if ($resdfbkey = $this->model->getDFBKey($dfbteams, 'FIRST')) {
                    $dfbday = array();
                    $dfbday = array_merge($dfbday, $resdfbkey);
                    $lists['dfbday'] = $dfbday;
                    unset($dfbday);

                    // matchdays available ?
                    if ( $resmatchdays = $this->model->getMatchdays($this->project_id) ) {

                        // matches available
                        if ( $resmatches = $this->model->getMatches($this->project_id,$this->division_id) ) {
                            JError::raiseNotice(500, JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_2'));
                            JError::raiseWarning(500, JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_7', $resmatches));
                            $this->app->redirect('index.php?option=' . $this->option . '&view=rounds');
                        } else {
//        JError::raiseWarning( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3' ) );
//        JError::raiseNotice( 500, JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4' ) );
                            $this->app->redirect('index.php?option=' . $this->option . '&view=jlextdfbkeyimport&layout=default_firstmatchday&divisionid='.$this->division_id);
                        }
                    } else {
                        JError::raiseWarning(500, JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_3'));
                        JError::raiseNotice(500, JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_4'));
                        $this->app->redirect('index.php?option=' . $this->option . '&view=jlextdfbkeyimport&layout=default_createdays&divisionid='.$this->division_id);
                    }
                } else {
                    $procountry = $this->model->getCountry($this->project_id);
                    //JError::raiseWarning( 500, JText::_( '[DFB-Key Tool] Error: No DFB-Key for '.$dfbteams.'  Teams available!' ) );
                    JError::raiseWarning(500, JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_6', $dfbteams, JSMCountries::getCountryFlag($procountry), $procountry));
                    $this->app->redirect('index.php?option=' . $this->option . '&view=projects');
                }

                unset($projectteams);
            } else {
//    JError::raiseNotice( 500, JText::_( '[DFB-Key Tool] Notice: No Teams assigned!' ) );
                JError::raiseError(500, JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_ERROR_5'));
                $this->app->redirect('index.php?option=' . $this->option . '&view=projectteams');
            }
        }
    }

    /**
     * sportsmanagementViewjlextdfbkeyimport::_displayDefaultCreatedays()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayDefaultCreatedays($tpl) {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        //$option = $jinput->getCmd('option');

        //$db = sportsmanagementHelper::getDBConnection();

//        if (version_compare(JSM_JVERSION, '4', 'eq')) {
//            $uri = JUri::getInstance();
//        } else {
//            $uri = JFactory::getURI();
//        }
        //$user = JFactory::getUser();
        $model = $this->getModel();
        //$projectid =& $this->projectid;
        //get the project
        //echo '_displayDefaultCreatedays project -> '.$projectid.'<br>';

//        $projectid = $app->getUserState("$this->option.pid", '0');
//        $this->projectid = $projectid;
        
        $this->division_id = $this->jinput->get('divisionid');
JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' division_id <pre>'.print_r($this->division_id,true).'</pre>', 'warning');
JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' project_id <pre>'.print_r($this->project_id,true).'</pre>', 'warning');
	    
        if ( $res = $model->getProjectteams($this->project_id,$this->division_id) ) {
            $projectteams[] = JHtml::_('select.option', '0', '- ' . JText::_('Select projectteams') . ' -');
            $projectteams = array_merge($projectteams, $res);
            //$lists['projectteams'] = $projectteams;


            $dfbteams = count($projectteams) - 1;
            if ($resdfbkey = $model->getDFBKey($dfbteams, 'ALL')) {
                /*
                  echo '<pre>';
                  print_r($resdfbkey);
                  echo '</pre>';
                 */
                $this->newmatchdays = $resdfbkey;
            }
            unset($projectteams);
        }

        //$this->request_url = $uri->toString();

        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/'.$this->option.'/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_MATCHDAY_INFO_1'), 'dfbkey');
        JToolBarHelper::back('JPREV','index.php?option='.$this->option.'&view=projects'); 
        JToolbarHelper::save('jlextdfbkeyimport.save', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_ROUNDS');
        JToolbarHelper::divider();
//        sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences($this->option);
    }

    /**
     * sportsmanagementViewjlextdfbkeyimport::_displayDefaultFirstMatchday()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayDefaultFirstMatchday($tpl) {
        $app = JFactory::getApplication();
        $jinput = $app->input;
        //$option = $jinput->getCmd('option');

        //$db = sportsmanagementHelper::getDBConnection();
        //$uri = JFactory::getURI();
        //$user = JFactory::getUser();
        $model = $this->getModel();

        //$projectid = $app->getUserState("$this->option.pid", '0');
        
        $this->division_id = $this->jinput->get('divisionid');

        if ($res = $model->getProjectteams($this->project_id,$this->division_id)) {
            $projectteams[] = JHtml::_('select.option', '0', '- ' . JText::_('Select projectteams') . ' -');
            $projectteams = array_merge($projectteams, $res);
            $lists['projectteams'] = $projectteams;


            $dfbteams = count($projectteams) - 1;
            if ($resdfbkey = $model->getDFBKey($dfbteams, 'FIRST')) {
                $dfbday = array();
                $dfbday = array_merge($dfbday, $resdfbkey);
                $lists['dfbday'] = $dfbday;
                unset($dfbday);
            }
        }

        $this->lists = $lists;
        $this->dfbteams = $dfbteams;
        //$this->request_url = $uri->toString();

        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/'.$this->option.'/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_FIRST_MATCHDAY_INFO_1'), 'dfbkey');
        JToolBarHelper::back('JPREV','index.php?option='.$this->option.'&view=projects');
        JToolbarHelper::apply('jlextdfbkeyimport.apply', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_FIRST_DAY');
        JToolbarHelper::divider();
//        sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences($this->option);
    }

    /**
     * sportsmanagementViewjlextdfbkeyimport::_displayDefaultSaveMatchdays()
     * 
     * @param mixed $tpl
     * @return void
     */
    function _displayDefaultSaveMatchdays($tpl) {
        //$app = JFactory::getApplication();
        //$jinput = $app->input;
        //$option = $jinput->getCmd('option');

        //$db = sportsmanagementHelper::getDBConnection();
        //$uri = JFactory::getURI();
        //$user = JFactory::getUser();
        $model = $this->getModel();


        //$post = $this->jinput->post->getArray(array());
        //JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' post <pre>'.print_r($post,true).'</pre>', 'warning');

// retrieve the value of the state variable. First see if the variable has been passed
// in the request. Otherwise retrieve the stored value. If none of these are specified,
// the specified default value will be returned
// function syntax is getUserStateFromRequest( $key, $request, $default );
//$post = $this->app->getUserStateFromRequest( "$this->option.first_post", 'first_post', '' );
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' post <pre>'.print_r($post,true).'</pre>', 'warning');

// retrieve the value of the state variable. If no value is specified,
// the specified default value will be returned.
// function syntax is getUserState( $key, $default );
$post = $this->app->getUserState( "$this->option.first_post", '' );
//JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' post <pre>'.print_r($post,true).'</pre>', 'warning');

        
        //$this->projectid = $this->app->getUserState("$this->option.pid", '0');
        //$this->projectid = $projectid;
        //$post = $input->post;
        $this->division_id = $this->jinput->get('divisionid');
        $this->import = $model->getSchedule($post, $this->project_id,$this->division_id);
        //$this->request_url = $uri->toString();

        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="' . JURI::root() . 'administrator/components/'.$this->option.'/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        // Set toolbar items for the page
        JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_SAVE_MATCHDAY_INFO_1'), 'dfbkey');
        JToolBarHelper::back('JPREV','index.php?option='.$this->option.'&view=projects');
        JToolbarHelper::save('jlextdfbkeyimport.insert', 'COM_SPORTSMANAGEMENT_ADMIN_DFBKEYS_INSERT_MATCHDAYS');
        JToolbarHelper::divider();
//        sportsmanagementHelper::ToolbarButtonOnlineHelp();
//        JToolbarHelper::preferences($this->option);
    }

}

?>
