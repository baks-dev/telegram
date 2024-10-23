<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Telegram\Api;

use App\Kernel;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Telegram\Bot\Repository\UsersTableTelegramSettings\TelegramBotSettingsInterface;
use BaksDev\Telegram\Messenger\TelegramMessage;
use BaksDev\Telegram\Messenger\TelegramSender;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class Telegram
{
    private ?MessageDispatchInterface $messageDispatch;

    protected string|int|null $chanel = null;

    private LoggerInterface $logger;

    public function __construct(
        private readonly AppCacheInterface $cache,
        private readonly TelegramBotSettingsInterface $telegramBotSettings,
        private readonly DeduplicatorInterface $deduplicator,
        LoggerInterface $telegramLogger,
        ?MessageDispatchInterface $messageDispatch = null,
    )
    {
        $this->messageDispatch = $messageDispatch;
        $this->logger = $telegramLogger;
    }


    abstract protected function option(): ?array;

    abstract protected function method(): string;

    /**
     * Токен авторизации.
     */
    private ?string $token = null;

    public function token(string $token): self
    {
        $this->token = $token;
        return $this;
    }


    public function chanel(int|string $chanel): self
    {
        if(is_string($chanel))
        {
            $chanel = (int) filter_var($chanel, FILTER_SANITIZE_NUMBER_INT);
        }

        $this->chanel = $chanel;

        return $this;
    }

    protected function getToken(): ?string
    {
        return $this->token;
    }

    public function send(bool $async = false): bool|array|null
    {
        if($this->token === null)
        {
            $settings = $this->telegramBotSettings->settings();

            if(!$settings)
            {
                throw new InvalidArgumentException('Не указан токен авторизации Telegram');
            }

            $this->token = $this->telegramBotSettings->settings()->getToken();
        }

        $TelegramMessage = new TelegramMessage(
            method: $this->method(),
            option: $this->option(),
            token: $this->token
        );

        if($async && $this->messageDispatch)
        {
            $this->messageDispatch->dispatch($TelegramMessage, transport: $this->method() === 'deleteMessage' ? 'async' : 'telegram');
            return true;
        }

        return (new TelegramSender($this->cache, $this->deduplicator, $this->logger))($TelegramMessage) ?: false;
    }

}
