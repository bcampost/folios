<?php

namespace App\Http\Controllers\Api;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends ApiController
{
    public function destroy(Media $media)
    {
        $media->delete();

        return $this->responseNoContent();
    }
}
