<?php
$mandat = $sf_data->getRaw('mandatory');
$ticket = $sf_data->getRaw('others');
$manif = $sf_data->getRaw('manif');
$gauge = $sf_data->getRaw('gauge');
$transaction = $sf_data->getRaw('transaction');

?>
<ul id="tabList" class = "ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header" role = "tablist">
    <li class = "ui-state-default ui-tabs-active ui-state-active ui-corner-all" role = "tab" tabindex = "0" aria-controls = "sf_fieldset_mandatory" aria-labelledby = "ui-id-1" aria-selected = "true">
        <a href = "#sf_fieldset_mandatory" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-1">Mandatory</a>
    </li>
    <li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "-1" aria-controls = "sf_fieldset_ticket" aria-labelledby = "ui-id-2" aria-selected = "false">
        <a href = "#sf_fieldset_ticket" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-2">Ticket</a>
    </li>
    <li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "-1" aria-controls = "sf_fieldset_manifestation" aria-labelledby = "ui-id-3" aria-selected = "false">
        <a href = "#sf_fieldset_manifestation" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-3">Manifestation</a>
    </li>
    <li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "-1" aria-controls = "sf_fieldset_transaction" aria-labelledby = "ui-id-4" aria-selected = "false">
        <a href = "#sf_fieldset_transaction" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-4">Transaction</a>
    </li>
    <li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "-1" aria-controls = "sf_fieldset_gauge" aria-labelledby = "ui-id-5" aria-selected = "false">
        <a href = "#sf_fieldset_gauge" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-4">Gauge</a>
    </li>
    <li class = "ui-state-default ui-corner-all" role = "tab" tabindex = "-1" aria-controls = "sf_fieldset_others" aria-labelledby = "ui-id-6" aria-selected = "false">
        <a href = "#sf_fieldset_others" class = "ui-tabs-anchor" role = "presentation" tabindex = "-1" id = "ui-id-4">Others</a>
    </li>
</ul>
<div id="sf_fieldset_mandatory" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
    
    <?php 
    foreach ($mandat as $value) {
            //echo '<div class="">';
            echo '<input id="'.$value.'" class="addLabelButton checked" type="button" value="'.$value.'"  name="'.$value.'" disabled>';
            //echo '</div>';
        }
    ?>
    
</div>

<div id="sf_fieldset_ticket" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
    
    <?php 
    foreach ($others as $value) {
            //echo '<div class="">';
            echo '<input id="'.$value.'" class="addLabelButton draggable" type="button" value="'.$value.'">';
            //echo '</div>';
        }
    ?>
    
</div>

<div id="sf_fieldset_manifestation" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
   
   
    <?php 
    foreach ($manif as $value) {
            //echo '<div class="">';
            echo '<input id="'.$value.'" class="addLabelButton draggable" type="button" value="'.$value.'">';
            //echo '</div>';
        }
    ?>

</div>

<div id="sf_fieldset_transaction" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
    
    <?php 
    foreach ($transaction as $value) {
            //echo '<div class="">';
            echo '<input id="'.$value.'" class="addLabelButton draggable" type="button" value="'.$value.'">';
            //echo '</div>';
        }
    ?>
    
</div>

<div id="sf_fieldset_gauge" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
    
    <?php 
    foreach ($gauge as $value) {
            //echo '<div class="">';
            echo '<input id="'.$value.'" class="addLabelButton draggable" type="button" value="'.$value.'">';
            //echo '</div>';
        }
    ?>
    
</div>

<div id="sf_fieldset_others" class="scrolled ui-corner-all ui-tabs-panel ui-widget-content ui-corner-bottom" aria-labelledby="ui-id-1" role="tabpanel" aria-expanded="true" aria-hidden="false">
    <h3>empty</h3>
</div>

