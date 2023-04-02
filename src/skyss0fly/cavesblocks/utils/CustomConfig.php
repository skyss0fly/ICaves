<?php

namespace skyss0fly\cavesblocks\utils;

use pocketmine\utils\Config;

class CustomConfig
{

    public function __construct(private Config $config)
    {
    }

    public function match(string $key): bool
    {
        $value = $this->config->getNested($key, true);
        return boolval($value);
    }

    public function isEnableDeepslate()
    {
        return $this->match("blocks.deepslate_block");
    }

}
    
