<?php

namespace App\Documentation\Controller;

use App\Documentation\Helpers\Content;

class PageController extends Controller
{
  public function get()
  {
    $fullPath = getRequest("fullPath");
    $content = Content::getByFullPath($fullPath);

    return successResponse([
      "page" => $content ? $content->content : null,
      "title" => $content ? $content->getTitle() : null,
    ]);
  }

  public function save()
  {
    $page = getPost("page");
    $fullPath = $page["fullPath"] ?? null;
    $title = $page["title"] ?? null;
    $newContent = json_decode($page["content"] ?? "", true);
    $content = Content::getByFullPath($fullPath) ?? new Content($fullPath, []);
    $content->setContent($newContent);
    $content->setTitle($title);
    $content->save();
    return successResponse([
      "page" => $content->getContent(),
      "title" => $content->getTitle(),
    ]);
  }
}
