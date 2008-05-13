<?php
/**
 * Google "I'm Feeling Lucky" Image
 *
 * This script prints the result of the very first image in a Google Image
 * search for the given query.
 *
 * GET Parameters: 
 *  - q: the query (string, required)
 *  - cache: show the cropped cached image instead of the original image (boolean int, defaults to 1)
 *  - p: which image to start at (positive int, defaults to 0)
 *  - redirect: redirect to the image rather than display its contents (boolean int, defaults to 1)
 *
 * Examples:
 * - google-image.php?q=escalope            # => redirect to the cached image
 * - google-image.php?cache=0&q=escalope    # => redirect to the original image
 * - google-image.php?q=escalope&p=1        # => redirect to the cached second image
 * - google-image.php?redirect=0&q=escalope # => print out the cached image
 *
 * @author Benjamin DANON [benjamin.danon@gmail.com]
 * @author Sunny RIPERT [negatif@gmail.com]
 * @version 8.03.18
 * @licence http://opensource.org/licenses/lgpl-3.0.html GNU LGPLv3
 */

class LuckyImage {
  function LuckyImage($query, $cache = true, $start = 0) {
    $this->query = urlencode($query);
    $this->cache = (bool) $cache;
    $this->start = intval($start) - 1;
    $this->imageData = false;
    $this->search(); // do the search!
  }
  
  // URI for the Google search
  function searchUri() {
    return "http://images.google.fr/images?q={$this->query}&start={$this->start}";
  }
  
  // get search data!
  function search() {
    $fileHandle = fopen($this->searchUri(), 'r');
    $fileBuffer = stream_get_contents($fileHandle);
    $googleCode = split('dyn.Img', $fileBuffer);
    $this->imageData = explode('","', $googleCode[1]);
  }
  
  // return the image URI (cached or original)
  function uri() {
    return $this->cache ? $this->imageData[14].'?q=tbn:'.$this->imageData[2] : str_replace('%25', '%', $this->imageData[3]);
  }
  
  // return the image data
  function image() {
    return file_get_contents($this->uri());
  }
  
  // returns the image format (defaults to jpg)
  function type() {
    return $this->imageData[10] ? $this->imageData[10] : 'jpg';
  }

  // print the image with the correct content-type
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


