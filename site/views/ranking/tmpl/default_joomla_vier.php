<?PHP
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_joomla_vier.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
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

echo JHtml::_('bootstrap.' . $this->startPane, 'ID-Tabs-Group', $tabsOptions);
echo JHtml::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab1_id', JText::_($this->config['table_text_1']));
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
echo JHtml::_('bootstrap.' . $this->endPanel);
echo JHtml::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab2_id', JText::_($this->config['table_text_2']));
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
echo JHtml::_('bootstrap.' . $this->endPanel);
echo JHtml::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab3_id', JText::_($this->config['table_text_3']));
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
echo JHtml::_('bootstrap.' . $this->endPanel);
echo JHtml::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab4_id', JText::_($this->config['table_text_4']));
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
echo JHtml::_('bootstrap.' . $this->endPanel);
echo JHtml::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab5_id', JText::_($this->config['table_text_5']));
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
echo JHtml::_('bootstrap.' . $this->endPanel);
echo JHtml::_('bootstrap.' . $this->endPane, 'ID-Tabs-Group');
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
