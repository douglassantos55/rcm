<?php

namespace App\Messenger;

interface Messenger
{
    public function send(mixed $data, string $key);
}
