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

class pi0ulailler extends eqLogic
{
   /*     * *************************Attributs****************************** */

   /*
   * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
   * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
	public static $_widgetPossibility = array();
   */

   /*     * ***********************Methode static*************************** */

   /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
      public static function cron5() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
      public static function cron10() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
      public static function cron15() {
      }
     */

   /*
     * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
      public static function cron30() {
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
      //================== rainmeter
      // rain
      $info = $this->getCmd(null, 'rain');
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__('Pluie', __FILE__));
      }
      $info->setLogicalId('rain');
      $info->setEqLogic_id($this->getId());
      $info->setType('info');
      $info->setSubType('numeric');
      $info->save();

      //================== chickencoop
      // opening time
      $info = $this->getCmd(null, 'openingTime');
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__('Heure ouverture', __FILE__));
      }
      $info->setLogicalId('openingTime');
      $info->setEqLogic_id($this->getId());
      $info->setType('info');
      $info->setSubType('other');
      $info->save();

      $info = $this->getCmd(null, 'setOpeningTime');
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__('Changer heure ouverture', __FILE__));
      }
      $info->setLogicalId('setOpeningTime');
      $info->setEqLogic_id($this->getId());
      $info->setType('action');
      $info->setSubType('other');
      $info->save();
      
      // closing time
      $info = $this->getCmd(null, 'closingTime');
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__('Heure fermeture', __FILE__));
      }
      $info->setLogicalId('closingTime');
      $info->setEqLogic_id($this->getId());
      $info->setType('info');
      $info->setSubType('other');
      $info->save();

      $info = $this->getCmd(null, 'setClosingTime');
      if (!is_object($info)) {
         $info = new pi0ulaillerCmd();
         $info->setName(__('Changer heure fermeture', __FILE__));
      }
      $info->setLogicalId('setClosingTime');
      $info->setEqLogic_id($this->getId());
      $info->setType('action');
      $info->setSubType('other');
      $info->save();
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
   public function updateRainData() {
      $result = $this->makeRequest('rain', null);

      if($result) {
         $this->getEqLogic()->checkAndUpdateCmd('rain', $result->rain_mm_value);
      }

      
   }

   public function updateChickenCoopData() {
      $this->makeRequest(null, null);
   }

   public function updateData() {
      $this->getRainData();
      $this->getChickenCoopData();
   }

   public function makeRequest($subEquip, $cmd) {

      if(!empty($subEquip)) {
         $url = 'http://' . $this->getConfiguration('addressip') . '/getdata';
      }
      else {
         $url = 'http://' . $this->getConfiguration('addressip') . '/' . $subEquip . '/getdata';
      }
		
		log::add('pi0ulailler', 'debug','('.__LINE__.') ' . __FUNCTION__.' - '. 'get URL '. $url);
		$request_http = new com_http($url);
		$return = $request_http->exec(10, 5);
		$return = json_decode($return);
		if($return->Info->RSP != 'OK') {
			log::add('pi0ulailler', 'error','('.__LINE__.') ' . __FUNCTION__.' - '. ' réponse erreur ' . $cmd);
			return false;
		} else {
			return $return;
		}
	}
}

class pi0ulaillerCmd extends cmd
{
   /*     * *************************Attributs****************************** */

   /*
      public static $_widgetPossibility = array();
    */

   /*     * ***********************Methode static*************************** */

   /*     * *********************Methode d'instance************************* */

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
      log::add('pi0ulailler', 'debug', '('.__LINE__.') ' . __FUNCTION__.' - '. 'Command: ' . $this->getLogicalId());

		switch ($this->getLogicalId()) {	// vérifie le logicalid de la commande 			
			case 'refresh': // LogicalId de la commande rafraîchir que l’on a créé dans la méthode Postsave 
				$info = $eqlogic->updateData(); 
            break;
         case 'rain': 
               $info = $eqlogic->updateRainData(); 
               break;
		}
   }
   

   /*     * **********************Getteur Setteur*************************** */
}
