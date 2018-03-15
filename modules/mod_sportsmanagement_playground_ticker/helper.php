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
 * OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
 * Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
 * Siehe die GNU General Public License für weitere Details.
 *
 * Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
 * Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
 *
 * Note : All ini files need to be saved as UTF-8 without BOM
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


/**
 * modJSMPlaygroundTicker
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class modJSMPlaygroundTicker
{

    /**
     * modJSMPlaygroundTicker::getData()
     * 
     * @param mixed $params
     * @return void
     */
    public static function getData($params)
    {
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $cfg_which_database = JRequest::getInt('cfg_which_database', 0);
        // Get a db connection.
        $db = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
        // Create a new query object.
        $query = $db->getQuery(true);

        /**
         * Nun möchte man aber manchmal mehrere Datensätze zufällig selektieren und nicht nur einen. 
         * Zuerst wird die Gesamtanzahl an Datensätzen bestimmt, die die Bedingungen erfüllt.
         * Anschließend müssen x Zufallszahlen gebildet werden. Und mit diesen wird dann eine SQL-Abfrage mit UNIONs gebaut.
         */
        // Select some fields
        $query->select('count( * )');
        // From table
        $query->from('#__sportsmanagement_playground');
        $db->setQuery($query);
        $anz_cnt = $db->loadResult();

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' anz_cnt<pre>'.print_r($anz_cnt,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<pre>'.print_r($params,true).'</pre>'),'');

        /**
         * Die Schleife beim Erhalten der Zufallszahlen ist deshalb eine while- und keine for-Schleife, weil es sonst passieren kann,
         * dass es zwar mehr als x Datensätze gibt, die die Bedingungen erfüllen, aber dummerweise 2 mal die gleiche Zufallszahl ermittelt wird.
         * Die Bedingung $anz_cnt>count($rands) dient dazu, dass keine Endlosschleife entsteht, wenn weniger als x Datensätze die Bedingung erfüllen.
         * Bei der abschließenden Abfrage wird UNION ALL benutzt statt UNION, damit MySQL die Einzelergebnisse nicht noch versucht zu gruppieren 
         * wir wissen ja durch die while-Schleife bereits, dass keine Duplikate selektiert werden können). UNION bedeutet nämlich in Wirklichkeit UNION DISTINCT.
         */
        $rands = array();
        $x = $params->get('limit', 1);
        while (count($rands) < $x && $anz_cnt > count($rands)) {
            $rand = mt_rand(0, $anz_cnt - 1);
            if (!isset($rands[$rand]))
                $rands[$rand] = $rand;
        }

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' rands<pre>'.print_r($rands,true).'</pre>'),'');

        $queryparts = array();
        foreach ($rands as $rand)
            $queryparts[] = "SELECT * FROM #__sportsmanagement_playground LIMIT " . $rand .
                ",1";

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' queryparts<pre>'.print_r($queryparts,true).'</pre>'),'');

        $query = "(" . implode(") UNION ALL (", $queryparts) . ")";
        $db->setQuery($query);
        $result = $db->loadObjectList();

        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' result<pre>'.print_r($result,true).'</pre>'),'');
        return $result;

    }

}
