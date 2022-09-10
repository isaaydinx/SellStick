<?php

namespace isaaydin;

use pocketmine\block\tile\Chest;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use isaaydin\Command\SellStickCommand;
use isaaydin\Form\SellStickSellForm;

class SellStick extends PluginBase implements Listener
{

	public static Config $db;

	public function onEnable(): void
	{
		$this->saveResource("sell.yml");
		self::$db = new Config($this->getDataFolder() . "sell.yml", Config::YAML);
		$this->getServer()->getPluginManager()->registerEvents($this, $this);

		$this->getServer()->getCommandMap()->register("sellstick", new SellStickCommand());
	}

	public function onInteract(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$hand = $player->getInventory()->getItemInHand();
		if ($hand->getId() == ItemIds::BLAZE_ROD and $hand->getLore() == ["SellStick"]) {
			if ($block->getId() == ItemIds::CHEST) {
				$tile = $player->getWorld()->getTile($block->getPosition());
				if ($tile instanceof Chest) {
					if (empty($tile->getInventory()->getContents())) {
						$player->sendMessage("§cSandık boş!");
						return;
					}
					$itemList = "";
					$totalPrice = 0;
					foreach (self::$db->get("Items") as $key) {
						$exp = explode(":", $key);
						$id = (int) $exp[0];
						$meta = (int) $exp[1];
						$name = (string) $exp[2];
						$price = (int) $exp[3];
						foreach ($tile->getInventory()->getContents() as $items) {
							if ($items->getID() == $id and $items->getMeta() == $meta) {
								$totalPrice += $price * $items->getCount();
								$itemList .= "§3".$items->getCount() . "x " . $name . "§6 => §b" . $items->getCount() * $price . " TL§f\n";
							}
						}
						if ($totalPrice == 0) {
							$player->sendMessage("§cSandıkta hiç satılabilecek bir eşya yok.");
						} else {
							$player->sendForm(new SellStickSellForm($tile, $itemList, $totalPrice));
						}
					}
				}
			}
		}
	}
}