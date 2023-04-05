<?php

namespace skyss0fly\cavesblocks\EventListener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use skyss0fly\cavesblocks\player\Player as MyPlayer;

class EventListener implements Listener {

    public function onPlayerCreation(PlayerCreationEvent $event){
        $event->setPlayerClass(MyPlayer::class);
    }
}
