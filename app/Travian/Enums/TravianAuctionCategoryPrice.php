<?php

namespace App\Travian\Enums;

use Illuminate\Support\Str;

final class TravianAuctionCategoryPrice
{
    const HELMET = 111;
    const BODY = 111;
    const LEFT_HAND = 105;
    const RIGHT_HAND = 111;
    const SHOES = 155;
    const HORSE = 222;
    const BANDAGE25 = 5;
    const BANDAGE33 = 10;
    const CAGE = 10;
    const SCROLL = 15;
    const OINTMENT = 10;
    const BUCKET_OF_WATER = 555;
    const BOOK_OF_WISDOM = 222;
    const LAW_TABLETS = 15;
    const ART_WORK = 555;

    public static function getPrice(string $itemCategory)
    {
        $category = Str::snake($itemCategory);
        $category = Str::upper($category);

        $code = implode('', [self::class, '::', $category]);

        return defined($code) ? constant($code) : 1;
    }
}
