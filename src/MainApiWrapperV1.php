<?php
declare(strict_types=1);

namespace Flyingmana\ApiDataWrapper;

class MainApiWrapperV1
{
    public function __construct(
        protected EnvStore $envStore,
        protected ?\Monolog\Logger $logger = null,
        protected ?\GuzzleHttp\Client $guzzle = null,

    )
    {
        if (is_null($this->logger)) {
            $this->initLogger();
        }

        if (is_null($this->guzzle)) {
            $this->initGuzzle();
        }
    }

    private function initLogger()
    {
        $fallbackLogFile = './guzzle_base.log';
        $streamHandler = new \Monolog\Handler\StreamHandler($fallbackLogFile);

        $logger = new \Monolog\Logger('logger');
        $logger->pushHandler($streamHandler);
        $logger->pushProcessor(new \Monolog\Processor\ProcessIdProcessor());
        $this->logger = $logger;
    }
    private function initGuzzle()
    {
        $stack = \GuzzleHttp\HandlerStack::create();
        $messageFormatter = new \GuzzleHttp\MessageFormatter();
        $guzzleMiddleware = \GuzzleHttp\Middleware::log($this->logger, $messageFormatter);

        $stack->push($guzzleMiddleware);
        $this->guzzle = new \GuzzleHttp\Client(
            [
                'base_uri' => $this->envStore->resolveVarInString('<<BASE_URL>>'),
                'handler' => $stack,
                'auth' => [
                    $this->envStore->resolveVarInString('<<user>>'),
                    $this->envStore->resolveVarInString('<<password>>')
                ],
                'headers' => [

               ]
            ]
        );
    }
}
