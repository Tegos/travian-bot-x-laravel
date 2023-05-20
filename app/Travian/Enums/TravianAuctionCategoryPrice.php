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
    const BANDAGE25 = 3;
    const BANDAGE33 = 5;
    const CAGE = 5;
    const SCROLL = 5;
    const OINTMENT = 3;
    const BUCKET_OF_WATER = 222;
    const BOOK_OF_WISDOM = 111;
    const LAW_TABLETS = 9;
    const ART_WORK = 555;

    public static function getPrice(string $itemCategory)
    {
        $category = Str::snake($itemCategory);
        $category = Str::upper($category);

        $code = implode('', [self::class, '::', $category]);

        return defined($code) ? constant($code) : 1;
    }
}
