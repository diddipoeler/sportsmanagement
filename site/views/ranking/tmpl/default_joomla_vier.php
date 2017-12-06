<?PHP 
/** Joomla Sports Management ein Programm zur Verwaltung für alle Sportarten
* @version 1.0.26
* @file		components/sportsmanagement/views/ranking/tmpl/default.php
* @author diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license This file is part of Joomla Sports Management.
*
* Joomla Sports Management is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Joomla Sports Management is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Joomla Sports Management. If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von Joomla Sports Management.
*
* Joomla Sports Management ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* Joomla Sports Management wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.switcher');
JHtml::_('behavior.modal');

$this->startPane = 'startTabSet';
$this->endPane = 'endTabSet';
$this->addPanel = 'addTab';
$this->endPanel = 'endTab';
$this->config['table_class'] = 'table table-striped';
// Define tabs options for version of Joomla! 4.0
        $tabsOptions = array(
            "active" => "tab1_id" // It is the ID of the active tab.
			); 
// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo $this->loadTemplate('projectheading');

echo JHtml::_('bootstrap.'.$this->startPane, 'ID-Tabs-Group', $tabsOptions);
echo JHtml::_('bootstrap.'.$this->addPanel, 'ID-Tabs-Group', 'tab1_id',JText::_($this->config['table_text_1']) ); 
//echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION_1') .'</h2>';
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('ranking');
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.'.$this->endPanel);
echo JHtml::_('bootstrap.'.$this->addPanel, 'ID-Tabs-Group', 'tab2_id',JText::_($this->config['table_text_2'])); 
//echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION_2') .'</h2>';
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('ranking_home');
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.'.$this->endPanel);
echo JHtml::_('bootstrap.'.$this->addPanel, 'ID-Tabs-Group', 'tab3_id',JText::_($this->config['table_text_3'])); 
//echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION_3') .'</h2>';
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('ranking_away');
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.'.$this->endPanel);
echo JHtml::_('bootstrap.'.$this->addPanel, 'ID-Tabs-Group', 'tab4_id',JText::_($this->config['table_text_4'])); 
//echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION_4') .'</h2>';
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('ranking_first');
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.'.$this->endPanel);
echo JHtml::_('bootstrap.'.$this->addPanel, 'ID-Tabs-Group', 'tab5_id',JText::_($this->config['table_text_5'])); 
//echo '<h2>' . JText::_('COM_SPORTSMANAGEMENT_DESCRIPTION_5') .'</h2>';
?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('ranking_second');
?>
</div>
</div>
<?PHP
echo JHtml::_('bootstrap.'.$this->endPanel);
echo JHtml::_('bootstrap.'.$this->endPane, 'ID-Tabs-Group');

?>
<div class="container">
<div class="row">
<?PHP
echo $this->loadTemplate('backbutton');
echo $this->loadTemplate('footer');
?>
</div>
</div>
<?PHP
	
?>
