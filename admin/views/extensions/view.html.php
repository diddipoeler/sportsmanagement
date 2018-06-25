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
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
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
 
// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 *  View
 */
class sportsmanagementViewextensions extends sportsmanagementView
{
	/**
	 *  view display method
	 * @return void
	 */
	public function init ()
	{
		//$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication();
        
        $params = JComponentHelper::getParams( $this->option );
        $this->sporttypes = $params->get( 'cfg_sport_types' );
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($this->sporttypes,true).'</pre>'),'');
 
//		// Set the toolbar
//		$this->addToolBar();
// 
//		// Display the template
//		parent::display($tpl);
// 
//		// Set the document
//		$this->setDocument();
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{ 
  		// Get a refrence of the page instance in joomla
	$document	= JFactory::getDocument();
    $option = JFactory::getApplication()->input->getCmd('option');
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		$canDo = sportsmanagementHelper::getActions();
		JToolbarHelper::title(JText::_('COM_SPORTSMANAGEMENT_MANAGER'), 'extensions');
		if ($canDo->get('core.admin')) 
		{
			JToolbarHelper::divider();           
			//JToolbarHelper::preferences($option);
		}
        parent::addToolbar();
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_EXTENSIONS'));
	}
	
	/**
	 * sportsmanagementViewextensions::addIcon()
	 * 
	 * @param mixed $image
	 * @param mixed $url
	 * @param mixed $text
	 * @param bool $newWindow
	 * @return void
	 */
	public function addIcon( $image , $url , $text , $newWindow = false )
	{
		$lang		= JFactory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo JHtml::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
	
}
