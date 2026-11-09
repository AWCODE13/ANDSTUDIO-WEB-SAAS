<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use BelongsToTenant;

    // Mengizinkan penyimpanan (mass-assignment) untuk semua kolom kecuali 'id'
    protected $guarded = ['id'];
}