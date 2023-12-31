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
    const BANDAGE25 = 6;
    const BANDAGE33 = 7;
    const CAGE = 2;
    const SCROLL = 3;
    const OINTMENT = 3;
    const BUCKET_OF_WATER = 111;
    const BOOK_OF_WISDOM = 11;
    const LAW_TABLETS = 1;
    const ART_WORK = 333;

    public static function getPrice(string $itemCategory)
    {
        $category = Str::snake($itemCategory);
        $category = Str::upper($category);

        $code = implode('', [self::class, '::', $category]);

        return defined($code) ? constant($code) : 1;
    }
}
