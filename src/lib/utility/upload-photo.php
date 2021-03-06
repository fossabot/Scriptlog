<?php
/**
 * upload_photo function 
 * Uploading picture or image with Intervention Image Manipulation Class Library
 * 
 * @category Functions
 * @author  Oliver Vogel the author of Intervention Image Class Library
 * @uses Intervention\Image\ImageManager
 * @see http://image.intervention.io/use/basics
 * @see https://anchetawern.github.io/blog/2016/02/18/using-the-intervention-image-library-in-php/
 * @see https://www.tutmecode.com/php/create-thumbnail-from-big-size-image-in-php-or-laravel/
 * @license MIT
 * 
 */

use Intervention\Image\ImageManager;
use Intervention\Image\ImageManagerStatic as Image;

/**
 * Photo instance function
 * // create an image manager instance with favored driver
 * @return object
 * 
 */
function photo_instance()
{
  // Currently you can choose between gd and imagick
  $manager = new ImageManager(['driver' => 'gd']);

  return $manager;

}

/**
 * Upload photo
 *
 * @param string $file_location
 * @param string $file_size
 * @param string $file_type
 * @param string $file_name
 * @return void
 * 
 */
function upload_photo($file_location, $file_size, $file_type, $file_name)
{

 // picture directory
 $image_path = __DIR__ . '/../../'.APP_IMAGE;
 $image_path_thumb = __DIR__ . '/../../'.APP_IMAGE_THUMB;
 $image_uploaded = $image_path.$file_name;
 
 list($current_width, $current_height) = getimagesize($file_location);

 if($file_type == "image/jpeg" || $file_type == "image/jpg" || $file_type == "image/png" || $file_type == "image/gif") {

  if(false === set_webp_regular($current_width, $current_height, $file_location, $file_size, $image_uploaded, $image_path, $file_name)) {

     scriptlog_error("Error creating regular size of webp image format", E_USER_WARNING);

  }

  if(false === set_webp_medium($current_width, $current_height, $image_uploaded, $image_path_thumb, $file_name)) {

     scriptlog_error("Error creating medium size of webp image format", E_USER_WARNING);

  }

  if(false === set_webp_small($current_width, $current_height, $image_uploaded, $image_path_thumb, $file_name)) {

     scriptlog_error("Error creating small of webp image format", E_USER_WARNING);

  }

}

// resize picture to regular size
if (false === set_regular_photo($current_width, $current_height, $file_location, $file_size, $image_uploaded ) ) {

    scriptlog_error("Error creating regular size of picture", E_USER_WARNING);

}

// crop to medium size
if( false === set_medium_photo($current_width, $current_height, $image_uploaded, $image_path_thumb, $file_name ) ) {

   scriptlog_error("Error creating medium size of picture", E_USER_WARNING);

}

// crop to smaller size
if( false === set_small_photo($current_width, $current_height, $image_uploaded, $image_path_thumb, $file_name ) ) {

 scriptlog_error("Error creating smaller size of picture", E_USER_WARNING);

}

}

// setting regular size of picture
function set_regular_photo( $current_width, $current_height, $file_location, $file_size, $file_path_uploaded ) {

$regular_size = 770;

if($current_width <= 0 || $current_height <= 0) {

  return false;

}

if( !move_uploaded_file($file_location, $file_path_uploaded) ) {

  return false;

} 

if( filesize($file_path_uploaded) !== $file_size ) {

    unlink($file_path_uploaded);
    return false;

}

$regular_scaled = min($regular_size/$current_width, $regular_size/$current_height);
$new_width = ceil($regular_scaled*$current_width);
$new_height = ceil($regular_scaled*$current_height);

$regular_photo = photo_instance()->make($file_path_uploaded);
$regular_photo->resize($new_width, $new_height);
if( $regular_photo->save($file_path_uploaded, 70)) {

  $regular_photo->destroy();
  return true;

}

}

// setting regular size of webp image format
function set_webp_regular($current_width, $current_height, $file_location, $file_size, $file_path_uploaded, $webp_path, $file_name)
{

$regular_size = 770;

if($current_width <= 0 || $current_height <= 0) {

  return false;

}

if( !move_uploaded_file($file_location, $file_path_uploaded) ) {

  return false;

}

if( filesize($file_path_uploaded) !== $file_size ) {

  unlink($file_path_uploaded);
  return false;

}

// get filename
$file_basename = substr($file_name, 0, strripos($file_name, '.'));
    
// get file extension
$file_ext = substr($file_name, strripos($file_name, '.'));

$regular_webp_scaled = min($regular_size/$current_width, $regular_size/$current_height);
$new_width = ceil($regular_webp_scaled*$current_width);
$new_height = ceil($regular_webp_scaled*$current_height);

$regular_webp = photo_instance()->make($file_path_uploaded);
$regular_webp->resize($new_width, $new_height);
if($regular_webp->save($webp_path.$file_basename.'.webp', 70, 'webp')){

   $regular_webp->destroy();
   return true;

}

}

// setting medium size of picture
function set_medium_photo( $current_width, $current_height, $file_path_uploaded, $file_path_thumb, $file_name )
{

$medium_size = 640;

if ($current_width <= 0 || $current_height <= 0) {

    return false;

}

$medium_scaled = min($medium_size/$current_width, $medium_size/$current_height);
$new_width = ceil($medium_scaled*$current_width);
$new_height = ceil($medium_scaled*$current_height);

$medium_photo = photo_instance()->make($file_path_uploaded);
$medium_photo->fit($new_width, $new_height);
if( $medium_photo->save($file_path_thumb .'medium_'.$file_name, 60 ) ) {

   $medium_photo->destroy();
   return true;

}

}

// setting medium size of webp image format
function set_webp_medium( $current_width, $current_height, $file_path_uploaded, $file_path_thumb, $file_name )
{

$medium_size = 640;

if ($current_width <= 0 || $current_height <= 0) {

  return false;

}

// get filename
$file_basename = substr($file_name, 0, strripos($file_name, '.'));
 
$medium_scaled = min($medium_size/$current_width, $medium_size/$current_height);
$new_width = ceil($medium_scaled*$current_width);
$new_height = ceil($medium_scaled*$current_height);

$medium_webp = photo_instance()->make($file_path_uploaded);
$medium_webp->fit($new_width, $new_height);
if($medium_webp->save($file_path_thumb.'medium_'.$file_basename.'.webp', 60, 'webp')) {

   $medium_webp->destroy();
   return true;

}

}

// setting smaller size of picture
function set_small_photo( $current_width, $current_height, $file_path_uploaded, $file_path_thumb, $file_name )
{

$small_size = 320;

if ($current_width <= 0 || $current_height <= 0) {

    return false;

}

$small_scaled = min($small_size/$current_width, $small_size/$current_height);
$new_width = ceil($small_scaled*$current_width);
$new_height = ceil($small_scaled*$current_height);

$small_photo = photo_instance()->make($file_path_uploaded);
$small_photo->fit($new_width, $new_height);
if( $small_photo->save($file_path_thumb.'small_'.$file_name, 60 ) ) {

  $small_photo->destroy();
  return true;

}

}

// setting smaller size of webp image format
function set_webp_small( $current_width, $current_height, $file_path_uploaded, $file_path_thumb, $file_name )
{

$small_size = 320;

if ($current_width <= 0 || $current_height <= 0) {

  return false;

}

// get filename
$file_basename = substr($file_name, 0, strripos($file_name, '.'));
 
$small_scaled = min($small_size/$current_width, $small_size/$current_height);
$new_width = ceil($small_scaled*$current_width);
$new_height = ceil($small_scaled*$current_height);

$small_webp = photo_instance()->make($file_path_uploaded);
$small_webp->fit($new_width, $new_height);
if($small_webp->save($file_path_thumb.'small_'.$file_basename.'.webp', 60, 'webp')) {

  $small_webp->destroy();
  return true;

}

}