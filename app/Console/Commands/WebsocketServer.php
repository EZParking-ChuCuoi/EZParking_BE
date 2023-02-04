<?php

namespace App\Console\Commands;

use App\Services\WebSocket\ChatService;
use Illuminate\Console\Command;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command as CommandAlias;

class WebsocketServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'websocket:start {port}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start a websocket';

    public function __construct(
        private readonly ChatService $chat
    ){
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->chat
                )
            ),
            $this->argument("port")
        );
        $server->run();
        return CommandAlias::SUCCESS;
    }
}
