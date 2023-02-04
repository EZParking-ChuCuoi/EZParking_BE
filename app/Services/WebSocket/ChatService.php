<?php

namespace App\Services\WebSocket;

use App\Repositories\Interfaces\IConversationRepository;
use App\Repositories\Interfaces\IMemberRepository;
use App\Repositories\Interfaces\IMessagesRepository;
use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IMessagesService;
use App\Services\Interfaces\IRedisService;
use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class ChatService implements MessageComponentInterface
{
    private array $userConnectionMap;
    private array $connectionIdUserIdMap;

    public function __construct(
        private readonly IRedisService $redisService,
        private readonly IUserRepository $userRepository,
        private readonly IMessagesService $messagesService,
        private readonly IMemberRepository $memberRepository
    )
    {

        $this->userConnectionMap = [];
        $this->connectionIdUserIdMap= [];
        echo "Server started!\n";
    }

    public function onOpen(ConnectionInterface $connection)
    {
        // Store the new connection to send messages to later
//        $this->clients->attach($connection);
        echo "New connection! ({$connection->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);
        $connectionId = $from->resourceId;
        $userId = $data->uid;

        switch ($data->type) {
            default:
            {
                return;
            }
            case "client":
            {
                $this->userConnectionMap[$userId] = $from;
                $this->connectionIdUserIdMap[$connectionId] = $userId;

                ["full_name" => $fullName] = $this->userRepository->getInfo($userId);
                $this->redisService->setFullName($userId, $fullName);
                $this->redisService->setActive($userId);
                $from->send(json_encode(["connectionId" => $connectionId, "type" => "system"]));

                echo "User {$userId} with connection {$connectionId}\n";
                return;
            }
            case "joinConversation": {
                $conversationId = $data->conId;
                if ($this->memberRepository->isMemberInConversation($userId, $conversationId)) {
                    $this->redisService->addActiveMemberToConversation($userId, $conversationId);
                }
                return;
            }
            case "sendMsg":
            {
                $fullName = $this->redisService->getFullName($userId);
                $conId = $data->conId;
                $this->messagesService->save($userId, $conId, $data->message);
                $isUserInConversation = $this->redisService->isUserInConversation($userId, $conId);
                if ($isUserInConversation) $this->sendMessageToConversation($conId,  ["message" => $data->message, "from" => $userId, "fullName" => $fullName]);
                return;
            }
        }
    }

    public function onClose(ConnectionInterface $connection)
    {
        $connectionId = $connection->resourceId;
        $userId = $this->connectionIdUserIdMap[$connectionId];

        $this->redisService->deleteFullName($userId);
        unset($this->userConnectionMap[$userId]);
        unset( $this->connectionIdUserIdMap[$connectionId]);
        echo "Connection {$connectionId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function sendMessageToConversation(int $conId, array $data)
    {
        $members = $this->redisService->getMemberOfConversation($conId);
        foreach ($members as $member) {
            if ($member != $data["from"]) $this->userConnectionMap[$member]->send(json_encode($data));
        }
        $this->redisService->renewExpireTimeOfConversation($conId);
    }

    private function sendMessage(int $userId, array $data): void
    {
        $this->userConnectionMap[$userId]->send(json_encode($data));
    }
}

