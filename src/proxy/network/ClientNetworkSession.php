<?php

namespace proxy\network;


use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\SetPlayerGameTypePacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\TextPacket;
use pocketmine\utils\Terminal;
use pocketmine\utils\TextFormat;
use proxy\hosts\Client;
use proxy\hosts\ProxyClient;
use proxy\ProxyServer;
use proxy\utils\PacketSession;

class ClientNetworkSession
{

    /**
     * @var ProxyServer $proxyServer
     */
    private $proxyServer;

    /**
     * @var Client $client
     */
    private $client;

    /**
     * ClientNetworkSession constructor.
     * @param ProxyClient $client
     * @param ProxyServer $proxyServer
     */
    public function __construct(ProxyClient $client, ProxyServer $proxyServer)
    {
        $this->client = $client;
        $this->proxyServer = $proxyServer;
    }

    /**
     * @return ProxyServer
     */
    public function getProxy() : ProxyServer{
        return $this->proxyServer;
    }

    /**
     * @return ProxyClient
     */
    public function getClient() : ProxyClient{
        return $this->client;
    }

    /**
     * @param DataPacket $packet
     */
    public function handleClientDataPacket(DataPacket $packet){
        $packet->decode();
        if(!$packet->feof() && !$packet->mayHaveUnreadBytes()){
            $remains = substr($packet->buffer, $packet->offset);
            echo Terminal::$COLOR_BLUE . "Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains) . PHP_EOL;
            return;
        }
        foreach($this->getProxy()->getPluginManager()->getPlugins() as $plugin){
            if($plugin->isEnabled()){
                $plugin->handlePacketSend($packet);
            }
        }
        switch($packet::NETWORK_ID){
            case LoginPacket::NETWORK_ID;
            $this->getClient()->handleLogin($packet);
            break;
            case MovePlayerPacket::NETWORK_ID;
            $this->getClient()->setPosition($packet->position);
            break;
            case SetPlayerGameTypePacket::NETWORK_ID;
            $this->getClient()->setGamemode($packet->gamemode, false);
            break;
            case StartGamePacket::NETWORK_ID;
            $this->getClient()->setGamemode($packet->gamemode, false);
            break;
            case TextPacket::NETWORK_ID;
            $cmd = "*/";
            if($packet->type == TextPacket::TYPE_CHAT){
                foreach($this->getProxy()->getCommandMap()->getCommands() as $command => $object){
                        $args = explode(" " , $packet->message);
                        if(strtolower($args[0]) == "*/".strtolower($command)){
                            $object->execute($this->getClient(), $args);
                        }elseif(strpos($cmd, $packet->message) !== false){
                            $this->getClient()->sendMessage("• " . TextFormat::AQUA . "Unknown command issued. Type " . TextFormat::WHITE . "*/help " . TextFormat::AQUA . " for list of all commands");
                        }
                }
            }
            break;
        }
    }

    /**
     * @param DataPacket $packet
     */
    public function handleServerDataPacket(DataPacket $packet){
        $packets = [SetPlayerGameTypePacket::NETWORK_ID, StartGamePacket::NETWORK_ID];
        switch($packet::NETWORK_ID){
            case SetPlayerGameTypePacket::NETWORK_ID;
            $packet->decode();
            ECHO 'CC';
            $this->getClient()->setGamemode($packet->gamemode, false);
            break;
            case StartGamePacket::NETWORK_ID;
            $packet->decode();
            echo 'aa';
            $this->getClient()->setGamemode($packet->gamemode, false);
            break;
        }
        foreach($this->getProxy()->getPluginManager()->getPlugins() as $plugin){
            if($plugin->isEnabled()){
                $plugin->handlePacketReceive($packet);
            }
        }

    }

}