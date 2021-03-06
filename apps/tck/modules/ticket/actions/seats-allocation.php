<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $this->getContext()->getConfiguration()->loadHelpers(array('I18N','Url'));
  $urls = array(
    'order'   => url_for('ticket/order?id='.$request->getParameter('id')),
    'print'   => url_for('ticket/print?id='.$request->getParameter('id')),
    'partial' => url_for('ticket/partial?id='.$request->getParameter('id').'&gauge_id='.$request->getParameter('gauge_id')),
    'integrate' => url_for('ticket/integrate?id='.$request->getParameter('id')),
    'close'   => 'javascript: window.close();',
  );
  $this->url_next = $urls[$request->getParameter('type', 'print')];
  
  $q = Doctrine::getTable('Transaction')->createQuery('t')
    ->leftJoin('m.Location l')
    ->leftJoin('l.SeatedPlans sp')
    ->leftJoin('tck.Gauge g')
    ->leftJoin('g.Workspace ws')
    ->leftJoin('m.Event e')
    ->andWhere('t.id = ?',$request->getParameter('id',0))
  ;
  $backup = $q->copy();
  $q->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.id NOT IN (SELECT tt.duplicating FROM ticket tt  WHERE tt.duplicating IS NOT NULL AND tt.transaction_id = t.id)')
    ->andWhere('tck.id NOT IN (SELECT ttt.cancelling FROM ticket ttt LEFT JOIN ttt.Transaction tttr WHERE ttt.cancelling IS NOT NULL AND tttr.transaction_id = t.id)')
    ->andWhere('tck.gauge_id = ?',$request->getParameter('gauge_id',0))
    //->andWhere('tck.seat_id IS NULL OR tck.price_id IS NULL')
    ->orderBy('tck.price_name');
  if ( $request->getParameter('toprint',false) && is_array($request->getParameter('toprint')) )
    $q->andwhereIn('tck.id', $request->getParameter('toprint'));
  $this->transaction = $q->select('t.*, tck.*, m.*, l.*, sp.*')->fetchOne();
  
  if ( !$this->transaction )
  {
    // WIPS
    $this->transaction = $backup->fetchOne();
    if ( !$this->transaction )
      throw new liSeatedPlanException('No transaction can be found to seat tickets with this id: '.$request->getParameter('id',0));
    
    // get the gauge w/o ticket
    $this->gauge = Doctrine::getTable('Gauge')->createQuery('g', false)
      ->leftJoin('g.Manifestation m')
      ->leftJoin('m.Event e')
      ->leftJoin('e.Translation et')
      ->leftJoin('m.Location l')
      ->andWhere('g.id = ?', $request->getParameter('gauge_id',0))
      ->fetchOne()
    ;
    
    $this->manifestation = $this->gauge->Manifestation;
    $this->seated_plan = $this->manifestation->Location
      ->getWorkspaceSeatedPlan($this->gauge->workspace_id);
  }
  else
  {
    // the seated plan
    $sample_ticket = $this->transaction->Tickets[0];
    $this->seated_plan = $sample_ticket->Manifestation->Location->getWorkspaceSeatedPlan($this->transaction->Tickets[0]->Gauge->workspace_id);
    $this->gauge = $sample_ticket->Gauge;
    $this->manifestation = $sample_ticket->Manifestation;
  }
  
  if ( $request->hasParameter('add_tickets') )
  {
    /* REMOVED because it's more ergonomic without this restriction - Brest Métropole 2015-01-06
    // artificially remove price'd tickets (WARNING: DO NOT SAVE THE TRANSACTION IN THIS ACTION !!)
    foreach ( $this->transaction->Tickets as $key => $ticket )
    if ( $ticket->price_id )
      unset($this->transaction->Tickets[$key]);
    */
    
    // add "fake" tickets to seat them before giving them a price
    for ( $i = 0 ; $i < 10 ; $i++ )
    {
      $ticket = $this->transaction->Tickets[$i+$this->transaction->Tickets->count()];
      $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
      $ticket->Gauge = $this->gauge;
    }
  }
  
  // error
  if ( ! $this->transaction instanceof Transaction )
  {
    $this->getUser()->setFlash('error','An error occured. Please try again.');
    $this->redirect($request->getReferer());
  }
  
  // if no plan available, try again the previous screen (RISK OF LOOPHOLES...)
  if ( !$this->seated_plan )
    $this->redirect($request->getReferer());
