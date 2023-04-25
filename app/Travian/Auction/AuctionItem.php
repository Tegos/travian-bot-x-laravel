<?php

namespace Application\Travian\Auction;

use Application\Utils\RandomBreak;

final class AuctionItem
{
	public static $items = [
		4 => ['title' => 'Helmet of Regeneration', 'price' => 101],
		111 => ['title' => 'artWork', 'price' => 555],
		109 => ['title' => 'lawTables', 'price' => 12],
		110 => ['title' => 'bookOfWisdom', 'price' => 333],
		108 => ['title' => 'bucketOfWater', 'price' => 555],
		106 => ['title' => 'ointment', 'price' => 17],
		107 => ['title' => 'scroll', 'price' => 40],
		114 => ['title' => 'cage', 'price' => 17],
		112 => ['title' => 'bandage25', 'price' => 10],
		113 => ['title' => 'bandage33', 'price' => 30],
		76 => ['title' => 'SmallShield', 'price' => 222],
		7 => ['title' => 'Helmet OfF The Gladiator', 'price' => 222],
		10 => ['title' => 'Helmet of the Horseman', 'price' => 222],
		104 => ['title' => 'Thoroughbred', 'price' => 222],
		98 => ['title' => 'Boots of the Warrior', 'price' => 222],
		101 => ['title' => 'Spurs', 'price' => 222],
		95 => ['title' => 'Boots of Health', 'price' => 222],
		119 => ['title' => 'Axe of the Ash Warden', 'price' => 111],
		122 => ['title' => 'Khopesh of the Warrior', 'price' => 111],
		80 => ['title' => 'Horn of the Natarian', 'price' => 111],
		79 => ['title' => 'Small Horn of the Natarian', 'price' => 111],
		62 => ['title' => 'Map', 'price' => 222],
		68 => ['title' => 'Standard', 'price' => 111],
		64 => ['title' => 'Small Pennant', 'price' => 111],
		65 => ['title' => 'Pennant', 'price' => 111],
		77 => ['title' => 'Shield', 'price' => 111],
		89 => ['title' => 'Breastplate', 'price' => 111],
		83 => ['title' => 'Armor of Health', 'price' => 111],
		92 => ['title' => 'Segmented Armor', 'price' => 111],
		8 => ['title' => 'Helmet of the Tribune', 'price' => 111],
		14 => ['title' => 'Helmet of the Warrior', 'price' => 111],
		2 => ['title' => 'Helmet of Enlightenment', 'price' => 111],
		11 => ['title' => 'Helmet of the Cavalry', 'price' => 111],
		73 => ['title' => 'Pouch of the Thief', 'price' => 111],
		85 => ['title' => 'Light Scale Armor', 'price' => 111],
		16 => ['title' => 'Short Sword of the Legionnaire', 'price' => 111],
	];

	/**
	 * @return false|string[]
	 */
	public static function getIgnoredItemIds()
	{
		$str_ignored_item_ids = getenv('IGNORED_ITEM_IDS') ?? '';
		return explode(',', $str_ignored_item_ids);
	}

	public static function getCoefficient($identifier, $selling_identifiers = [])
	{
		return in_array($identifier, $selling_identifiers) ? random_int(5, 7) : (RandomBreak::randomFloat(.3, .9));
	}

	public static function getPrice($auction_data): int
	{
		$item_type_id = $auction_data['item_type_id'] ?? 0;
		$item = self::$items[$item_type_id] ?? [];
		if (!empty($item)) {
			return $item['price'];
		}

		return 111;
	}
}