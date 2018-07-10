<?PHP        
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      model.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage libraries
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla library
jimport('joomla.application.component.modeladmin');
// import Joomla library
jimport('joomla.application.component.modellist');

/**
 * JSMModelAdmin
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelAdmin extends JModelAdmin
{
    

/**
 * JSMModelAdmin::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmview = $this->jsmjinput->getCmd('view');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 
        $this->jsmdate = JFactory::getDate();
	$this->jsmmessage = '';
	$this->jsmmessagetype = 'notice';

$this->project_id = $this->jsmjinput->getint('pid');
if ( !$this->project_id )
{
$post = $this->jsmjinput->post->getArray();
if ( isset($post['pid']) )
{
$this->project_id = $post['pid'];
}
if ( !$this->project_id )
{
$this->project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
}
}
$this->jsmjinput->set('pid', $this->project_id);
$this->jsmapp->setUserState( "$this->jsmoption.pid", $this->project_id );
	
/**
 * abfrage nach backend und frontend  
 */ 
if ( $this->jsmapp->isAdmin() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmoption<br><pre>'.print_r($this->jsmoption,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmview<br><pre>'.print_r($this->jsmview,true).'</pre>'),'Notice');    
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
}  
if( $this->jsmapp->isSite() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
}    
        }    

    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
       $post = $this->jsmjinput->post->getArray();
       $address_parts = array();
       $person_double = array();
       //$view = $this->jsmjinput->getCmd('view');
       //$view = $this->jsmjinput->get('view', '', 'CMD');
       
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmoption<br><pre>'.print_r($this->jsmoption,true).'</pre>'),'Notice');
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmview<br><pre>'.print_r($this->jsmview,true).'</pre>'),'Notice');
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view_item<br><pre>'.print_r($this->view_item,true).'</pre>'),'Notice');
//       $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' view<br><pre>'.print_r($view,true).'</pre>'),'Notice');

$input_options = JFilterInput::getInstance(
        array(
            'img','p','a','u','i','b','strong','span','div','ul','li','ol','h1','h2','h3','h4','h5',
            'table','tr','td','th','tbody','theader','tfooter','br'
        ),
        array(
            'src','width','height','alt','style','href','rel','target','align','valign','border','cellpading',
            'cellspacing','title','id','class'
        )
    );

    $postData = new JInput($this->jsmjinput->get('jform', '', 'array'), array('filter' => $input_options));		
		
if (array_key_exists('notes', $data)) 
{    
$html = $postData->get('notes','','raw');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' postData <br><pre>'.print_r($postData ,true).'</pre>'),'Notice');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' html <br><pre>'.print_r($html ,true).'</pre>'),'Notice');
$data['notes'] = $html;
//$html = $postData->get('notes','','raw');
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' html <br><pre>'.print_r($html ,true).'</pre>'),'Notice');
}
		
		
		
		
if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') )
{
$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
}

if( version_compare(JSM_JVERSION,'4','eq') ) 
{
$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'Notice');    
}

    
///**
// * differenzierung zwischen den views
// */       
//       switch ( $this->jsmview )
//       {
//       case 'league': 
//       $data['sports_type_id'] = $data['request']['sports_type_id'];
//       $data['agegroup_id'] = $data['request']['agegroup_id'];
//       break; 
//       default:
//       break; 
//       }
       
       //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        if (isset($post['extendeduser']) && is_array($post['extendeduser'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string)$parameter;
		}
       
        // Set the values
	   $data['modified'] = $this->jsmdate->toSql();
	   $data['modified_by'] = $this->jsmuser->get('id');
       $data['checked_out'] = 0;
       $data['checked_out_time'] = $this->jsmdb->getNullDate();

/**
 * differenzierung zwischen den views
 */       
       switch ( $this->jsmview )
       {
/**
 * gruppen 
 */        
       case 'division': 
       if ( !$data['id'] )
       {
       //$data['project_id'] = $post['pid'];
       $data['project_id'] = $this->project_id;       
       }
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['rankingparams'] = (string)$parameter;
		}

       break;
/**
 * runde 
 */        
       case 'round':
       if ( $data['round_date_first'] != '00-00-0000' && $data['round_date_first'] != '' )
       {
       $data['round_date_first'] = sportsmanagementHelper::convertDate($data['round_date_first'],0);
       }
       if ( $data['round_date_last'] != '00-00-0000' && $data['round_date_last'] != '' )
       {
       $data['round_date_last'] = sportsmanagementHelper::convertDate($data['round_date_last'],0);
       }
       break;
/**
 * runden 
 */       
       case 'rounds':
       $data['round_date_first'] = sportsmanagementHelper::convertDate($data['round_date_first'],0);
       $data['round_date_last']	= sportsmanagementHelper::convertDate($data['round_date_last'],0);
       if ( !isset($data['id']) )
       {
        $data['id'] = 0;
        $data['project_id'] = $post['pid'];
        $data['roundcode'] = $post['next_roundcode'];
       }
       break;
/**
 * projektteam 
 */        
       case 'projectteam':
       if ( $post['delete'] )
        {
            $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
            $mdlTeam->DeleteTrainigData($post['delete'][0]);
        }
        
        if ( $post['add_trainingData'] )
        {
            $row = JTable::getInstance( 'seasonteam', 'sportsmanagementTable' );
            $row->load( (int)$post['jform']['team_id'] );
            $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
            $mdlTeam->addNewTrainigData($row->team_id);
        }
        
        if ( $post['tdids'] )
        {
        $mdlTeam = JModelLegacy::getInstance("Team", "sportsmanagementModel");
        $mdlTeam->UpdateTrainigData($post);
        }
        
/**
 * das mannschaftsfoto wird zusätzlich abgespeichert,
 * damit man die historischen kader sieht        
 */
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = (int)$post['jform']['team_id'];
        $object->picture = $post['jform']['picture'];
        $object->modified = $this->jsmdate->toSql();
	    $object->modified_by = $this->jsmuser->get('id');
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_id', $object, 'id'); 

       break;  
/**
 * liga 
 */       
       case 'league': 
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];
       break; 
/**
 * person 
 */       
       case 'person': 
       $data['person_art'] = $data['request']['person_art'];
       $data['person_id1'] = $data['request']['person_id1'];
       $data['person_id2'] = $data['request']['person_id2'];
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['position_id'] = $data['request']['position_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];

       
       switch($data['person_art'])
        {
            case 1:
            break;
            case 2:
            if ( $data['person_id1'] && $data['person_id2'] )
            {
            $person_1 = $data['person_id1'];
            $person_2 = $data['person_id2'];
            $table = 'person';
            $row = JTable::getInstance( $table, 'sportsmanagementTable' );
            $row->load((int) $person_1);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $row->load((int) $person_2);
            $person_double[] = $row->firstname.' '.$row->lastname;
            $data['lastname'] = implode(" - ",$person_double);
            $data['firstname'] = '';
            }
            break;
            
        }
        
/**
 * hat der user die bildfelder geleert, werden die standards gesichert.
 */
       if ( empty($data['picture']) )
       {
	switch($data['gender'])
        {
	case 0:
       $data['picture'] = JComponentHelper::getParams($this->jsmoption)->get('ph_player','');
	break;	
		case 1:
       $data['picture'] = JComponentHelper::getParams($this->jsmoption)->get('ph_player_men_small','');
	break;
			case 2:
       $data['picture'] = JComponentHelper::getParams($this->jsmoption)->get('ph_player_woman_small','');
	break;
	}
       }
       $data['birthday'] = sportsmanagementHelper::convertDate($data['birthday'],0);
       $data['deathday'] = sportsmanagementHelper::convertDate($data['deathday'],0);
       $data['injury_date_start'] = sportsmanagementHelper::convertDate($data['injury_date_start'],0);
       $data['injury_date_end'] = sportsmanagementHelper::convertDate($data['injury_date_end'],0);
       $data['susp_date_start'] = sportsmanagementHelper::convertDate($data['susp_date_start'],0);
       $data['susp_date_end'] = sportsmanagementHelper::convertDate($data['susp_date_end'],0);
       $data['away_date_start'] = sportsmanagementHelper::convertDate($data['away_date_start'],0);
       $data['away_date_end'] = sportsmanagementHelper::convertDate($data['away_date_end'],0);
       break;
/**
 * template 
 */       
       case 'template': 
       if (isset($post['params']['colors_ranking']) && is_array($post['params']['colors_ranking'])) 
	   {
	   $colors = array();
       foreach ( $post['params']['colors_ranking'] as $key => $value )
       {
       if ( !empty($value['von']) )
       {
       $colors[] = implode(",",$value);
       }
       }
       $post['params']['colors'] = implode(";",$colors); 
       }       
       break; 
/**
 * verein 
 */       
       case 'club':
/**
 * gibt es vereinsnamen zum ändern ?
 */
       if (isset($post['team_id']) && is_array($post['team_id'])) 
       {
        foreach ( $post['team_id'] as $key => $value )
        {
        $team_id = $post['team_id'][$key];  
        $team_name = $post['team_value_id'][$key];  
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->id = $team_id;
        $object->name = $team_name;
        $object->alias = JFilterOutput::stringURLSafe( $team_name );
        // Update their details in the table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__sportsmanagement_team', $object, 'id');
        }
        
       }
       
/**
 * hat der user die bildfelder geleert, werden die standards gesichert.
 */
       if ( empty($data['logo_big']) )
       {
       $data['logo_big'] = JComponentHelper::getParams($option)->get('ph_logo_big','');
       }
       if ( empty($data['logo_middle']) )
       {
       $data['logo_middle'] = JComponentHelper::getParams($option)->get('ph_logo_medium','');
       }
       if ( empty($data['logo_small']) )
       {
       $data['logo_small'] = JComponentHelper::getParams($option)->get('ph_logo_small','');
       }
 
/**
 * wurden jahre mitgegeben ?
 */
       if ( $data['founded'] != '0000-00-00' && $data['founded'] != '' )
       {
       $data['founded']	= sportsmanagementHelper::convertDate($data['founded'],0);
       }
       if ( $data['dissolved'] != '0000-00-00' && $data['dissolved'] != ''  )
       {
       $data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'],0);
       }
       
       if ( $data['founded'] == '0000-00-00' || $data['founded'] == '' )
        {
        $data['founded'] = '0000-00-00';   
        }
       if ( $data['founded'] != '0000-00-00' && $data['founded'] != ''  )
        {
        $data['founded_year'] = date('Y',strtotime($data['founded']));
        $data['founded_timestamp'] = sportsmanagementHelper::getTimestamp($data['founded']);
        }
        else
        {
        $data['founded_year'] = $data['founded_year'];
        }
        
        if ( $data['dissolved'] == '0000-00-00' || $data['dissolved'] == '' )
        {
        $data['dissolved'] = '0000-00-00';   
        }
        
        if ( $data['dissolved'] != '0000-00-00' && $data['dissolved'] != '' )  
        {
        $data['dissolved_year'] = date('Y',strtotime($data['dissolved']));
        $data['dissolved_timestamp'] = sportsmanagementHelper::getTimestamp($data['dissolved']);
        }
        else
        {
        $data['dissolved_year'] = $data['dissolved_year'];
        }
       break;
/**
 * mannschaft 
 */       
       case 'team':
       if ( $post['delete'] )
        {
            sportsmanagementModelteam::DeleteTrainigData($post['delete'][0]);
        }
        
        if ( $post['tdids'] )
        {
            sportsmanagementModelteam::UpdateTrainigData($post);
        }
        
        if ( $post['add_trainingData'] )
        {
            sportsmanagementModelteam::addNewTrainigData($data[id]);
        }
       break;  
/**
 * projekt 
 */        
       case 'project':
       $data['start_date']	= sportsmanagementHelper::convertDate($data['start_date'],0);
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];
       //$data['fav_team'] = implode(',',$post['jform']['fav_team']);
       $data['modified_timestamp'] = sportsmanagementHelper::getTimestamp($data['modified']);
       if ( !$post['jform']['fav_team'] )
       {
       $data['fav_team'] = '';
       }
       else
       {
       $data['fav_team'] = implode(',',$post['jform']['fav_team']);
       }


       break; 
       default:
       break; 
       }
       
       if (isset($post['params']) && is_array($post['params'])) 
		{
			// Convert the params field to a string.
			//$parameter = new JRegistry;
			//$parameter->loadArray($post['params']);
            $paramsString = json_encode( $post['params'] );
			//$data['params'] = (string)$parameter;
            $data['params'] = $paramsString;
		}
               
/**
 * Alter the title for Save as Copy
 */ 
 		if ($this->jsmjinput->get('task') == 'save2copy') 
 		{ 
 			$orig_table = $this->getTable(); 
 			$orig_table->load((int) $this->jsmjinput->getInt('id')); 
            $data['id'] = 0; 
/**
 * differenzierung zwischen den views
 */       
            switch ( $this->jsmview )
            {
/**
 * template 
 */                
            case 'template': 
            $data['project_id'] = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
            $data['title'] = $post['title'];      
            $data['template'] = $post['template'];
            break;
/**
 * projekt 
 */              
            case 'project':
            $data['current_round'] = 0;
	        $project_old = (int) $this->jsmjinput->getInt('id');		    
            break;  
            default:
            break; 
            }
            
 			if ($data['name'] == $orig_table->name) 
 			{ 
 				$data['name'] .= ' ' . JText::_('JGLOBAL_COPY'); 
 				$data['alias'] = JFilterOutput::stringURLSafe( $data['name'] ); 
 			} 
 		} 

/**
 * zuerst sichern, damit wir bei einer neuanlage die id haben
 */         
       if ( parent::save($data) )
       {
	$id =  (int) $this->getState($this->getName().'.id');
            $isNew = $this->getState($this->getName() . '.new');
            $data['id'] = $id;
            $this->jsmapp->setUserState( "$this->jsmoption.club_id", $id );
	    $this->jsmapp->setUserState( "$this->jsmoption.person_id", $id );   
            $this->jsmjinput->set('insert_id', $id);
	    $this->jsmjinput->set('person_id', $id);   
            if ( $isNew )
            {
/**
 * Here you can do other tasks with your newly saved record...
 */                
                $this->jsmapp->enqueueMessage(JText::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id),'');

if ($this->jsmjinput->get('task') == 'save2copy') 
 		{
/**
 * differenzierung zwischen den views
 */       
            switch ( $this->jsmview )
            {
            case 'project':
$this->jsmquery->clear(); 
$this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_division');
$this->jsmquery->where('project_id ='. $project_old );
$this->jsmdb->setQuery($this->jsmquery);
$result = $this->jsmdb->loadObjectList();
foreach($result as $field )
{
// Create and populate an object.
$profile = new stdClass();
$profile->project_id = $id;
$profile->name = $field->name;
$profile->alias = $field->alias;
$profile->shortname = $field->shortname;
$profile->published = $field->published;
$profile->ordering = $field->ordering;
// Insert the object into the user profile table.
$insertresult = $this->jsmdb->insertObject('#__sportsmanagement_division', $profile);


}

            break;  
            default:
            break; 
            }
		}		    
	    
	    
	    }

/**
 * differenzierung zwischen den views
 */       
		switch ( $this->jsmview )
		{
/**
 * person 
 */		  
		case 'person':
        if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		$message = '';  
		foreach( $data['season_ids'] as $key => $value )
        {
        $this->jsmquery->clear();  
        $this->jsmquery->select('spi.id,s.name');
        $this->jsmquery->from('#__sportsmanagement_season_person_id as spi');
        $this->jsmquery->join('INNER', '#__sportsmanagement_season AS s ON s.id = spi.season_id ');
        $this->jsmquery->where('spi.person_id ='. $data['id'] );
        $this->jsmquery->where('spi.season_id ='. $value );
        $this->jsmdb->setQuery($this->jsmquery);
		$res = $this->jsmdb->loadObject();
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Error');
        if ( !$res )
        {
        $this->jsmquery->clear();
        // Insert columns.
        $columns = array('person_id','season_id','modified','modified_by');
        // Insert values.
        $values = array($data['id'],$value,$this->jsmdb->Quote(''.$data['modified'].''),$data['modified_by']);
        // Prepare the insert query.
        $this->jsmquery
            ->insert($this->jsmdb->quoteName('#__sportsmanagement_season_person_id'))
            ->columns($this->jsmdb->quoteName($columns))
            ->values(implode(',', $values));
        $this->jsmdb->setQuery($this->jsmquery);
        
try{
sportsmanagementModeldatabasetool::runJoomlaQuery();
}
catch (Exception $e) {
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($e,true).'</pre>'),'');    
}

//		if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
//		{
//        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($this->jsmdb->getErrorMsg(),true).'</pre>'),'Error');
//		}  
        $message .= 'Saisonzuordnung : '.$res->name.' angelegt.<br>';
        }
        else
        {
        $message .= 'Saisonzuordnung : '.$res->name.' schon vorhanden.<br>';    
        }
          
        }
        $this->jsmapp->enqueueMessage($message, 'message');

		}
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
        
        break;
/**
 * position 
 */        
        case 'position':
        /**
         * ereignisse der positionen speichern
         */
		if (isset($post['position_eventslist']) && is_array($post['position_eventslist']))
		{
		if ( $data['id'] )
		{
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info') )
        {  
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' position_eventslist <br><pre>'.print_r($post['position_eventslist'],true).'</pre>'),'Notice');
        }
		$mdl = JModelLegacy::getInstance("positioneventtype", "sportsmanagementModel");
		$mdl->store($post,$data['id']);
		}
		}

		/**
		 * statistiken der positionen speichern
		 */
        if (isset($post['position_statistic']) && is_array($post['position_statistic'])) 
		{
		if ( $data['id'] )
		{
		if ( JComponentHelper::getParams($this->jsmoption)->get('show_debug_info') )
        {  
		$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' position_statistic <br><pre>'.print_r($post['position_statistic'],true).'</pre>'),'Notice');
        }
		$mdl = JModelLegacy::getInstance("positionstatistic", "sportsmanagementModel");
		$mdl->store($post,$data['id']);
		}
		}
		break;
/**
 * verein 
 */        
		case 'club':
		sportsmanagementHelper::saveExtraFields($post,$data['id']);
		$this->jsmapp->setUserState( "$this->jsmoption.club_id", $data['id'] );		
		break;
/**
 * projekt 
 */        
		case 'project':
		sportsmanagementHelper::saveExtraFields($post,$data['id']);
		break;
/**
 * mannschaft 
 */        
		case 'team':
		if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		foreach( $data['season_ids'] as $key => $value )
		{
		$this->jsmquery->clear();
		$this->jsmquery->select('id');
		$this->jsmquery->from('#__sportsmanagement_season_team_id');
		$this->jsmquery->where('team_id ='. $data['id'] );
		$this->jsmquery->where('season_id ='. $value );
		//JFactory::getDbo()->setQuery($query);
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();

		if ( !$result )
		{
		$this->jsmquery->clear();
        // Insert columns.
		$columns = array('team_id','season_id');
		// Insert values.
		$values = array($data['id'],$value);
		// Prepare the insert query.
		$this->jsmquery
			->insert($this->jsmdb->quoteName('#__sportsmanagement_season_team_id'))
			->columns($this->jsmdb->quoteName($columns))
			->values(implode(',', $values));
		// Set the query using our newly populated query object and execute it.
		$this->jsmdb->setQuery($this->jsmquery);

		if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
//            $this->app->enqueueMessage(JText::_('sportsmanagementModelteam save<br><pre>'.print_r(JFactory::getDbo()->getErrorMsg(),true).'</pre>'),'Error');
		}
          
        }
		//$mdl = JModelLegacy::getInstance("seasonteam", "sportsmanagementModel");
		}
        }
		sportsmanagementHelper::saveExtraFields($post,$data['id']);
        $this->jsmapp->setUserState( "$this->jsmoption.team_id", $data['id'] );	
		break;
		default:
		break;
		}
     
		return true;
		}
		else
		{
		return false;
		}
	}
    
    /**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		$cfg_which_media_tool = JComponentHelper::getParams($this->jsmoption)->get('cfg_which_media_tool',0);
        $show_team_community = JComponentHelper::getParams($this->jsmoption)->get('show_team_community',0);
        $cfg_use_plz_table = JComponentHelper::getParams($this->jsmoption)->get('cfg_use_plz_table',0);
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.'.$this->getName(), $this->getName(), array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $prefix = $this->jsmapp->getCfg('dbprefix');
        
        switch ($this->getName())
		{
		case 'position':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
         $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_position' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
            
			$result = $this->jsmdb->loadObjectList();

            
            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        break;
        case 'statistic':
        $form->setFieldAttribute('icon', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/statistics');
        $form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);
         $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_statistic' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
            
			$result = $this->jsmdb->loadObjectList();

            
            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        break;
        case 'projectreferee':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/projectreferees');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'division':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/divisions');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'teamperson':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teamplayers');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'smquote':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'jlextfederation':
         $form->setFieldAttribute('assocflag', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_flags',''));
        $form->setFieldAttribute('assocflag', 'directory', 'com_sportsmanagement/database/flags_associations');
        $form->setFieldAttribute('assocflag', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/associations');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_federations' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
            
			$result = $this->jsmdb->loadObjectList();

            
            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        break;
        case 'jlextcountry':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_flags',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/flags');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_countries' ");
			try {
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();
            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
            }
catch (Exception $e) {
//    // catch any database errors.
//    $db->transactionRollback();
//    JErrorPage::render($e);
}

            
        break;
        case 'jlextassociation':
        $form->setFieldAttribute('assocflag', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_flags',''));
        $form->setFieldAttribute('assocflag', 'directory', 'com_sportsmanagement/database/flags_associations');
        $form->setFieldAttribute('assocflag', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/associations');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
       
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_jlextassociation' ");
			
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadObjectList();

            foreach($result as $field )
        {


            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }

           } 
        break;
        case 'eventtype':
         $form->setFieldAttribute('icon', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/events');
        $form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);

       $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_eventtype' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
 
			$result = $this->jsmdb->loadObjectList();
            
            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        break;  
		case 'round':
	/*
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('round_date_first', 'type', 'calendar');
        $form->setFieldAttribute('round_date_last', 'type', 'calendar');       
        }
        else
        {
        $form->setFieldAttribute('round_date_first', 'type', 'customcalendar');
        $form->setFieldAttribute('round_date_last', 'type', 'customcalendar');
        }
	*/
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/rounds');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;  
		case 'project':
        $sports_type_id = $form->getValue('sports_type_id');
        $this->jsmquery->clear();
        // select some fields
		$this->jsmquery->select('name');
		// from table
		$this->jsmquery->from('#__sportsmanagement_sports_type');
        // where
        $this->jsmquery->where('id = '.(int) $sports_type_id);
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();
        
        switch ($result)
        {
            case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
            break;
            default:
            $form->setFieldAttribute('use_tie_break', 'type', 'hidden');
            $form->setFieldAttribute('tennis_single_matches', 'type', 'hidden');
            $form->setFieldAttribute('tennis_double_matches', 'type', 'hidden');
            break;
        }
        
        switch ( JComponentHelper::getParams($this->jsmoption)->get('which_article_component') )
        {
        case 'com_content':
        $form->setFieldAttribute('category_id', 'type', 'category');
        $form->setFieldAttribute('category_id', 'extension', 'com_content');
        break;
        case 'com_k2':
        $form->setFieldAttribute('category_id', 'type', 'categorylistk2');

        break;
        }
        /*
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('start_date', 'type', 'calendar'); 
        }
        else
        {
        $form->setFieldAttribute('start_date', 'type', 'customcalendar');  
        }
        */
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/projects');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        
        break;
        case 'projectteam':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/projectteams');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_home', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        $form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/projectteams/trikot_home');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        $form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/projectteams/trikot_away');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
        break;
        case 'club':
        $row = $this->getTable();
        $row->load((int) $form->getValue('id'));
        $country = $row->country;
        
        /**
         * soll die postleitzahlendatentabelle genutzt werden ?
         */        
        if ( $cfg_use_plz_table )
        {
        /**
        * wenn es aber zu dem land keine einträge
        * in der plz tabelle gibt, dann die normale
        * eingabe dem user anbieten        
        */
        $this->jsmquery->clear();
        $this->jsmquery->select('count(*) as anzahl');
        $this->jsmquery->from('#__sportsmanagement_countries_plz as a');
        $this->jsmquery->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        $this->jsmquery->where('c.alpha3 LIKE ' . $this->jsmdb->Quote(''.$country.'') );
        $this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadResult();

        if ( $result )
        {    
        $form->setFieldAttribute('zipcode', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('zipcode', 'size', '10', 'request');     
        $form->setFieldAttribute('location', 'type', 'dependsql', 'request');
        $form->setFieldAttribute('location', 'size', '10', 'request');
        }
        
        }

        if ( !$show_team_community )
        {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }
        /*
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('founded', 'type', 'calendar');
        $form->setFieldAttribute('dissolved', 'type', 'calendar');  
        }
        else
        {
        $form->setFieldAttribute('founded', 'type', 'customcalendar');  
        $form->setFieldAttribute('dissolved', 'type', 'customcalendar');
        }
        */
        $form->setFieldAttribute('logo_small', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        $form->setFieldAttribute('logo_small', 'directory', 'com_sportsmanagement/database/clubs/small');
        $form->setFieldAttribute('logo_small', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_middle', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_medium',''));
        $form->setFieldAttribute('logo_middle', 'directory', 'com_sportsmanagement/database/clubs/medium');
        $form->setFieldAttribute('logo_middle', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('logo_big', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
        $form->setFieldAttribute('logo_big', 'directory', 'com_sportsmanagement/database/clubs/large');
        $form->setFieldAttribute('logo_big', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_home', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot_home');
        $form->setFieldAttribute('trikot_home', 'directory', 'com_sportsmanagement/database/clubs/trikot');
        $form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);
        
        $form->setFieldAttribute('trikot_away', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
        //$form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot_away');
        $form->setFieldAttribute('trikot_away', 'directory', 'com_sportsmanagement/database/clubs/trikot');
        $form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_club' ");
			
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadObjectList();

            foreach($result as $field )
        {

            switch ($field->COLUMN_NAME)
            {
                case 'country':
                case 'merge_teams':
                break;
                default:
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
                break;
            }
           } 
        break;
        case 'team':
        if ( !$show_team_community )
        {
            $form->setFieldAttribute('merge_clubs', 'type', 'hidden');
        }
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/teams');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
		$this->jsmquery->from('information_schema.columns');
        $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_team' ");
		$this->jsmdb->setQuery($this->jsmquery);
        $result = $this->jsmdb->loadObjectList();
        
        foreach($result as $field )
        {
            switch ($field->COLUMN_NAME)
            {
                case 'country':
                case 'merge_clubs':
                break;
                default:
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            break;
            }
           } 
       
        break;
        case 'sportstype':
        $form->setFieldAttribute('icon', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('icon', 'directory', 'com_sportsmanagement/database/sport_types');
        $form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);
        
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_sports_type' ");
			
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadObjectList();

            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        
        break;
        
        case 'playground':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/playgrounds');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_playground' ");
			
			$this->jsmdb->setQuery($this->jsmquery);

			$result = $this->jsmdb->loadObjectList();

            foreach($result as $field )
        {

            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        break;
        case 'agegroup':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/agegroups');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        break;
        case 'league':
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/leagues');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);  
        break;
        case 'person':
                switch($form->getValue('person_art'))
        {
            case 1:
//            $form->setFieldAttribute('person_id1', 'type', 'hidden');
//            $form->setFieldAttribute('person_id2', 'type', 'hidden');            
            break;
            case 2:
//            $form->setFieldAttribute('person_id1', 'type', 'personlist');
//            $form->setFieldAttribute('person_id2', 'type', 'personlist');
            break;
            
        }
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        /*
        // welche joomla version ?
        if(version_compare(JVERSION,'3.0.0','ge')) 
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contact');
        $form->setFieldAttribute('birthday', 'type', 'calendar');
        $form->setFieldAttribute('deathday', 'type', 'calendar'); 
        }
        else
        {
        $form->setFieldAttribute('contact_id', 'type', 'modal_contacts');  
        $form->setFieldAttribute('birthday', 'type', 'customcalendar');
        $form->setFieldAttribute('deathday', 'type', 'customcalendar');  
        }
        */
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
			$this->jsmquery->from('information_schema.columns');
            $this->jsmquery->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_person' ");
			
			$this->jsmdb->setQuery($this->jsmquery);
            
			$result = $this->jsmdb->loadObjectList();
            
            foreach($result as $field )
        {
            
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            
           } 
        
        break;
        }
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.'.$this->getName().'.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

    /**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = '', $prefix = 'sportsmanagementTable', $config = array()) 
	{
       $config['dbo'] = sportsmanagementHelper::getDBConnection();
       if ( empty($type) )
       {
       $type = $this->getName();
       } 
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{

        $row = $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
                $row->modified = $this->jsmdate->toSql();
                $row->modified_by = $this->jsmuser->get('id');
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return JText::_('JGLOBAL_SAVE_SORT_NO');
				}
			}
		}
		return JText::_('JGLOBAL_SAVE_SORT_YES');
	}
                
}

/**
 * JSMModelList
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelList extends JModelList
{
    
/**
 * JSMModelList::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 
	$this->jsmpks = $this->jsmjinput->get('cid',array(),'array');
        $this->jsmpost = $this->jsmjinput->post->getArray(array());  
	$this->jsmmessage = '';
	$this->jsmmessagetype = 'notice';

/**
 * abfrage nach backend und frontend  
 */ 
if ( $this->jsmapp->isAdmin() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
}  
if( $this->jsmapp->isSite() )
{
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
}    
        }    
    
}


/**
 * JSMModelLegacy
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class JSMModelLegacy extends JModelLegacy
{
    
/**
 * JSMModelLegacy::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
 
        parent::__construct($config);
        $getDBConnection = sportsmanagementHelper::getDBConnection();
        parent::setDbo($getDBConnection);
        $this->jsmdb = sportsmanagementHelper::getDBConnection();
        parent::setDbo($this->jsmdb);
        $this->jsmquery = $this->jsmdb->getQuery(true);
        $this->jsmsubquery1 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery2 = $this->jsmdb->getQuery(true); 
        $this->jsmsubquery3 = $this->jsmdb->getQuery(true);  
        // Reference global application object
        $this->jsmapp = JFactory::getApplication();
        // JInput object
        $this->jsmjinput = $this->jsmapp->input;
        $this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdocument = JFactory::getDocument();
        $this->jsmuser = JFactory::getUser(); 
	$this->jsmpks = $this->jsmjinput->get('cid',array(),'array');
        $this->jsmpost = $this->jsmjinput->post->getArray(array()); 
	$this->jsmmessage = '';
	$this->jsmmessagetype = 'notice';
        
/**
 * abfrage nach backend und frontend  
 */        
        if ( $this->jsmapp->isAdmin() )
        {
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isAdmin<br><pre>'.print_r($this->jsmapp->isAdmin(),true).'</pre>'),'');    
        }  
        if( $this->jsmapp->isSite() )
        {
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' isSite<br><pre>'.print_r($this->jsmapp->isSite(),true).'</pre>'),'');    
        } 
        
        }    
    
}


?>    
