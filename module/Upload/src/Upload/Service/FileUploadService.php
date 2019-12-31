<?php
/**
 * DBShop 电子商务系统
 *
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2014 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 */

namespace Upload\Common\Service;
/**
 * 文件附件上传服务
 * Class FileUploadService
 * @package Upload\Common\Service
 */
class FileUploadService
{
    private $uploadFile;

    public function __construct()
    {
        if(!$this->uploadFile) {
            $this->uploadFile = new FileUpload();
        }
    }
    public function uploadFile($fileName, $userId, $oldFile=null, $fileType=array('zip'))
    {
        $fileArray = array(
            'save_path'     => '/public/upload/download/' . $userId . '/',
            'download_file' => $fileName,
            'file_rename'  => 'down' . md5(time() . $fileName),
        );
        $fileInfo = $this->uploadFile->uploadDownloadFile($fileArray, $fileType);
        $fileInfo['file'] = $this->delOldUpload(
            $fileInfo['file'],
            (empty($oldFile) ? '' : $fileArray['save_path'] . basename($oldFile))
        );

        return $fileInfo;
    }
    /**
     * 对新旧图片的对比并删除旧有图片
     * @param null $newFile
     * @param null $oldFile
     * @return null|string
     */
    private function delOldUpload($newFile=null, $oldFile=null)
    {
        $file = '';
        if(!empty($newFile)) {
            $file = $newFile;
            if(isset($oldFile) and !empty($oldFile) and $newFile != $oldFile) @unlink(DBSHOP_PATH . $oldFile);
        } else {
            if(!empty($oldFile)) $file = $oldFile;
        }
        return $file;
    }
}