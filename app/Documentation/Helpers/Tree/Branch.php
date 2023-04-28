<?php

namespace App\Documentation\Helpers\Tree;

use App\Documentation\Helpers\Content;
use App\Documentation\Helpers\Tree;

class Branch extends Tree
{
  public string $name;
  public string $title;
  public int $position;
  /**
   * Enfants
   * @var Branch[]
   */
  public array $children = [];
  /**
   * 
   * @var Tree|Branch
   */
  protected $parent;
  public static array $renamed = [];

  function __construct($item, $parent)
  {
    $this->name = $item["name"];
    $this->title = $item["title"];
    $this->position = $item["position"];
    $this->parent = $parent;
    if (isset($item["items"])) {
      $this->children = self::createFromItems($item["items"], $this);
    }
    $this->root = $parent->root;
  }

  public static function createFromItems($items, $parent)
  {
    $branches = [];
    $count = 0;
    foreach ($items as $item) {
      $branches[] = new Branch($item, $parent);
      $count++;
    }
    return $branches;
  }

  public function setName($name)
  {
    $oldFullPath = $this->getFullPath();
    $oldName = $this->name;
    if ($oldName == $name) {
      return $this;
    }
    if ($this->parent->getChildByName($name)) {
      throw new \Exception("Un élément du même nom existe déjà");
    }
    $this->name = $name;
    $this->parent->sortChildren();
    self::$renamed[$oldFullPath] = $this->getFullPath();
    return $this;
  }

  public function toArray()
  {
    $items = [];
    if (isset($this->children)) {
      foreach ($this->children as $child) {
        $items[$child->name] = $child->toArray();
      }
    }
    return [
      "name" => $this->name,
      "title" => $this->title,
      "position" => $this->position,
      "items" => $items
    ];
  }

  public function moveUp()
  {
    $upperPosition = $this->position - 1;
    $upperChild = $this->parent->getChildByPosition($upperPosition);
    if ($upperChild) {
      $this->position = $upperPosition;
      $upperChild->position = $this->position + 1;
    }
    //On ordonne les enfants par position en gardant les clés
    $this->parent->sortChildren();
  }

  public function moveDown()
  {
    $upperPosition = $this->position + 1;
    $upperChild = $this->parent->getChildByPosition($upperPosition);
    if ($upperChild) {
      $this->position = $upperPosition;
      $upperChild->position = $this->position - 1;
    }
    //On ordonne les enfants par position en gardant les clés
    $this->parent->sortChildren();
  }

  public function move($direction)
  {
    if ($direction == "up") {
      $this->moveUp();
    } else {
      $this->moveDown();
    }
  }

  public function getChildByPosition($position)
  {
    foreach ($this->children as $child) {
      if ($child->position == $position) {
        return $child;
      }
    }
    return null;
  }

  public function remove()
  {
    self::removeBranch($this);
  }

  public function getFullPath()
  {
    return implode(".", $this->getFullPathArray());
  }

  public static function repercussionContent()
  {
    foreach (self::$renamed as $oldFullPath => $newFullPath) {
      $content = Content::getByFullPath($oldFullPath);
      $content->setFullPath($newFullPath);
    }
  }

  public function getFullPathArray()
  {
    $path = [$this->name];
    $parent = $this->parent;
    while ($parent instanceof Branch) {
      array_unshift($path, $parent->name);
      $parent = $parent->parent;
    }
    return $path;
  }

  public function getFilAriane()
  {
    $path = [$this];
    $parent = $this->parent;
    while ($parent instanceof Branch) {
      $path[] = $parent;
      $parent = $parent->parent;
    }
    return array_reverse($path);
  }

  public function getFilArianeString()
  {
    $filAriane = $this->getFilAriane();
    return implode(" > ", array_map(function ($item) {
      return $item->title;
    }, $filAriane));
  }

  public function getChildren()
  {
    return $this->children;
  }

  /**
   * On récupère un score selon le nombre de termes trouvés dans le titre (rapporte plus de points que dans le contenu) et le contenu, si les mots sont retrouvés dans l'ordre dans le contenu, on rapporte plus de points
   * @param array $terms 
   * @return mixed 
   */
  public function getScore(array $terms)
  {
    $contenu = Content::getByFullPath($this->getFullPath())->getRawTextContent();
    $score = 0;
    foreach ($terms as $term) {
      $score += substr_count(self::cleanString($this->title), self::cleanString($term)) * 2;
      $score += substr_count(self::cleanString($contenu), self::cleanString($term));
      $score += substr_count(self::cleanString($this->name), self::cleanString($term)) * 2;
    }
    return $score;
  }

  public static function cleanString($string)
  {
    return stripAccents(strtolower(trim($string)));
  }
}
