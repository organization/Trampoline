<?

namespace ifteam\Trampoline\task;

use ifteam\Trampoline\Trampoline;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\scheduler\Task;

class FallenTimeOutTask extends Task implements PluginOwned{
    protected Trampoline $owner;
    protected PLayer $player;

    public function __construct(Trampoline $owner, Player $player){
        $this->owner = $owner;
        $this->player = $player;
    }

    public function getOwningPlugin() : Trampoline{
        return $this->owner;
    }

    public function onRun() : void{
        $this->owner->fallenTimeOut($this->player);
    }
}