<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'countries.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');


/**
 * modJSMNewProjectHelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class modJSMNewProjectHelper
{

	
	/**
	 * modJSMNewProjectHelper::getData()
	 * 
	 * @return
	 */
	public static function getData($new_project_article,$mycategory)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
    $db = JFactory::getDBO();
    $query = $db->getQuery(true);
        $result = array();
    
$heutestart = date("Y-m-d");		
$heuteende = date("Y-m-d");
		
$heutestart .= ' 00:00:00';		
$heuteende .= ' 23:59:00';

$query->select('pro.id,pro.name,pro.current_round as roundcode,CONCAT_WS(\':\',pro.id,pro.alias) AS project_slug,le.name as liganame,le.country');
$query->select('le.picture as league_picture,pro.picture as project_picture');
$query->from('#__sportsmanagement_project as pro');
$query->join('INNER','#__sportsmanagement_league as le on le.id = pro.league_id');
$query->where('pro.modified BETWEEN '.$db->Quote(''.$heutestart.''). ' AND '.$db->Quote(''.$heuteende.'')  );
$query->order('pro.name ASC');

$db->setQuery( $query );
$anzahl = $db->loadObjectList();

//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
		
foreach ( $anzahl as $row )
{

if ( $row->roundcode )
{
$query->clear();
$query->select('r.name,CONCAT_WS(\':\',r.id,r.alias) AS round_slug');
$query->from('#__sportsmanagement_round as r');
$query->where('r.project_id = ' . $row->id);
$query->where('r.id = ' . $row->roundcode);

$db->setQuery( $query );

$result2 = $db->loadObject();
$row->roundcode = $result2->round_slug;

}

$temp = new stdClass();
$temp->name = $row->name;
$temp->liganame = $row->liganame;
$temp->roundcode = $row->roundcode;
//$temp->id = $row->id;
$temp->id = $row->project_slug;
$temp->country = $row->country;
$result[] = $temp;
$result = array_merge($result);

/**
 * soll ein artikel erstellt werden ?
 */
if ( $new_project_article )
{
$query->clear();
$query->select('id');
$query->from('#__content');
$query->where('title LIKE '.$db->Quote(''.$row->name.'') );
$query->where('created BETWEEN '.$db->Quote(''.$heutestart.''). ' AND '.$db->Quote(''.$heuteende.'')  );
$db->setQuery( $query );
$article = $db->loadObject();

if ( $article )
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' vorhanden<pre>'.print_r($article,true).'</pre>'),'Notice');
}
else
{
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' nicht vorhanden<pre>'.print_r($article,true).'</pre>'),'Error');  

// Create and populate an object.
$profile = new stdClass();
$profile->title = $row->name;
$profile->alias = JFilterOutput::stringURLSafe( $row->name );
$profile->catid = $mycategory;
$profile->state = 1;
$profile->access = 1;
$profile->featured = 1;
$profile->language = '*';

$profile->created = $date->toSql();
$profile->created_by = $user->get('id');
$profile->modified = $date->toSql();
$profile->modified_by = $user->get('id');

$createroute = array(	"option" => "com_sportsmanagement",
							"view" => "resultsranking",
                            "cfg_which_database" => 0,
                            "s" => 0,
							"p" => $row->id,
              "r" => $row->roundcode );

$query = sportsmanagementHelperRoute::buildQuery( $createroute );
$link = JRoute::_( 'index.php?' . $query, false );

$profile->introtext = '<p><a href="'.$link.'">
<img src="'.$row->league_picture.'" alt="'.$row->liganame.'" style="float: left;" width="200" height="auto" />
'.$row->name.' - ( '.$row->liganame.' )</a> neu angelegt/aktualisiert.
<img src="'.$row->project_picture.'" alt="'.$row->name.'" style="float: right;" width="200" height="auto" />
</p>';

$profile->publish_up = $date->toSql();


$resultinsert = JFactory::getDbo()->insertObject('#__content', $profile);  

if ( $resultinsert )
{
// Create and populate an object.
$profile = new stdClass();
$profile->content_id = $db->insertid();
$profile->ordering = $db->insertid();
$resultfrontpage = JFactory::getDbo()->insertObject('#__content_frontpage', $profile);    
}
  
}

    
}


}

		return $result;
		
	}
	
	

	
}