<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       templates.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModelTemplates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelTemplates extends JSMModelList
{
    var $_identifier = "templates";
    var $_project_id = 0;

    /**
     * sportsmanagementModelTemplates::__construct()
     * 
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = array())
    {   
                $config['filter_fields'] = array(
                        'tmpl.template',
                        'tmpl.title',
                        'tmpl.id',
                        'tmpl.ordering',
                        'tmpl.checked_out_time',
                        'tmpl.checked_out',
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
     * @since 1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {

        if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend') ) {
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''), '');
            $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' identifier -> '.$this->_identifier.''), '');
        }

        // Load the filter state.
        $search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        // List state information.
        parent::populateState('tmpl.template', 'asc');
    }    
    
    /**
     * sportsmanagementModelTemplates::getListQuery()
     * 
     * @return
     */
    protected function getListQuery()
    {

        $this->jsmquery->clear();
        //$this->_project_id = $this->jsmapp->getUserState( "$this->jsmoption.pid", '0' );
        $this->checklist($this->project_id);
        $this->jsmquery->clear();
        $this->jsmquery->select('tmpl.template,tmpl.title,tmpl.id,tmpl.checked_out,u.name AS editor,(0) AS isMaster,tmpl.checked_out_time,tmpl.modified,tmpl.modified_by');
        $this->jsmquery->select('u1.username');
        $this->jsmquery->from('#__sportsmanagement_template_config AS tmpl');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = tmpl.checked_out');
        $this->jsmquery->join('LEFT', '#__users AS u1 ON u1.id = tmpl.modified_by');
        $this->jsmquery->where('tmpl.project_id = '.(int) $this->project_id);
        
        $oldTemplates="frontpage'";
        $oldTemplates .= ",'do_tipsl','tipranking','tipresults','user'";
        $oldTemplates .= ",'tippentry','tippoverall','tippranking','tippresults','tipprules','tippusers'";
        $oldTemplates .= ",'predictionentry','predictionoverall','predictionranking','predictionresults','predictionrules','predictionusers";
        
        $this->jsmquery->where("tmpl.template NOT IN ('".$oldTemplates."')");
        
        if ($this->getState('filter.search')) {
            $this->jsmquery->where(' ( LOWER(tmpl.title) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%') .' OR '.' LOWER(tmpl.template) LIKE '.$this->jsmdb->Quote('%'.$this->getState('filter.search').'%').' )');
        }



        $this->jsmquery->order(
            $this->jsmdb->escape($this->getState('list.ordering', 'tmpl.template')).' '.
            $this->jsmdb->escape($this->getState('list.direction', 'ASC'))
        );

        return $this->jsmquery;
    }

    /**
     * check that all templates in default location have a corresponding record,except if project has a master template
     */
    function checklist($project_id)
    {
        $defaultpath = JPATH_COMPONENT_SITE.DIRECTORY_SEPARATOR.'settings';
        // Get the views for this component.
        $path = JPATH_SITE.'/components/'.$this->jsmoption.'/views';
        $predictionTemplatePrefix = 'prediction';

        if (!$project_id) {
            return;
        }

        // get info from project
        $this->jsmquery->clear();
        $this->jsmquery->select('master_template,extension');
        $this->jsmquery->from('#__sportsmanagement_project');
        $this->jsmquery->where('id = '.(int)$project_id);
        $this->jsmdb->setQuery($this->jsmquery);

        $params = $this->jsmdb->loadObject();

        // if it's not a master template,do not create records.
        if ($params->master_template) {
            return true;
        }

        // otherwise,compare the records with the files
        // get records
        $this->jsmquery->clear();
        $this->jsmquery->select('template');
        $this->jsmquery->from('#__sportsmanagement_template_config');
        $this->jsmquery->where('project_id = '.(int)$project_id);
        $this->jsmdb->setQuery($this->jsmquery);

        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla! 3.0 code here
            $records = $this->jsmdb->loadColumn();
        }
        elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
             // Joomla! 2.5 code here
             $records = $this->jsmdb->loadResultArray();
        }

        if (empty($records)) { 
            $records=array(); 
        }
        
        // add default folder
        $xmldirs[] = $defaultpath.DIRECTORY_SEPARATOR.'default';
        
        $extensions = sportsmanagementHelper::getExtensions($this->_project_id);
        foreach ($extensions as $e => $extension) {
            $extensiontpath =  JPATH_COMPONENT_SITE .DIRECTORY_SEPARATOR. 'extensions' .DIRECTORY_SEPARATOR. $extension;
            if (is_dir($extensiontpath.DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'default')) {
                $xmldirs[] = $extensiontpath.DIRECTORY_SEPARATOR.'settings'.DIRECTORY_SEPARATOR.'default';
            }
        }

        // now check for all xml files in these folders
        foreach ($xmldirs as $xmldir)
        {
         
            if ($handle=opendir($xmldir)) {
                /**
* 
 * check that each xml template has a corresponding record in the
                database for this project. If not,create the rows with default values
                from the xml file 
*/
                while ($file=readdir($handle))
                {
                    if    ($file!='.' 
                        && $file!='..' 
                        && $file!='do_tipsl' 
                        && strtolower(substr($file, -3))=='xml' 
                        && strtolower(substr($file, 0, strlen($predictionTemplatePrefix))) != $predictionTemplatePrefix
                    ) {
                        $template = substr($file, 0, (strlen($file)-4));
                        
                              // Determine if a metadata file exists for the view.
                              $metafile = $path.'/'.$template.'/tmpl/default.xml';
                              $attributetitle = '';
                        if (is_file($metafile)) {
                            if (version_compare(JVERSION, '3.0.0', 'ge')) {
                                $xml = simplexml_load_file($metafile);
                            } else {
                                           $xml = Factory::getXML($metafile, true);
                            }
                        
                               $attributetitle = (string)$xml->layout->attributes()->title;
                        }

                        if ((empty($records)) || (!in_array($template, $records))) {
                                        $xmlfile = $xmldir.DIRECTORY_SEPARATOR.$file;
                                        $arrStandardSettings = array();
                            if(file_exists($xmlfile)) {
                                $strXmlFile = $xmlfile;
                                            $form = JForm::getInstance($template, $strXmlFile, array('control'=> ''));
                                            $fieldsets = $form->getFieldsets();
                                foreach ($fieldsets as $fieldset) 
                                            {
                                    foreach($form->getFieldset($fieldset->name) as $field) 
                                                   {
                                        $arrStandardSettings[$field->name] = $field->value;
                                    }
                                }
                                
                            }
                            
                                                 $defaultvalues = json_encode($arrStandardSettings);
                           
                                           $this->jsmquery->clear();
                                           // Select some fields
                                         $this->jsmquery->select('id');
                                  // From the table
                                  $this->jsmquery->from('#__sportsmanagement_template_config');
                                         $this->jsmquery->where('template LIKE '.$this->jsmdb->Quote(''.$template.''));
                                         $this->jsmquery->where('project_id = '.(int)$project_id);
                                  $this->jsmdb->setQuery($this->jsmquery);
                                  $resulttemplate = $this->jsmdb->loadResult();

                            if (!$resulttemplate ) {
                                // Create and populate an object.
                                $object_template = new stdClass();
                                $object_template->template = $template;
                                $object_template->title = $attributetitle;
                                $object_template->params = $defaultvalues;
                                $object_template->project_id = $project_id;
                                // Insert the object into the user profile table.
                                $result = $this->jsmdb->insertObject('#__sportsmanagement_template_config', $object_template);
                            }

                                        array_push($records, $template);
                        }
                        else
                        {
                                 $this->jsmquery->clear();
                                   $this->jsmquery->select('id');
                                        $this->jsmquery->from('#__sportsmanagement_template_config');
                                   $this->jsmquery->where('template LIKE '.$this->jsmdb->Quote(''.$template.''));
                                   $this->jsmquery->where('project_id = '.(int)$project_id);
                                        $this->jsmdb->setQuery($this->jsmquery);
                                        $resulttemplate = $this->jsmdb->loadResult();
                        
                                       $object_template = new stdClass();
                                       $object_template->id = $resulttemplate;    
                                       $object_template->title = $attributetitle;    
                                       $result_update = $this->jsmdb->updateObject('#__sportsmanagement_template_config', $object_template, 'id', true);                        
                        }                        
                    }
                }
                                
                closedir($handle);
            }
        }        
        
    }

    
    function getMasterTemplates()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('id AS value, name AS text');
        $this->jsmquery->from('#__sportsmanagement_project');
        $this->jsmquery->where('master_template=0 ');
        $this->jsmquery->order('name');
        $this->jsmdb->setQuery($this->jsmquery);
        $options = $this->jsmdb->loadObjectList();    
        return $options;    
    }    
    /**
     * sportsmanagementModelTemplates::getMasterTemplatesList()
     * 
     * @return
     */
    function getMasterTemplatesList()
    {
         
        $this->jsmquery->clear();
        // get current project settings
        $this->jsmquery->select('template');
        $this->jsmquery->from('#__sportsmanagement_template_config');
        $this->jsmquery->where('project_id = '.(int)$this->project_id);
        $this->jsmdb->setQuery($this->jsmquery);
        
        if(version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla! 3.0 code here
            $current = $this->jsmdb->loadColumn();
        }
        elseif(version_compare(JVERSION, '2.5.0', 'ge')) {
             // Joomla! 2.5 code here
             $current = $this->jsmdb->loadResultArray();
        }
       
        $this->jsmquery->clear();

        if ($this->_getALL) {
            $this->jsmquery->select('t.*,(1) AS isMaster');
        }
        else
        {
            $this->jsmquery->select('t.id as value, t.title as text, t.template as template');
        }
        
        $this->jsmquery->select('u1.username');
        $this->jsmquery->from('#__sportsmanagement_template_config as t');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project as pm ON pm.id = t.project_id');            
        $this->jsmquery->join('INNER', '#__sportsmanagement_project as p ON p.master_template = pm.id');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = t.checked_out');
        $this->jsmquery->join('LEFT', '#__users AS u1 ON u1.id = t.modified_by');
        $this->jsmquery->where('p.id = '.(int)$this->project_id);

        $oldTemplates="frontpage'";
        $oldTemplates .= ",'do_tipsl','tipranking','tipresults','user'";
        $oldTemplates .= ",'tippentry','tippoverall','tippranking','tippresults','tipprules','tippusers'";
        $oldTemplates .= ",'predictionentry','predictionoverall','predictionranking','predictionresults','predictionrules','predictionusers";
        $this->jsmquery->where(" t.template NOT IN ('".$oldTemplates."')");

        if (count($current)) {
            $this->jsmquery->where(" t.template NOT IN ('".implode("','", $current)."')");
        }

        $this->jsmquery->order('t.title');

        $this->jsmdb->setQuery($this->jsmquery);

        $current = $this->jsmdb->loadObjectList();
        return (count($current)) ? $current : array();
    }

    /**
     * sportsmanagementModelTemplates::getMasterName()
     * 
     * @return
     */
    function getMasterName()
    {
        $this->jsmquery->clear();
        $this->jsmquery->select('master.name');
        $this->jsmquery->from('#__sportsmanagement_project as master');
        $this->jsmquery->join('INNER', '#__sportsmanagement_project as p ON p.master_template = master.id');
        $this->jsmquery->where('p.id = '.(int)$this->project_id);
        try { 
            $this->jsmdb->setQuery($this->jsmquery);
            $result = $this->jsmdb->loadResult();
        }
        catch (Exception $e) {
             $result = false;
             $this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.' <pre>'.$e->getMessage().'</pre><br>', 'Error');    
        }

        return $result;
    }

}
?>
