<?php

namespace App\Classes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BaseModel extends Model
{
    /**
     * @inheritdoc
     */
    public function getTable()
    {
        // get singular name instead of plural
        if (! isset($this->table)) {
            return str_replace('\\', '', Str::snake(class_basename($this)));
        }

        return $this->table;
    }
}
