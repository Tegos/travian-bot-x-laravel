<?php

namespace App\Travian\Api;

final class ApiTravian extends Api
{
    public function getBalances($data = []): array
    {
        return $this->queryRequest('balances', $data);
    }
}
