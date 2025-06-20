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

namespace BaksDev\Telegram\Api\Webhook;

use BaksDev\Telegram\Api\Telegram;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * Используйте этот метод, чтобы получить текущий статус веб-перехватчика. Не требует параметров.
 * В случае успеха возвращает объект WebhookInfo.
 * Если бот использует getUpdates, он вернет объект с пустым полем URL.
 *
 * @see https://core.telegram.org/bots/api#getwebhookinfo
 * @see TelegramWebhookInfoTest
 */
#[Autoconfigure(public: true)]
final class TelegramWebhookInfo extends Telegram
{
    protected function method(): string
    {
        return 'getWebhookInfo';
    }

    protected function option(): ?array
    {
        return null;
    }
}
