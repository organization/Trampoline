<?php

namespace ifteam\Trampoline\task;

use ifteam\Trampoline\Trampoline;
use pocketmine\scheduler\Task;

class fallenTimeOutTask extends Task{
    public $name;
    protected $owner;

    public function __construct(Trampoline $owner, $name){
        $this->owner = $owner;
        $this->name = $name;
    }

    public function onRun($currentTick){
        $this->owner->fallenTimeOut($this->name);
    }
}

?>