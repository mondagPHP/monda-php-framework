<?php

namespace framework\image;

/**
 * Class VerifyCode
 * @package framework\image
 * 生成验证码
 */
class VerifyCode
{
    /*letters generator array.*/
    private static $_letters = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
        'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D',
        'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
        'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    private $_length;

    /*configuration array.*/
    private $_config = ['x' => 0, 'y' => 0, 'w' => 100, 'h' => 50, 'f' => 3];

    /*image resource pointer*/
    private $_image = null;
    private $_codes;

    /*instance pointer.*/
    private static $_instance = null;

    private function __construct()
    {
        $this->_length = count(self::$_letters);
    }

    /**
     * @return VerifyCode
     * get the instance of the VerifyCode.
     */
    public static function getInstance(): self
    {
        if (! (self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * configure the attribute of the Verify code.
     * @param array $_array
     * @return VerifyCode
     */
    public function configure($_array = []): self
    {
        isset($_array['x']) && $this->_config['x'] = $_array['x'];
        isset($_array['y']) && $this->_config['y'] = $_array['y'];
        isset($_array['w']) && $this->_config['w'] = $_array['w'];
        isset($_array['h']) && $this->_config['h'] = $_array['h'];
        isset($_array['f']) && $this->_config['f'] = $_array['f'];
        return self::$_instance;
    }

    /**
     * generate some new verify code chars
     *    and draw them on the image. <br />
     *
     * @param int $_size number of chars to generate
     * @return  string
     */
    public function generate(int $_size = 3): string
    {
        if ($_size <= 0) {
            throw new \RuntimeException('验证码的长度必须大于0');
        }
        $this->_codes = [];
        while ($_size-- > 0) {
            $this->_codes[] = self::$_letters[mt_rand() % $this->_length];
        }
        return implode('', $this->_codes);
    }

    /**
     * show the image resource to the browser
     *    by send a header message. <br />
     *
     * @param string $_suffix image suffix
     */
    public function show($_suffix = 'png'): void
    {
        $this->createImage();
        if ($_suffix === 'gif' && function_exists('imagegif')) {
            $header = 'image/gif';
            imagegif($this->_image);
        } elseif ($_suffix === 'jpeg' && function_exists('imagejpeg')) {
            $header = 'image/jpeg';
            imagejpeg($this->_image, '', 0.9);
        } elseif ($_suffix === 'png' && function_exists('imagepng')) {
            $header = 'image/png';
            imagepng($this->_image);
        } else {
            throw new \RuntimeException('No image support in this PHP server');
        }
        response()->addHeader('Content-Type', $header);
    }

    /**
     * create a image resource,
     *        draw the codes just generated. <br />
     */
    private function createImage(): void
    {
        $this->_image !== null && imagedestroy($this->_image);
        $this->_image = imagecreatetruecolor($this->_config['w'], $this->_config['h']);
        switch (mt_rand() % 4) {
            case 0:
                $_bg = imagecolorallocate($this->_image, 250, 250, 250);
                break;
            case 1:
                $_bg = imagecolorallocate($this->_image, 255, 252, 232);
                break;
            case 2:
                $_bg = imagecolorallocate($this->_image, 254, 245, 243);
                break;
            case 3:
                $_bg = imagecolorallocate($this->_image, 233, 255, 242);
                break;
        }
        imagefilledrectangle($this->_image, 0, 0, $this->_config['w'], $this->_config['h'], $_bg);

        switch (mt_rand() % 5) {
            case 0:
                $_color = imagecolorallocate($this->_image, 128, 128, 128);
                break;    //gray
            case 1:
                $_color = imagecolorallocate($this->_image, 16, 9, 140);
                break;    //blue
            case 2:
                $_color = imagecolorallocate($this->_image, 65, 125, 0);
                break;    //green
            case 3:
                $_color = imagecolorallocate($this->_image, 255, 75, 45);
                break;    //read
                break;    //orange
            case 4:
            default:
                $_color = imagecolorallocate($this->_image, 238, 175, 7);
                break;
        }
        $_font = __DIR__ . '/fonts/ariblk.ttf';
        //$_angle = (mt_rand() & 0x01) == 0 ? mt_rand() % 30 : - mt_rand() % 30;

        //draw the code chars
        $_size = count($this->_codes);
        $_xstep = ($this->_config['w'] - 2 * $this->_config['x']) / $_size;
        foreach ($this->_codes as $i => $iValue) {
            $_ret = mt_rand();
            imagettftext($this->_image, $this->_config['f'], (int)($_ret & 0x01) === 0 ? $_ret % 30 : -($_ret % 30), $this->_config['x'] + $i * $_xstep, $this->_config['y'], $_color, $_font, $iValue);
        }
    }
}
