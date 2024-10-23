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
use DateTimeImmutable;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Используйте этот метод для отправки текстовых сообщений.
 *
 * @see https://core.telegram.org/bots/api#sendmessage
 */
final class TelegramSendMessages extends Telegram
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

    public function option(): ?array
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
