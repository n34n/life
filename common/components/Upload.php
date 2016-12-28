<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 2016/11/14
 * Time: 上午12:41
 */

namespace common\components;

use yii;
use yii\web\UploadedFile;
use yii\imagine\Image;


class Upload extends UploadedFile
{
    public $UploadPath;

    public $file;

    /*
     * 初始化上传路径
     */
    public function __construct()
    {
        $this->UploadPath = Yii::$app->params['UploadPath'];
    }


    /*
     * 创建实例
     */
    public function createInstance($field)
    {
        return $this->file = UploadedFile::getInstanceByName($field);
    }


    /*
     * 保存文件
     */
    public function saveFile()
    {
        if ($this->file && $this->file->tempName) {
            //生成目录
            $path = $this->generateDir();
            if($path == 60001){
                return $path;
            }

            //文件信息数据重组
            $f['name']      = md5($path.time());
            $f['ext']		= (empty($this->file->extension) || $this->file->extension=="")?'jps':$this->file->extension;
            $f['path']		= $path;
            $f['savepath']	= $this->UploadPath.$path;
            $f['file']		= $f['name'].'.'.$f['ext'];

            //保存文件
            $code  = ($this->file->saveAs($f['savepath'].$f['file']))?$f:60002;
            return $code;
        }else{
            return 60000;
        }
    }


    /*
     * 生成目录
     */
    public function generateDir()
    {
        $char = Yii::$app->params['UperChar'];
        $path = $char[rand(1,25)].$char[rand(1,25)].'/'.$char[rand(1,25)].$char[rand(1,25)].'/';
        if(!is_dir($this->UploadPath.$path)){
            $code = $this->makeDir($this->UploadPath.$path);
            if($code == 60001) {
                return $code;
            }
        }
        return $path;
    }


    /*
     * 创建目录
     */
    public function makeDir($path)
    {
        if (!file_exists($path)){
            $this->makeDir(dirname($path));
            mkdir($path, 0777);
        }else{
            return 60001;
        }
    }


    /*
     * 删除文件
     */
    public function delFile($file)
    {
        $file = $this->UploadPath.$file;
        if($file!='' && file_exists($file) && !is_dir($file)){
            unlink($file);
        }
    }


    /*
     * 计算图片width/height宽高比
     */
    public function getSize($data,$width,$height)
    {
        $_width 	= $data[0];
        $_height	= $data[1];

        if($_width > $_height){
            $w = $width;
            $h = $_height/$_width*$w;
        }else{
            $h = $height;
            $w = $_width/$_height*$h;
        }

        $size['w'] = (int)$w;
        $size['h'] = (int)$h;

        return $size;
    }


    /*
     * 生成缩略图
     */
    public function thumb($file,$tag,$width,$height,$quality=100)
    {
        $src_file	= $file['savepath'].$file['file'];
        $save_file	= $file['savepath'].$file['name'].'-'.$tag.'.'.$file['ext'];
        $dbpath	    = $file['path'].$file['name'].'-'.$tag.'.'.$file['ext'];

        $data = getimagesize($src_file);
        if($data[0] == $data[1]){
            $size['w'] = $size['h'] = $width;
        }else{
            $size = $this->getSize($data,$width,$height);
        }

        Image::thumbnail($src_file,$size['w'],$size['h'])
            ->save(Yii::getAlias($save_file), ['quality' => $quality]);
        return $dbpath;
    }


    /*
     * 获取远程图片,保存到本地
     */
    public function getFileAndSave($url)
    {
        $img = file_get_contents($url);
        if($img){
            $path = $this->generateDir();
            if($path == 60001){
                return $path;
            }

            $f['name']      = md5($path.time());
            $f['ext']		= 'jpg';
            $f['path']		= $path;
            $f['savepath']	= $this->UploadPath.$path;
            $f['file']		= $f['name'].'.'.$f['ext'];

            file_put_contents($f['savepath'].$f['file'], $img);
            return $f;
        }
        return 60003;
    }

}