<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    // Tentukan 'kode' sebagai primary key
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'kode',
        'nama_lengkap',
        'target_harian_kg',
    ];

    /**
     * Relasi one-to-many: Satu Fakultas punya banyak data Sampah
     */
    public function sampahEntries()
    {
        return $this->hasMany(Sampah::class, 'fakultas_kode', 'kode');
    }
}
