<?php
class Post
{
    public $id;
    public $title;
    public $content;
    public $views;

    public function getLabel()
    {
        return '#' . $this->id . ' ' . $this->title;
    }

    public function getSnippet($length = 60)
    {
        if (mb_strlen($this->content) <= $length) {
            return $this->content;
        }
        return mb_substr($this->content, 0, $length) . '...';
    }

    public function rename($newTitle)
    {
        $this->title = trim($newTitle);
    }
}