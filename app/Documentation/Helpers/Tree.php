<?php

namespace App\Documentation\Helpers;

use App\Documentation\Helpers\Tree\Branch;

class Tree
{
  public const FILE = __DIR__ . '/../data/menu.json';
  public static $tree = null;
  public $root = null;
  /**
   * Enfants
   * @var Branch[]
   */
  public array $children = [];

  public function __construct()
  {
    $json = json_decode(file_get_contents(self::FILE), true);
    $this->children = Branch::createFromItems($json, $this);
    $this->root = $this;
    Tree::$tree = $this;
  }

  public function toArray()
  {
    $array = [];
    foreach ($this->children as $child) {
      $array[$child->name] = $child->toArray();
    }
    return $array;
  }

  public function save()
  {
    file_put_contents(self::FILE, json_encode($this->toArray()));
    Branch::repercussionContent();
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

  public function sortChildren()
  {
    $children = $this->children;
    usort($children, function ($a, $b) {
      return $a->position <=> $b->position;
    });
    $this->children = $children;
    foreach ($this->children as $child) {
      $child->sortChildren();
    }
  }

  public static function add($name, $title, $parent)
  {
    $branch = new Branch([
      "name" => $name,
      "title" => $title,
      "position" => count($parent->children)
    ], $parent);
    $parent->children[] = $branch;
    return $branch;
  }

  public function addChild($name, $title)
  {
    return self::add($name, $title, $this);
  }

  public function rePositionChildren()
  {
    $count = 0;
    foreach ($this->children as $child) {
      $child->position = $count;
      $count++;
    }
  }

  public static function removeBranch(Branch $branch)
  {
    $parent = $branch->parent;
    $position = $branch->position;
    unset($parent->children[$position]);
    $parent->children = array_values($parent->children);
    $parent->rePositionChildren();
  }


  /**
   * 
   * @param string $path 
   * @return Branch|null 
   */
  public static function getByPath(string $path)
  {
    $path = explode(".", $path);
    $current = self::getTree();
    foreach ($path as $name) {
      $current = $current->getChildByName($name) ?? null;
      if (!$current) {
        return null;
      }
    }
    return $current;
  }

  public static function getTree()
  {
    return Tree::$tree ?? new Tree();
  }

  public function getChildByName($name)
  {
    foreach ($this->children as $child) {
      if ($child->name == $name) {
        return $child;
      }
    }
    return null;
  }
}
