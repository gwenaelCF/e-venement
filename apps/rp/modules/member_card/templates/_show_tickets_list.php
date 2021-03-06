<?php use_helper('Number') ?>
<?php
  $tickets = new Doctrine_Collection('Ticket');
  foreach ( $member_card->Tickets as $ticket )
  if ( is_null($ticket->duplicating) && ($ticket->printed_at || $ticket->integrated_at || !is_null($ticket->cancelling)) )
    $tickets[] = $ticket;
?>
<?php if ( $tickets->count() > 0 ): ?>
<?php $value = $nb = 0 ?>
<div class="sf_admin_form_row">
  <label><?php echo __('List of tickets') ?>:</label>
  <table class="tickets_list ui-widget ui-corner-all ui-widget-content">
  <tbody>
  <?php foreach ( $tickets as $ticket ): ?>
    <tr>
      <td class="transation_id">#<?php echo cross_app_link_to($ticket->transaction_id,'tck','transaction/edit?id='.$ticket->transaction_id) ?></td>
      <td class="ticket_id">#<?php echo cross_app_link_to($ticket->id, 'tck', 'ticket/show?id='.$ticket->id) ?></td>
      <td class="price_name"><?php echo $ticket->price_name ?></td>
      <td class="ticket_value"><?php echo format_currency($ticket->value,$sf_context->getConfiguration()->getCurrency()); $value += $ticket->value; $nb += is_null($ticket->cancelling)*2-1 ?></td>
      <td class="ticket_manifestation"><?php echo cross_app_link_to($ticket->Manifestation,'event','manifestation/show?id='.$ticket->manifestation_id) ?></td>
    </tr>
  <?php endforeach ?>
  </tbody>
  <tfoot>
    <tr>
      <td class="transation_id"></td>
      <td class="ticket_id" colspan="2"><?php echo __('%%nb%% ticket(s)',array('%%nb%%' => $nb)) ?></td>
      <td class="ticket_value"><?php echo format_currency($value,$sf_context->getConfiguration()->getCurrency()) ?></td>
      <td class="ticket_manifestation"></td>
    </tr>
  </tfoot>
  </table>
</div>
<?php endif ?>
