<?php

namespace EdoGaming\Sekolah;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\Server;
use pocketmine\scheduler\Task;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener {

	private $lastExec = [];

	private $config = [];

	public function onEnable() {
		$this->saveDefaultConfig();
		$this->config = $this->getConfig()->getAll();

		if (is_numeric($this->config["delay"])) {
			$this->getServer()->getLogger()->info("[Sekolah] Plugin enabled");
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		} else {
			$this->getServer()->getLogger()->info("[Sekolah] Plugin disabled. Please check the config file, there seems to be an error!");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
	}

	public function onDisable() {
		$this->getServer()->getLogger()->info("[Sekolah] Plugin disabled");
	}

	public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool {
		switch ($cmd->getName()) {
			case "sans":
				if ($sender instanceof Player) {
					$name = $sender->getName();
					if ((isset($this->lastExec[$name])) && (($this->lastExec[$name] + 5 + $this->config["delay"]) > (microtime(true)))) {
						$sender->sendMessage($this->config["msg_too_fast"]);
					} else {
						$this->getScheduler()->scheduleDelayedTask(new SekolahTask($this, $sender->getName()), (20*$this->config["delay"]));
						$message = str_replace("{delay}", $this->config["delay"], $this->config["msg_being_teleported"]);
						$sender->sendPopup($message);
						$this->lastExec[$name] = microtime(true);
					}
					if (!isset($this->lastExec[$name])) {
						$this->lastExec[$name] = microtime(true);
					}
				} else {
					$sender->sendMessage("§cPlease run this command in-game!");
				}
			break;
		}
		return true;
	}

	public function onPlayerQuit(PlayerQuitEvent $evt) {
		unset($this->lastExec[$evt->getPlayer()->getName()]);
	}
}
