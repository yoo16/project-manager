<?php
/**
 * PwColor
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwColor {

    /**
     * convert hex to RGB array
     *
     *  @param   string  $color_hex  色(16進)
     *  @return  array
     */
    function convertColorRGB($color_hex)
    {
        $color = str_replace('#', '', $color_hex);
        $values = array(
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        );
        return $values;
    }

    /**
     *  convert image color by color hex
     *
     *  @param   string  $color_hex  色(16進)
     *  @return  int
     */
    function convertImageColor($color_hex)
    {
        $rgb = $this->convertColorRGB($color_hex);
        $color = imagecolorallocate($this->img, $rgb['r'], $rgb['g'], $rgb['b']);
        return $color;
    }

    /**
     * RGB list
     *
     * @param integer $count
     * @return array
     */
    static function rgbList($count = 256) {
        if (!$count) $count = 256;

        $r = 0;
        $g = 0;
        $b = 0;
        $r_hex = '00';
        $g_hex = '00';
        $b_hex = '00';
        $color = '0000';

        $color_numbers = [256, 64, 192, 128];

        $color_index = 0;
        for ($i = 1; $i < 256; $i++) {
            $color_number = $color_numbers[$color_index];
            if ($i != 0 && $i % 6 == 0) {
                $color_index++;
                if ($color_index >= 4) $color_index = 0;

                $r = $color_number;
                $g = 0;
                $b = $color_number;
            } else if ($i != 0 && $i % 5 == 0) {
                $r = 0;
                $g = $color_number;
                $b = $color_number;
            } else if ($i != 0 && $i % 4 == 0) {
                $r = $color_number;
                $g = $color_number;
                $b = 0;
            } else if ($i != 0 && $i % 3 == 0) {
                $r = $color_number;
                $g = 0;
                $b = 0;
            } else if ($i != 0 && $i % 2 == 0) {
                $r = 0;
                $g = $color_number;
                $b = 0;
            } else {
                $r = 0;
                $g = 0;
                $b = $color_number;
            }

            if ($r >= 256) $r = 255;
            if ($g >= 256) $g = 255;
            if ($b >= 256) $b = 255;

            $r_hex = dechex($r);
            $g_hex = dechex($g);
            $b_hex = dechex($b);

            $r_hex = sprintf('%02s', $r_hex);
            $g_hex = sprintf('%02s', $g_hex);
            $b_hex = sprintf('%02s', $b_hex);

            $color = "#{$r_hex}{$g_hex}{$b_hex}";
            $colors[] = $color;
        }
        return $colors;
    }
}