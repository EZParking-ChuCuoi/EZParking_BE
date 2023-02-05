<?php

namespace App\Repositories\Implementations;
 
class MessagesRepository extends BaseRepository implements IMessagesRepository
{

    public function getModel(): string
    {
        return Message::class;
    }

    public function all($toArray = false): Collection|array|null
    {
        return null;
    }

    public function findMessagesByContent(int $conversationId, string $content, bool $toArray = false): Collection|array|null
    {
        $messages =
            $this->model
                ->where("con_id", $conversationId)
                ->where("content","LIKE", "%$content%")
                ->orderBy("created_at", "asc")
                ->get();
        return ($messages ? null : $toArray) ? $messages->toArray() : $messages;
    }

    public function getMessagesBy(int $conversationId, array $filter = ["offset" => 0, "limit" => 10], bool $toArray = false): Collection|array|null
    {
        $messages =
            $this->model
                ->where("con_id", $conversationId)
                ->skip($filter["offset"])
                ->take($filter["limit"])
                ->orderBy("created_at", "desc")
                ->get();
        return ($messages ? null : $toArray) ? $messages->toArray() : $messages;
    }
}
