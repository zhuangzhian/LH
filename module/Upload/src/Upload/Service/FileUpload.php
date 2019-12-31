<?php
/**
 * DBShop 电子商务系统
 *
 * @link      http://www.dbshop.net/
 * @copyright Copyright (c) 2012-2014 DBShop.net Inc. (http://www.dbshop.net)
 * @license   http://www.dbshop.net/license.html License
 */

namespace Upload\Common\Service;

class FileUpload
{
    private $upAdapter;

    public function __construct()
    {
        if(!$this->upAdapter) {
            $this->upAdapter  = new \Zend\File\Transfer\Adapter\Http();
        }
    }

    /**
     * 文件上传
     * @param array $uploadFile
     * @param array $checkFileType
     * @return array
     */
    public function uploadDownloadFile(array $uploadFile, $checkFileType=array())
    {
        $returnArray = array('file'=>'');

        $fileInfo = $this->upAdapter->getFileInfo();
        $savePath = $uploadFile['save_path'];
        $fileName = $fileInfo[$uploadFile['download_file']]['name'];
        $fileExt  = $this->_getExtension($fileInfo[$uploadFile['download_file']]['name']);

        if(isset($fileName) and !empty($fileName)) {
            //检测附件存放目录，设定存放目录
            if(!is_dir(DBSHOP_PATH . $savePath)) {
                mkdir(rtrim(DBSHOP_PATH . $savePath,'/'),0755, true);
                chmod(DBSHOP_PATH . $savePath, 0755);
            }

            //扩展名检测
            if(!empty($checkFileType)) {
                $this->upAdapter->addValidator('Extension', FALSE, array(@implode(',', $checkFileType),'messages'=>array(
                    \Zend\Validator\File\Extension::FALSE_EXTENSION => "文件'%value%'扩展名不允许",
                    \Zend\Validator\File\Extension::NOT_FOUND       => "文件'%value%'无法读取或不存在"
                )));
            }

            $this->upAdapter->setDestination(DBSHOP_PATH . $savePath);

            //上传处理
            if($this->upAdapter->receive($uploadFile['download_file'])) {
                $returnArray['file'] = $savePath . $fileName;
                chmod(DBSHOP_PATH . $returnArray['file'], 0766);
            }

            //重命名
            if(isset($uploadFile['file_rename']) and $uploadFile['file_rename'] != '' and file_exists(DBSHOP_PATH . $returnArray['file'])) {
                if(rename(DBSHOP_PATH . $returnArray['file'], DBSHOP_PATH . $savePath . $uploadFile['file_rename'] . '.' . $fileExt)) {
                    $imageName = $uploadFile['file_rename'] . '.' . $fileExt;
                    $returnArray['file'] = $savePath . $imageName;
                }
            }
        }
        return $returnArray;
    }
    /**
     * 获取图片后缀
     * @param null $imageName
     * @return array
     */
    private function _getExtension ($imageName=null)
    {
        if(empty($imageName)) return ;
        $exts = @split("[/.]", $imageName);
        $exts = $exts[count($exts)-1];
        return $exts;
    }
}