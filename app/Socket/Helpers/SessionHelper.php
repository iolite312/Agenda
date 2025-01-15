<?php

namespace App\Socket\Helpers;

class SessionHelper
{
    public static function getSessionId(int $clientId, \SplObjectStorage $clients)
    {
        foreach ($clients as $client) {
            if ($client->resourceId === $clientId) {
                return $clients->offsetGet($client);
            }
        }
    }
}
