<?php

namespace App\Documentation\Helpers;

class File
{
  protected string|null $name = null;
  protected string|null $fullPath = null;
  protected string|null $type = null;
  protected string|null $size = null;
  protected string|null $tmpName = null;
  protected string|null $error = null;

  public function __construct($file)
  {
    $this->name = $file['name'];
    $this->fullPath = $file['tmp_name'];
    $this->type = $file['type'];
    $this->size = $file['size'];
    $this->tmpName = $file['tmp_name'];
    $this->error = $file['error'];
  }

  /**
   * Get the value of name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set the value of name
   *
   * @return  self
   */
  public function setName($name)
  {
    $this->name = $name;

    return $this;
  }

  /**
   * Get the value of fullPath
   */
  public function getFullPath()
  {
    return $this->fullPath;
  }

  /**
   * Set the value of fullPath
   *
   * @return  self
   */
  public function setFullPath($fullPath)
  {
    $this->fullPath = $fullPath;

    return $this;
  }

  /**
   * Get the value of type
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Set the value of type
   *
   * @return  self
   */
  public function setType($type)
  {
    $this->type = $type;

    return $this;
  }

  /**
   * Get the value of size
   */
  public function getSize()
  {
    return $this->size;
  }

  /**
   * Set the value of size
   *
   * @return  self
   */
  public function setSize($size)
  {
    $this->size = $size;

    return $this;
  }

  /**
   * Get the value of tmpName
   */
  public function getTmpName()
  {
    return $this->tmpName;
  }

  /**
   * Set the value of tmpName
   *
   * @return  self
   */
  public function setTmpName($tmpName)
  {
    $this->tmpName = $tmpName;

    return $this;
  }

  /**
   * Get the value of error
   */
  public function getError()
  {
    return $this->error;
  }

  /**
   * Set the value of error
   *
   * @return  self
   */
  public function setError($error)
  {
    $this->error = $error;

    return $this;
  }

  public function save(string $path, string|null $newFileName = null)
  {
    $newFileName = $newFileName ?? $this->name;
    $newFullPath = $path . $newFileName;
    if (!move_uploaded_file($this->tmpName ?? $this->fullPath, $newFullPath)) {
      throw new \Exception("Erreur lors de l'upload du fichier", 1);
    }
    if (file_exists($newFullPath)) {
      $this->fullPath = $newFullPath;
      $this->name = $newFileName;
      $this->tmpName = null;
      return $this;
    } else {
      throw new \Exception("Erreur lors de l'upload du fichier", 1);
    }
  }

  public function delete()
  {
    if (file_exists($this->fullPath)) {
      unlink($this->fullPath);
    }
  }

  public function getExtension()
  {
    return pathinfo($this->name, PATHINFO_EXTENSION);
  }

  /**
   * Si le nom change, on change le nom du fichier aussi
   * Si aucun nom n'est donné, on garde le nom actuel
   * Si aucune extension n'est donnée, on garde l'extension actuelle
   * On renomme le fichier
   * @param string $newName 
   * @return $this 
   */
  public function rename(string $newName)
  {
    $newName = $newName ?? $this->name;
    $newExtension = pathinfo($newName, PATHINFO_EXTENSION);
    $newExtension = $newExtension ?? $this->getExtension();
    $newName = pathinfo($newName, PATHINFO_FILENAME) . '.' . $newExtension;
    $newFullPath = pathinfo($this->fullPath, PATHINFO_DIRNAME) . '/' . $newName;
    if (file_exists($newFullPath)) {
      throw new \Exception("Le fichier existe déjà", 1);
    }
    if (!rename($this->fullPath, $newFullPath)) {
      throw new \Exception("Erreur lors du renommage du fichier", 1);
    }
    $this->fullPath = $newFullPath;
    $this->name = $newName;
    return $this;
  }

  public function getBase64()
  {
    $type = pathinfo($this->fullPath, PATHINFO_EXTENSION);
    $data = file_get_contents($this->fullPath);
    return 'data:image/' . $type . ';base64,' . base64_encode($data);
  }

  public function getRelativePathToDomain()
  {
    return str_replace(ROOT_DIR, '/', $this->fullPath);
  }

  /**
   * 
   * @param string $name 
   * @return File[]
   */
  public static function getFilesFromName(string $name)
  {
    $files = [];
    if (isset($_FILES[$name])) {
      if (is_array($_FILES[$name]['name'])) {
        foreach ($_FILES[$name]['name'] as $key => $value) {
          $files[] = new File([
            'name' => $_FILES[$name]['name'][$key],
            'fullPath' => $_FILES[$name]['tmp_name'][$key],
            'type' => $_FILES[$name]['type'][$key],
            'size' => $_FILES[$name]['size'][$key],
            'tmpName' => $_FILES[$name]['tmp_name'][$key],
            'error' => $_FILES[$name]['error'][$key],
          ]);
        }
      } else {
        $files[] = new File($_FILES[$name]);
      }
    }
    return $files;
  }

  /**
   * 
   * @param string $name 
   * @return File|null 
   */
  public static function getFileFromName(string $name)
  {
    $files = self::getFilesFromName($name);
    return $files[0] ?? null;
  }
}
