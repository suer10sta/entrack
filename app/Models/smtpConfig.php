<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class smtpConfig extends Model
{
    use HasFactory;

    protected $table = 'smtp_config';
    protected $fillable = ['mail_host', 'mail_port', 'mail_password', 'mail_username', 'entreprise_id'];
}
