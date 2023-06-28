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

use BaksDev\Telegram\Messenger\TelegramMessage;
use CURLFile;

final class TelegramSendPhoto extends Telegram
{
    private const METHOD = 'sendPhoto';

    private CURLFile|string $photo;

    private string $caption;

    public function photo(CURLFile|string $photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    /** подпись */
    public function caption(string $caption): self
    {
        $this->caption = $caption;
        return $this;
    }

    public function send(): void
    {
        $TelegramMessage = new TelegramMessage(
            self::METHOD,
            [
                'photo' => $this->photo,
                'caption' => $this->caption,
            ],
            $this->getChanel(),
            $this->getToken()
        );

        $this->getMessageDispatch()->dispatch($TelegramMessage);
    }
}
