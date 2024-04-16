<?php

namespace Extra\Src\Sheath;

use GdImage;

/**
 *  Extra collection
 *  
 *  Image - to work with images
 * 
 *  @package Extra\Src\Sheath
 *  @version 3.0
 *  @author itachi
 */
class Image
{   
    /** @var GdImage $imgInput image resource */
    private GdImage $imgInput;
    /** @var string $paramFont font file path */
    private string $paramFont;
    /** @var string $paramText text */
    private string $paramText;
    /** @var int $imgColorallocate color index */
    private int $imgColorallocate;
    /** @var float $imgRotation angle */
    private float $imgRotation = 0;
    /** @var float $imgFontSize fontsize */
    private float $imgFontSize = 14;
    /** @var int $oX x-ordinate */
    private int $oX = 0;
    /** @var int $oY y-ordinate */
    private int $oY = 0;

    /**
     * Create a new image from file or URL
     * 
     * @param string $filename — Path to the PNG image.
     * 
     * @return bool
     */
    public function create(string $imgInputPath): bool
    {
        if(!file_exists($imgInputPath)) return false;
        $this->imgInput = imagecreatefrompng($imgInputPath);
        if ($this->imgInput == false) return false;
        else return true;
    }

    /**
     * Set font file path 
     * 
     * @param string $font_filename The path to the TrueType font you wish to use.
     * Depending on which version of the GD library PHP is using, when font_filename does not begin with a leading / then .ttf will be 
     * appended to the filename and the library will attempt to search for that filename along a library-defined font path.
     * 
     * When using versions of the GD library lower than 2.0.18, a space character, rather than a semicolon, was used as the 'path separator' for 
     * different font files. Unintentional use of this feature will result in the warning message: Warning: Could not find/open font. 
     * For these affected versions, the only solution is moving the font to a path which does not contain spaces.
     * 
     * In many cases where a font resides in the same directory as the script using it the following trick will alleviate any include problems.
     * 
     * Note: open_basedir does not apply to font_filename.
     * 
     * @return Image|false
     */
    public function setFontPath(string $fontPath): Image|false
    {
        if(!file_exists($fontPath)) return false;
        $this->paramFont = $fontPath;
        return $this;
    }

    /**
     * Set color (RGB format)
     * 
     * @param int $red — Value of red component.
     * @param int $green — Value of green component.
     * @param int $blue — Value of blue component.
     * 
     * @return Image
     */
    public function setColorallocate(int $red, int $gren, int $blue): Image
    {
        $this->imgColorallocate = imagecolorallocate($this->imgInput, $red, $gren, $blue);
        return $this;
    }

    /**
     * Set angle
     * 
     * @param float $rotation The angle in degrees, with 0 degrees being left-to-right reading text. 
     * Higher values represent a counter-clockwise rotation. For example, a value of 90 would result in bottom-to-top reading text.
     * 
     * @return Image
     */
    public function setRotation(float $rotation): Image
    {
        $this->imgRotation = $rotation;
        return $this;
    }

    /**
     * Set x-ordinate
     * 
     * @param int $x The coordinates given by x and y will define the basepoint of the first 
     * character (roughly the lower-left corner of the character). 
     * This is different from the image string, where x and y define the upper-left corner of the first character.
     * For example, "top left" is 0, 0.
     * 
     * @return Image
     */
    public function setX(int $position): Image
    {
        $this->oX = $position;
        return $this;
    }

    /**
     * Set y-ordinate
     * 
     * @param int $y The y-ordinate.
     * This sets the position of the fonts baseline, not the very bottom of the character.
     * 
     * @return Image
     */
    public function setY(int $position): Image
    {
        $this->oY = $position;
        return $this;
    }

    /**
     * Set font size
     * 
     * @param float $fontSize The font size. 
     * Depending on your version of GD, this should be specified as the pixel size (GD1) or point size (GD2).
     * 
     * @return Image
     */
    public function setFontSize(float $fontSize): Image
    {
        $this->imgFontSize = $fontSize;
        return $this;
    }

    /**
     * Set text
     * 
     * @param string $text The text string in UTF-8 encoding.
     * May include decimal numeric character references (of the form: €) to access characters in a font beyond position 127. 
     * The hexadecimal format (like ©) is supported. Strings in UTF-8 encoding can be passed directly.
     * 
     * Named entities, such as ©, are not supported. Consider using html_entity_decode to decode 
     * these named entities into UTF-8 strings (html_entity_decode() supports this as of PHP 5.0.0).
     * 
     * If a character is used in the string which is not supported by the font, a hollow rectangle will replace the character.
     * 
     * @return Image
     */
    public function setText(string $text): Image
    {
        $this->paramText = $text;
        return $this;
    }

    /**
     * Write text to the image using TrueType fonts
     * 
     * @return array|false an array with 8 elements representing four points making the bounding box of the text. 
     * The order of the points is lower left, lower right, upper right, upper left. 
     * The points are relative to the text regardless of the angle, so "upper left" means in the top left-hand corner when you see the text horizontally. 
     * Returns false on error.
     */
    public function fixed(): array|false
    {
        return imagettftext($this->imgInput, $this->imgFontSize, $this->imgRotation, $this->oX, $this->oY, $this->imgColorallocate, $this->paramFont, $this->paramText);
    }

    /**
     * Output a PNG image to either the browser or a file
     * 
     * @param string $outputPath
     * [optional]
     * 
     * The path to save the file to. If not set or null, the raw image stream will be outputted directly.
     * 
     * null is invalid if the quality and filters arguments are not used.
     * 
     * @param int $quality
     * [optional]
     * 
     * Compression level: from 0 (no compression) to 9.
     * 
     * @param int $filters
     * [optional]
     * 
     * Allows reducing the PNG file size. It is a bitmask field which may be set to any combination of the PNG_FILTER_XXX constants. 
     * PNG_NO_FILTER or PNG_ALL_FILTERS may also be used to respectively disable or activate all filters.
     * 
     * @return bool true on success or false on failure.
     */
    public function saveIn(string $outputPath, int $quality = -1, int $filters = -1): bool
    {
        return imagepng($this->imgInput, $outputPath, $quality, $filters);
    }

    /**
     * Retrieves the current clipping rectangle, i.e. the area beyond which no pixels will be drawn.
     * 
     * @return array|false
     * 
     * an indexed array with the coordinates of the clipping rectangle which has the following entries:
     *  * x-coordinate of the upper left corner
     *  * y-coordinate of the upper left corner
     *  * x-coordinate of the lower right corner
     *  * y-coordinate of the lower right corner
     * 
     * Returns FALSE on error.
     */
    public function getClip(): array
    {
        return imagegetclip($this->imgInput);
    }
}