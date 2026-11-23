<?php

class User extends Model
{
    protected static string $defaultTable = 'users';
    
    public function posts(): array
    {
        return $this->hasMany(Post::class, 'user_id');
    }
}