<?php
class Post
{
    public $title;

    public function __construct($title)
    {
        $this->title = $title;
    }

    public function getLabel()
    {
        return 'Допис: "' . $this->title . '"';
    }
}

$first = new Post('Вступ до PHP 8.1');
$second = new Post('Налаштування OSPanel');

echo "До зміни:\n";
echo $first->getLabel() . "\n";
echo $second->getLabel() . "\n\n";

$first->title = 'Вступ до PHP 8.1 та ООП';

echo "Після зміни назви першого об’єкта:\n";
echo $first->getLabel() . "\n";
echo $second->getLabel() . "\n";