<?php

namespace iJoshuaHD;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerKickEvent;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\Format;
use pocketmine\utils\Config;


class VIPSlots extends PluginBase implements Listener{

    public function onEnable(){

		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->saveDefaultConfig();
		$this->reloadConfig();
		
		$this->createPlayersVIP();
		$this->vip = $this->getDataFolder(). "vip_players.txt";
		
		$this->getLogger()->info("VIPSlots Enabled!");
	}
    
    public function onDisable(){
		$this->getLogger()->info("VIPSlots Disabled!");
    }

/*****************
*================*
*===[ Events ]===*
*================*
*****************/

	public function onPlayerKick(PlayerKickEvent $event){
		
			$p = strtolower($event->getPlayer()->getName());
			$vip_players = $this->vip;
			if(strpos(file_get_contents($vip_players), $p) !== false) {
				$event->setCancelled(true);
			}
			
	}


/*****************
*================*
*==[ Commands ]==*
*================*
*****************/
	
	public function onCommand(CommandSender $p, Command $command, $label, array $args){

		switch($command->getName()){
		
			case "vips":
			
				if(!isset($args[0])){
						$p->sendMessage("Usage: /vips <add/remove>");
				}elseif(count($args) > 3){
						$p->sendMessage("Usage: /vips <add/remove>");
				}else{
				
					$cmds = strtolower($args[0]);

					if($cmds === "add"){
						
						if(isset($args[1])){
							$nugget_head = $args[1];
							$who_player = $this->getValidPlayer($nugget_head);
							if($who_player instanceof Player){
								$target = $who_player->getName();
							}else{
								$target = $args[1];
							}
							if($this->addPlayer($target)){
								$p->sendMessage("Successfully added '$target' on VIPSlots!");
							}else{
								$p->sendMessage("$target is already added on VIPSlots!");
							}
						}else{
							$p->sendMessage("Usage: /vips add <player>");
						}

					}elseif($cmds === "remove"){

						if(isset($args[1])){
							$who_player = $this->getValidPlayer($args[1]);
							if($who_player instanceof Player){
								$target = $who_player->getName();
							}else{
								$target = $args[1];
							}
							if($this->removePlayer($target)){
								$p->sendMessage("Successfully removed '$target' on VIPSlots!");
							}else{
								$p->sendMessage("$target is doesnt exist on VIPSlots!");
							}
						}else{
							$p->sendMessage("Usage: /vips remove <player>");
						}
						
					}else{
						$p->sendMessage("Usage: /vips <add/remove>");
					}
					
				}
				
			break;
		
		}
	
	}
	
/*****************
*================*
*==[ Non-APIs ]==*
*================*
*****************/
	
	public function createPlayersVIP(){
		
		$fileLocation = $this->getDataFolder() . "vip_players.txt";
		if(!file_exists($fileLocation)){
			fopen($fileLocation,"w");
		}

	}
	
	public function addPlayer($player){
		$target = $this->getValidPlayer($player);
		if($target instanceof Player){
			$p = strtolower($target->getName());
		}else{
			$p = strtolower($player);
		}
		$vip_players = $this->vip;
		if(strpos(file_get_contents($vip_players), $p) !== false) {
			return false;
		}else{
			file_put_contents($vip_players, $p . PHP_EOL, FILE_APPEND);
			return true;
		}
	}
	
	public function removePlayer($player){
	
		$target = $this->getValidPlayer($player);
		$vip_players = $this->vip;
		
		if($target instanceof Player){
			$p = strtolower($target->getName());
		}else{
			$p = strtolower($player);
		}
		
		if(strpos(file_get_contents($vip_players),$p) !== false) {
										
			$DELETE = $p;
			$data = file($vip_players);
			$out = array();
			
			foreach($data as $line){
				if(trim($line) != $DELETE) {
					$out[] = $line;
				}
			}
			
			$fp = fopen($vip_players, "w+");
											 
			flock($fp, LOCK_EX);
			
			foreach($out as $line) {
				fwrite($fp, $line);
			}
			
			flock($fp, LOCK_UN);
			
			fclose($fp);
			
			return true;
											 
		}else{
			return false;
		}
	
	}
	
	public function getValidPlayer($target_p){
		$player = $this->getServer()->getPlayer($target_p);
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($target_p);
	}

}
