<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description'];
    
    /**
     * The users that belong to the department.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    /**
     * The permissions that belong to the department.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    
    /**
     * Check if department has a specific permission
     */
    public function hasPermission($permissionSlug)
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }
}
