<?php

namespace isaaydin\Form;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use isaaydin\SellStick;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use onebone\economyapi\EconomyAPI;

class SellStickSellForm extends MenuForm
{
	private $selledsArray = [];

	public function __construct($tile, $itemsText, $totalPrice)
	{
		parent::__construct(
			"Sat Menüsü", $itemsText . "\n\n§3Kazanılacak Tutar: §b" . $totalPrice ."TL\n§aSatmayı onaylıyor musun?",
			[
				new MenuOption("Evet"),
				new MenuOption("Hayır")
			],
			function (Player $player, int $s) use ($tile): void {
				if ($s == 0) {
					if (empty($tile->getInventory()->getContents())) {
						$player->sendMessage("§cSandık boş");
						return;
					}
					$totalPrice = 0;
					$eco = EconomyAPI::getInstance();
					$selleds = "";
					foreach (SellStick::$db->get("Items") as $value) {
						$exp = explode(":", $value);
						$id = (int)$exp[0];
						$meta = (int)$exp[1];
						$name = (string)$exp[2];
						$price = (int)$exp[3];
						foreach ($tile->getInventory()->getContents() as $items) {
							if ($items->getID() == $id and $items->getMeta() == $meta) {
								$tile->getInventory()->remove($items);
								$nowPrice = $items->getCount() * $price;
								$eco->addMoney($player, $nowPrice);
								$totalPrice += $nowPrice;
								if (!isset($this->selledsArray[$name])) $this->selledsArray[$name] = 0;
								$this->selledsArray[$name] += $items->getCount();
								$selleds .= "§3" . $items->getCount() . "x §8" . $name . " §6--> §b" . $nowPrice . " §aTL§f\n";
							}
						}
					}
					if ($totalPrice == 0) {
						$player->sendMessage("§cSandıkta hiç satılabilecek bir eşya yok.");
						return;
					}
					$selledItems = "";
					foreach ($this->selledsArray as $key => $value) $selledItems .= "§3" . $value . "x " . $key."\n";
						$player->sendMessage("§aSandıkta satılabilir tüm ürünler satıldı. Toplam kazanç: §b" . $totalPrice . " §aTL. Satılan eşyalar;\n" . $selledItems);
				}
			}
		);
	}
}