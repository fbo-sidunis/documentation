<?php

namespace App\Documentation\Command;

use App\Documentation\Helpers\Content;

class OrderFonctionsJs extends \Core\CommandHandler
{
  public function execute()
  {
    $content = Content::getByFullPath("fonctions.js");
    $content_ = $content->getContent();
    $blocks = $content_["blocks"];
    $lastFunction = null;
    $blocksByFunction = [];
    foreach ($blocks as $block) {
      if ($block["type"] == "header") {
        $lastFunction = $block["data"]["text"];
      }
      if ($lastFunction) {
        $blocksByFunction[strtolower($lastFunction)][] = $block;
      }
    }
    ksort($blocksByFunction);
    $sortedBlocks = array_merge(...array_values($blocksByFunction));
    $content_["blocks"] = $sortedBlocks;
    $content->setContent($content_);
    $content->save();
  }
}
