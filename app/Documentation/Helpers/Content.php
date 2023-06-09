<?php

namespace App\Documentation\Helpers;

use App\Documentation\Helpers\Tree\Branch;

class Content
{
  public const FILE = __DIR__ . '/../data/pages.json';
  public static $rawContents = null;
  public $content = null;
  public $fullPath = null;
  public $title = null;

  public static function getRawContents()
  {
    if (self::$rawContents === null) {
      self::$rawContents = json_decode(file_get_contents(self::FILE), true);
    }
    return self::$rawContents;
  }

  public static function saveRawContents()
  {
    $json = preg_replace_callback('/^ +/m', function ($m) {
      return str_repeat("\t", strlen($m[0]) / 4);
      // return str_repeat(' ', strlen($m[0]) / 2);
    }, json_encode(self::$rawContents, JSON_PRETTY_PRINT));
    file_put_contents(self::FILE, $json, 0);
  }

  public static function addRawContent($fullPath, $content)
  {
    self::$rawContents[$fullPath] = $content;
    self::saveRawContents();
  }

  public static function removeRawContent($fullPath)
  {
    unset(self::$rawContents[$fullPath]);
    self::saveRawContents();
  }

  public static function getByFullPath($fullPath)
  {
    $rawContents = self::getRawContents();
    $data = $rawContents[$fullPath] ?? null;
    if (!$data) {
      return null;
    }
    return new Content($fullPath, $data);
  }

  public function __construct($fullPath, $data = [])
  {
    $this->fullPath = $fullPath;
    $this->content = $data["content"] ?? [];
    $this->title = $data["title"] ?? "";
  }


  public function toArray()
  {
    return [
      "title" => $this->title,
      "content" => $this->content,
      "fullPath" => $this->fullPath ?? "",
    ];
  }

  public function save()
  {
    self::addRawContent($this->fullPath, $this->toArray());
  }

  public function setContent($content)
  {
    $this->content = $content;
    $this->save();
  }

  public function getContent()
  {
    return $this->content;
  }

  public function remove()
  {
    self::removeRawContent($this->fullPath);
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getRawTextContent()
  {
    $blocks = $this->content["blocks"] ?? [];
    $text = "";
    foreach ($blocks as $block) {
      $data = $block["data"] ?? [];
      foreach ($data as $key => $value) {
        switch ($key) {
          case "code":
          case "text":
            $text .= $value . "\n";
            break;
          case "items":
            foreach ($value as $item) {
              $text .= $item . "\n";
            }
            break;
        }
      }
    }
    return $text;
  }

  public function setTitle($title)
  {
    $this->title = $title;
  }

  public function setFullPath($fullPath)
  {
    $this->remove();
    $this->fullPath = $fullPath;
    $this->save();
  }
}
