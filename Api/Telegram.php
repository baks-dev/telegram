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

abstract class Telegram
{
    private MessageDispatchInterface $messageDispatch;

    public function __construct(MessageDispatchInterface $messageDispatch)
    {
        $this->messageDispatch = $messageDispatch;
    }

    abstract public function send(): void;

    /**
     * Токен авторизации.
     */
    private ?string $token = null;

    /**
     * Chanel.
     */
    private ?int $chanel = null;

    /**
     * Токен авторизации.
     */
    public function withToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Chanel.
     */
    public function getChanel(): ?int
    {
        return $this->chanel;
    }

    public function withChanel(?int $chanel): self
    {
        $this->chanel = $chanel;
        return $this;
    }

    /**
     * MessageDispatch.
     */
    public function getMessageDispatch(): MessageDispatchInterface
    {
        return $this->messageDispatch
            ->transport('telegram');
    }

}
