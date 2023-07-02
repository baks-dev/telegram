<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Telegram\Api\Webhook;

use BaksDev\Telegram\Api\Telegram;

/**
 * Используйте этот метод, чтобы указать URL-адрес и получать входящие обновления через исходящий веб-перехватчик.
 * Всякий раз, когда для бота появляется обновление, мы отправляем HTTPS-запрос POST на указанный URL-адрес,
 * содержащий сериализованное обновление JSON. В случае неудачного запроса мы откажемся после разумного количества попыток.
 * Возвращает True в случае успеха.
 *
 * @see https://core.telegram.org/bots/api#setwebhook
 */
final class TelegramSetWebhook extends Telegram
{
    private string $url;

    /**
     * Секретный токен, который будет отправлен в заголовке «X-Telegram-Bot-Api-Secret-Token» в каждом запросе вебхука, 1-256 символов.
     * Допускаются только символы A-Z, a-z, 0-9, _ и -.
     * Заголовок полезен для того, чтобы убедиться, что запрос исходит от установленного вами веб-перехватчика.
     */
    private ?string $secret = null;

    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

     public function secret(string $secret): self
     {
         $this->secret = $secret;
         return $this;
     }

    protected function method(): string
    {
        return 'setWebhook';
    }

    protected function option(): ?array
    {
        $option['url'] = $this->url;

        if ($this->secret) {
            $option['secret_token'] = $this->secret;
        }

        return $option;
    }
}
