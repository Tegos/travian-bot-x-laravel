<?php

namespace App\Travian\Enums;

use Illuminate\Support\Str;

final class TravianAuctionCategoryPrice
{
    const HELMET = 177;
    const BODY = 177;
    const LEFT_HAND = 105;
    const RIGHT_HAND = 177;
    const SHOES = 222;
    const HORSE = 555;
    const BANDAGE25 = 10;
    const BANDAGE33 = 15;
    const CAGE = 15;
    const SCROLL = 25;
    const OINTMENT = 15;
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
