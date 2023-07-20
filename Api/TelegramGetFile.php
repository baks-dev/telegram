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
 * Используйте этот метод для отправки фотографий.
 *
 * @see https://core.telegram.org/bots/api#sendphoto
 */
final class TelegramGetFile extends Telegram
{
    /** Идентификатор файла */
    private string $id;

    public function file(string $id): self
    {
        $this->id = $id;
        return $this;
    }


    protected function method(): string
    {
        return 'getFile';
    }

    protected function option(): ?array
    {
        return [
            'file_id' => $this->id
        ];
    }
}
