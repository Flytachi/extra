<?php

namespace Extra\Src;

use GdImage;

class Image
{
    /**
     * 
     * Image
     * 
     * @version 1.0
     */
    
    private GdImage $imgInput;
    private string $paramFont;
    private string $paramText;
    private int $imgColorallocate;
    private float $imgRotation = 0;
    private float $imgFontSize = 14;
    private int $oX = 0;
    private int $oY = 0;

    public function create(string $imgInputPath): bool
    {
        if(!file_exists($imgInputPath)) return false;
        $this->imgInput = imagecreatefrompng($imgInputPath);
        if ($this->imgInput == false) return false;
        else return true;
    }

    public function setFontPath(string $fontPath): Image|false
    {
        if(!file_exists($fontPath)) return false;
        $this->paramFont = $fontPath;
        return $this;
    }

    public function setColorallocate(int $red, int $gren, int $blue): Image
    {
        $this->imgColorallocate = imagecolorallocate($this->imgInput, $red, $gren, $blue);
        return $this;
    }

    public function setRotation(float $rotation): Image
    {
        $this->imgRotation = $rotation;
        return $this;
    }

    public function setX(int $position): Image
    {
        $this->oX = $position;
        return $this;
    }

    public function setY(int $position): Image
    {
        $this->oY = $position;
        return $this;
    }

    public function setFontSize(float $fontSize): Image
    {
        $this->imgFontSize = $fontSize;
        return $this;
    }

    public function setText(string $text): Image
    {
        $this->paramText = $text;
        return $this;
    }

    public function fixed(): array|false
    {
        return imagettftext($this->imgInput, $this->imgFontSize, $this->imgRotation, $this->oX, $this->oY, $this->imgColorallocate, $this->paramFont, $this->paramText);
    }

    public function saveIn(string $outputPath, int $quality = -1, int $filters = -1): bool
    {
        return imagepng($this->imgInput, $outputPath, $quality, $filters);
    }

    public function getClip(): array
    {
        return imagegetclip($this->imgInput);
    }
}

?>