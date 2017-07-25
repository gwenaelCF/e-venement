<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of components
 *
 * @author gwenael
 */
class ticketComponents extends sfComponents {
    public function executeCustomTckMenu(){
        $myMenu = array();
        //TODO more generic arrays
        $mandatory = array();
        $manif = array();
        $gauge = array();
        $transaction = array();
        $others = array();
        $try = $this->json;
        foreach ($try as $key => $value) {
           if (!$value['optional']){
               $mandatory[]=$key;
           }else if($value['displayed']){
               switch(explode('.',$key)[0]){
                   case 'Manifestation':
                       $manif[] = $key;
                       break;
                   case 'Transaction':
                       $transaction[] = $key;
                       break;
                   case 'Gauge':
                       $gauge[] = $key;
                       break;
                   default:
                       $others[] = $key;
                       break;
               }
               
           }
        }
        $this->manif=$manif;
        $this->mandatory=$mandatory;
        $this->gauge=$gauge;
        $this->transaction=$transaction;
        $this->others=$others;
        
    }
}
