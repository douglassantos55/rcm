<?php

namespace App\Messenger;

use JsonSerializable;

interface Messenger
{
    public function send(JsonSerializable $data, string $key);
}
