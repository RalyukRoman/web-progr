<?php

class Post extends Model
{
    protected static string $defaultTable = 'posts';

    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}