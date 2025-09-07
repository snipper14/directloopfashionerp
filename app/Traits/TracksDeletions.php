<?php 
namespace App\Traits;

trait TracksDeletions
{
    public static function bootTracksDeletions()
    { 
        static::deleting(function ($model) {
          
            if (auth()->check()) {
                $model->deleted_by = auth()->id();
                $model->saveQuietly();
            }
        });
    }
}
