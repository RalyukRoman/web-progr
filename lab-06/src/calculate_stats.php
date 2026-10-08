<?php
if (!defined('SECURE_CORE')) {
    exit('Доступ заборонено');
}

function words(string $text): array
{
    $text = mb_strtolower(str_replace(["’", "ʼ"], "'", $text), 'UTF-8');
    preg_match_all("/\\p{L}+(?:'\\p{L}+)*/u", $text, $matches);
    return $matches[0];
}

function calculateTextStats(string $text): array
{
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    $clean_text = preg_replace('/\n\z/', '', $text);
    $raw_lines = explode("\n", $clean_text);
    $non_empty_lines = 0;

    foreach ($raw_lines as $line) {
        if (trim($line) !== '') {
            $non_empty_lines++;
        }
    }
    
    $all_words = words($text);
    $total_words = count($all_words);
    $unique_words_count = 0;
    $top_words = [];

    if ($total_words > 0) {
        $word_counts = array_count_values($all_words);
        $unique_words_count = count($word_counts);
        
        arsort($word_counts);
        uksort($word_counts, function($a, $b) use ($word_counts) {
            if ($word_counts[$a] === $word_counts[$b]) {
                return strcmp($a, $b);
            }
            return $word_counts[$b] <=> $word_counts[$a];
        });
        
        $top_words = array_slice($word_counts, 0, 5, true);
    }

    return [
        'total' => $total_words,
        'unique' => $unique_words_count,
        'top' => $top_words,
        'non_empty_lines' => $non_empty_lines
    ];
}
