<?php

namespace App\PrintLabel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrintLabelDimension extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'bar_font_size',
        'bar_height',
        'bar_width',
        'wrapper_height',
        'wrapper_width',
        'item_description_fontsize',
      
    ];
}
