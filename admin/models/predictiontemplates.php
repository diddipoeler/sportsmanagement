<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

jimport( 'joomla.application.component.modellist' );



/**
 * sportsmanagementModelPredictionTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelPredictionTemplates extends JModelList
{

	var $_identifier = "predictiontemplates";
	
    /**
     * sportsmanagementModelPredictionTemplates::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'tmpl.title',
                        'tmpl.template',
                        'tmpl.id',
                        'tmpl.ordering',
                        'tmpl.modified',
                        'tmpl.modified_by'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }
    
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id_select', 'filter_prediction_id_select', '');
		
        
        if (is_numeric($temp_user_request) )
		{
		  $this->setState('filter.prediction_id_select', $temp_user_request);
		}
        else
        {
            $this->setState('filter.prediction_id_select', $app->getUserState( "$option.predid", '0' ));
        }  

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('tmpl.title', 'asc');
	}
	

	/**
	 * sportsmanagementModelPredictionTemplates::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $search	= $this->getState('filter.search');
        $prediction_id	= $this->getState('filter.prediction_id_select');

        $query->select(array('tmpl.*', 'u.name AS editor','u1.username'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template AS tmpl')
        ->join('LEFT', '#__users AS u ON u.id = tmpl.checked_out')
        ->join('LEFT', '#__users AS u1 ON u1.id = tmpl.modified_by');
        
        if (is_numeric($prediction_id) )
		{
		$app->setUserState( "$option.predid", $prediction_id );  
		$query->where('tmpl.prediction_id = ' . $prediction_id);	
		}
        else
        {
            $prediction_id	= $app->getUserState( "$option.predid", '0' );
            $query->where('tmpl.prediction_id = ' . $prediction_id);
        }


        
        $query->order($db->escape($this->getState('list.ordering', 'tmpl.title')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }

		
		return $query;
	}



	
	/**
	 * check that all prediction templates in default location have a corresponding record, except if game has a master template
	 *
	 */
	function checklist($prediction_id)
	{
	  // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
        
		//$prediction_id	= $this->_prediction_id;
		//$defaultpath	= JLG_PATH_EXTENSION_PREDICTIONGAME.DS.'settings';
		$defaultpath	= JPATH_COMPONENT_SITE.DS.'settings';
    //$extensionspath	= JPATH_COMPONENT_SITE . DS . 'extensions' . DS;
    // Get the views for this component.
	$path = JPATH_SITE.'/components/'.$option.'/views';
        
		$templatePrefix	= 'prediction';
//    $defaultvalues = array();
    
		if (!$prediction_id)
        {
            return;
        }

		// get info from prediction game
        // Select some fields
        $query->select('master_template');
		// From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game ');
        $query->where('id = ' . $prediction_id );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
//		$query = 'SELECT master_template 
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game 
//					WHERE id = ' . (int) $prediction_id;

		$db->setQuery($query);
		$params = $db->loadObject();

		// if it's not a master template, do not create records.
		if ($params->master_template)
        {
            return true;
        }

		// otherwise, compare the records with the files // get records
        $query->clear('');
        // Select some fields
        $query->select('template');
		// From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template ');
        $query->where('prediction_id = ' . $prediction_id );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
//		$query = 'SELECT template 
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template 
//					WHERE prediction_id = ' . (int) $prediction_id;

		$db->setQuery($query);
		if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}
		if (empty($records))
        {
            $records = array();
        }
    
		// add default folder
		$xmldirs[] = $defaultpath . DS . 'default';

		// now check for all xml files in these folders
		foreach ($xmldirs as $xmldir)
		{
			if ($handle = opendir($xmldir))
			{
				/* check that each xml template has a corresponding record in the
				database for this project. If not, create the rows with default values
				from the xml file */
				while ($file = readdir($handle))
				{
					if ($file!='.'&&$file!='..'&&strtolower(substr($file,(-3)))=='xml'&&
						strtolower(substr($file,0,strlen($templatePrefix)))==$templatePrefix)
					{
						$template = substr($file,0,(strlen($file)-4));
                        
                        //$app->enqueueMessage(JText::_('PredictionGame template -> '.$template),'');
                        
						// Determine if a metadata file exists for the view.
				        //$metafile = $path.'/'.$template.'/metadata.xml';
                        $metafile = $path.'/'.$template.'/tmpl/default.xml';
                        $attributetitle = '';
                        if (is_file($metafile)) 
                        {
                        // Attempt to load the xml file.
					   if ($metaxml = simplexml_load_file($metafile)) 
                        {
                        //$app->enqueueMessage(JText::_('PredictionGame template metaxml-> '.'<br /><pre>~' . print_r($metaxml,true) . '~</pre><br />'),'');    
                        // This will save the value of the attribute, and not the objet
                        //$attributetitle = (string)$metaxml->view->attributes()->title;
                        $attributetitle = (string)$metaxml->layout->attributes()->title;
                        //$app->enqueueMessage(JText::_('PredictionGame template attribute-> '.'<br /><pre>~' . print_r($attributetitle,true) . '~</pre><br />'),'');
                        if ($menu = $metaxml->xpath('view[1]')) 
                        {
							$menu = $menu[0];
                            //$app->enqueueMessage(JText::_('PredictionGame template menu-> '.'<br /><pre>~' . print_r($menu,true) . '~</pre><br />'),'');
                            }
                        }
                        }
                        
                        if ((empty($records)) || (!in_array($template,$records)))
						{
						  $jRegistry = new JRegistry();
							$form = JForm::getInstance($file, $xmldir.DS.$file);
							$fieldsets = $form->getFieldsets();
							
							//echo 'fieldsets<br /><pre>~' . print_r($fieldsets,true) . '~</pre><br />';
							//echo 'form<br /><pre>~' . print_r($form,true) . '~</pre><br />';
							
							$defaultvalues = array();
							foreach ($fieldsets as $fieldset) 
              {
								foreach($form->getFieldset($fieldset->name) as $field) 
                {
									//echo 'field<br /><pre>~' . print_r($field,true) . '~</pre><br />';
                  $jRegistry->set($field->name, $field->value);
                  $defaultvalues[] = $field->name.'='.$field->value;
								}				
							}
							
                            $defaultvalues = $jRegistry->toString('ini');
                            
                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' defaultvalues<br><pre>'.print_r($defaultvalues,true).'</pre>'),'');
                            
                            $parameter = new JRegistry;
			                if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            $ini = $parameter->loadString($defaultvalues);
        }
        else
        {
            $ini = $parameter->loadINI($defaultvalues);
        }
			                $ini = $parameter->toArray($ini);
			                $defaultvalues = json_encode( $ini );
                            	
                            //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' defaultvalues<br><pre>'.print_r($defaultvalues,true).'</pre>'),'');

                            
							//$defaultvalues = ereg_replace('"', '', $defaultvalues);
                            //$defaultvalues = preg_replace('"', '', $defaultvalues);
							//$defaultvalues = implode('\n', $defaultvalues);
							//echo 'defaultvalues<br /><pre>~' . print_r($defaultvalues,true) . '~</pre><br />';
							
							//$tblTemplate_Config = JTable::getInstance('predictiontemplate', 'table');
                            
                            // otherwise, compare the records with the files // get records
        $query->clear('');
        // Select some fields
        $query->select('id');
		// From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template ');
        $query->where('prediction_id = ' . $prediction_id );
        $query->where('template LIKE '.$db->Quote(''.$template.''));
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        
//		$query = 'SELECT template 
//					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_template 
//					WHERE prediction_id = ' . (int) $prediction_id;

		$db->setQuery($query);
		$record_tpl = $db->loadResult();
        if( !$record_tpl )
        {
                            $mdl = JModelLegacy::getInstance("predictiontemplate", "sportsmanagementModel");
                            $tblTemplate_Config = $mdl->getTable();
							
                            $tblTemplate_Config->template = $template;
                            if ( $attributetitle )
                            {
                                $tblTemplate_Config->title = $attributetitle;
                            }
                            else
                            {
                                $tblTemplate_Config->title = $file;
                            }
							
							$tblTemplate_Config->params = $defaultvalues;
							$tblTemplate_Config->prediction_id = $prediction_id;
							
							// Store the item to the database
							if (!$tblTemplate_Config->store())
							{
								$this->setError($this->_db->getErrorMsg());
								return false;
							}
							array_push($records,$template);
		}					
							
						}
					}
				}
				closedir($handle);
			}
		}
	}

}
?>