<?php

namespace App\Category;

use App\dept\Department;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
   use SoftDeletes;
   protected $fillable = [
      'name',
      'department_id',
      'details',
      'banner',
      'show_pos',//bolean
      'isSync'//[0,1]
   ];
   protected $appends = ['img_url'];
   public  function getImgUrlAttribute()
   {
       return  App::make('url')->to('/') . '/stock_img/' . $this->banner;
   }

   function department()
   {
      return $this->belongsTo(Department::class)->withTrashed();
   }
   function pos_department(){
      return $this->department()->where('show_pos',true);
   }
}
