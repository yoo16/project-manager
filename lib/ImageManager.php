<?php
/**
 * ImageManager
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
class ImageManager {

    static function tag($values, $is_image_dir=true) {
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if ($value) {
                    if ($key == 'src' && $is_image_dir) {
                        $value = image("/{$value}");
                    }
                    $attribute.= " {$key}=\"{$value}\"";
                }
            }
            $tag = "<img{$attribute}>";
        }
        return $tag;
    }

    /**
     * resize
     *
     * @param string $org_path
     * @param string $new_path
     * @param double $to_width
     * @param double $to_height
     * @return void
     **/
    static function resize($org_path, $new_path, $to_width, $to_height) {
        $org_image_path = UPLOAD_IMAGE_DIR.$org_path['dir'].$org_path['file_name'];
        $new_image_path = UPLOAD_IMAGE_DIR.$new_path['dir'].$new_path['file_name'];

        list($org_width , $org_height) = getimagesize($org_image_path);
        $src_image = imagecreatefromjpeg($org_image_path);

        // 再サンプル
        $new_image = imagecreatetruecolor($to_width, $to_height);
        imagefill($new_image, 0, 0, 0xFFFFFF);

        //縮小
        $rate = 1;
        if ($org_width > $org_height) {
            $rate = $to_width / $org_width;
        } elseif ($org_width < $org_height) {
            $rate = $to_height / $org_height;
        } else {
            if ($to_width > $to_height) {
                $rate = $to_height / $org_height;
            } else {
                $rate = $to_width / $org_width;
            }
        }
        $to_width = $rate * $org_width;
        $to_height = $rate * $org_height;
        imagecopyresampled($new_image, $src_image, 0, 0, 0, 0, $to_width, $to_height, $org_width, $org_height);

        imagejpeg($new_image, $new_image_path, 100);
        imagedestroy($src_image);
        imagedestroy($new_image);
    }
}