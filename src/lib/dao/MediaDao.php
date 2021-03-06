<?php 
/**
 * Class Media extends Dao
 * 
 * 
 * @category Dao Class
 * @author   M.Noermoehammad
 * @license  MIT
 * @version  1.0
 * @since    Since Release 1.0
 * 
 */
class MediaDao extends Dao
{

const TIME_BEFORE_EXPIRED = 8;

public function __construct()
{
    parent::__construct();
}

/**
 * Find All Media
 * 
 * @method public findAllMedia()
 * @param integer $ID
 * @return array
 * 
 */
public function findAllMedia($orderBy = 'ID', $user_level = null)
{

  if (!is_null($user_level)) {

    $sql = "SELECT m.ID, 
                 m.media_filename, 
                 m.media_caption, 
                 m.media_type, 
                 m.media_target,
                 m.media_user, 
                 m.media_access,
                 m.media_status,
                 u.user_level
         FROM tbl_media AS m
         INNER JOIN tbl_users AS u ON m.media_user = u.user_level
         WHERE m.media_user = :user_level
         ORDER BY :orderBy DESC";

    $this->setSQL($sql);
  
    $allMedia = $this->findAll([':user_level'=> $user_level, ':orderBy' => $orderBy]);

  } else {

     $sql = "SELECT ID, 
                    media_filename, 
                    media_caption, 
                    media_type, 
                    media_target,
                    media_user, 
                    media_access,
                    media_status
            FROM tbl_media
            ORDER BY :orderBy DESC";

    $this->setSQL($sql);
    
    $allMedia = $this->findAll([':orderBy' => $orderBy]);
    
  }
  
  return (empty($allMedia)) ?: $allMedia;

}

/**
 * Find media by Id
 * 
 * @method public findMediaById()
 * @param integer $mediaId
 * @param object $sanitize
 * 
 */
public function findMediaById($mediaId, $sanitize)
{
  $idsanitized = $this->filteringId($sanitize, $mediaId, 'sql');

  $sql = "SELECT ID, 
            media_filename, 
            media_caption,
            media_type,
            media_target,
            media_user, 
            media_access,
            media_status
          FROM tbl_media
          WHERE ID = :ID";

  $this->setSQL($sql);

  $mediaById = $this->findRow([':ID' => $idsanitized]);

  return (empty($mediaById)) ?: $mediaById;

}

/**
 * Find media by media format type
 * 
 * @method public findMediaByType()
 * @param string $type
 * @return array
 * 
 */
public function findMediaByType($type)
{
  $sql = "SELECT ID,
                 media_filename,
                 media_caption,
                 media_type, 
                 media_target,
                 media_user,
                 media_access,
                 media_status
          FROM tbl_media
          WHERE media_type = :media_type 
          AND media_status = '1'";

  $this->setSQL($sql);
  
  $mediaByType = $this->findRow([':media_type' => $type]);

  return (empty($mediaByType)) ?: $mediaByType;
  
}

/**
 * find mediameta by it's id and key
 * 
 * @method public findMediaMeta()
 * @param int $mediaId
 * @param object $sanitize
 * 
 */
public function findMediaMetaValue($mediaId, $media_filename, $sanitize)
{
 
 $idsanitized = $this->filteringId($sanitize, $mediaId, 'sql');

 $sql = "SELECT ID, media_id, meta_key, meta_value FROM tbl_mediameta 
         WHERE media_id = :media_id AND meta_key = :meta_key";

 $this->setSQL($sql);

 $mediameta = $this->findRow([':media_id' => $idsanitized, ':meta_key' => $media_filename]);

 return (empty($mediameta)) ?: $mediameta;

}

/**
 * Find all media for downloaded
 *
 * @param int $orderBy
 * @return void
 * 
 */
public function findAllMediaDownload($orderBy = 'ID')
{
   
 $sql = "SELECT ID, media_filename, media_caption, media_type, media_taget, 
                media_user, media_access, media_status
         FROM tbl_media 
         WHERE media_target = 'download' 
         AND media_access = 'public' AND media_status = '1'
         ORDER BY :orderBy DESC";
 
  $this->setSQL($sql);

  $items = $this->findAll([':orderBy' => $orderBy]);

  return (empty($items)) ?:  $items;
   
}

/**
 * Find media for downloaded based on ID
 *
 * @param int $mediaId
 * @param obj $sanitize
 * @return void
 * 
 */
public function findMediaDownload($mediaId, $sanitize)
{

 $id_sanitized = $this->filteringId($sanitize, $mediaId, 'sql');

 $sql = "SELECT ID, media_filename, media_caption, media_type, media_taget, 
                media_user, media_access, media_status
         FROM tbl_media 
         WHERE ID = :ID 
         AND media_target = 'download' 
         AND media_access = 'public' AND media_status = '1' ";

 $this->setSQL($sql);

 $item = $this->findRow([':ID' => $id_sanitized]);
 
 return (empty($item)) ?: $item;

}

/**
 * Find all media for Blog
 *
 * @return void
 * 
 */
public function findAllMediaBlog($orderBy = 'ID')
{
  
 $sql = "SELECT ID, media_filename, media_caption, media_type, media_target
         FROM tbl_media  WHERE media_target = 'blog' 
         AND media_access = 'public' AND media_status = '1'
         ORDER BY :orderBy DESC";

 $this->setSQL($sql);

 $items = $this->findAll([':orderBy' => $orderBy]);

 return (empty($items)) ?: $items;

}

/**
 * Find media for blog based on ID
 *
 * @param int $mediaId
 * @param obj $sanitize
 * @return mixed
 */
public function findMediaBlog($mediaId)
{

 $sql = "SELECT ID, media_filename, media_caption, media_type, media_target, 
                media_user, media_access, media_status 
         FROM tbl_media
         WHERE ID = :ID 
         AND media_target = 'blog'
         AND media_access = 'public'
         AND media_status = '1'";
  
  $this->setSQL($sql);

  $item = $this->findRow([':ID' => (int)$mediaId]);

  return (empty($item)) ?: $item;

}

/**
 * Find media download based on Id,time before expired and ip
 *
 * @param integer $mediaId
 * @param object $sanitize
 * @return array
 * 
 */
public function findMediaDownloadUrl($mediaId, $sanitize)
{

  $ip_address = get_ip_address();

  $id_sanitized = $this->filteringId($sanitize, $mediaId, 'sql');

  $sql = "SELECT ID, media_id, media_identifier, before_expired, ip_address, created_at
          FROM tbl_media_download 
          WHERE media_id = :media_id 
          AND ip_address = '".$ip_address."'
          AND before_expired >= '".time()."'";

  $this->setSQL($sql);

  $item = $this->findAll([':media_id'=>$id_sanitized]);

  return (empty($item)) ?: $item;

}

/**
 * Find media download by it's identifier
 *
 * @param string $media_identifier
 * @return array
 * 
 */
public function findMediaDownloadByIdentifier($media_identifier)
{
  $sql = "SELECT ID, media_id, media_identifier, before_expired, ip_address, created_at
          FROM tbl_media_download
          WHERE media_identifier = ?";

  $this->setSQL($sql);

  $item = $this->findAll([$media_identifier]);

  return (empty($item)) ?: $item;

}

/**
 * Add new media
 * 
 * @method public addMedia()
 * @param string|array $bind
 * 
 */
public function createMedia($bind)
{
  
  $this->create("tbl_media", [

      'media_filename' => $bind['media_filename'],
      'media_caption'  => $bind['media_caption'],
      'media_type'     => $bind['media_type'],
      'media_target'   => $bind['media_target'],
      'media_user'     => $bind['media_user'],
      'media_access'   => $bind['media_access'],
      'media_status'   => $bind['media_status']

  ]);

  return $this->lastId();

}

/**
 * Add new media meta
 * 
 * @param integer $mediaId
 * @param string|array $bind
 * 
 */
public function createMediaMeta($bind)
{

  $this->create("tbl_mediameta", [

     'media_id' => $bind['media_id'],
     'meta_key' => $bind['meta_key'],
     'meta_value' => $bind['meta_value']

  ]);

}

/**
 * create media downloaded
 *
 * @param array $bind
 * 
 */
public function createMediaDownload($bind)
{

  $this->create("tbl_media_download", [

     'media_id' => $bind['media_id'],
     'media_identifier' => generate_media_identifier(),
     'before_expired' => (time()+self::TIME_BEFORE_EXPIRED*60*60),
     'ip_addres' => (get_ip_address())

  ]);

}

/**
 * Update Media
 * 
 * @method public updateMedia()
 * @param object $sanitize
 * @param array $bind
 * @param integer $ID
 * 
 */
public function updateMedia($sanitize, $bind, $ID)
{
  
  $id_sanitized = $this->filteringId($sanitize, $ID, 'sql');
 
  if(!empty($bind['media_filename'])) {

     $this->modify("tbl_media", [
        
         'media_filename' => $bind['media_filename'],
         'media_caption'  => $bind['media_caption'],
         'media_type'     => $bind['media_type'],
         'media_target'   => $bind['media_target'],
         'media_access'   => $bind['media_access'],
         'media_status'   => $bind['media_status']

     ], "ID = {$id_sanitized}");

  } else {
    
     $this->modify("tbl_media", [
        
        'media_caption' => $bind['media_caption'],
        'media_target'  => $bind['media_target'],
        'media_access'  => $bind['media_access'],
        'media_status'  => $bind['media_status']

     ], "ID = {$id_sanitized}");

  }

}

/**
 * Update media meta
 *
 * @param object $sanitize
 * @param array $bind
 * @param integer $ID
 * @return void
 * 
 */
public function updateMediaMeta($sanitize, $bind, $ID)
{
  $idsanitized = $this->filteringId($sanitize, $ID, 'sql');

  if (!empty($bind['meta_key'])) {

      $this->modify("tbl_mediameta", [

          'meta_key' => $bind['meta_key'],
          'meta_value' => $bind['meta_value']
          
      ],  "media_id = {$idsanitized}");

  }

}

/**
 * Delete Media
 * 
 * @method public deleteMedia()
 * @param integer $ID
 * @param object $sanitize
 * 
 */
public function deleteMedia($ID, $sanitize)
{
  
  $clean_id = $this->filteringId($sanitize, $ID, 'sql');
  
  $this->deleteRecord("tbl_media", "ID = ".(int)$clean_id, 1);
  $this->deleteRecord("tbl_mediameta", "media_id = ".(int)$clean_id, 1);

}

/**
 * Check media's Id
 * 
 * @method public checkMediaId()
 * @param integer|numeric $id
 * @param object $sanitize
 * @return numeric
 * 
 */
public function checkMediaId($id, $sanitize)
{
 
   $sql = "SELECT ID from tbl_media WHERE ID = ?";
   $id_sanitized = $this->filteringId($sanitize, $id, 'sql');
   $this->setSQL($sql);
   $stmt = $this->checkCountValue([$id_sanitized]);
   return($stmt > 0);

}

/**
 * drop down media access
 * set media access
 * 
 * @param string $selected
 * @return string
 * 
 */
public function dropDownMediaAccess($selected = "")
{
  $name = 'media_access';

  $media_access = array('public' => 'Public', 'private' => 'Private');

  if($selected != '') {
    
    $selected = $selected;

  }

  return dropdown($name, $media_access, $selected);

}

/**
 * drop down media target
 * set media target
 * 
 * @param string $selected
 * @return string
 * 
 */
public function dropDownMediaTarget($selected = "")
{
 $name = 'media_target';

 $media_target = array('blog' => 'Blog', 'download' => 'Download', 'gallery' => 'Gallery');

 if($selected != '') {

    $selected = $selected;

 }

 return dropdown($name, $media_target, $selected);

}

/**
 * Drop down media status
 * 
 * @param int $selected
 * @return int
 * 
 */
public function dropDownMediaStatus($selected = "")
{
  $name = 'media_status';

  $media_status = array('Enabled', 'Disabled');

  if ($selected) {

     $selected = $selected;

  }

  return dropdown($name, $media_status, $selected);

}

public function dropDownMediaSelect($selected = null)
{

  $dropdown  = '<div class="form-group">';
  $dropdown .= '<label>Uploaded image</label><br>';
  $dropdown .= '<br><select name="image_id" class="selectpicker" ><br><br>';

  if (is_null($selected)) {

     $selected = "";

  }

  $media_ids = [];

  $media_ids = $this->findAllMediaBlog();

  $sanitizer = new Sanitize;

  $picture_bucket_list = ["image/jpeg", "image/png", "image/gif", "image/webp"];

  if (is_array($media_ids)) {

       $dropdown .= '<option>Select primary image</option>';

       foreach ($media_ids as $m => $media) {

           $media_meta = $this->findMediaMetaValue($media['ID'], $media['media_filename'], $sanitizer);
           
           $media_properties = isset($media_meta['meta_value']) ? media_properties($media_meta['meta_value']) : null;

           $select = $selected === $media['ID'] ? ' selected' : null;

           if(in_array($media['media_type'], $picture_bucket_list)) {

            $dropdown .= '<option data-content="<img src='.app_url().DS.APP_IMAGE_THUMB.'small_'.rawurlencode(basename(safe_html($media['media_filename']))).'></img>" value="'.(int)$media['ID'].'"'.$select.'>'.safe_html($media_properties['Origin']).'</option>'."\n";

           } 
           
       }

  } 
  
  $dropdown .= '</select>'."\n";

  $dropdown .= '</div>';

  return $dropdown;

}

public function imageUploadHandler($mediaId = null) 
{

  $mediablog  = '<div class="form-group">';

  if (!empty($mediaId) && $mediaId != 0) {

    $data_media = $this->findMediaBlog((int)$mediaId);

    $image_src = invoke_image_uploaded($data_media['media_filename'], false);
    $image_src_thumb = invoke_image_uploaded($data_media['media_filename']);

     if (!$image_src_thumb) {
         
         $image_src_thumb = app_url().'/public/files/pictures/thumbs/nophoto.jpg';

     }

     if ($image_src) {

       $mediablog .= '<a class="thumbnail" href="'.$image_src.'" ><img src="'.$image_src_thumb.'" class="img-responsive pad"></a>';
       $mediablog .= '<label>Change picture:</label>';
       $mediablog .= '<input type="file" name="image" id="file" accept="image/*" onchange="loadFile(event)" maxlength="512" >';
       $mediablog .= '<input type="hidden" name="image_id"  value="'.$mediaId.'">';
       $mediablog .= '<img id="output" class="img-responsive pad">';
       $mediablog .= '<p class="help-block>Maximum file size: '.format_size_unit(APP_FILE_SIZE).'</p>';
        
     } else {

        $mediablog .= '<br><img src="'.$image_src_thumb.'" class="img-responsive pad"><br>';
        $mediablog .= '<label>Change picture:</label>';
        $mediablog .= '<input type="file" name="image" id="file" accept="image/*" onchange="loadFile(event)"  maxlength="512" >';
        $mediablog .= '<input type="hidden" name="image_id"  value="'.$mediaId.'">';
        $mediablog .= '<img id="output" class="img-responsive pad">';
        $mediablog .= '<p class="help-block">Maximum file size:'.format_size_unit(APP_FILE_SIZE).'</p>';

     }

  } else {

    $mediablog .= '<div id="image-preview">';
    $mediablog .= '<label for="image-upload" id="image-label">Choose picture</label>';
    $mediablog .= '<input type="file" name="media" id="image-upload" accept="image/*" maxlength="512" >';
    $mediablog .= '</div>';
    $mediablog .= '<p class="help-block"> Maximum file size: '.format_size_unit(APP_FILE_SIZE).'</p>'; 
  
  }
 
  $mediablog .= '</div>';

  return $mediablog;

}

/**
 * Total media records
 * 
 * @method public totalMediaRecords()
 * @param array $data = null
 * @return integer|numeric
 * 
 */
public function totalMediaRecords($data = null)
{

  if (!empty($data)) {

    $sql = "SELECT ID FROM tbl_media WHERE media_user = ? ";

  } else {

    $sql = "SELECT ID FROM tbl_media";
     
  }

   $this->setSQL($sql);
   
   return $this->checkCountValue($data);  
   
}

}