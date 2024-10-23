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

/**
 * Используйте этот метод для удаления сообщения, в том числе служебного, со следующими ограничениями:
 * - Сообщение может быть удалено только в том случае, если оно было отправлено менее 48 часов назад.
 * - Служебные сообщения о создании супергруппы, канала или темы форума не могут быть удалены.
 * - Сообщение с кубиками в приватном чате можно удалить только в том случае, если оно было отправлено более 24 часов назад.
 * - Боты могут удалять исходящие сообщения в приватных чатах, группах и супергруппах.
 * - Боты могут удалять входящие сообщения в приватных чатах.
 * - Боты с разрешениями can_post_messages могут удалять исходящие сообщения в каналах.
 * - Если бот является администратором группы, он может удалить там любое сообщение.
 * - Если у бота есть разрешение can_delete_messages в супергруппе или канале, он может удалить там любое сообщение.
 * Возвращает True в случае успеха.
 *
 * @see https://core.telegram.org/bots/api#deletemessage
 */
final class TelegramDeleteMessages extends Telegram
{
    private int $message;

    public function delete(int $message): self
    {
        $this->message = $message;
        return $this;
    }

    protected function method(): string
    {
        return 'deleteMessage';
    }

    protected function option(): ?array
    {
        $option['chat_id'] = $this->chanel;
        $option['message_id'] = $this->message;

        return $option;

    }

}
