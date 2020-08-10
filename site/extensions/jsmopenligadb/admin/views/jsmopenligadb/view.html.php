<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsmopenligadb
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * 60  header icons
 * 61  .icon-48-generic         { background-image: url(../images/header/icon-48-generic.png); }
 * 62  .icon-48-checkin         { background-image: url(../images/header/icon-48-checkin.png); }
 * 63  .icon-48-cpanel         { background-image: url(../images/header/icon-48-cpanel.png); }
 * 64  .icon-48-config         { background-image: url(../images/header/icon-48-config.png); }
 * 65  .icon-48-module         { background-image: url(../images/header/icon-48-module.png); }
 * 66  .icon-48-menu             { background-image: url(../images/header/icon-48-menu.png); }
 * 67  .icon-48-menumgr         { background-image: url(../images/header/icon-48-menumgr.png); }
 * 68  .icon-48-trash         { background-image: url(../images/header/icon-48-trash.png); }
 * 69  .icon-48-user             { background-image: url(../images/header/icon-48-user.png); }
 * 70  .icon-48-inbox         { background-image: url(../images/header/icon-48-inbox.png); }
 * 71  .icon-48-msgconfig     { background-image: url(../images/header/icon-48-message_config.png); }
 * 72  .icon-48-langmanager { background-image: url(../images/header/icon-48-language.png); }
 * 73  .icon-48-mediamanager{ background-image: url(../images/header/icon-48-media.png); }
 * 74  .icon-48-plugin     { background-image: url(../images/header/icon-48-plugin.png); }
 * 75  .icon-48-help_header { background-image: url(../images/header/icon-48-help_header.png); }
 * 76  .icon-48-impressions { background-image: url(../images/header/icon-48-stats.png); }
 * 77  .icon-48-browser         { background-image: url(../images/header/icon-48-stats.png); }
 * 78  .icon-48-searchtext     { background-image: url(../images/header/icon-48-stats.png); }
 * 79  .icon-48-thememanager{ background-image: url(../images/header/icon-48-themes.png); }
 * 80  .icon-48-massemail     { background-image: url(../images/header/icon-48-massemail.png); }
 * 81  .icon-48-frontpage     { background-image: url(../images/header/icon-48-frontpage.png); }
 * 82  .icon-48-sections     { background-image: url(../images/header/icon-48-section.png); }
 * 83  .icon-48-addedit         { background-image: url(../images/header/icon-48-article-add.png); }
 * 84  .icon-48-article         { background-image: url(../images/header/icon-48-article.png); }
 * 85  .icon-48-categories     { background-image: url(../images/header/icon-48-category.png); }
 * 86  .icon-48-install         { background-image: url(../images/header/icon-48-extension.png); }
 * 87  .icon-48-dbbackup        { background-image: url(../images/header/icon-48-backup.png); }
 * 88  .icon-48-dbrestore     { background-image: url(../images/header/icon-48-dbrestore.png); }
 * 89  .icon-48-dbquery         { background-image: url(../images/header/icon-48-query.png); }
 * 90  .icon-48-systeminfo     { background-image: url(../images/header/icon-48-info.png); }
 * 91  .icon-48-massemail     { background-image: url(../images/header/icon-48-massmail.png); }
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewjsmopenligadb
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewjsmopenligadb extends sportsmanagementView
{


	/**
	 * sportsmanagementViewjsmopenligadb::init()
	 * 
	 * @return void
	 */
	function init()
	{

		$this->projectid = $this->jinput->get("pid", '0');

		if (!$this->projectid)
		{
			$this->projectid = $this->app->getUserState("$this->option.pid", '0');
		}
        $this->projectlink = $this->model->getMatchLink($this->projectid);
        $this->getdata = $this->model->getdata($this->projectlink);
        
   }
   
   /**
    * sportsmanagementViewjsmopenligadb::addToolbar()
    * 
    * @return void
    */
   protected function addToolbar()
	{
$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JSMOPENLIGADB_TITLE');
ToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=projects'); 
		if ($this->projectid)
		{
			ToolBarHelper::save('jsmopenligadb.getmatches', 'COM_SPORTSMANAGEMENT_JSMOPENLIGADB_GET_MATCHES');
		}
	   parent::addToolbar();
	}
        
        
}
