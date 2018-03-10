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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.filesystem.folder');
JFormHelper::loadFieldClass('list');


/**
 * JFormFieldAssociationsList
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class JFormFieldAssociationsList extends JFormFieldList
{
	/**
	 * field type
	 * @var string
	 */
	public $type = 'AssociationsList';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $selected = 0;
        //$app->enqueueMessage(JText::_('JFormFieldAssociationsList getOptions<br><pre>'.print_r($this->element,true).'</pre>'),'Notice');
        // Initialize variables.
		$options = array();
    //echo 'this->element<br /><pre>~' . print_r($this->element,true) . '~</pre><br />';
		//$varname = (string) $this->element['varname'];
    $vartable = (string) $this->element['targettable'];
		$select_id = JFactory::getApplication()->input->getVar('id');
//echo 'select_id<br /><pre>~' . print_r($select_id,true) . '~</pre><br />';		
 		if (is_array($select_id)) {
 			$select_id = $select_id[0];
 		}
		
		
		if ($select_id)
		{		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('country');		
		$query->from('#__sportsmanagement_'.$vartable.' AS t');
		$query->where('t.id = '.$select_id);
		$db->setQuery($query);
		$country = $db->loadResult();
		//echo 'country<br /><pre>~' . print_r($country,true) . '~</pre><br />';
				
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query->select('t.id AS value, t.name AS text');
			$query->from('#__sportsmanagement_associations AS t');
			$query->where("t.country = '".$country."'");
			$query->where('t.parent_id = 0');
			$query->order('t.name');
			$db->setQuery($query);
			//$options = $db->loadObjectList();
			
			$sections = $db->loadObjectList ();
            
            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' sections<br><pre>'.print_r($sections,true).'</pre>'),'Notice');
            
  $categoryparent = empty($sections) ? 0 : $sections[0]->value;
  //echo 'categoryparent<br /><pre>~' . print_r($categoryparent,true) . '~</pre><br />';
  //$options = $this->JJ_categoryArray();
$list = $this->JJ_categoryArray(0, $country);

$preoptions = array();
$name = 'parent_id';
foreach ( $list as $item ) 
    {
			if (!$preoptions && !$selected && ($sections || !$item->section)) 
      {
				$selected = $item->id;
			}
			$options [] = JHtml::_ ( 'select.option', $item->id, $item->treename, 'value', 'text', !$sections && $item->section);
		}
		
		
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);
		return $options;
	}
	
/**
 * JFormFieldAssociationsList::JJ_categoryArray()
 * 
 * @param integer $admin
 * @param mixed $country
 * @return
 */
function JJ_categoryArray($admin=0,$country) 
  {
$db = sportsmanagementHelper::getDBConnection(); 
    // get a list of the menu items
	$query = "SELECT * FROM #__sportsmanagement_associations where country = '".$country."'";

    $query .= " ORDER BY ordering, name";
    $db->setQuery($query);
    $items = $db->loadObjectList();

// echo 'JJ_categoryArray items<pre>';
//  	print_r($items);
//  	echo '</pre>';
    
    // establish the hierarchy of the menu
    $children = array ();

    // first pass - collect children
    foreach ($items as $v) 
    {
        $pt = $v->parent_id;
        $list = isset($children[$pt]) ? $children[$pt] : array ();
        array_push($list, $v);
        $children[$pt] = $list;
        }

    // second pass - get an indent list of the items
    $array = $this->fbTreeRecurse(0, '', array (), $children, 10, 0, 1);
    
//    echo 'JJ_categoryArray array<pre>';
//  	print_r($array);
//  	echo '</pre>';
	
    return $array;
    }    	
	
/**
 * JFormFieldAssociationsList::fbTreeRecurse()
 * 
 * @param mixed $id
 * @param mixed $indent
 * @param mixed $list
 * @param mixed $children
 * @param integer $maxlevel
 * @param integer $level
 * @param integer $type
 * @return
 */
function fbTreeRecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 ) 
    {

    if (isset($children[$id]) && $level <= $maxlevel) {
        foreach ($children[$id] as $v) {
            $id = $v->id;
			if ( $type ) {
                $pre     = '&nbsp;';
                $spacer = '...';
            } else {
                $pre     = '- ';
                $spacer = '&nbsp;&nbsp;';
            }

            if ( $v->parent_id == 0 ) {
                $txt     = $this->sm_htmlspecialchars($v->name);
            } else {
                $txt     = $pre . $this->sm_htmlspecialchars($v->name);
            }
            $pt = $v->parent_id;
            $list[$id] = $v;
            $list[$id]->treename = $indent . $txt;
            $list[$id]->children = !empty($children[$id]) ? count( $children[$id] ) : 0;
            $list[$id]->section = ($v->parent_id==0);

            $list = $this->fbTreeRecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
        }
    }
    return $list;
}    
	
/**
 * JFormFieldAssociationsList::sm_htmlspecialchars()
 * 
 * @param mixed $string
 * @param mixed $quote_style
 * @param string $charset
 * @return
 */
function sm_htmlspecialchars($string, $quote_style=ENT_COMPAT, $charset='UTF-8') 
  {
	return htmlspecialchars($string, $quote_style, $charset);
}	
	
}