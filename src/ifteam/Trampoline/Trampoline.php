<?php

namespace ifteam\Trampoline;

use ifteam\Trampoline\task\FallenTimeOutTask;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\color\Color;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\particle\DustParticle;

class Trampoline extends PluginBase implements Listener{
    public const MULTIPLY_MAP = [
        4 => 1,
        5 => 3,
        10 => 5
    ];

    /** @var int[] int(count)[string(playerHash)] */
    public array $fallen = [];

    public function onEnable() : void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onMove(PlayerMoveEvent $event) : void{
        $player = $event->getPlayer();
        $block = $player->getWorld()->getBlock($player->getPosition()->add(0.5, 0, 0.5)->round());

        //Check under block
        $underBlock = $block->getSide(Facing::DOWN);
        if(!$this->run($player, $underBlock, new Vector3(0, 1, 0))
            && $underBlock->getId() == BlockLegacyIds::DIAMOND_BLOCK
        ){
            //Support diamond block
            $this->setMotion($player, $player->getDirectionVector()->multiply(3));
            return;
        }

        //Check horizontal blocks
        foreach(Facing::HORIZONTAL as $side){
            if($this->run($player, $block->getSide($side), (new Vector3(0, 0, 0))->getSide($side)))
                break;
        }
    }

    public function run(Player $player, Block $block, Vector3 $direction) : bool{
        if($block->getId() === BlockLegacyIds::WOOL){
            $multiply = self::MULTIPLY_MAP[$block->getMeta()] ?? null;
            if($multiply !== null){
                $this->setMotion($player, $direction->multiply($multiply));
                return true;
            }
        }
        return false;
    }

    public function setMotion(Player $player, Vector3 $motion) : void{
        $player->setMotion($motion);

        $pos = $player->getPosition();
        $particle = new DustParticle(new Color(188, 32, 255, 255));

        $particlePackets = [];
        $particlePackets[] = $particle->encode($pos->add(0.4, 2, 0.0));
        $particlePackets[] = $particle->encode($pos->add(0.0, 2, 0.4));
        $particlePackets[] = $particle->encode($pos->add(-.6, 2, 0.0));
        $particlePackets[] = $particle->encode($pos->add(0.0, 2, -.6));
        $particlePackets[] = $particle->encode($pos->add(0.4, 2, 0.4));
        $this->getServer()->broadcastPackets($player->getViewers(), $particlePackets);

        if(!isset($this->fallen[$hash = spl_object_hash($player)])){
            $this->fallen[$hash] = 0;
        }
        $this->fallen[$hash]++;
        $this->getScheduler()->scheduleDelayedTask(new FallenTimeOutTask($this, $player), 100);
    }

    public function fallenTimeOut(Player $player) : void{
        if(isset($this->fallen[$hash = spl_object_hash($player)])){
            $this->fallen[$hash]--;
            if($this->fallen[$hash] <= 0){
                unset($this->fallen[$hash]);
            }
        }
    }

    public function preventFallDamage(EntityDamageEvent $event){
        if($event->getCause() === EntityDamageEvent::CAUSE_FALL
            && isset($this->fallen[spl_object_hash($event->getEntity())])
        ){
            $event->cancel();
        }
    }

    public function preventFlyKick(PlayerKickEvent $event) : void{
        if($event->getReason() === $this->getServer()->getLanguage()->translateString("kick.reason.cheat", ["%ability.flight"])
            && isset($this->fallen[spl_object_hash($event->getPlayer())])
        ){
            $event->cancel();
        }
    }
}