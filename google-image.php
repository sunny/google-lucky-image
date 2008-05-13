<?php
/**
 * Google "I'm Feeling Lucky" Image
 *
 * This script prints the result of the very first image in a Google Image
 * search for the given query.
 *
 * GET Parameters:
 *  - q= The query
 *  - noCache= shows the original image
 *  - p= which image to start at (defaults to 0)
 *
 * Examples:
 * - google-image.php?q=escalope
 * - google-image.php?q=escalope&noCache
 * - google-image.php?q=escalope&noCache&p=1
 *
 * @author Benjamin DANON [benjamin.danon@gmail.com]
 * @author Sunny RIPERT [negatif@gmail.com]
 * @version 8.03.18
 * @licence http://opensource.org/licenses/lgpl-3.0.html GNU LGPLv3
 */

class LuckyImage {
  function LuckyImage($query, $cache = true, $start = 0) {
    $this->query = urlencode($query);
    $this->cache = $cache;
    $this->start = $start - 1;
    $this->imageData = false;
    $this->search(); // do the search!
  }
  
  // URI for the Google search
  function searchUri() {
    return "http://images.google.fr/images?q={$this->query}&start={$this->start}";
  }
  
  // Get search data!
  function search() {
    $fileHandle = fopen($this->searchUri(), 'r');
    $fileBuffer = stream_get_contents($fileHandle);
    $googleCode = split('dyn.Img', $fileBuffer);
    $this->imageData = explode('","', $googleCode[1]);
  }
  
  // Returns the image URI (cached or original)
  function uri() {
    return $this->cache ? $this->imageData[14].'?q=tbn:'.$this->imageData[2] : str_replace('%25', '%', $this->imageData[3]);
  }
  
  // Returns the image data
  function image() {
    return file_get_contents($this->uri());
  }
  
  // Returns the image format (defaults to jpg)
  function type() {
    return $this->imageData[10] ? $this->imageData[10] : 'jpg';
  }

  // Prints the image with the correct content-type
  function printImage() {
     header('Content-type: image/' . $this->type());
     print($this->image());
  }

  // redirect to the image
  function redirect() {
    header('Location: ' . $this->uri());
  }
}

if (isset($_GET['q'])) {
  $q = $_GET['q'];
  $start = isset($_GET['p']) ? intval($_GET['p']) : 1;
  $cache = isset($_GET['cache']) ? intval($_GET['cache']) : 1;
  $redirect = isset($_GET['redirect']) ? intval($_GET['redirect']) : 1;

  $lucky = new LuckyImage($q, $cache, $start);
  if ($redirect)
    $lucky->redirect();
  else
    $lucky->printImage();

} else {
  // show the usage by cherry-picking the comments  
  $doc = preg_grep('/^ \*($| [^@])/', explode("\n", file_get_contents(__FILE__)));
  $doc = preg_replace("/\n? \* ?/", "\n", implode("\n", $doc));
  header('Content-type: text/plain');
  echo $doc;
}


