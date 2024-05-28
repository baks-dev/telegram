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
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Используйте этот метод для отправки текстовых сообщений.
 *
 * @see https://core.telegram.org/bots/api#sendmessage
 */
final class TelegramSendMessage extends Telegram
{
    /**
     * Сообщение
     */
    #[Assert\NotBlank]
    private ?string $message = null;

    /**
     * Встраиваемая клавиатура
     */
    private ?string $markup = null;

    /**
     * Идентификатор предыдущего сообщения для удаления
     */
    private array $delete = [];


    public function message(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Message
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function markup(array|string|null $markup): self
    {
        $this->markup = is_array($markup) ? json_encode($markup) : $markup;
        return $this;
    }


    protected function method(): string
    {
        return 'sendMessage';
    }

    public function delete(int|array $delete): self
    {
        if(is_array($delete) && !empty($delete))
        {
            $this->delete = $delete;
            return $this;
        }

        if($delete)
        {
            $this->delete = [$delete];
        }

        return $this;
    }

    function option(): ?array
    {
        if($this->chanel === null)
        {
            throw new InvalidArgumentException('Не указан идентификатор чата Telegram');
        }

        $option['chat_id'] = $this->chanel;

        if($this->message === null)
        {
            throw new InvalidArgumentException('Не указан текст сообщения для отправки в Telegram');
        }


        if(Kernel::isTestEnvironment())
        {
            $option['disable_notification'] = true;
        }

        $now = new DateTimeImmutable();

        /** Сегодня с 00:00 до 8:00 */
        $startNightsTime = DateTimeImmutable::createFromFormat('H:i', '00:00');
        $endNightsTime = DateTimeImmutable::createFromFormat('H:i', '08:00');

        /** Сегодня с 20:00 до 00:00 */
        $startEveningTime = DateTimeImmutable::createFromFormat('H:i', '20:00');
        $endEveningTime = DateTimeImmutable::createFromFormat('H:i', '00:00');

        /**
         * Отправляем сообщение без звука:
         * - ночное время (20:00 - 8:00)
         * - субботу или воскресенье
        */
        if(
            ($now > $startNightsTime && $now < $endNightsTime) ||
            ($now > $startEveningTime && $now < $endEveningTime) ||
            in_array($now->format('D'), ['Sat', 'Sun'])
        )
        {
            $option['disable_notification'] = true;
        }

        $option['text'] = $this->message;

        if($this->markup)
        {
            $option['reply_markup'] = $this->markup;
        }

        $option['parse_mode'] = 'html';

        if($this->delete)
        {
            $option['delete'] = $this->delete;
        }

        return $option;
    }


}
