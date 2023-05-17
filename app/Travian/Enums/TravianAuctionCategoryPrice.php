<?php

namespace App\Travian\Enums;

use Illuminate\Support\Str;

final class TravianAuctionCategoryPrice
{
    const HELMET = 105;
    const BODY = 105;
    const LEFT_HAND = 101;
    const RIGHT_HAND = 105;
    const SHOES = 122;
    const HORSE = 222;
    const BANDAGE25 = 4;
    const BANDAGE33 = 7;
    const CAGE = 7;
    const SCROLL = 10;
    const OINTMENT = 7;
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
