<?php

namespace App\Travian\Enums;

use ReflectionClass;

final class TravianAuctionCategory
{
    const HELMET = 'helmet';
    const BODY = 'body';
    const LEFT_HAND = 'leftHand';
    const RIGHT_HAND = 'rightHand';
    const SHOES = 'shoes';
    const HORSE = 'horse';
    const BANDAGE25 = 'bandage25';
    const BANDAGE33 = 'bandage33';
    const CAGE = 'cage';
    const SCROLL = 'scroll';
    const OINTMENT = 'ointment';
    const BUCKET_OF_WATER = 'bucketOfWater';
    const BOOK_OF_WISDOM = 'bookOfWisdom';
    const LAW_TABLETS = 'lawTablets';
    const ARTWORK = 'artWork';

    public static function getCategories(): array
    {
        $refInstanceClass = new ReflectionClass(__CLASS__);
        return $refInstanceClass->getConstants();
    }
}
