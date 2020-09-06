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
require_once __DIR__  . '/../../../../data/php/user.function.class.php';

class pi0ulailler extends eqLogic
{
   /*     * *************************Attributs****************************** */

   /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

      public static function cron5() {
         foreach (self::byType('pi0ulailler') as $chickencoop) {//parcours tous les équipements du plugin vdm
            if ($chickencoop->getIsEnable() == 1) {//vérifie que l'équipement est actif
               $cmd = $chickencoop->getCmd(null, 'refresh');//retourne la commande "refresh si elle exxiste
               if (!is_object($cmd)) {//Si la commande n'existe pas
                  continue; //continue la boucle
               }
               $cmd->execCmd(); // la commande existe on la lance
            }
         }
      }


   /*     * *********************Méthodes d'instance************************* */

   // Fonction exécutée automatiquement avant la création de l'équipement 
   public function preInsert()
   {
   }

   // Fonction exécutée automatiquement après la création de l'équipement 
   public function postInsert()
   {
   }

   // Fonction exécutée automatiquement avant la mise à jour de l'équipement 
   public function preUpdate()
   {
   }

   // Fonction exécutée automatiquement après la mise à jour de l'équipement 
   public function postUpdate()
   {
   }

   // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement 
   public function preSave()
   {
   }

   // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement 
   public function postSave()
   {

      $this->createCommand('refresh', 'Rafraichir', 'action', 'other'); // refresh

      // rainmeter
      $this->createCommand('rain', 'Pluie', 'info', 'numeric'); // rain

      // chickencoop
      $this->createCommand('openingTime', 'Heure ouverture', 'info', 'string'); // opening time
      $this->createCommand('setOpeningTime', 'Changer heure ouverture', 'action', 'message'); // set opening time
      $this->createCommand('closingTime', 'Heure fermeture', 'info', 'string'); // closing time
      $this->createCommand('setClosingTime', 'Changer heure fermeture', 'action', 'message'); // set closing time
   }

   // Fonction exécutée automatiquement avant la suppression de l'équipement 
   public function preRemove()
   {
   }

   // Fonction exécutée automatiquement après la suppression de l'équipement 
   public function postRemove()
   {
   }

   /*
     * Non obligatoire : permet de modifier l'affichage du widget (également utilisable par les commandes)
      public function toHtml($_version = 'dashboard') {

      }
     */

   /*
     * Non obligatoire : permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

   /*
     * Non obligatoire : permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

   /*     * **********************Getteur Setteur*************************** */
   public function refreshRainData()
   {
      $result = $this->sendGetRequest('rain', null);
      $this->updateRainData($result);
   }

   public function updateRainData($data)
   {
      if ($data) {
         log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Update rain data: ' . $data->rain_mm_value);
         $this->checkAndUpdateCmd('rain', $data->rain_mm_value);
      }
   }

   public function refreshChickenData()
   {
      $result = $this->sendGetRequest('chicken', null);
      $this->updateChickenData($result);
   }

   public function updateChickenData($data)
   {
      if ($data) {
         log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Update chicken data: ' . json_encode($data));
         $this->checkAndUpdateCmd('openingTime', $data->openingTime);
         $this->checkAndUpdateCmd('setOpeningTime', $data->openingTime);
         $this->checkAndUpdateCmd('closingTime', $data->closingTime);
         $this->checkAndUpdateCmd('setClosingTime', $data->closingTime);

         foreach ($data->doors as $door) {

            // check if door commands exist or create it
            log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Checking door exists: ' . json_encode($door));
            $this->checkAndCreateDoorCommands($door);

            // refresh door data
            $this->checkAndUpdateCmd('door_' . $door->id, $door->name);
            $this->checkAndUpdateCmd('door_' . $door->id . '_status', $door->status);
         }
      }
   }

   private function checkAndCreateDoorCommands($door)
   {
      $this->createCommand('door_' . $door->id, $door->name, 'info', 'string'); // name
      $this->createCommand('door_' . $door->id . '_status', $door->name . ' statut', 'info', 'string'); // statut
      $this->createCommand('door_' . $door->id . '_open', 'Ouvrir ' . strtolower($door->name), 'action', 'other'); // open
      $this->createCommand('door_' . $door->id . '_close', 'Fermer ' . strtolower($door->name), 'action', 'other'); // close
      $this->createCommand('door_' . $door->id . '_stop', 'Arreter ' . strtolower($door->name), 'action', 'other'); // stop
   }

   private function createCommand($id, $name, $type, $subtype, $addMsg = false)
   {
      $info = $this->getCmd(null, $id);
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__($name, __FILE__));
      }
      $info->setLogicalId($id);
      $info->setEqLogic_id($this->getId());
      $info->setType($type);
      $info->setSubType($subtype);
      if($addMsg) {
         // $info->setDisplay('title_placeholder', __('Options', __FILE__));
			$info->setDisplay('message_placeholder', __('Heure (format: "12:34")', __FILE__));
      }
      $info->save();
   }

   public function refreshData()
   {
      $this->refreshRainData();
      $this->refreshChickenData();
   }

   private function getUrl($category, $cmd)
   {
      log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Get URL ' . json_encode($this->getConfiguration()));

      // get config ip address
      $url = 'http://' . $this->getConfiguration('ipAddress');

      // add port 
      if (!empty($this->getConfiguration('port'))) $url .= ':' . $this->getConfiguration('port');
      // add category
      if (!empty($category)) $url .= '/' . $category;
      // cmd
      if (!empty($cmd))
         $url .= '/' . $cmd;
      else
         $url .= '/getdata';

      log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'URL: ' . $url);
      return $url;
   }

   public function sendGetRequest($category, $cmd)
   {

      log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - sendGetRequest: ' . $category . ' - ' . $cmd);
      $url = $this->getUrl($category, $cmd);

      $request_http = new com_http($url);
      $return = $request_http->exec(10, 5);
      if (!isset($return)) {
         log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - Response error on cmd: ' . $cmd);
      } else {
         log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Result: ' . $return);
      }
      return json_decode($return);
   }

   public function sendPostRequest($category, $cmd, $data = null, $needResponse = false)
   {
      $data = json_encode($data);
      log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . $category . ' - ' . $cmd. ' with data ' . $data);
      $url = $this->getUrl($category, $cmd);

      $request_http = new com_http($url);
      $request_http->setHeader(array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
      $request_http->setPost($data);
      $result = $request_http->exec(10, 5);

      if ($result === FALSE) {
         log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - Response error on cmd: ' . $cmd . ' with data ' . $data);
      } else {
         if($needResponse) {
            $result = json_decode(stripslashes($result));
            if ($result->status == "OK") {
               log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Result: ' . json_encode($result));
               $result = $result->data;
               log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Data: ' . json_encode($result));
            }
            else {
               log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Error on command ' . $cmd . ' with data ' . $data . ' - Result: ' . $result);
               $result = null;
            }
         }
      }

      return $result;
   }
}

class pi0ulaillerCmd extends cmd
{
   public function preSave() {
      if ($this->getSubtype() == 'message') {
          $this->setDisplay('title_disable', 1);
          $this->setDisplay('message_disable', 0);
          $this->setDisplay('message_placeholder', 'Heure (format: 12:34)');
      }
  }
   /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

   // Exécution d'une commande  
   public function execute($_options = array())
   {
      $eqlogic = $this->getEqLogic(); // récupère l'éqlogic de la commande $this
      $cmd = $this->getLogicalId();
      log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Command: ' . $cmd . ' with options: ' . json_encode($_options));

      switch ($cmd) {   // vérifie le logicalid de la commande 			
         case 'refresh': // LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave 
            $eqlogic->refreshData();
            break;
         case 'rain':
            $eqlogic->refreshRainData();
            break;
         case 'chicken':
            $eqlogic->refreshChickenData();
            break;
         case 'setOpeningTime':

            $time = $_options['message']; 
            $time = userFunction::userGetTimeFromJeedomString($time);
            log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Time is: "' . $time . '"');

            if(preg_match('#^([01][0-9])|(2[0-4])(:[0-5][0-9]){1,2}$#', $time))
            {
               $data = (object) [
                  'id' => 'openingTime',
                  'value' => $time
               ];

               $result = $eqlogic->sendPostRequest('chicken', 'postjson', $data, true);
               $eqlogic->updateChickenData($result);
            }
            else {
               log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Time is not in a correct format: "' . $time . '"');
            }
            break;
         case 'setClosingTime':
            
            $time = $_options['message']; 
            $time = userFunction::userGetTimeFromJeedomString($time);
            log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Time is: "' . $time . '"');

            if(preg_match('#^([01][0-9])|(2[0-4])(:[0-5][0-9]){1,2}$#', $time))
            {
               $data = (object) [
                  'id' => 'closingTime',
                  'value' => $time
               ];

               $result = $eqlogic->sendPostRequest('chicken', 'postjson', $data, true);
               $eqlogic->updateChickenData($result);
            }
            else {
               log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Time is not in a correct format: "' . $time . '"');
            }
            break;
         default:
            // handle doors control
            if (substr($cmd, 0, 5) === "door_") {
               // split door id and command action
               //Door command: cmdData=_porteinterieure_sto cmdAction=_porteinterieure_ doorId=sto
               $cmdData = substr($cmd, 5, strlen($cmd) - 5);
               $lastIndex = strrpos($cmdData, "_");
               $doorId = substr($cmdData, 0, $lastIndex);
               $cmdAction = substr($cmdData, $lastIndex + 1, strlen($cmd) - $lastIndex);
               log::add('pi0ulailler', 'debug', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Door command: cmdData=' . $cmdData . ' cmdAction=' . $cmdAction . ' doorId=' . $doorId);

               $result = $eqlogic->sendPostRequest('chicken', $doorId . '/' . $cmdAction);
            } else {
               log::add('pi0ulailler', 'error', '(' . __LINE__ . ') ' . __FUNCTION__ . ' - ' . 'Command not implemented: ' . $cmd);
            }
            break;
      }
   }


   /*     * **********************Getteur Setteur*************************** */
}
