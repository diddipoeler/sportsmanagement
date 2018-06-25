<?php
/** SportsManagement ein Programm zur Verwaltung f�r Sportarten
 * @version   1.0.05
 * @file      ajax.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );


/**
 * sportsmanagementModelAjax
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelAjax extends JModelLegacy
{
        /**
         * sportsmanagementModelAjax::addGlobalSelectElement()
         * 
         * @param mixed $elements
         * @param bool $required
         * @return
         */
        public static function addGlobalSelectElement($elements, $required=false) 
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $mitems = '';
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' elements<br><pre>'.print_r($elements,true).'</pre>'),'Notice');
        
                if(!$required) 
                {
                $mitems = array(JHTML::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mitems<br><pre>'.print_r($mitems,true).'</pre>'),'Notice');
                
                return array_merge($mitems, $elements);
                //return $elements;
                }
                else
                {
                $mitems = array(JHTML::_('select.option', '0', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
                
                //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' mitems<br><pre>'.print_r($mitems,true).'</pre>'),'Notice');
                
                if ( $elements )
                {
                return array_merge($mitems, $elements);
                //return $elements;
                }
                else
                {
                return $elements;    
                }
                
                }
                //return $elements;
        }
        
        
/**
 * sportsmanagementModelAjax::getpredictionid()
 * 
 * @param bool $dabse
 * @param bool $required
 * @param bool $slug
 * @return
 */
static function getPredictionId($dabse = false, $required = false, $slug = false)
        {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        //$required = 0;
        
        // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', id, alias) AS value,name AS text');
        }
        else
        {
        $query->select('id AS value,name AS text');
        }
        // From 
		$query->from('#__sportsmanagement_prediction_game');
        $query->where('published = 1');
        $query->order('name DESC'); 
        
        $db->setQuery($query);
                //return $db->loadObjectList();
                return self::addGlobalSelectElement($db->loadObjectList(), $required);    
            
            
        }
        
 /**
  * sportsmanagementModelAjax::getPredictionProjects()
  * 
  * @param integer $prediction_id
  * @param bool $required
  * @param bool $slug
  * @param bool $dabse
  * @return
  */
 public static function getPredictionPj($prediction_id = 0, $required = false, $slug = false, $dabse = false)
        {
	   $app = JFactory::getApplication();
	         $option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }

        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        }
        else
        {
        $query->select('p.id AS value,p.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project as p');
        $query->join('INNER',' #__sportsmanagement_prediction_project AS prpro ON p.id = prpro.project_id ');
        // Where
        $query->where('prpro.prediction_id = ' . (int)$prediction_id );
        $query->where('prpro.published = 1');
        // order
        $query->order('p.name');
        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
         

/**
 * sportsmanagementModelAjax::getPredictionGroups()
 * 
 * @param integer $prediction_id
 * @param bool $required
 * @param bool $slug
 * @param bool $dabse
 * @return
 */
public static function getPredictionGroups($prediction_id = 0, $required = false, $slug = false, $dabse = false)
        {
	   $app = JFactory::getApplication();
	   $option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }

        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        }
        else
        {
        $query->select('p.id AS value,p.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_prediction_groups as p');
        // Where
        $query->where('p.published = 1');    
        // order
        $query->order('p.name');
        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }         
                        
        /**
         * sportsmanagementModelAjax::getpersoncontactid()
         * 
         * @param integer $show_user_profile
         * @param bool $required
         * @return
         */
        static function getpersoncontactid($show_user_profile=0, $required = false)
        {
        $db = sportsmanagementHelper::getDBConnection();   
        $query = $db->getQuery(true); 
        switch ($show_user_profile)
        {
            case 0:
            
            break;
            case 1:
            $query = $db->getQuery(true);
            // Select some fields
            $query->select('a.id AS value,concat(a.name, \' - \',a.username) AS text');
            // From 
		    $query->from('#__users AS a');
            $query->order('a.name');
            $db->setQuery( $query );
            return self::addGlobalSelectElement($db->loadObjectList(), $required);  
            break;
            case 2;
            $query = $db->getQuery(true);
            // Select some fields
            $query->select('a.id AS value,concat(a.firstname, \' - \',a.lastname) AS text');
            // From 
		    $query->from('#__comprofiler AS a');
            $query->order('a.lastname');
            $db->setQuery( $query );
            return self::addGlobalSelectElement($db->loadObjectList(), $required);  
            break;
        }    
          
        }
        
        
        /**
         * sportsmanagementModelAjax::getcountryclubagegroupoptions()
         * 
         * @param integer $club_id
         * @param bool $required
         * @param bool $slug
         * @param bool $dbase
         * @return
         */
        static function getcountryclubagegroupoptions($club_id = 0, $required = false, $slug = false,$dbase = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sports_type_id<br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
       
       // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        //$query->select('CONCAT_WS(\':\', a.id, a.alias) AS value,concat(a.name, \' - \',a.country) AS text');
        $query->select('a.id AS value,concat(a.name, \' - \',a.country) AS text');
        // From 
		$query->from('#__sportsmanagement_agegroup AS a');
        
        // Where
        if ( $club_id )
        {
        $query->join('INNER',' #__sportsmanagement_club AS c ON c.country = a.country ');
        $query->where('c.id = ' . $club_id );
        // order
        $query->order('a.name');
        $db->setQuery( $query );
        return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        else
        {
        $temp = new stdClass();
        $temp->value = 0;
        //$temp->text = ''; 
        $temp->text = JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_CLUB');
        $export[] = $temp;
        // COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB
        // COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CLUBS_LEGEND
        // COM_SPORTSMANAGEMENT_GLOBAL_SELECT_CLUB   
        return self::addGlobalSelectElement($export, $required);    
        }
            
            
        }
        
        /**
         * sportsmanagementModelAjax::getassociationsoptions()
         * 
         * @param mixed $country
         * @param bool $required
         * @param bool $slug
         * @param bool $dabse
         * @return
         */
        static function getassociationsoptions($country = NULL, $required = false, $slug = false,$dabse = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $db = JFactory::getDbo();
		$query = $db->getQuery(true);
        
        
			
			$query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country LIKE " . $db->Quote(''.$country.'') );
			//$query->where('t.parent_id = 0');
			$query->order('t.name');
			$db->setQuery($query);
			//$options = $db->loadObjectList();
			
			$sections = $db->loadObjectList();
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' country<br><pre>'.print_r($country,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sections<br><pre>'.print_r($sections,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice'); 
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jinput<br><pre>'.print_r($jinput,true).'</pre>'),'Notice');
            
            return self::addGlobalSelectElement($sections, $required);     
            
        }
        
        /**
         * sportsmanagementModelAjax::getseasons()
         * 
         * @param bool $dabse
         * @param bool $required
         * @return
         */
        static function getseasons($dabse = false, $required = false, $slug = false)
        {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        //$required = 0;
        
        // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', id, alias) AS value,name AS text');
        }
        else
        {
        $query->select('id AS value,name AS text');
        }
        // From 
		$query->from('#__sportsmanagement_season');
        $query->order('name DESC'); 
        
        $db->setQuery($query);
                //return $db->loadObjectList();
                return self::addGlobalSelectElement($db->loadObjectList(), $required);    
            
            
        }
        
        /**
         * sportsmanagementModelAjax::getlocationzipcodeoptions()
         * 
         * @param mixed $zipcode
         * @param bool $required
         * @param bool $slug
         * @param bool $dabse
         * @param integer $project_id
         * @return
         */
        static function getlocationzipcodeoptions($zipcode, $required = false, $slug = false, $dabse = false, $country = NULL)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        
        $result = array();

        // Get a db connection.
        if ( $dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,FALSE);
        }
        $query = $db->getQuery(true);
        
        if ( $zipcode || $country )
        {
        $query->select('a.place_name AS value, concat(a.place_name, \' ( \',a.country_code,\' ) ( \',a.postal_code,\' ) \',a.admin_name1) AS text');
        $query->from('#__sportsmanagement_countries_plz as a');
        $query->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        }
        if ( $zipcode )
        {
        $query->where('a.postal_code LIKE ' . $db->Quote(''.$zipcode.'') );
        $query->order('a.postal_code'); 
        }
        if ( $country )
        {
        $query->where('c.alpha3 LIKE ' . $db->Quote(''.$country.'') );
        $query->order('a.place_name'); 
        }
        
        //$query->order('a.postal_code');    
        if ( $zipcode || $country )
        {            
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $result = $db->loadObjectList();
        }
            
        return self::addGlobalSelectElement($result, $required); 
            
        }
        
        /**
         * sportsmanagementModelAjax::getcountryzipcodeoptions()
         * 
         * @param mixed $country
         * @param bool $required
         * @param bool $slug
         * @param bool $dabse
         * @param integer $project_id
         * @return
         */
        static function getcountryzipcodeoptions($country, $required = false, $slug = false, $dabse = false, $project_id = 0)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        
        $result = array();

        // Get a db connection.
        if ( $dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,FALSE);
        }
        $query = $db->getQuery(true);
        
        $query->select('a.postal_code AS value, concat(a.postal_code, \' ( \',a.country_code,\' )  \',a.admin_name1) AS text');
        $query->from('#__sportsmanagement_countries_plz as a');
        $query->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code'); 
        $query->where('c.alpha3 LIKE ' . $db->Quote(''.$country.'') );
        $query->group('a.postal_code'); 
        $query->order('a.postal_code');    
                    
        $db->setQuery($query);
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
        $result = $db->loadObjectList();
            
        return self::addGlobalSelectElement($result, $required);    
        }
        
        
        
        /**
         * sportsmanagementModelAjax::getProjectRoundOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getProjectRoundOptions($project_id, $required = false, $slug = false, $ordering = 'ASC' , $round_ids = NULL,  $dabse = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
       
       // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        
       $query = $db->getQuery(true);
       if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', id, alias) AS value,name AS text');
        }
        else
        {
        $query->select('id AS value,name AS text');    
        }
        $query->select('id, name, round_date_first, round_date_last, roundcode');
        
        $query->from('#__sportsmanagement_round');
        if ( $project_id )
        {
        $query->where('project_id = '.(int) $project_id);
        }
        else
        {
            $query->where('project_id = 0');
        }
        $query->where('published = 1');
        
        if ( $round_ids )
    {
    $query->where('id IN (' . implode(',', $round_ids) . ')');   
    }
        
        $query->order('roundcode '.$ordering);  
       $db->setQuery($query);    
            
        $result = $db->loadObjectList();
        
//        foreach ($result as $row)
//        {
//            $row->name = JText::_($row->name);
//        }
        
        return self::addGlobalSelectElement($result, $required);
       }
       
        /**
         * sportsmanagementModelAjax::getpersonpositionoptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getpersonpositionoptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
$option = $app->input->getCmd('option');
        // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        $query->select('pos.id AS value, pos.name AS text');
			$query->from('#__sportsmanagement_position as pos');
            
            if ( $sports_type_id )
            {
			$query->where('pos.sports_type_id = '.$sports_type_id);
            }
            
            $query->order('pos.name');  
			$db->setQuery($query);    
            
        $result = $db->loadObjectList();
        
        foreach ($result as $row)
        {
            $row->text = JText::_($row->text);
        }
        
        return self::addGlobalSelectElement($result, $required);
        }
        
        /**
         * sportsmanagementModelAjax::getpersonagegroupoptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        public static function getpersonagegroupoptions($sports_type_id=0, $required = false, $slug = false, $dabse = false, $project_id = 0, $country = '' )
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        
       $result = array();

        // Get a db connection.
        if ( $dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,FALSE);
        }
        $query = $db->getQuery(true);
        
         $query->select('a.id AS value, concat(a.country, \'-\',a.name, \' von: \',a.age_from,\' bis: \',a.age_to,\' Stichtag: \',a.deadline_day) AS text');
			$query->from('#__sportsmanagement_agegroup as a');
            
            if ( $project_id )
            {
            $query->join('LEFT', '#__sportsmanagement_league AS l ON l.country = a.country'); 
            $query->join('LEFT', '#__sportsmanagement_project AS p ON p.league_id = l.id');    
            $query->where('p.id = '.$project_id);
            }
            
            if ( $sports_type_id )
            {
            $query->where('a.sportstype_id = '.$sports_type_id);
            }
            
            if ( $country )
            {
            $query->where('a.country LIKE ' . $db->Quote(''.$country.'') );    
                
            }
            
			$query->order('a.name');    
                    
        $db->setQuery($query);
        
        $result = $db->loadObjectList();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');
        
//        foreach ($result as $row)
//        {
//            $row->name = JText::_($row->name);
//        }
           
        return self::addGlobalSelectElement($result, $required);
        }
        
        
        /**
         * sportsmanagementModelAjax::getpredictionmembersoptions()
         * 
         * @param mixed $prgame_id
         * @param bool $required
         * @param bool $slug
         * @param bool $dbase
         * @return
         */
        static function getpredictionmembersoptions($prgame_id, $required = false, $slug = false, $dbase = false)
        {
        // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
        $db = sportsmanagementHelper::getDBConnection(); 
		$query = $db->getQuery(true);
        
        $query->select('a.user_id AS value, concat(u.name, \' ( \',u.username,\' ) \') AS text');
		$query->from('#__sportsmanagement_prediction_member as a');
        $query->join('LEFT', '#__users AS u ON u.id = a.user_id');   
        $query->where('a.prediction_id = '.$prgame_id);
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return self::addGlobalSelectElement($result, $required);    
        }
        
        /**
         * sportsmanagementModelAjax::getpersonlistoptions()
         * 
         * @param mixed $person_art
         * @param bool $required
         * @return
         */
        static function getpersonlistoptions($person_art, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        if ( $person_art == 2 )
        {
        $query->select("id AS value, concat(lastname,' - ',firstname,'' ) AS text");
			$query->from('#__sportsmanagement_person ');
			$query->order('lastname');
			$db->setQuery($query);    
            $result = $db->loadObjectList();
        }
        
        return self::addGlobalSelectElement($result, $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectsBySportsTypesOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        function getProjectsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        // From 
		$query->from('#__sportsmanagement_project AS p');
        $query->join('INNER',' #__sportsmanagement_sports_type AS st ON st.id = p.sports_type_id ');
        // Where
        $query->where('p.sports_type_id = ' . $db->Quote($sports_type_id) );
        // order
        $query->order('p.name');

                $db->setQuery( $query );
                                                           
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
         /**
         * sportsmanagementModelAjax::getAgeGroupsBySportsTypesOptions()
         * 
         * @param mixed $sports_type_id
         * @param bool $required
         * @return
         */
        function getAgeGroupsBySportsTypesOptions($sports_type_id, $required = false, $slug = false, $dbase = false)
        {
            // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $option = $app->input->getCmd('option');
       
       //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sports_type_id<br><pre>'.print_r($sports_type_id,true).'</pre>'),'');
       
       // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', a.id, a.alias) AS value,a.name AS text');
        // From 
		$query->from('#__sportsmanagement_agegroup AS a');
        $query->join('INNER',' #__sportsmanagement_sports_type AS st ON st.id = a.sportstype_id ');
        // Where
        if ( $sports_type_id )
        {
        $query->where('a.sports_type_id = ' . $sports_type_id );
        }
        // order
        $query->order('a.name');

                $db->setQuery( $query );
                                                           
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectDivisionsOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectDivisionsOptions($project_id, $required = false, $slug = false, $dabse = false)
        {
          
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
        // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', d.id, d.alias) AS value,d.name AS text');
        }
        else
        {
        $query->select('d.id AS value,d.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_division AS d');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = d.project_id ');
        // Where
        $query->where('d.project_id = ' . $db->Quote($project_id) );
        // group
        //$query->group('d.id');
        // order
        $query->order('d.name');

                $db->setQuery( $query );
                
                
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTeamsByDivisionOptions()
         * 
         * @param mixed $project_id
         * @param integer $division_id
         * @param bool $required
         * @return
         */
        public static function getProjectTeamsByDivisionOptions($project_id, $division_id=0, $required=false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select('CONCAT_WS(\':\', t.id, t.alias) AS value,t.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('pt.project_id = ' . $db->Quote($project_id) );
                                
                if($division_id>0) {
                    // Where
        $query->where('pt.division_id = ' . $db->Quote($division_id) );
                        
                }
                
                // order
        $query->order('t.name');
                
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectsByClubOptions()
         * 
         * @param mixed $club_id
         * @param bool $required
         * @return
         */
        public static function getProjectsByClubOptions($club_id, $required=false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
                if($club_id == 0) 
                {
                        $projects = (array) self::getProjects();
                        return self::addGlobalSelectElement($projects, $required);
                } 
                else 
                {
                    // Select some fields
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('t.club_id = ' . $db->Quote($club_id) );
        
                    
                    
                        
                        
                        $db->setQuery($query);
                        return self::addGlobalSelectElement($db->loadObjectList(), $required);
                }
        }
        
        /**
         * sportsmanagementModelAjax::getProjects()
         * 
         * @return
         */
        public static function getProjects($season_id = 0, $required = false, $slug = false, $dabse = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }

        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', p.id, p.alias) AS value,p.name AS text');
        }
        else
        {
        $query->select('p.id AS value,p.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project as p');
        // Where
        if ( $season_id )
        {
        $query->where('p.season_id = ' . (int)$season_id );
        } 
        else
        {
        $query->where('p.season_id = 0');    
        }                       
        // order
        $query->order('p.name');
        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }
        
        /**
         * sportsmanagementModelAjax::getProjectTeamOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectTeamOptions($project_id, $required = false, $slug = false, $dabse = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
       // Get a db connection.
        if ( !$dabse )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', t.id, t.alias) AS value,t.name AS text');
        }
        else
        {
        $query->select('t.id AS value,t.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
        // Where
        if ( $project_id )
        {
        // ist es ein array ?   
        if ( is_array($project_id) )
        {
             
        $ids = implode(",",array_map('intval', $project_id));
        $query->where('pt.project_id IN (' . $ids .')' );    
        } 
        else
        {
        $query->where('pt.project_id = ' . (int)$project_id );
        }
        
        }
        else
        {
            $query->where('pt.project_id = 0' );
        }
        $query->group('t.id,t.alias,t.name');
        // order
        $query->order('t.name');
                       
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTeamPtidOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        function getProjectTeamPtidOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', pt.id, t.alias) AS value,t.name AS text');
        }
        else
        {
        $query->select('pt.id AS value,t.name AS text');    
        }
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
                // Where
        $query->where('pt.project_id = ' . $db->Quote($project_id) );
        // order
        $query->order('t.name');
        
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectPlayerOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectPlayerOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
                
        // Select some fields
        $query->select("CONCAT_WS(':', p.id, p.alias) AS value");
        $query->select("CONCAT(p.lastname, ', ', p.firstname, ' (', p.birthday, ')') AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS stp ON stp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team pt ON pt.team_id = st.id ');
        // Where
        if ( $project_id )
        {
        $query->where('pt.project_id = ' . (int) $project_id);
        }
        $query->where('p.published = 1');
        $query->where('stp.persontype = 1');
        // group
        $query->group('p.id');
        // order
        $query->order('text');        
                        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectStaffOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectStaffOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select("CONCAT_WS(':', p.id, p.alias) AS value");
        $query->select("CONCAT(p.lastname, ', ', p.firstname, ' (', p.birthday, ')') AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_team_person_id AS stp ON stp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id AS st ON st.team_id = stp.team_id ');
        $query->join('INNER',' #__sportsmanagement_project_team pt ON pt.team_id = st.id ');
        // Where
        if ( $project_id )
        {
        $query->where('pt.project_id = ' . $project_id );
        }
        $query->where('p.published = 1');
        $query->where('stp.persontype = 2');
        // group
        $query->group('p.id');
        // order
        $query->order('text');
                 
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectClubOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectClubOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        if ( $slug )
        {
        $query->select('CONCAT_WS(\':\', c.id, c.alias) AS value,c.name AS text');    
        }
        else
        {
        $query->select('c.id AS value,c.name AS text');    
        }
                
        // From 
		$query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
        $query->join('INNER',' #__sportsmanagement_team t ON t.id = st.team_id ');
        $query->join('INNER',' #__sportsmanagement_club AS c ON c.id = t.club_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = pt.project_id ');
                                
        // Where
        if ( $project_id )
        {
        // ist es ein array ? 
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'Notice');  
        if ( is_array($project_id) )
        {
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($project_id,true).'</pre>'),'Notice');    
        $ids = implode(",",array_map('intval', $project_id) ) ;
        $query->where('pt.project_id IN (' . $ids .')' );    
        } 
        else
        {
        $query->where('pt.project_id = ' . (int)$project_id );
        }
        
        }
        else
        {
        $query->where('pt.project_id = 0');    
        }
        // group
        $query->group('c.id');
        // order
        $query->order('c.name');
                                                                
$db->setQuery($query);                                                                        
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectEventsOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectEventsOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS(\':\', et.id, et.alias) AS value,et.name AS text');
        // From 
		$query->from('#__sportsmanagement_eventtype as et');
        $query->join('INNER',' #__sportsmanagement_match_event as me ON et.id = me.event_type_id ');
        $query->join('INNER',' #__sportsmanagement_match as m ON m.id = me.match_id');
        $query->join('INNER',' #__sportsmanagement_round as r ON m.round_id = r.id ');
        // Where
        $query->where('r.project_id = ' . (int) $project_id );
        // group
        $query->group('et.id');
        // order
        $query->order('et.ordering');
try{
                $db->setQuery( $query );
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
		 }
        catch (Exception $e)
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
        }


        /**
         * sportsmanagementModelAjax::getProjectStatOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectStatOptions($project_id, $required=false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('CONCAT_WS(\':\', s.id, s.alias) AS value,s.name AS text');
        // From 
		$query->from('#__sportsmanagement_project_position AS ppos');
        $query->join('INNER',' #__sportsmanagement_position_statistic AS ps ON ps.position_id = ppos.position_id ');
        $query->join('INNER',' #__sportsmanagement_statistic AS s ON s.id = ps.statistic_id ');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = ppos.project_id ');
        // Where
        $query->where('ppos.project_id = ' . $db->Quote($project_id));
        // group
        $query->group('s.id');
        // order
        $query->order('s.name');
        
        $db->setQuery($query);

                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getMatchesOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getMatchesOptions($project_id, $required=false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select("m.id AS value,CONCAT('(', m.match_date, ') - ', t1.middle_name, ' - ', t2.middle_name) AS text");
        // From 
		$query->from('#__sportsmanagement_match AS m');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt1 ON m.projectteam1_id = pt1.id ');
        $query->join('INNER',' #__sportsmanagement_project_team AS pt2 ON m.projectteam2_id = pt2.id ');
        
        $query->join('INNER',' #__sportsmanagement_season_team_id as st1 ON st1.id = pt1.team_id ');
        $query->join('INNER',' #__sportsmanagement_season_team_id as st2 ON st2.id = pt2.team_id ');
        
        $query->join('INNER',' #__sportsmanagement_team AS t1 ON st1.team_id = t1.id ');
        $query->join('INNER',' #__sportsmanagement_team AS t2 ON st2.team_id = t2.id ');
        
                                
                // Where
        $query->where('pt1.project_id = ' . $db->Quote($project_id) );
        
        // order
        $query->order('m.match_date, t1.short_name');

                                        
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getRefereesOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        function getRefereesOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        // Select some fields
        $query->select("p.id AS value,CONCAT(p.firstname, ' ', p.lastname) AS text");
        // From 
		$query->from('#__sportsmanagement_person AS p');
        $query->join('INNER',' #__sportsmanagement_season_person_id AS sp ON sp.person_id = p.id ');
        $query->join('INNER',' #__sportsmanagement_project_referee AS pr ON pr.person_id = sp.id ');
        // Where
        $query->where('pr.project_id = ' . $db->Quote($project_id));
        $query->where('p.published = 1');
        // order
        $query->order('text');
                    
                
                $db->setQuery($query);
                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }

        /**
         * sportsmanagementModelAjax::getProjectTreenodeOptions()
         * 
         * @param mixed $project_id
         * @param bool $required
         * @return
         */
        public static function getProjectTreenodeOptions($project_id, $required = false, $slug = false, $dbase = false)
        {
            
	   $app = JFactory::getApplication();
		$option = $app->input->getCmd('option');
      // Get a db connection.
        if ( !$dbase )
        {
            $db = sportsmanagementHelper::getDBConnection();
        }
        else
        {
            $db = sportsmanagementHelper::getDBConnection(TRUE,TRUE);
        }
        $query = $db->getQuery(true);
        
        // Select some fields
        $query->select('tt.id AS value,tt.id AS text');
        // From 
		$query->from('#__sportsmanagement_treeto AS tt');
        $query->join('INNER',' #__sportsmanagement_project p ON p.id = tt.project_id ');
        // Where
        $query->where('tt.project_id = ' . $db->Quote($project_id));
        // order
        $query->order('tt.id');
        
        $db->setQuery($query);

                return self::addGlobalSelectElement($db->loadObjectList(), $required);
        }


}
?>
