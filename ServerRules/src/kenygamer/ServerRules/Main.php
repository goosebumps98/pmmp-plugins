<?php

/*
 * ServerRules plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/ServerRules>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

namespace kenygamer\ServerRules;

use pocketmine\command\{Command, CommandSender, CommandExecutor};
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase implements Listener{
  
  /** Plugin name */
  const PLUGIN_NAME = "Asylum Laws";
  /** Plugin version */
  const PLUGIN_VERSION = "1.0";
  
  /**
   * @return void
   */
  public function onEnable(){
    $this->getLogger()->info(TF::GREEN."Enabling ".$this->getDescription()->getFullName()."...");
    new AutoNotifier($this);
    $this->loadConfig();
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  /**
   * @return void
   */
  public function onDisable(){
    $this->getLogger()->info(TF::RED."Disabling ".$this->getDescription()->getFullName()."...");
  }
  
  /**
   * Loads configuration file
   *
   * @return void
   */
  private function loadConfig(){
    if(!is_dir($this->getDataFolder())){
      @mkdir($this->getDataFolder());
    }
    if(!file_exists($this->getDataFolder()."config.yml")){
      $this->saveDefaultConfig();
    }
  }
  
  /**
   * Returns command prefix
   *
   * @return string
   */
  private function getPrefix(){
    return TF::GREEN."§l§5LAWS §4)) ".TF::RESET;
  }
  
  /**
   * @param CommandSender $sender
   * @param Command $command
   * @param string $label
   * @param string[] $args
   *
   * @return bool
   */
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
    if($command->getName() === "laws"){
      if(!$sender->hasPermission("laws.command")){
        $sender->sendMessage($this->getPrefix().TF::RED."You do not have permission to use this command.");
        return true;
      }
      if(!is_array($rules = $this->getConfig()->get("rules"))){
        $this->getLogger()->error("Unable to execute command: invalid value for rules in config.yml");
        return true;
      }
      if(($pageRules = (int) $this->getConfig()->get("page-rules")) < 1){
        $this->getLogger()->warning("Law per page in config.yml is less than 1. Resetting to 1");
        $this->getConfig()->set("page-rules", 1);
        $this->getConfig()->save();
      } 
      $page = !isset($args[0]) ? 1 : (int) $args[0];
      $offset = ($page - 1) * $pageRules;
      $totalRules = count($rules);
      $totalPages = ceil($totalRules / $pageRules);
      $currPageRules = array_splice($rules, $offset, $pageRules);
      if($page < 1 || $page > $totalPages){
        $sender->sendMessage($this->getPrefix().TF::RED." Page not found. Please use /rules for a list of rules.");
        return true;
      }
      $sender->sendMessage(TF::WHITE."--- §l§4Laws Page");
      foreach($currPageRules as $rule){
        $sender->sendMessage(TF::DARK_GREEN."- ".TF::WHITE.$rule.TF::RESET);
      }
      return true;
    }
  }
  
}
