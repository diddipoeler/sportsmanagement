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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if (! defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

jimport('joomla.installer.installer');
 
if(version_compare(JVERSION,'3.0.0','ge')) 
{
jimport('joomla.html.html.bootstrap');
}
        
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
		//$parent->getParent()->setRedirectURL('index.php?option=com_sportsmanagement');
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
	
    /*   
    switch ($type)        
    {
    case "update":
    self::deleteFolders($parent);
    break;
    }
    */
       
       if(version_compare(JVERSION,'3.0.0','ge')) 
        {
            // Define tabs options for version of Joomla! 3.0
        $tabsOptions = array(
            "active" => "tab1_id" // It is the ID of the active tab.
        );  
            $image1 = '<img src="../media/com_sportsmanagement/jl_images/ext_com.png">';
            $image2 = '<img src="../media/com_sportsmanagement/jl_images/ext_mod.png">';
            $image3 = '<img src="../media/com_sportsmanagement/jl_images/ext_plugin.png">';
            $image4 = '<img src="../media/com_sportsmanagement/jl_images/ext_esp.png">';
            ?>
            <!-- This is a list with tabs names. -->
    	<ul class="nav nav-tabs" id="ID-Tabs-Group">
        	<li class="active">
        		<a data-toggle="tab" href="#tab1_id"><?php echo $image1.JText::_(' Component'); ?></a>
        	</li>
        	<li>
        		<a data-toggle="tab" href="#tab2_id"><?php echo $image2.JText::_(' Modules'); ?></a>
    		</li>
            <li>
        		<a data-toggle="tab" href="#tab3_id"><?php echo $image3.JText::_(' Plugins'); ?></a>
    		</li>
            <li>
        		<a data-toggle="tab" href="#tab4_id"><?php echo $image4.JText::_(' Create/Update Images Folders'); ?></a>
    		</li>
            
        </ul>
            
            <?PHP
            echo JHtml::_('bootstrap.startPane', 'ID-Tabs-Group', $tabsOptions);
            echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab1_id'); 
            echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION') .'</h2>';
            echo JHtml::_('bootstrap.endPanel');
 
             
            }
            else
            {
	   echo JHtml::_('sliders.start','steps',array(
						'allowAllClose' => true,
						'startTransition' => true,
						true));
       $image = '<img src="../media/com_sportsmanagement/jl_images/ext_com.png">';
		echo JHtml::_('sliders.panel', $image.' Component', 'panel-component');
        echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION') .'</h2>';
        }                      
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		
        
        
        ?>
		
		<img
			src="../administrator/components/com_sportsmanagement/assets/icons/logo_transparent.png"
			alt="JoomLeague" title="JoomLeague" width="180"/>
		<?php
        $j = new JVersion();
        echo '<h1>' . sprintf(JText::_('COM_SPORTSMANAGEMENT_JOOMLA_VERSION'), $j->getShortVersion() ) .'</h1>';
        ?>
        <img
			src="../media/com_sportsmanagement/jl_images/compat_25.png"
			alt="JSM Joomla Sports Management" title="JSM Joomla Sports Management" width="auto"/>
        <img
			src="../media/com_sportsmanagement/jl_images/compat_30.png"
			alt="JSM Joomla Sports Management" title="JSM Joomla Sports Management" width="auto"/>
        
         <?php       
        echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_PREFLIGHT_' . $type . '_TEXT' ) . $parent->get('manifest')->version . '</p>';
        
        
        
        
        
	}
 
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
	$mainframe = JFactory::getApplication();
    $db = JFactory::getDbo();
    
    // sicherheitshalber 4 dateien löschen, die ich falsch angelegt habe.
    // aber nur wenn sie vorhanden sind
    $file_to_delete = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'fields'.DS.'link.php'; 
    JFile::delete($file_to_delete);
    $file_to_delete = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'fields'.DS.'message.php'; 
    JFile::delete($file_to_delete);
    $file_to_delete = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'fields'.DS.'subtitle.php'; 
    JFile::delete($file_to_delete);
    $file_to_delete = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'models'.DS.'fields'.DS.'title.php'; 
    JFile::delete($file_to_delete);
    
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
        {

            echo '<p>' . JText::_('COM_SPORTSMANAGEMENT_POSTFLIGHT_' . $type . '_TEXT' ) . $parent->get('manifest')->version . '</p>';

$params = JComponentHelper::getParams('com_sportsmanagement');
$xmlfile = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_sportsmanagement'.DS.'config.xml';  
$jRegistry = new JRegistry;
$jRegistry->loadString($params->toString('ini'), 'ini');
////$form =& JForm::getInstance('com_sportsmanagement', $xmlfile, array('control'=> 'params'), false, "/config");
$newparams = array();

/*
$form = JForm::getInstance('com_sportsmanagement', $xmlfile,array('control'=> ''), false, "/config");
$form->bind($jRegistry);
foreach($form->getFieldset($fieldset->name) as $field)
        {
        $newparams[$field->name] = $field->value;
        }
*/


    switch ($type)        
    {
    case "install":
    self::setParams($newparams);
//    self::installComponentLanguages();

echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab2_id'); 

    self::installModules($parent);
    echo JHtml::_('bootstrap.endPanel'); 
    
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab3_id'); 

    self::installPlugins($parent);
    echo JHtml::_('bootstrap.endPanel');
    
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab4_id');  

    self::createImagesFolder();
    echo JHtml::_('bootstrap.endPanel'); 
    
//    self::migratePicturePath();
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "update":
//    self::installComponentLanguages();
echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab2_id');

    self::installModules($parent);
    echo JHtml::_('bootstrap.endPanel'); 
    
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab3_id');  

    self::installPlugins($parent);
    echo JHtml::_('bootstrap.endPanel'); 
    
    echo JHtml::_('bootstrap.addPanel', 'ID-Tabs-Group', 'tab4_id');  

    self::createImagesFolder();
    echo JHtml::_('bootstrap.endPanel');
    
//    self::migratePicturePath();
      self::setParams($newparams);
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "discover_install":
    break;
        
    }
            
            
            echo JHtml::_('bootstrap.endPane', 'ID-Tabs-Group');
            }
            else
            {
//    echo JHtml::_('sliders.start','steps',array(
//						'allowAllClose' => true,
//						'startTransition' => true,
//						true));
//       $image = '<img src="../media/com_sportsmanagement/jl_images/ext_com.png">';
//		echo JHtml::_('sliders.panel', $image.' Component', 'panel-component');
             
    //echo JHtml::_('sliders.start','steps',array(
//						'allowAllClose' => true,
//						'startTransition' => true,
//						true));
                   
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
////$form =& JForm::getInstance('com_sportsmanagement', $xmlfile, array('control'=> 'params'), false, "/config");
$form = JForm::getInstance('com_sportsmanagement', $xmlfile,array('control'=> ''), false, "/config");
$form->bind($jRegistry);
$newparams = array();
foreach($form->getFieldset($fieldset->name) as $field)
        {
        $newparams[$field->name] = $field->value;
        }

switch ($type)        
    {
    case "install":
    self::setParams($newparams);
//    self::installComponentLanguages();
$image = '<img src="../media/com_sportsmanagement/jl_images/ext_mod.png">';
		echo JHtml::_('sliders.panel', $image.' Modules', 'panel-modules');
    self::installModules($parent);
    $image = '<img src="../media/com_sportsmanagement/jl_images/ext_plugin.png">';
		echo JHtml::_('sliders.panel', $image.' Plugins', 'panel-plugins');
    self::installPlugins($parent);
    $image = '<img src="../media/com_sportsmanagement/jl_images/ext_esp.png">';
		echo JHtml::_('sliders.panel', $image.' Create/Update Images Folders', 'panel-images');
    self::createImagesFolder();
//    self::migratePicturePath();
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "update":
//    self::installComponentLanguages();
$image = '<img src="../media/com_sportsmanagement/jl_images/ext_mod.png">';
		echo JHtml::_('sliders.panel', $image.' Modules', 'panel-modules');
    self::installModules($parent);
    $image = '<img src="../media/com_sportsmanagement/jl_images/ext_plugin.png">';
		echo JHtml::_('sliders.panel', $image.' Plugins', 'panel-plugins');
    self::installPlugins($parent);
    $image = '<img src="../media/com_sportsmanagement/jl_images/ext_esp.png">';
		echo JHtml::_('sliders.panel', $image.' Create/Update Images Folders', 'panel-images');
    self::createImagesFolder();
//    self::migratePicturePath();
      self::setParams($newparams);
//    self::deleteInstallFolders();
//    self::sendInfoMail();
    break;
    case "discover_install":
    break;
        
    }

echo JHtml::_('sliders.end');
echo self::getFxInitJSCode('steps');

}

	}
    
    
    public function deleteFolders($parent)
    {
    $mainframe = JFactory::getApplication();
    $excludeExtension = array();
    $excludeExtension[] = 'extensions';    
    $excludeExtension[] = 'sisdata';
    $folderAdmin  = JFolder::folders(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_sportsmanagement',
													'.', false, false, $excludeExtension);
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' folderAdmin<br><pre>'.print_r($folderAdmin,true).'</pre>'),'');                                                    
    
    foreach ($folderAdmin as $key => $value )
    {
        if( JFolder::delete(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_sportsmanagement'.DS.$value) )
        {
        //echo 'Der Ordner wurde gelöscht';
        }
    }
    $folderSite  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement',
													'.', false, false, $excludeExtension);
    //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' folderSite<br><pre>'.print_r($folderSite,true).'</pre>'),'');
    
    foreach ($folderSite as $key => $value )
    {
        if( JFolder::delete(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.$value) )
        {
        //echo 'Der Ordner wurde gelöscht';
        }
    }
    
    }
    
    /**
     * com_sportsmanagementInstallerScript::getFxInitJSCode()
     * 
     * @param mixed $group
     * @return
     */
    private function getFxInitJSCode ($group) 
    {
		$params = array();
		$params['allowAllClose'] = 'true';
		$display = (isset($params['startOffset']) && isset($params['startTransition']) && $params['startTransition'])
		? (int) $params['startOffset'] : null;
		$show = (isset($params['startOffset']) && !(isset($params['startTransition']) && $params['startTransition']))
		? (int) $params['startOffset'] : null;
		$options = '{';
		$opt['onActive'] = "function(toggler, i) {toggler.addClass('pane-toggler-down');" .
				"toggler.removeClass('pane-toggler');i.addClass('pane-down');i.removeClass('pane-hide');Cookie.write('jpanesliders_"
				. $group . "',$$('div#" . $group . ".pane-sliders > .panel > h3').indexOf(toggler));}";
		$opt['onBackground'] = "function(toggler, i) {toggler.addClass('pane-toggler');" .
				"toggler.removeClass('pane-toggler-down');i.addClass('pane-hide');i.removeClass('pane-down');if($$('div#"
				. $group . ".pane-sliders > .panel > h3').length==$$('div#" . $group
				. ".pane-sliders > .panel > h3.pane-toggler').length) Cookie.write('jpanesliders_" . $group . "',-1);}";
		$opt['duration'] = (isset($params['duration'])) ? (int) $params['duration'] : 300;
		$opt['display'] = (isset($params['useCookie']) && $params['useCookie']) ? JRequest::getInt('jpanesliders_' . $group, $display, 'cookie')
		: $display;
		$opt['show'] = (isset($params['useCookie']) && $params['useCookie']) ? JRequest::getInt('jpanesliders_' . $group, $show, 'cookie') : $show;
		$opt['opacity'] = (isset($params['opacityTransition']) && ($params['opacityTransition'])) ? 'true' : 'false';
		$opt['alwaysHide'] = (isset($params['allowAllClose']) && (!$params['allowAllClose'])) ? 'false' : 'true';
		foreach ($opt as $k => $v)
		{
			if ($v)
			{
				$options .= $k . ': ' . $v . ',';
			}
		}
		if (substr($options, -1) == ',')
		{
			$options = substr($options, 0, -1);
		}
		$options .= '}';
		
		$js = "window.addEvent('domready', function(){ new Fx.Accordion($$('div#" . $group
		. ".pane-sliders > .panel > h3.pane-toggler'), $$('div#" . $group . ".pane-sliders > .panel > div.pane-slider'), " . $options
		. "); });";
		
		return '<script>'.$js.'</script>';
	}
    
    /**
     * com_sportsmanagementInstallerScript::createImagesFolder()
     * 
     * @return void
     */
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
        'laender_karten',
		'events',
		'leagues',
        'positions',
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
        'jl_images',
		'statistics');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/index.html');
		JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database');
		JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/index.html');
		
        foreach ($folders as $folder) 
        {
			JFolder::create(JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder);
			JFile::copy(JPATH_ROOT.'/images/index.html', JPATH_ROOT.'/images/com_sportsmanagement/database/'.$folder.'/index.html');
            
            echo '<p>' . JText::_('Imagefolder : ' ) . $folder . ' angelegt!</p>';
            
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
        
        $mainframe = JFactory::getApplication();
        $db = JFactory::getDbo();
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params_array<br><pre>'.print_r($param_array,true).'</pre>'   ),'');
        
                if ( count($param_array) > 0 ) 
                {
                        // read the existing component value(s)
                        $db = JFactory::getDbo();
                        $db->setQuery('SELECT params FROM #__extensions WHERE name = "com_sportsmanagement"');
                        $params = json_decode( $db->loadResult(), true );
                        
                        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params_array<br><pre>'.print_r($param_array,true).'</pre>'   ),'');
                        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params aus db<br><pre>'.print_r($params,true).'</pre>'   ),'');
                        
                        // add the new variable(s) to the existing one(s)
                        foreach ( $param_array as $name => $value ) {
                                $params[ (string) $name ] = (string) $value;
                        }
                        
                        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params neu<br><pre>'.print_r($params,true).'</pre>'   ),'');
                        
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
  //$j = new JVersion();
//  $joomla_version = $j->RELEASE;
  
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($manifest,true).'</pre>'),'');
  //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($src,true).'</pre>'),'');
  
  $plugins = $manifest->xpath('plugins/plugin');
  $plugins3 = $manifest->xpath('plugins3/plugin');
  
  if(version_compare(JVERSION,'3.0.0','ge')) 
        {
  foreach ($plugins3 as $plugin)
        {
        $name = (string)$plugin->attributes()->plugin;
        $group = (string)$plugin->attributes()->group;
        
        echo '<p>' . JText::_('Plugin : ' ) . $name . ' installiert!</p>';
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($group,true).'</pre>'),'');
        
        // Select some fields
        $query = $db->getQuery(true);
        $query->clear();
		$query->select('extension_id');
        // From table
        $query->from('#__extensions');
        $query->where("type = 'plugin' ");
        $query->where("element = '".$name."' ");
        $query->where("folder = '".$group."' ");
        $db->setQuery($query);
        $plugin_id = $db->loadResult();

        switch ( $name )
        {
        case 'jqueryeasy';
        if ( $plugin_id )
        {
            // plugin ist vorhanden
            // wurde vielleicht schon aktualisiert
        }
        else
        {
            // plugin ist nicht vorhanden
            // also installieren
            $path = $src.DS.'plugins'.DS.$name.'_3';
            $installer = new JInstaller;
            $result = $installer->install($path);    
        }    
        break;
        default:    
        $path = $src.DS.'plugins'.DS.$name;
        $installer = new JInstaller;
        $result = $installer->install($path);
        break;
        }
        
        // auf alle faelle einschalten        
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->extension_id = $plugin_id;
        $object->enabled = 1;
        // Update their details in the users table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__extensions', $object, 'extension_id');  
        
        }
  }
  else
  {
  
  foreach ($plugins as $plugin)
        {
        $name = (string)$plugin->attributes()->plugin;
        $group = (string)$plugin->attributes()->group;
        
        echo '<p>' . JText::_('Plugin : ' ) . $name . ' installiert!</p>';
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'');
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($group,true).'</pre>'),'');
        
        // Select some fields
        $query = $db->getQuery(true);
        $query->clear();
		$query->select('extension_id');
        // From table
        $query->from('#__extensions');
        $query->where("type = 'plugin' ");
        $query->where("element = '".$name."' ");
        $query->where("folder = '".$group."' ");
        $db->setQuery($query);
        $plugin_id = $db->loadResult();

        switch ( $name )
        {
        case 'jqueryeasy';
        case 'jw_ts';
        case 'plugin_googlemap3';
        if ( $plugin_id )
        {
            // plugin ist vorhanden
            // wurde vielleicht schon aktualisiert
        }
        else
        {
            // plugin ist nicht vorhanden
            // also installieren
            $path = $src.DS.'plugins'.DS.$name;
            $installer = new JInstaller;
            $result = $installer->install($path);    
        }    
        break;
        default:    
        $path = $src.DS.'plugins'.DS.$name;
        $installer = new JInstaller;
        $result = $installer->install($path);
        break;
        }
        
        // auf alle faelle einschalten        
        // Create an object for the record we are going to update.
        $object = new stdClass();
        // Must be a valid primary key value.
        $object->extension_id = $plugin_id;
        $object->enabled = 1;
        // Update their details in the users table using id as the primary key.
        $result = JFactory::getDbo()->updateObject('#__extensions', $object, 'extension_id');  
        
        }
        
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
            
            echo '<p>' . JText::_('Modul : ' ) . $name . ' installiert!</p>';
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($client,true).'</pre>'),'');
            
            if (is_null($client))
            {
                $client = 'site';
            }
            $path = $client == 'administrator' ? $src.DS.'admin'.DS.'modules'.DS.$name : $src.DS.'modules'.DS.$name;
            $installer = new JInstaller;
            $result = $installer->install($path);
            $ordering = '99';
            
            if( $client == 'administrator' )
			{
				$position = version_compare(JVERSION, '3.0', '<') && $name == 'mod_sportsmanagement_quickicon' ? 'icon' : 'cpanel';
                $ordering = '1';
            }    
            
            if ( $position )
            {
                $query = "UPDATE #__modules SET position='".$position."', ordering=".$ordering.", published=".$published." WHERE module='".$name."' ";
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
