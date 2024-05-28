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

/**
* Используйте этот метод для удаления сообщения, в том числе служебного, со следующими ограничениями:
- Сообщение может быть удалено только в том случае, если оно было отправлено менее 48 часов назад.
- Служебные сообщения о создании супергруппы, канала или темы форума не могут быть удалены.
- Сообщение с кубиками в приватном чате можно удалить только в том случае, если оно было отправлено более 24 часов назад.
- Боты могут удалять исходящие сообщения в приватных чатах, группах и супергруппах.
- Боты могут удалять входящие сообщения в приватных чатах.
- Боты с разрешениями can_post_messages могут удалять исходящие сообщения в каналах.
- Если бот является администратором группы, он может удалить там любое сообщение.
- Если у бота есть разрешение can_delete_messages в супергруппе или канале, он может удалить там любое сообщение.
Возвращает True в случае успеха.
 *
 * @see https://core.telegram.org/bots/api#deletemessage
 */
final class TelegramDeleteMessage extends Telegram
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
