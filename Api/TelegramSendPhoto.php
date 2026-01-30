<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
     * Фото для отправки. Передайте file_id в виде строки, чтобы отправить фотографию, которая существует на серверах
     * Telegram (рекомендуется), передайте URL-адрес HTTP в виде строки, чтобы Telegram мог получить фотографию из
     * Интернета, или загрузите новую фотографию, используя multipart/form-data. Фотография должна быть размером не
     * более 10 МБ. Суммарная ширина и высота фотографии не должны превышать 10000. Соотношение ширины и высоты должно
     * быть не более 20.
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

        if($this->chanel === null)
        {
            throw new InvalidArgumentException('Не указан идентификатор чата Telegram');
        }


        $option['chat_id'] = $this->chanel;


        if($this->photo === null)
        {
            throw new InvalidArgumentException('Не указано фото для отправки в Telegram');
        }


        $option['photo'] = $this->photo;


        if($this->caption)
        {
            $option['caption'] = $this->caption;
            $option['parse_mode'] = 'html';
        }

        if($this->markup)
        {
            $option['reply_markup'] = $this->markup;
        }


        return $option;
    }

    public function url(string $url): void
    {
        $this->photo = $url;
    }

    /** Пережимает файл изображения подходящий под отправку */
    public function file(string $filepath): self
    {
        if(false === file_exists($filepath))
        {
            $this->logger()->critical(
                'telegram: Файл для отправки фото не найден',
                [self::class.':'.__LINE__, $filepath],
            );

            return $this;
        }

        $fileInfo = pathinfo($filepath);

        /** Присваиваем путь к сжатому локальному изображению */
        $photo = $fileInfo['dirname'].DIRECTORY_SEPARATOR.'small.webp';

        if(true === file_exists($photo))
        {
            unlink($photo);
        }

        /** Получаем файл для конвертации  */
        $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()

        $allowedTypes = [
            1, // gif
            2, // jpg
            3, // png
            6, // bmp
            18, // webp
        ];

        if(false === in_array($type, $allowedTypes, true))
        {
            $this->logger()->critical(
                'telegram: Неподдерживаемый тип файла изображения',
                [self::class.':'.__LINE__, $filepath],
            );

            return $this;
        }

        $img = match ($type)
        {
            1 => imageCreateFromGif($filepath),
            2 => imageCreateFromJpeg($filepath),
            3 => imageCreateFromPng($filepath),
            6 => imageCreateFromBmp($filepath),
            18 => imageCreateFromWebp($filepath),
        };

        $img_small = $this->resize($img, 640);
        imagewebp($img_small, $photo, 80);
        imagedestroy($img_small);

        return $this;
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
