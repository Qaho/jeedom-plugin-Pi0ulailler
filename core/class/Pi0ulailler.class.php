<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class template extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        this->createCommands()
        
    }

    private function createCommands() {
        /************ Chickencoop commands **************/
        // Opening time
        $ChickencoopCmd = $this->getCmd(null, 'Opening time');
		if (!is_object($ChickencoopCmd)) {
			$ChickencoopCmd = new ChickencoopCmd();
		}
        $ChickencoopCmd->setName(__('Opening time', __FILE__));
        $ChickencoopCmd->setEqLogic_id($this->getId());
        $ChickencoopCmd->setLogicalId('IOpeningTime');
        $ChickencoopCmd->setType('info');
        $ChickencoopCmd->setSubType('string');
        $ChickencoopCmd->setIsVisible(1);
        $ChickencoopCmd->save();

        // Closing time
        $ChickencoopCmd = $this->getCmd(null, 'Closing time');
		if (!is_object($ChickencoopCmd)) {
			$ChickencoopCmd = new ChickencoopCmd();
		}
        $ChickencoopCmd->setName(__('Closing time', __FILE__));
        $ChickencoopCmd->setEqLogic_id($this->getId());
        $ChickencoopCmd->setLogicalId('IClosingTime');
        $ChickencoopCmd->setType('info');
        $ChickencoopCmd->setSubType('string');
        $ChickencoopCmd->setIsVisible(1);
        $ChickencoopCmd->save();

        /************ Rainmeter commands **************/
        $ChickencoopCmd = $this->getCmd(null, 'Rainmeter');
		if (!is_object($ChickencoopCmd)) {
			$ChickencoopCmd = new ChickencoopCmd();
		}
        $ChickencoopCmd->setName(__('Rainmeter', __FILE__));
        $ChickencoopCmd->setEqLogic_id($this->getId());
        $ChickencoopCmd->setLogicalId('IRainmeter');
        $ChickencoopCmd->setType('info');
        $ChickencoopCmd->setSubType('int');
        $ChickencoopCmd->setIsVisible(1);
        $ChickencoopCmd->save();
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        if ($this->getConfiguration('ip') == '') {
            throw new Exception(__('L\'adresse ne peut etre vide',__FILE__));
          }
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */

    // recuperation automatique des informations
    public function getAllData() {
        $this->getRainData()
        $this->getChickenCoopData()
    }

    public function getRainData() {

    }

    public function getChickenCoopData() {
        
    }

    public function sendRequest($CMD, $_options) {

        // requete http
        $cmdString = $CMD->getConfiguration('actionCmd');
        // si option value ajout dans la requete
        if(isset($_options) && $_options!='') {
            if(is_array($_options)) {
                // cas ph
                if(isset($_options['jour']) && isset($_options['tranche']) && isset($_options['programme'])) {
                    $cmdString = $cmdString . $_options['jour'] . '+' . $_options['tranche'] . '+' . $_options['programme'];	
                } else if(isset($_options['numero']) && isset($_options['temperature']) && isset($_options['h1']) && isset($_options['m1']) && isset($_options['h2']) && isset($_options['m2'])) {
                    $cmdString = $cmdString . $_options['numero'] . '+' . $_options['temperature'] . '+' . $_options['h1']. '+' . $_options['m1']. '+' . $_options['h2']. '+' . $_options['m2'];
                }
            } else {
                    $cmdString = $cmdString . $_options;					
            }
            log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. ' commande ' . $cmdString);
            log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. ' commande ' . json_encode($_options));
        }
        $DATA = $this->makeRequest($cmdString) ;
        if($DATA == false) { return 'ERROR'; }
        // verification succes du traitement
        if($DATA->Info->RSP != 'OK') {
            log::add('Palazzetti', 'error','('.__LINE__.') ' . __FUNCTION__.' - '. ' réponse erreur ' . $DATA->Info->RSP);
            return false;
        } 
        // definition patern de comparaison
        $expl = explode('+',$cmdString);
        $pattern = $expl[0] . '+' . $expl[1];

        // traitement suivant commande
        switch($pattern) {
            // allumage, extinction, status
            case 'CMD+ON': 
            case 'CMD+OFF': 
            case 'GET+STAT': 
                $value = $this->getStoveState($DATA->Status->STATUS);
            break;
            // nom poele
            case 'GET+LABL': 
            case 'SET+LABL':
                $value = $DATA->StoveData->LABEL;
            break;
            // force du feu
            case 'SET+POWR':
                $value = $DATA->Power->POWER;
            break;
            // température de consigne
            case 'GET+SETP': 
            case 'SET+SETP':
                $value = $DATA->Setpoint->SETP;
            break;
            // force du ventilateur
            case 'GET+FAND': 
                $value = $this->getFanState($DATA->Fans->FAN_FAN2LEVEL);
            break;
            case 'SET+RFAN':
                $value = $this->getFanState($DATA->RoomFan->FAN_FAN2LEVEL);
            break;
            // température ambiance
            case 'GET+TMPS': 
                $value = $DATA->Temperatures->TMP_ROOM_WATER;
            break;
            // programmes horaires
            case 'GET+CHRD': 
                $value = json_encode($DATA->{'Chrono Info'});
            break;
            // programmes horaires
            case 'SET+CSST': 
            break;
            // affectation programme
            // options +JOUR+TRANCHE+PH 
            case 'SET+CDAY':
            break;
            // informations automate
            case 'EXT+ADRD':
                $value = $DATA->Data->{'ADDR_'+ $expl[2]};
                log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'response '. $value);
            break;
        }
        // mise a jour variables info
        if($CMD->getConfiguration('updateLogicalId')) {
            $INFO = $this->getCmd(null, $CMD->getConfiguration('updateLogicalId'));
            $INFO->event($value);
            $INFO->save();
            log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'response '. $value);
            log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'updatelogicalId '.  $CMD->getConfiguration('updateLogicalId') . ' = ' . $value);
        }
        // mise à jour lastvalue commande
        $CMD->setConfiguration('lastCmdValue',$value);
        $CMD->save();
        return 'OK';
    }

    // methode requete
	public function makeRequest($cmd) {
		$url = 'http://' . $this->getConfiguration('addressip') . '/sendmsg.php?cmd=' . $cmd;
		log::add('Palazzetti', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'get URL '. $url);
		$request_http = new com_http($url);
		$return = $request_http->exec(10, 5);
		$return = json_decode($return);
		if($return->Info->RSP != 'OK') {
			log::add('Palazzetti', 'error','('.__LINE__.') ' . __FUNCTION__.' - '. ' réponse erreur ' . $cmd);
			return false;
		} else {
			return $return;
		}
	}
}
    

}

class chickencoopCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
        $eqLogic = $this->getEqLogic();
		$idCmd = $this->getLogicalId();

		log::add('Pi0ulailler', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'options '. json_encode($this->getConfiguration('options')));
		log::add('Pi0ulailler', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. '$_options '. json_encode($_options));

		$eqLogic->sendCommand($this, $_options);
		$eqLogic->refreshWidget();
    }

    /*     * **********************Getteur Setteur*************************** */
}


