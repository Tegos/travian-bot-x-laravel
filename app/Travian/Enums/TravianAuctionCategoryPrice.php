<?php

namespace App\Travian\Enums;

use Illuminate\Support\Str;

final class TravianAuctionCategoryPrice
{
    const HELMET = 107;
    const BODY = 107;
    const LEFT_HAND = 111;
    const RIGHT_HAND = 115;
    const SHOES = 122;
    const HORSE = 222;
    const BANDAGE25 = 3;
    const BANDAGE33 = 5;
    const CAGE = 3;
    const SCROLL = 4;
    const OINTMENT = 3;
    const BUCKET_OF_WATER = 222;
    const BOOK_OF_WISDOM = 111;
    const LAW_TABLETS = 3;
    const ART_WORK = 333;

    public static function getPrice(string $itemCategory)
    {
        $category = Str::snake($itemCategory);
        $category = Str::upper($category);

        $code = implode('', [self::class, '::', $category]);

        return defined($code) ? constant($code) : 1;
    }
}
