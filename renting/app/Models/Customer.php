<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'cpf_cnpj',
        'rg_insc_est',
        'birthdate',
        'phone',
        'cellphone',
        'ocupation',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'postcode',
        'observations',
    ];
}
