<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employees;

class employees_tasks extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'employee_name',
        'task_id',
        'task_name',
        'entreprise_id', 
    ];

    public function employee()
    {
        return $this->belongsTo(Employees::class, 'employee_id');
    }
}
