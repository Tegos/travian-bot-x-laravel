<?php

namespace App\Travian\Enums;

use ReflectionClass;

final class TravianAuctionCategory
{
    const LAW_TABLETS = 'lawTablets';

    const OINTMENT = 'ointment';

    const CAGE = 'cage';

    const SCROLL = 'scroll';

    const BANDAGE25 = 'bandage25';
    const BANDAGE33 = 'bandage33';

    const HELMET = 'helmet';
    const BODY = 'body';
    const LEFT_HAND = 'leftHand';
    const RIGHT_HAND = 'rightHand';
    const SHOES = 'shoes';
    const HORSE = 'horse';

    const BUCKET_OF_WATER = 'bucketOfWater';
    const BOOK_OF_WISDOM = 'bookOfWisdom';

    const ARTWORK = 'artWork';

    public static function getCategories(): array
    {
        $refInstanceClass = new ReflectionClass(__CLASS__);
        return $refInstanceClass->getConstants();
    }
}
