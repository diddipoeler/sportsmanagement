<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage extensions
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
 

/**
 * sportsmanagementViewextensions
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementViewextensions extends sportsmanagementView
{
	/**
	 *  view display method
	 * @return void
	 */
	public function init ()
	{
        $params = ComponentHelper::getParams( $this->option );
        $this->sporttypes = $params->get( 'cfg_sport_types' );
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{ 
        $stylelink = '<link rel="stylesheet" href="'.Uri::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        ToolbarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=cpanel');
		$canDo = sportsmanagementHelper::getActions();
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_MANAGER'), 'extensions');
		if ($canDo->get('core.admin')) 
		{
			ToolbarHelper::divider();           
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
		$this->document->setTitle(Text::_('COM_SPORTSMANAGEMENT_ADMIN_EXTENSIONS'));
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
		$lang		= Factory::getLanguage();
		$newWindow	= ( $newWindow ) ? ' target="_blank"' : '';
?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $url; ?>"<?php echo $newWindow; ?>>
					<?php echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/icons/' . $image , NULL, NULL ); ?>
					<span><?php echo $text; ?></span></a>
			</div>
		</div>
<?php
	}
	
}
