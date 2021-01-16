<?php

namespace ifteam\Trampoline\task;

use ifteam\Trampoline\Trampoline;
use pocketmine\scheduler\PluginTask;

class fallenTimeOutTask extends PluginTask{
    public $name;

    public function __construct(Trampoline $owner, $name){
        parent::__construct($owner);
        $this->name = $name;
    }

    public function onRun($currentTick){
        $this->owner->fallenTimeOut($this->name);
    }
}

?>