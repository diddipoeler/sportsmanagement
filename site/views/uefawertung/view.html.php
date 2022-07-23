<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * sportsmanagementViewuefawertung
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2017
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewuefawertung extends sportsmanagementView
{

	/**
	 * sportsmanagementViewuefawertung::init()
	 *
	 * @return void
	 */
	function init()
	{
		
		$config = sportsmanagementModelProject::getTemplateConfig('uefawertung');

		$this->project       = sportsmanagementModelProject::getProject();
		$this->overallconfig = sportsmanagementModelProject::getOverallConfig();
		$this->config        = $config;


/** Build the html options for coefficientyears */
$coefficientyears[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON'));
$res = $this->model->getcoefficientyears();
$coefficientyears = array_merge($coefficientyears, $res);
$lists['coefficientyears'] = HTMLHelper::_(
			'select.genericList',
			$coefficientyears,
			'coefficientyear',
			'class="inputbox" onChange="this.form.submit();" style="width:120px"',
			'id',
			'name',
			$this->jinput->post->getString('coefficientyear', '')
		);


		$this->document->setTitle($this->pagetitle);
        $this->lists         = $lists;

	}

}
