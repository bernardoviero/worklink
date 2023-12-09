<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'nome',
        'email',
        'cpf_cnpj',
        'senha',
        'senha',
        'cep',
        'cidade',
        'contato',
        'data_nascimento',
        'genero',
        'uf',
    ];

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;
}
