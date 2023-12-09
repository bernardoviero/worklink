<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Trabalho extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'trabalhos';

    protected $fillable = [
        'id_trabalho',
        'id_usuario',
        'id_tipo_trabalho',
        'pagamento',
        'valor',
        'ativo'
    ];

    protected $primaryKey = 'id_trabalho';

    public $timestamps = false;
}
