<?php

namespace isaaydin\Command;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\player\Player;
use onebone\economyapi\EconomyAPI;

class SellStickCommand extends Command{

	public function __construct(){
		parent::__construct(
		"sellstick",
		"Satış Çubuğu",
			"/sellstick"
		);
	}

	public function execute(CommandSender $player, string $lbl, array $data)
	{
		if($player instanceof Player){
			$player->sendForm(new class extends MenuForm{

				public function __construct(){
					parent::__construct(
					"Satış Çubuğu",
					"Satış Çubuğu Tutarı: 150000TL\n\n Almayı onaylıyormusun?",
					[
						new MenuOption("Evet"),
						new MenuOption("Hayır")
					],
						function (Player $player, int $s):void{
						if($s == 0){
							$eco = EconomyAPI::getInstance();
							if($eco->myMoney($player) >= 150000){
								$item = ItemFactory::getInstance()->get(ItemIds::BLAZE_ROD, 0, 1);
								if($player->getInventory()->canAddItem($item)){
									$player->getInventory()->addItem($item->setLore(["SellStick"]));
									$eco->reduceMoney($player, 150000);
									$player->sendMessage("§aBaşarılıyla 150000TL karşılığında Satış Çubuğu Satın Aldınız!");
								}else{
									$player->sendMessage("§cEnvanterinizde yeterli yer yok!");
								}
							}else{
								$player->sendMessage("§cParanız Yetersiz!");
							}
						}
						}
					);
				}
			});
		}else{
			$player->sendMessage("§cLütfen bu komutu oyunda kullanın!");
		}
	}
}