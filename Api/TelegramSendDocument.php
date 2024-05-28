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

use CURLFile;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Используйте этот метод для отправки фотографий.
 *
 * @see https://core.telegram.org/bots/api#sendphoto
 */
final class TelegramSendDocument extends Telegram
{
    /**
     * Видео для отправки.
     *  Передайте file_id в качестве строки, чтобы отправить видео, существующее на серверах Telegram (рекомендуется),
     *  передайте URL-адрес HTTP в качестве строки для Telegram, чтобы получить видео из Интернета,
     *  загрузите новое видео, используя multipart/form-data.
     */
    #[Assert\NotBlank]
    private ?string $document = null;


    /**
     * Встраиваемая клавиатура
     */
    private ?string $markup = null;


    public function document(string $document): self
    {
        $this->document = $document;
        return $this;
    }

    protected function method(): string
    {
        return 'sendDocument';
    }

    protected function option(): ?array
    {
        if($this->chanel === null)
        {
            throw new InvalidArgumentException('Не указан идентификатор чата Telegram');
        }

        $option['chat_id'] = $this->chanel;

        if($this->document === null)
        {
            throw new InvalidArgumentException('Не указано видео для отправки в Telegram');
        }

        $option['document'] = $this->document;

        return $option;
    }
}
