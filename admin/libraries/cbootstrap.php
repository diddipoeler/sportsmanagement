<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
 * @version         1.0.05
 * @file                agegroup.php
 * @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
 * SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
 * der GNU General Public License, wie von der Free Software Foundation,
 * Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
 * ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
 *
 * SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
 * OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License f�r weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

/**
 * @package		Twitter Bootstrap Integration
 * @subpackage	com_cbootstrap
 * @copyright	Copyright (C) 2012 Conflate. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link		http://www.conflate.nl
 */

defined('_JEXEC') or die('Restricted access');


/**
 * CBootstrap
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class CBootstrap
{

    private $_errors;
    private static $_actions;

    /**
     * CBootstrap::__construct()
     * 
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * CBootstrap::load()
     * 
     * @return void
     */
    public static function load()
    {
        $doc = JFactory::getDocument();

        if (version_compare(JVERSION, '3.0.0', 'ge')) {
            // Joomla! 3.0 code here
            JFactory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js');
            //JFactory::getDocument()->addScript('http://getbootstrap.com/2.3.2/assets/js/bootstrap-tab.js');
            JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
            JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css');
        } elseif (version_compare(JVERSION, '2.5.0', 'ge')) {
            // Joomla! 2.5 code here
            JFactory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js');
            //JFactory::getDocument()->addScript('http://getbootstrap.com/2.3.2/assets/js/bootstrap-tab.js');
            JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
            JFactory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css');

        } elseif (version_compare(JVERSION, '1.7.0', 'ge')) {
            // Joomla! 1.7 code here
        } elseif (version_compare(JVERSION, '1.6.0', 'ge')) {
            // Joomla! 1.6 code here
        } else {
            // Joomla! 1.5 code here
        }

    }

}
