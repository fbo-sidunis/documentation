<?php

namespace App\Documentation\Controller;

use App\Documentation\Helpers\Tree;

class MenuController extends Controller
{

  private $tree;

  function __construct()
  {
    parent::__construct(...func_get_args());
    $this->tree = Tree::getTree();
  }

  public function get()
  {
    header('Content-Type: application/json');
    die(file_get_contents(Tree::FILE));
  }

  public function saveItem()
  {
    $datas = getPost("item") ?: [];
    $action = $datas["action"] ?? null;
    if ($action == "add") {
      return $this->addItem();
    }
    $fullPath = $datas["fullPath"] ?? null;
    $itemName = $datas["itemName"] ?? null;
    $title = $datas["title"] ?? null;
    $branch = $this->tree->getByPath($fullPath);
    if (!$branch) {
      return errorResponse(message: "Branch not found");
    }
    try {
      $branch->setName($itemName);
    } catch (\Exception $e) {
      return errorResponse(message: $e->getMessage());
    }
    $branch->title = $title;
    $this->tree->save();
    return successResponse([
      "menus" => $this->tree->toArray()
    ]);
  }

  public function removeItem()
  {
    $fullPath = getRequest("fullPath");
    $branch = $this->tree->getByPath($fullPath);
    if (!$branch) {
      return errorResponse(message: "Branch not found");
    }
    $branch->remove();
    $this->tree->save();
    return successResponse([
      "menus" => $this->tree->toArray()
    ]);
  }

  public function moveItem()
  {
    $fullPath = getRequest("fullPath");
    $direction = getRequest("direction"); //"up" ou "down"
    $branch = $this->tree->getByPath($fullPath);
    if (!$branch) {
      return errorResponse(message: "Branch not found");
    }
    $branch->move($direction);
    $this->tree->save();
    return successResponse([
      "menus" => $this->tree->toArray()
    ]);
  }

  public function addItem()
  {
    $item = getPost("item");
    $path = $item["path"];
    $title = $item["title"];
    $name = $item["itemName"];
    $branch = $path ? $this->tree->getByPath($path) : $this->tree;
    if (!$branch) {
      return errorResponse(message: "Branch not found");
    }
    $branch->addChild($name, $title);
    $this->tree->save();
    return successResponse([
      "menus" => $this->tree->toArray()
    ]);
  }
}
