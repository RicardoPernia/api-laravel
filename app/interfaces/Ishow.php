<?php


namespace App\interfaces;


use Illuminate\Database\Eloquent\Model;

interface Ishow
{
   public function _showOne($id);
   public function _getInstance($id);
}
