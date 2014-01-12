<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
/**
 * Script file of com_sportsmanagement component
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
//    self::installModules();
    self::installPlugins();
    self::createImagesFolder();
//    self::migratePicturePath();
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "update":
//    self::installComponentLanguages();
//    self::installModules();
    self::installPlugins();
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
		echo JText::_('Creating new Image Folder structure');
		$dest = JPATH_ROOT.'/images/com_sportsmanagement';
		$update = JFolder::exists($dest);
		$folders = array('agegroups','clubs', 'clubs/large', 'clubs/medium', 'clubs/small', 'clubs/trikot_home', 'clubs/trikot_away','events','leagues','divisions','person_playground',
							'associations','flags_associations','persons', 'placeholders', 'predictionusers','playgrounds', 'projects','projectreferees','projectteams','projectteams/trikot_home', 'projectteams/trikot_away',
              'associations','rosterground','matchreport','seasons','sport_types', 'rounds','teams','flags','teamplayers','teamstaffs','venues', 'statistics');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/index.html');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/index.html');
		
        foreach ($folders as $folder) 
        {
			JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder);
			JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder.'/index.html');
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
		echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
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
	public function installPlugins()
	{
  $mainframe = JFactory::getApplication();
// 	$lang = JFactory::getLanguage(); 
//   $languages = JLanguageHelper::getLanguages('lang_code');
  
  $db = JFactory::getDBO();
/*  
  $query = $db->getQuery(true);
  $type = "language";
  $query->select('a.element');
  $query->from('#__extensions AS a');
  $type = $db->Quote($type);
	$query->where('(a.type = '.$type.')');
	$query->group('a.element');
  $db->setQuery($query);
  $langlist = $db->loadObjectList();
*/
  
//		echo 'Copy Plugin(s) language(s) provided by <a href="https://opentranslators.transifex.com/projects/p/joomleague/">Transifex</a>';
		$src = JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'plugins'.DS.'system';
		$dest = JPATH_SITE.DS.'plugins';
		//$groups = JFolder::folders($src);
 /*   
    foreach ( $langlist as $key )
    {
    echo 'Copy Plugin(s) language( '.$key->element.' ) provided by <a href="https://opentranslators.transifex.com/projects/p/joomleague/">Transifex</a><br />';
		foreach ($groups as $group)
		{
			$plugins = JFolder::folders($src.DS.$group);
			foreach ($plugins as $plugin)
			{
      if ( JFolder::exists($src.DS.$group.DS.$plugin.DS.'language'.DS.$key->element) )
		{
				JFolder::copy($src.DS.$group.DS.$plugin.DS.'language'.DS.$key->element, JPATH_ADMINISTRATOR.DS.'language'.DS.$key->element, '', true);
		echo ' - <span style="color:green">'.JText::_('Success -> '.$group.' - '.$plugin.' - '.$key->element).'</span><br />';
    }
    	}
		}
    }
		//echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
		echo 'Copy Plugin(s)';
		JFolder::copy($src, $dest, '', true);
		echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
*/	
    
    // wenn alles kopiert wurde gleich installieren
    $ordner = JFolder::folders($src);
    foreach ( $ordner as $key => $value)
    {
    JFolder::copy($src.DS.$value, $dest.DS.$value, '', true);    
    }    
    //echo 'ordner<br><pre>'.print_r($ordner,true).'</pre>';
    
    $mainframe->enqueueMessage(JText::_('ordner<br><pre>'.print_r($ordner,true).'</pre>'   ),'');
    
    foreach ( $ordner as $key => $value)
    {
    $query = $db->getQuery(true);
    $query->select('a.extension_id');
  $query->from('#__extensions AS a');
  //$type = $db->Quote($type);
	$query->where("a.type LIKE 'plugin' ");
    $query->where("a.element LIKE '".$value."'");
	
  $db->setQuery($query);
  $install_id = $db->loadResult();    

$mainframe->enqueueMessage(JText::_('install_id<br><pre>'.print_r($install_id,true).'</pre>'   ),'');
$mainframe->enqueueMessage(JText::_('value<br><pre>'.print_r($value,true).'</pre>'   ),'');

if ( $install_id )
{
    
    $installer = JInstaller::getInstance();
    $result = $installer->discover_install($install_id);
    if (!$result)
     {
	$mainframe->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLFAILED').': '. $install_id,'Error');
	}
    else
    {
        $mainframe->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLSUCCESSFUL'));
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->extension_id = $install_id;
        $object->enabled = 1;
        // Update their details in the users table using id as the primary key.
        $result_update = JFactory::getDbo()->updateObject('#__extensions', $object, 'extension_id');
    }
}


/*        
    // Get an installer instance
$installer = JInstaller::getInstance();
// Get the path to the package to install
$p_dir = $src.DS.$value.DS;
// Detect the package type
$type = JInstallerHelper::detectType($p_dir);        


$package['packagefile'] = null;
$package['extractdir'] = null;
$package['dir'] = $p_dir;
$package['type'] = $type;

echo 'package<br><pre>'.print_r($package,true).'</pre>';

// Install the package
		if (!$installer->install($package['dir'])) 
        {
			// There was an error installing the package
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			//$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			//$result = true;
		}
        */
    }
    
    }
    
    
    
    
    
                  
}
