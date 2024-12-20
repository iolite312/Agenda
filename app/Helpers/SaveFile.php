<?php

namespace App\Helpers;

use Ramsey\Uuid\Uuid;
use App\Enums\ResponseEnum;

class SaveFile
{
    public static function saveFile($file)
    {
        $base64String = $file;
        $data = explode(',', $base64String);
        if (count($data) === 2 && preg_match('/^data:image\/(\w+);base64/', $data[0], $matches) === 1) {
            $imageType = $matches[1];
            $allowedTypes = ['png', 'jpeg', 'jpg', 'gif'];

            if (in_array($imageType, $allowedTypes)) {
                $imageData = base64_decode($data[1]);
                $newAvatarName = Uuid::uuid4()->toString() . '.' . $imageType;
                $uploadDir = '/app/public/assets/images/uploads/';
                $destination = "{$uploadDir}{$newAvatarName}";

                if (file_put_contents($destination, $imageData)) {
                    return ['type' => ResponseEnum::SUCCESS, 'name' => $newAvatarName];
                } else {
                    return ['type' => ResponseEnum::ERROR, 'Error' => 'Failed to save image.'];
                }
            } else {
                return ['type' => ResponseEnum::UNKOWN, 'Error' => 'Internal Server Error'];
            }
        }
    }

    public static function deleteFile($file)
    {
        $uploadDir = '/app/public/assets/images/uploads/';
        $destination = "{$uploadDir}{$file}";
        file_exists($destination) && unlink($destination);
    }
}
