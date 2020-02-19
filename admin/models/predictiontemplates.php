<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      predictiontemplates.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage predictiontemplates
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelPredictionTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelPredictionTemplates extends JSMModelList
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
                parent::setDbo($this->jsmdb);
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
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        if ( $this->jsmjinput->getInt('prediction_id') )
		{
		$this->setState('filter.prediction_id', $this->jsmjinput->getInt('prediction_id') );
        $this->jsmapp->setUserState( "com_sportsmanagement.prediction_id", $this->jsmjinput->getInt('prediction_id') );	
		}
		else
		{
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
		$this->setState('filter.prediction_id', $temp_user_request);
        $this->jsmapp->setUserState( "com_sportsmanagement.prediction_id", $temp_user_request );
	}
        // List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);       
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'tmpl.title';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);

	}

	/**
	 * sportsmanagementModelPredictionTemplates::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmquery->select(array('tmpl.*', 'u.name AS editor','u1.username'))
        ->from('#__sportsmanagement_prediction_template AS tmpl')
        ->join('LEFT', '#__users AS u ON u.id = tmpl.checked_out')
        ->join('LEFT', '#__users AS u1 ON u1.id = tmpl.modified_by');
        
        if (is_numeric($this->getState('filter.prediction_id')) )
		{
        $this->setState('filter.prediction_id', $this->getState('filter.prediction_id'));  
		$this->jsmquery->where('tmpl.prediction_id = ' . $this->getState('filter.prediction_id'));	
		}
        else
        {
            $this->jsmquery->where('tmpl.prediction_id = ' . $this->getState('filter.prediction_id'));
        }
        
        $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'tmpl.title')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
	
		return $this->jsmquery;
	}
	
	/**
     * sportsmanagementModelPredictionTemplates::checklist()
	 * check that all prediction templates in default location have a corresponding record, except if game has a master template
	 *
	 */
	function checklist($prediction_id)
	{
	  // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
        
		$defaultpath	= JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'settings';
    // Get the views for this component.
	$path = JPATH_SITE.'/components/'.$option.'/views';
        
		$templatePrefix	= 'prediction';
    
		if (!$prediction_id)
        {
            return;
        }

		// get info from prediction game
        // Select some fields
        $query->select('master_template');
		// From table
		$query->from('#__sportsmanagement_prediction_game ');
        $query->where('id = ' . $prediction_id );
        
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
		$query->from('#__sportsmanagement_prediction_template ');
        $query->where('prediction_id = ' . $prediction_id );

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
		$xmldirs[] = $defaultpath .DIRECTORY_SEPARATOR. 'default';

		// now check for all xml files in these folders
		foreach ($xmldirs as $xmldir)
		{
			if ($handle = opendir($xmldir))
			{
				/**
                 *  check that each xml template has a corresponding record in the
                 * 	database for this project. If not, create the rows with default values
                 * 	from the xml file 
                 */
				while ($file = readdir($handle))
				{
					if ($file!='.'&&$file!='..'&&strtolower(substr($file,(-3)))=='xml'&&
						strtolower(substr($file,0,strlen($templatePrefix)))==$templatePrefix)
					{
						$template = substr($file,0,(strlen($file)-4));
                        
/**
 * Determine if a metadata file exists for the view.
 */
                        $metafile = $path.'/'.$template.'/tmpl/default.xml';
                        $attributetitle = '';
                        if (is_file($metafile)) 
                        {
/**
 * Attempt to load the xml file.
 */
					   if ($metaxml = simplexml_load_file($metafile)) 
                        {

/**
 * This will save the value of the attribute, and not the objet
 */
                        $attributetitle = (string)$metaxml->layout->attributes()->title;
                        
                        if ($menu = $metaxml->xpath('view[1]')) 
                        {
							$menu = $menu[0];
                            }
                        }
                        }
                        
                        if ((empty($records)) || (!in_array($template,$records)))
						{
						  $jRegistry = new Registry();
							$form = JForm::getInstance($file, $xmldir.DIRECTORY_SEPARATOR.$file);
							$fieldsets = $form->getFieldsets();
						
							$defaultvalues = array();
							foreach ($fieldsets as $fieldset) 
              {
								foreach($form->getFieldset($fieldset->name) as $field) 
                {
                  $jRegistry->set($field->name, $field->value);
                  $defaultvalues[] = $field->name.'='.$field->value;
								}				
							}
							
                            $defaultvalues = $jRegistry->toString('ini');
                           
                            $parameter = new Registry;
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
                            
/**
 * otherwise, compare the records with the files
 */ 
        $query->clear('');
        // Select some fields
        $query->select('id');
		// From table
		$query->from('#__sportsmanagement_prediction_template ');
        $query->where('prediction_id = ' . $prediction_id );
        $query->where('template LIKE '.$db->Quote(''.$template.''));
		$db->setQuery($query);
		$record_tpl = $db->loadResult();
        if( !$record_tpl )
        {
                            $mdl = BaseDatabaseModel::getInstance("predictiontemplate", "sportsmanagementModel");
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
                          else
                          {
$newround = new stdClass();
$newround->id = $record_tpl;
$newround->title = $attributetitle;
// update the object
$result = $db->updateObject('#__sportsmanagement_prediction_template', $newround, 'id',true);                            
                            
                            
                          }
							
						}
                      else
						{
// Select some fields
                        $query->clear('');
        $query->select('id');
		// From table
		$query->from('#__sportsmanagement_prediction_template ');
        $query->where('prediction_id = ' . $prediction_id );
        $query->where('template LIKE '.$db->Quote(''.$template.''));
		$db->setQuery($query);
		$record_tpl = $db->loadResult();
if( $record_tpl )
        {
  $newround = new stdClass();
$newround->id = $record_tpl;
$newround->title = $attributetitle;
// update the object
$result = $db->updateObject('#__sportsmanagement_prediction_template', $newround, 'id',true);   
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
