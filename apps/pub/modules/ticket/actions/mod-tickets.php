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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $this->debug($request);
  $this->json = array('success' => array(), 'error' => array());
  
  // given tickets data
  $tmp = $request->getParameter('tickets', array());
  $data = array();
  if ( $tmp && is_array($tmp) )
  foreach ( $tmp as $tck )
    $data[isset($tck['ticket_id']) ? $tck['ticket_id'] : 'new-'.count($data)] = $tck;
  
  $q = Doctrine::getTable('Manifestation')->createQuery('m', true)->leftJoin('m.Gauges g');
  if ( $request->getParameter('gauge_id') )
    $q->andWhere('g.id = ?', $request->getParameter('gauge_id'));
  elseif ( $request->getParameter('manifestation_id') )
    $q->andWhere('m.id = ?', $request->getParameter('manifestation_id'));
  
  $manifestation = $q->fetchOne();
  $this->dispatcher->notify($event = new sfEvent($this, 'pub.before_adding_tickets', array('manifestation' => $manifestation)));
  if ( !$event->getReturnValue() )
  {
    $this->json['error']['message'] = $event['message'];
    return 'Success';
  }
  
  // the existing tickets
  $q = Doctrine::getTable('Ticket')->createQuery('tck')
    ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
    ->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'))
    ->andWhere('tck.printed_at IS NULL')
    ->andWhere('tck.integrated_at IS NULL')
    ->andWhere('tck.cancelling IS NULL')
    ->andWhere('tck.duplicating IS NULL')
    
    ->leftJoin('tck.Seat s')
    ->leftJoin('tck.Gauge g')
    ->leftJoin('g.Workspace ws')
    ->leftJoin('tck.Price p')
    ->leftJoin('p.Translation pt WITH pt.lang = ?', $this->getUser()->getCulture())
    
    ->leftJoin('tck.Transaction t')
    ->leftJoin('t.Order o')
    ->andWhere('o.id IS NULL')
    
    ->orderBy('ws.name, pt.name, s.rank DESC, tck.value, tck.id')
  ;
  if ( intval($gauge_id = $request->getParameter('gauge_id')).'' === ''.$gauge_id )
    $q->andWhere('tck.gauge_id = ?', $gauge_id);
  $tickets = $q->execute();
  
  $default_prices = array();
  foreach ( $tickets as $key => $ticket )
  if ( isset($data[$ticket->id]) && isset($data[$ticket->id]['action']) )
  {
    switch ( $data[$ticket->id]['action'] ) {
    case 'del':
      $ticket->delete();
      unset($tickets[$key]);
      break;
    case 'mod':
      if ( isset($data[$ticket->id]['price_id']) && $data[$ticket->id]['price_id'] )
      {
        $ticket->price_id   = $data[$ticket->id]['price_id'];
        $ticket->price_name = NULL;
        $ticket->value      = NULL;
        $ticket->vat        = NULL;
      }
      else
      {
        $ticket->price_id   = NULL;
        $ticket->price_name = sfConfig::get('app_tickets_wip_price', 'WIP');
        $ticket->value      = 0;
        $ticket->vat        = 0;
      }
      
      if ( isset($data[$ticket->id]['seat_id']) && $data[$ticket->id]['seat_id'] )
      {
        // checking if the seat is already booked
        $q = Doctrine::getTable('Ticket')->createQuery('tck')
          ->andWhere('tck.seat_id = ?', $data[$ticket->id]['seat_id'])
          ->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'))
          ->andWhere('tck.transaction_id != ?', $this->getUser()->getTransactionId())
        ;
        if ( $q->count() > 0 )
        {
          $this->json['error']['message'] = 'We are sorry, this seat has being booked in the meantime.';
          if ( !isset($this->json['error']['seats_to_remove']) )
            $this->json['error']['seats_to_remove'] = array();
          $this->json['error']['seats_to_remove'][] = $data[$ticket->id]['seat_id'];
          break;
        }
        $ticket->seat_id = $data[$ticket->id]['seat_id'];
      }
      
      if ( !$ticket->trySave()
        || !(isset($data[$ticket->id]['seat_id']) && $data[$ticket->id]['seat_id'] && $ticket->seat_id != $data[$ticket->id]['seat_id']) )
        $this->json['error']['message'] = 'An error occurred updating your cart, try again please...';
      break;
    }
  }
  
  // WIPs & "to seat" tickets
  $wips = $to_seat = array();
  foreach ( $tickets as $key => $ticket )
  if ( !$ticket->price_id && $ticket->price_name )
  {
    if ( !isset($wips[$ticket->gauge_id]) )
      $wips[$ticket->gauge_id] = array();
    if ( $ticket->seat_id )
      $wips[$ticket->gauge_id][] = $ticket;
    else
    {
      // useless WIP (with no seat_id)
      unset($tickets[$key]);
      $ticket->delete();
    }
  }
  elseif ( !$ticket->seat_id )
  {
    $id = $ticket->Gauge->group_name ? 'cat-'.$ticket->Gauge->group_name : 'gid-'.$ticket->gauge_id;
    if ( !isset($to_seat[$id]) )
      $to_seat[$id] = array();
    $to_seat[$id][] = $ticket;
  }
  
  // get back the $max value
  $vel = sfConfig::get('app_tickets_vel');
  if ( $tickets->count() > 0 )
    $max = $this->getMaxPerManifestation($tickets[0]->Gauge->Manifestation);
  else
    $max = $vel['max_per_manifestation'] ? $vel['max_per_manifestation'] : 9;
  $overbooking = 0;

  // adding tickets
  foreach ( $data as $tck )
  if ( $tck['action'] == 'add' )
  {
    if ( $tickets->count() >= $max )
    {
      $overbooking++;
      continue;
    }
    
    $gauge = NULL;
    // finding back the gauge_id using manifestation_id + seat_id
    if (!( isset($tck['gauge_id']) && $tck['gauge_id'] ))
    {
      $q = Doctrine::getTable('Gauge')->createQuery('g', false)
        ->select('g.id')
        ->andWhere('g.manifestation_id = ?', $request->getParameter('manifestation_id'))
        ->leftJoin('g.Manifestation m')
        ->leftJoin('g.Workspace ws')
        ->leftJoin('ws.SeatedPlans sp WITH sp.location_id = m.location_id')
        ->leftJoin('sp.Seats s')
        ->andWhere('s.id = ?', $tck['seat_id'])
        
        ->andWhere('g.online = ?', true)
        ->leftJoin('ws.Users wsu')
        ->andWhere('wsu.id = ?', $this->getUser()->getId())
      ;
      $gauge = $q->fetchOne();
      $tck['gauge_id'] = $gauge->id;
    }
    if ( !$gauge )
      $gauge = Doctrine::getTable('Gauge')->find($tck['gauge_id']);
    $gid = $gauge->group_name ? 'cat-'.$gauge->group_name : 'gid-'.$gauge->id;
    
    if ( !isset($wips[$tck['gauge_id']]) )
      $wips[$tck['gauge_id']] = array();
    if ( !isset($to_seat[$gid]) )
      $to_seat[$gid] = array();
    
    $ticket = isset($tck['price_id']) && $tck['price_id']
      ? array_shift($wips[$tck['gauge_id']])      // get WIPs for normal tickets
      : array_shift($to_seat[$gid])               // get "waiting" tickets, to seat
    ;
    if ( $ticket === NULL )
    {
      $ticket = new Ticket;
      $tickets[] = $ticket;
      //$ticket->transaction_id = $this->getUser()->getTransactionId();
      $ticket->Transaction = $this->getUser()->getTransaction();
      $ticket->gauge_id = $tck['gauge_id'];
    }
    
    // the price
    if ( isset($tck['price_id']) && $tck['price_id'] )
      $ticket->price_id = $tck['price_id'];
    
    // the seat
    if ( isset($tck['seat_id']) && $tck['seat_id'] )
    {
      // checking if the seat is already booked
      $q = Doctrine::getTable('Ticket')->createQuery('tck')
        ->andWhere('tck.seat_id = ?', $tck['seat_id'])
        ->andWhere('tck.manifestation_id = ?', $request->getParameter('manifestation_id'))
        ->andWhere('tck.transaction_id != ?', $this->getUser()->getTransactionId())
      ;
      if ( $q->count() > 0 )
      {
        $this->json['error']['message'] = 'We are sorry, this seat has being booked in the meantime.';
        if ( !isset($this->json['error']['seats_to_remove']) )
          $this->json['error']['seats_to_remove'] = array();
        $this->json['error']['seats_to_remove'][] = $tck['seat_id'];
        break;
      }
      $ticket->seat_id = $tck['seat_id'];
    }
    
    // setting the most expansive price by default, if none given
    if ( !$ticket->price_id )
    {
      if ( $this->getUser()->hasContact() && sfConfig::get('app_options_pass_price_first') )
      {
        $manifestation = Doctrine::getTable('Manifestation')->findOneById($request->getParameter('manifestation_id'));
        $mc_prices = $this->getUser()->getAvailableMCPrices($manifestation);
        if ( count($mc_prices) > 0 ) 
          $ticket->Price = Doctrine::getTable('price')->findOneById(array_keys($mc_prices)[0]);
      }

      if ( !$ticket->price_id )
      {
        $q = Doctrine::getTable('price')->createQueryToFindTheMostExpansiveForGauge($tck['gauge_id']);
        $ticket->Price = $q->andWhere('wsu.id = ?', $this->getUser()->getId())->fetchOne();  
      }
    }
    $ticket->price_name = NULL;
    $ticket->value      = NULL;
    $ticket->vat        = NULL;
    
    try {
      // auto link tickets to member cards
      try {
        $ticket->linkToMemberCard();
      } catch ( liEvenementException $e ) {
        error_log('ticket/modTickets: on adding member card links: '.$e->getMessage());
      }
      
      // save
      $ticket->save();
      
      // changing seat
      if ( isset($tck['seat_id']) && $tck['seat_id'] && $ticket->seat_id != $tck['seat_id'] )
      {
        $ticket->delete();
        unset($tickets[$ticket->key()]);
        $this->json['error']['message'] = 'An error occurred updating your cart, try again please...';
        continue;
      }
    }
    catch ( Doctrine_Connection_Exception $e )
    {
      if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
        throw $e;
      else
        error_log('An error occurred updating your cart, try again please... (executeModTickets) '.$e->getMessage());
      $this->json['error']['message'] = 'An error occurred updating your cart, try again please...';
      continue;
    }
    
    // linked products
    $ticket->addLinkedProducts()->save();
    
    // auto_passes
    if ( ($nb = sfConfig::get('app_member_cards_trigger_after_manifestations', false))
      && ($mcid = sfConfig::get('app_member_cards_type_id', false)) )
    {
      if ( sfConfig::get('sf_web_debug') )
        error_log('[auto_pass] trying to add a member card');
      if ( Doctrine::getTable('Manifestation')->createQuery('m')
        ->leftJoin('m.Tickets tck')
        ->leftJoin('tck.Price price')
        ->andWhere('price.member_card_linked = ?', false)
        ->andWhere('tck.transaction_id = ?', $this->getUser()->getTransactionId())
        ->count() >= $nb
      && !($this->getUser()->getTransaction()->contact_id && $this->getUser()->getTransaction()->getContact()->MemberCards->count() > 0)
      && $this->getUser()->getTransaction()->MemberCards->count() == 0 )
      {
        if ( sfConfig::get('sf_web_debug') )
          error_log('[auto_pass] adding a member card');
        $this->getContext()->getConfiguration()->addMemberCard($this->getUser()->getTransaction(), $mcid);
      }
    }
  }
  
  // return back the list of real tickets
  $this->data = array('tickets' => array());
  foreach ( $tickets as $ticket )
  if ( $ticket->id )
  {
    // the json data
    $this->data['tickets'][] = array(
      'ticket_id'         => $ticket->id,
      'seat_name'         => is_object($ticket->Seat) ? (string)$ticket->Seat : (string)Doctrine::getTable('Seat')->find($ticket->seat_id),
      'seat_id'           => $ticket->seat_id,
      'price_name'        => $ticket->price_id ? (string)$ticket->Price : $ticket->price_name,
      'price_id'          => $ticket->price_id,
      'gauge_name'        => (string)$ticket->Gauge,
      'gauge_id'          => $ticket->gauge_id,
      'extra-taxes'       => (float)$ticket->taxes,
      'value'             => (float)$ticket->value,
    );
  }
  
  if ( $tickets->count() > 0 )
    $this->data['orphans'] = $this->getContext()->getConfiguration()->getOrphans($this->getUser()->getTransaction(), array('manifestation_id' => $request->getParameter('manifestation_id')));
  
  if ( $overbooking > 0 )
    $this->message = "Some tickets have not been added because you reached the limit of tickets for this manifestation.";
  
  $this->dispatcher->notify($event = new sfEvent($this, 'pub.after_adding_tickets', array('tickets' => $tickets, 'direct_contact' => sfConfig::get('app_tickets_direct_contact', 'auto') == 'auto')));
  return 'Success';
