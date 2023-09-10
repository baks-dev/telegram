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

use CURLFile;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Используйте этот метод для отправки фотографий.
 *
 * @see https://core.telegram.org/bots/api#sendphoto
 */
final class TelegramSendPhoto extends Telegram
{
    /**
     * Фото для отправки. Передайте file_id в виде строки, чтобы отправить фотографию, которая существует на серверах Telegram (рекомендуется),
     * передайте URL-адрес HTTP в виде строки, чтобы Telegram мог получить фотографию из Интернета,
     * или загрузите новую фотографию, используя multipart/form-data. Фотография должна быть размером не более 10 МБ.
     * Суммарная ширина и высота фотографии не должны превышать 10000. Соотношение ширины и высоты должно быть не более 20.
     */
    #[Assert\NotBlank]
    private CURLFile|string|null $photo = null;


    private ?string $caption = null;

    /**
     * Встраиваемая клавиатура
     */
    private ?string $markup = null;

    
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


    protected function method(): string
    {
        return 'sendPhoto';
    }

    protected function option(): ?array
    {

        if ($this->chanel === null)
        {
            throw new InvalidArgumentException('Не указан идентификатор чата Telegram');
        }


        $option['chat_id'] = $this->chanel;


        if ($this->photo === null)
        {
            throw new InvalidArgumentException('Не указано фото для отправки в Telegram');
        }


        $option['photo'] = $this->photo;


        if ($this->caption)
        {
            $option['caption'] = $this->caption;
            $option['parse_mode'] = 'html';
        }

        if ($this->markup)
        {
            $option['reply_markup'] = $this->markup;
        }

        
        return $option;
    }

    public function url(string $url): void
    {
        $this->photo = $url;
    }

    public function file(string $filepath): void
    {
        $fileInfo = pathinfo($filepath);

        if(file_exists($fileInfo['dirname'].'/'.$fileInfo['filename'].'.small.webp'))
        {
            return;
        }

        /** Получаем файл для конвертации  */
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()

        $allowedTypes = [
            1,  // [] gif
            2,  // [] jpg
            3,  // [] png
            6,   // [] bmp
            18,   // [] webp
        ];

        if (!in_array($type, $allowedTypes, true)) {
            throw new InvalidArgumentException('Error type images');
        }

        switch ($type) {
            case 1:
                $img = imageCreateFromGif($filepath);

                break;

            case 2:
                $img = imageCreateFromJpeg($filepath);

                break;

            case 3:
                $img = imageCreateFromPng($filepath);

                break;

            case 6:
                $img = imageCreateFromBmp($filepath);

                break;

            case 18:
                $img = imageCreateFromWebp($filepath);

                break;
        }

        $img_small = $this->resize($img, 240);
        imagewebp($img_small, $fileInfo['dirname'].'/'.$fileInfo['filename'].'.small.webp', 80);
        imagedestroy($img_small);
    }


    private function resize($img, $height)
    {
        $getWidth = imagesx($img);
        $getHeight = imagesy($img);

        $ratio = $height / $getHeight;
        $width = $getWidth * $ratio;

        $newImage = imagecreatetruecolor($width, $height);
        imagepalettetotruecolor($newImage);
        imagealphablending($newImage, false);
        imagecopyresampled($newImage, $img, 0, 0, 0, 0, $width, $height, $getWidth, $getHeight);

        return $newImage;
    }

    public function markup(array|string $markup): self
    {
        $this->markup = is_array($markup) ? json_encode($markup) : $markup;
        return $this;
    }


}
