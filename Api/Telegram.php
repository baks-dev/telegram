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

use BaksDev\Core\Services\Messenger\MessageDispatchInterface;
use BaksDev\Telegram\Messenger\TelegramMessage;
use BaksDev\Telegram\Messenger\TelegramSender;
use InvalidArgumentException;

abstract class Telegram
{
    private ?MessageDispatchInterface $messageDispatch;

    protected string|int|null $chanel = null;

    public function __construct(MessageDispatchInterface $messageDispatch = null)
    {
        $this->messageDispatch = $messageDispatch;
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

    public function send(bool $async = true): bool|array|null
    {
        if ($this->token === null)
        {
            throw new InvalidArgumentException('Не указан токен авторизации Telegram');
        }

        $TelegramMessage = new TelegramMessage(
            method: $this->method(),
            option: $this->option(),
            token: $this->token
        );

        if ($async)
        {
            $this->messageDispatch->dispatch($TelegramMessage, transport: 'telegram');
            return true;
        }

        return (new TelegramSender())($TelegramMessage) ?: false;
    }

}
