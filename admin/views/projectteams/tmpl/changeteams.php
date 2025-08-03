<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteams
 * @file       changeteams.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * https://codepen.io/johnbocook/pen/vZoZpK
 * https://cdnjs.com/libraries/select2
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

?>
<!--
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
-->

<!--
<script>
$(document).ready(function() {
    var $progControl = $(".progControlSelect2").select2({
       // placeholder: "Neue Mannschaft",
      //maximumSelectionLength: 2
    });
    $(".AGSPlan").on("click", function() {
        $progControl.val(["aa", "ac", "ae"]).trigger("change");
    });
    $(".TradPlan").on("click", function() {
        $progControl.val(["aa", "ab", "af"]).trigger("change");
    });
    $(".RegStudent").on("click", function() {
        $progControl.val(["ag", "ah", "ai", "aj"]).trigger("change");
    });
    $(".Other").on("click", function() {
        $progControl.val(["ak"]).trigger("change");
    });
    $(".clearSelect2").on("click", function() {
        $progControl.val(null).trigger("change");
    });
    $(".Submit").on("click", function(event) {
        alertify.alert(
            '<strong class="purple">Silly guy</strong>, This is a fake button.'
        );
    });

    alertify.defaults = {
        glossary: {
            title: "Nothing to see here...",
            ok: "I AM a silly guy"
        },
        theme: {
            input: "ajs-input",
            ok: "ajs-ok",
            cancel: "ajs-cancel"
        }
    };

    //Disable select open on option remove
    $("select").on("select2:unselect", function (evt) {
        if (!evt.params.originalEvent) {
            return;
        }
        evt.params.originalEvent.stopPropagation();
    });


});
</script>
-->


<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />  -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/alertify.min.css'>         -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/default.min.css'>      -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/semantic.min.css'>        -->
<!-- <link rel='stylesheet' href='//cdn.jsdelivr.net/alertifyjs/1.10.0/css/themes/bootstrap.min.css'>          -->

<!--
<style>
.container {
    //font-size: 20px;
    //width: 1000px;
}
.title {
    font-size: 16px;
    background: black;
    width: 100%;
    margin: 0;
    padding: 8px;
    color: white;
    text-align: center;
}
.content {
    padding-top: 20px;
}
.full {
    text-align: center;
}
.bg {
    padding: 15px;
    background: #bfe6e4;
}
input[type="button"] {
    display: block;
    width: 200px;
    margin-bottom: 4px;
    cursor: pointer;
}
.btn-success {
    color: #fff;
    background-color: #246b24;
    border-color: #1e5f1e;
}

.btn.btn-default.btn-sm.Other {
    border: 1px solid #292b2c;
    border: 1px solid rgba(0, 0, 0, 0.17);
    background: #464646;
    color: white;
}
.btn.btn-default.btn-sm.Other:hover {
    background: #333333;
}
input.clearSelect2, input.Submit {
    margin-top: 15px;
    width: 99px;
    display: inline-block;
}
.clearSelect2 {
    background: red;
}
.progControlSelect2 {
    width: 350px;
}
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    border-radius: 0;
}
.purple {
    color: #980798;
}
.select2-selection__choice__remove {
    color: #5a5a5a !important;
    position: relative;
    top: -1px;
    font-size: 13px;
}
.ajs-button {
    cursor: pointer;
}
@media (min-width: 576px)
_grid.scss:24
.container {
    width: 100%;
    max-width: 100%;
}
</style> -->



<?php
/** Some CSS */
/**
        $this->document->addStyleDeclaration(
            '
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
        );
*/
        // String $opt - second parameter of formbehavior2::select2
        // for details http://ivaynberg.github.io/select2/

/**
        $opt = ' allowClear: true,
   width: "50%",

   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = teampicture[state.id];
   if (!state.id)
   return state.text;

   },

   escapeMarkup: function(m) { return m; }
';
*/

/**
if (version_compare( substr(JVERSION, 0, 3), '5.0', 'ge'))
{
HTMLHelper::_('formbehavior.chosen', '.test1', $opt);
}
else
{
HTMLHelper::_('formbehavior2.select2', '.test1', $opt);
}
*/


?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<div class="container">
<div class="row bg content">

<div class="col-md-6 bg">
<legend>
        <?php
        echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGEASSIGN_TEAMS');
        ?>
    </legend>
   </div>

<div class="col-md-3 bg">
<button type="button" onclick="Joomla.submitform('projectteam.storechangeteams', this.form)">
        <?php echo Text::_('JSAVE'); ?></button>
   </div>

<div class="col-md-3 bg">
    <button id="cancel" type="button" onclick="Joomla.submitform('projectteam.cancelmodal', this.form)">
        <?php echo Text::_('JCANCEL'); ?></button>


  </div>
  </div>

<div class="row bg content">

<div class="col-md-2 bg">
  <?PHP echo Text::_(''); ?>
            </div>
            <div class="col-md-2 bg">
              <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?>
            </div>
            <div class="col-md-4 bg">
              <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM'); ?>
            </div>
            <div class="col-md-4 bg">
              <?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM'); ?>
            </div>

  </div>


  <div class="row bg content">

    <div class="col-md-12 bg">




    <!-- <table class="<?php echo $this->table_data_class; ?>"> -->
      <!--
        <thead>
        <tr>
            <th class="title"><?PHP echo Text::_(''); ?>
            </th>
            <th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_CHANGE'); ?>
            </th>
            <th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_OLD_TEAM'); ?>
            </th>
            <th class="title"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_SELECT_NEW_TEAM'); ?>
            </th>
        </tr>
        </thead>
-->
        <?PHP

        // $lfdnummer = 1;
        $k = 0;
        $i = 0;

        foreach ($this->projectteam as $row)
        {


            $checked       = HTMLHelper::_('grid.id', 'oldteamid' . $i, $row->id, $row->checked_out, 'oldteamid');
            $append        = ' style="background-color:#bbffff"';
            $inputappend   = '';
            $selectedvalue = 0;
            ?>
      <div class="row bg content">
            <!-- <tr class="<?php echo "row$k"; ?>"> -->
                <div class="col-md-2 bg" >
                  <?php	echo $i; ?>
                </div>
                <div class="col-md-2 bg" >
                  <?php	echo $checked; ?>
                </div>
                <div class="col-md-4 bg">
                  <?php	echo $row->name; ?>
                </div>

                <div class="col-md-4 bg"  id="newteamid<?php echo $row->id; ?>" name="newteamid[<?php echo $row->id; ?>]" >

                  <!--
                <select class="progControlSelect2" multiple="" style="width: 200px;">
                <option value="1">India</option>
                <option value="2">Japan</option>
                <option value="3">France</option>
            </select>
 -->

                  <?php
          /**
echo JHtmlSelect::genericlist(
                $this->lists['all_teams'], 'project_teamslist_name[]',
                ' id="project_teamslist_name" style="" class="progControlSelect2" multiple="" size="1"',
                'value',
                'text'
            );
          */
//echo HTMLHelper::_('select.genericlist', $this->lists['all_teams'], 'newteamid[' . $row->id . ']', $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cboldteamid' . $i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue);
echo HTMLHelper::_('select.genericlist', $this->lists['all_teams'], 'newteamid[' . $row->id . ']', $inputappend . 'class="progControlSelect2 form-control form-control-inline" size="1" onchange="document.getElementById(\'cboldteamid' . $i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue);
                    ?>
                </div>

      </div>
            <!-- </tr> -->
            <?php
            $i++;
            $k = (1 - $k);
        }
        ?>
   <!-- </table> -->

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="option" value="com_sportsmanagement"/>
    <?php echo HTMLHelper::_('form.token') . "\n"; ?>

</div>
    </div>
  </div>

</form>
