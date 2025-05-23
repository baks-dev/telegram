<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Telegram\Builder\ReplyKeyboardMarkup;

/**
 * @see https://core.telegram.org/bots/api#inlinekeyboardbutton
 */
final class ReplyKeyboardButton
{
    /**
     * Текст на кнопке.
     */
    private string|false $text = false;

    /**
     * HTTP или tg:// URL, который будет открываться при нажатии кнопки.
     */
    private array|false $url = false;

    /**
     * Данные, которые будут отправлены в обратном запросе боту при нажатии кнопки.
     * Размер - 1-64 байта.
     *
     * @see https://core.telegram.org/bots/api#callbackquery
     */
    private array|false $callbackData = false;

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    public function setUrl(string $url): self
    {
        $this->url = ['url' => $url];
        return $this;
    }

    public function setCallbackData(string $callbackData): self
    {
        $this->callbackData = ['callback_data' => $callbackData];
        return $this;
    }

    public function build(): array|null
    {
        if(false === $this->text)
        {
            return null;
        }

        $button = ['text' => $this->text];

        if(false !== $this->url)
        {
            $button += $this->url;
        }

        if(false !== $this->callbackData)
        {
            $button += $this->callbackData;
        }

        return $button;
    }
}
