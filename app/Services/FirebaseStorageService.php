<?php

namespace App\Services;

use Kreait\Firebase\Storage;

class FirebaseStorageService
{
    protected $storage;
    protected $signedUrlExpiration;
    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
        $this->signedUrlExpiration = '+1 year';
    }

    public function uploadImage($file, $path = 'images')
    {
        $bucket = $this->storage->getBucket();
        $image = file_get_contents($file->getRealPath());
        $filename = time() . '_' . $file->getClientOriginalName();
        $object = $bucket->upload($image, [
            'name' => "$path/$filename"
        ]);

        return $object->signedUrl(new \DateTime($this->signedUrlExpiration));
        // sử dungj public url thay signedUrl
        //return $this->getPublicUrl($bucket->name(), "$path/$filename");
    }

}