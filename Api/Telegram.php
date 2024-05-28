<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Telegram\Api;

use App\Kernel;
use BaksDev\Core\Cache\AppCacheInterface;
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
    private TelegramBotSettingsInterface $telegramBotSettings;
    private AppCacheInterface $cache;
    private LoggerInterface $logger;

    public function __construct(
        AppCacheInterface $cache,
        TelegramBotSettingsInterface $telegramBotSettings,
        LoggerInterface $telegramLogger,
        MessageDispatchInterface $messageDispatch = null,
    )
    {
        $this->messageDispatch = $messageDispatch;
        $this->telegramBotSettings = $telegramBotSettings;
        $this->cache = $cache;
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

    /**
     * Token.
     */
    protected function getToken(): ?string
    {
        return $this->token;
    }

    public function send(bool $async = false): bool|array|null
    {
        //        if(Kernel::isTestEnvironment())
        //        {
        //            return null;
        //        }

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

        return (new TelegramSender($this->cache, $this->logger))($TelegramMessage) ?: false;
    }

}
