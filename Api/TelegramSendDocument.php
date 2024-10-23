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
