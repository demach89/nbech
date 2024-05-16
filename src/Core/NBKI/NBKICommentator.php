<?php

namespace Core\NBKI;
use Core\Saver\ANSISaver;

/**
 * Класс добавления комментариев к НБКИ-выгрузке
 * Результат сохраняется в файл с аналогичным именем с окончанием '_comments'
 *
 * Class NBKICommentator
 * @package Core\NBKI
 */
class NBKICommentator
{
    protected string $nbkiFilePath;
    protected string $nbkiCommentsFilePath;
    protected array  $nbkiBlocks;

    use ANSISaver;

    public function __construct(string $nbkiFileName)
    {
        $this->nbkiFilePath = __DIR__ . "/../../../" . NBKI_FILES_DIR . "/$nbkiFileName";
        $this->nbkiCommentsFilePath = __DIR__ . "/../../../" . NBKI_FILES_DIR . "/{$nbkiFileName}_comments.txt";
    }

    public function load() : self
    {
        try {
            $content = file_get_contents($this->nbkiFilePath);

            if (!mb_check_encoding($content, 'UTF-8')) {
                $content = iconv("CP1251//TRANSLIT", "UTF-8", $content);
            }

            $csv = explode("\n", $content);
            $csv = array_filter($csv); // убирает пустые строки (актуально для концовки)

            array_walk($csv, function (&$a) {
                $a = explode("\t", $a);
            });
        } catch (\Throwable $e) {
            echo "NBKICommentator|load|Ошибка загрузки НБКИ-файла|{$e->getMessage()}\n";
            exit();
        }

        $this->nbkiBlocks = $csv;

        return $this;
    }

    /** Добавить комментарии */
    public function addComments() : self
    {
        $this->nbkiBlocks = (new NBKIHelper($this->nbkiBlocks))->addHelperBlocks();

        return $this;
    }

    /** Сохранить в файл */
    public function save() : void
    {
        try {
            $this->rowsToTXT($this->nbkiBlocks, $this->nbkiCommentsFilePath);
        } catch (\Throwable $e){
            echo "NBKICommentator|saveToFile|Ошибка выгрузки|{$e->getMessage()}\n";
            exit();
        }
    }
}