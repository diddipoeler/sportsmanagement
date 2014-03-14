<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
jimport('joomla.installer.installer');
 

/**
 * com_sportsmanagementInstallerScript
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class com_sportsmanagementInstallerScript
{
	/*
     * The release value would ideally be extracted from <version> in the manifest file,
     * but at preflight, the manifest file exists only in the uploaded temp folder.
     */
    //private $release = '1.0.00';
    
    /**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent) 
	{
		// $parent is the class calling this method
		$parent->getParent()->setRedirectURL('index.php?option=com_sportsmanagement');
	}
 
	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_UNINSTALL_TEXT') . '</p>';
	}
 
	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent) 
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_UPDATE_TEXT') . $parent->get('manifest')->version . '</p>';
	}
 
	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_PREFLIGHT_' . $type . '_TEXT' ) . $parent->get('manifest')->version . '</p>';
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
	$mainframe =& JFactory::getApplication();
    $db = JFactory::getDbo();
        // $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $type . '_TEXT' ) . $parent->get('manifest')->version . '</p>';
    
//$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement" and type ="component"');
//$paramsdata = json_decode( $db->loadResult(), true );

//$mainframe->enqueueMessage(JText::_('postflight paramsdata<br><pre>'.print_r($paramsdata,true).'</pre>'   ),'');

$params = JComponentHelper::getParams('com_sportsmanagement');
$xmlfile = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'config.xml';  
$jRegistry = new JRegistry;
$jRegistry->loadString($params->toString('ini'), 'ini');
//$form =& JForm::getInstance('com_sportsmanagement', $xmlfile, array('control'=> 'params'), false, "/config");
$form =& JForm::getInstance('com_sportsmanagement', $xmlfile,array('control'=> ''), false, "/config");
$form->bind($jRegistry);
$newparams = array();
foreach($form->getFieldset($fieldset->name) as $field)
        {
//         echo 'name -> '. $field->name.'<br>';
//         echo 'type -> '. $field->type.'<br>';
//         echo 'input -> '. $field->input.'<br>';
//         echo 'value -> '. $field->value.'<br>';
        $newparams[$field->name] = $field->value;
        
        }

switch ($type)        
    {
    case "install":
    self::setParams($newparams);
//    self::installComponentLanguages();
    self::installModules($parent);
    self::installPlugins($parent);
    self::createImagesFolder();
//    self::migratePicturePath();
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "update":
//    self::installComponentLanguages();
    self::installModules($parent);
    self::installPlugins($parent);
    self::createImagesFolder();
//    self::migratePicturePath();
      self::setParams($newparams);
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "discover_install":
    break;
        
    }


	}
    
    
    public function createImagesFolder()
	{
		$mainframe = JFactory::getApplication();
  $db = JFactory::getDBO();
  
        //echo JText::_('Creating new Image Folder structure');
		$dest = JPATH_ROOT.'/images/com_sportsmanagement';
		$update = JFolder::exists($dest);
		$folders = array('agegroups',
		'clubs',
		'clubs/large',
		'clubs/medium',
		'clubs/small',
		'clubs/trikot_home',
		'clubs/trikot_away',
		'events',
		'leagues',
		'divisions',
		'person_playground',
		'associations',
		'flags_associations',
		'persons',
		'placeholders',
		'predictionusers',
		'playgrounds',
		'projects',
		'projectreferees',
		'projectteams',
		'projectteams/trikot_home',
		'projectteams/trikot_away',
		'associations',
		'rosterground',
		'matchreport',
		'seasons',
		'sport_types',
		'rounds',
		'teams',
		'flags',
		'teamplayers',
		'teamstaffs',
		'venues',
		'statistics');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/index.html');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/index.html');
		
        foreach ($folders as $folder) 
        {
			JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder);
			JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder.'/index.html');
            
            //$mainframe->enqueueMessage(JText::sprintf('Verzeichnis [ %1$s ] angelegt!',$folder),'Notice');
            
		}
        
		foreach ($folders as $folder) {
			$from = JPath::clean(JPATH_ROOT.'/media/com_sportsmanagement/'.$folder);
			if(JFolder::exists($from)) {
				$to = JPath::clean($dest.'/database/'.$folder);
				if(!JFolder::exists($to)) {
					$ret = JFolder::move($from, $to);
				} else {
					$ret = JFolder::copy($from, $to, '', true);
					//$ret = JFolder::delete($from);
				}
			}
		}
		//echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
	}
    
    
    /*
    * sets parameter values in the component's row of the extension table
    */
    function setParams($param_array) 
    {
        
        $mainframe =& JFactory::getApplication();
        $db = JFactory::getDbo();
        /*
        if ( count($param_array) > 0 )
        {
            // store the combined new and existing values back as a JSON string
                        $paramsString = json_encode( $param_array );

echo '<pre>' . print_r($paramsString,true). '</pre><br>';
                        
                        $db->setQuery('UPDATE #__extensions SET params = ' .
                                $db->quote( $paramsString ) .
                                ' WHERE name = "com_sportsmanagement" and type ="component"' );
                                $db->query();
        $mainframe->enqueueMessage(JText::_('Sportsmanagement Konfiguration gesichert'),'');
        }
        */                
                                
                if ( count($param_array) > 0 ) {
                        // read the existing component value(s)
                        $db = JFactory::getDbo();
                        $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement"');
                        $params = json_decode( $db->loadResult(), true );
                        //$mainframe->enqueueMessage(JText::_('setParams params_array<br><pre>'.print_r($param_array,true).'</pre>'   ),'');
                        //$mainframe->enqueueMessage(JText::_('setParams params aus db<br><pre>'.print_r($params,true).'</pre>'   ),'');
                        // add the new variable(s) to the existing one(s)
                        foreach ( $param_array as $name => $value ) {
                                $params[ (string) $name ] = (string) $value;
                        }
                        //$mainframe->enqueueMessage(JText::_('setParams params neu<br><pre>'.print_r($params,true).'</pre>'   ),'');
                        // store the combined new and existing values back as a JSON string
                        $paramsString = json_encode( $params );
                        $db->setQuery('UPDATE #__extensions SET params = ' .
                                $db->quote( $paramsString ) .
                                ' WHERE name = "com_sportsmanagement"' );
                                $db->query();
                }
                
        }
        
        
	/**
	 * method to install the plugins
	 *
	 * @return void
	 */
	public function installPlugins($parent)
	{
  $mainframe = JFactory::getApplication();
  $src = $parent->getParent()->getPath('source');
  $manifest = $parent->getParent()->manifest;
  $db = JFactory::getDBO();
  
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($manifest,true).'</pre>'),'');
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($src,true).'</pre>'),'');
  
  $plugins = $manifest->xpath('plugins/plugin');
  foreach ($plugins as $plugin)
        {
        $name = (string)$plugin->attributes()->plugin;
        $group = (string)$plugin->attributes()->group;
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($group,true).'</pre>'),'');
        
        
        $path = $src.DS.'plugins'.DS.$name;
        $installer = new JInstaller;
        $result = $installer->install($path);
        
        $query = "UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='".$name."' AND folder='".$group."'";
        $db->setQuery($query);
        $db->query();
        
        
        }    

    
    }
    
    
    /**
	 * method to install the modules
	 *
	 * @return void
	 */
	public function installModules($parent)
	{
  $mainframe = JFactory::getApplication();
  $src = $parent->getParent()->getPath('source');
  $manifest = $parent->getParent()->manifest;
  $db = JFactory::getDBO();
  
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($manifest,true).'</pre>'),'');
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($src,true).'</pre>'),'');
  
  
  $modules = $manifest->xpath('modules/module');
        foreach ($modules as $module)
        {
            $name = (string)$module->attributes()->module;
            $client = (string)$module->attributes()->client;
            
            $position = (string)$module->attributes()->position;
            $published = (string)$module->attributes()->published;
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($client,true).'</pre>'),'');
            
            if (is_null($client))
            {
                $client = 'site';
            }
            $path = $client == 'administrator' ? $src.DS.'admin'.DS.'modules'.DS.$name : $src.DS.'modules'.DS.$name;
            $installer = new JInstaller;
            $result = $installer->install($path);
            
            if ( $position )
            {
                $query = "UPDATE #__modules SET position='".$position."', ordering=99, published=".$published." WHERE module='".$name."' ";
                $db->setQuery($query);
                $db->query();
                if ( $client == 'administrator' )
                {
                $query		 = $db->getQuery(true);
								$query->select('id')->from($db->qn('#__modules'))
									->where($db->qn('module') . ' = ' . $db->q($name));
								$db->setQuery($query);
								$moduleid	 = $db->loadResult();

								$query		 = $db->getQuery(true);
								$query->select('*')->from($db->qn('#__modules_menu'))
									->where($db->qn('moduleid') . ' = ' . $db->q($moduleid));
								$db->setQuery($query);
								$assignments = $db->loadObjectList();
								$isAssigned	 = !empty($assignments);

								if (!$isAssigned)
								{
									$o = (object) array(
											'moduleid'	 => $moduleid,
											'menuid'	 => 0
									);
									$db->insertObject('#__modules_menu', $o);
								}
                
                
                }   
                
            }
        }    
  
  
  

    }
    
    
                  
}
