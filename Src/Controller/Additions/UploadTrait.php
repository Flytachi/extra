<?php

namespace Extra\Src\Controller\Additions;

use Extra\Src\Error\UploadError;
use Extra\Src\HttpCode;

/**
 * Trait UploadTrait
 *
 * `UploadTrait` provides functionality for handling file uploads in the controllers.
 * It validates and moves uploaded files into appropriate directories.
 *
 * The methods provided by `UploadTrait` include:
 *
 * - `uploadFile(array $file, ?string $prefixFolder = null): string`: Validates and saves uploaded file to the specified directory. Returns the path to saved file.
 *
 * @version 1.0
 * @author Flytachi
 */
trait UploadTrait {
    /** @var array $uploadFileFormat upload file format */
    protected array $uploadFileFormat;
    /** @var int $uploadFileSize upload file size (byte) */
    protected int $uploadFileSize;

    /**
     * Upload File
     *
     * Saves the file in the folder PATH_MEDIA/'the name of the api controller'.
     *
     * @param array $file variable from array $_FILES[?]
     * @param ?string $prefixFolder Prefix Folder Name
     * @return string the path to the saved file
     */
    final protected function uploadFile(array $file, ?string $prefixFolder = null): string
    {
        if( !is_dir(PATH_MEDIA) ) mkdir(PATH_MEDIA, 0777, true);
        $uploadFolder = '';

        if ($prefixFolder) {
            $uploadFolder .= $prefixFolder;
            if( !is_dir(PATH_MEDIA . '/' . $uploadFolder) ) mkdir(PATH_MEDIA . '/' . $uploadFolder, 0777, true);
        }

        if ( $file['name'] ) {
            // Upload File
            if ($file['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $file['tmp_name'];
                $fileSize = $file['size'];
                $fileNameCms = explode(".", $file['name']);
                $fileExtension = strtolower(end($fileNameCms));
                $newFileName = sha1(time() . $file['name']) . '.' . $fileExtension;
                // $fileType = $file['type'];

                // File size
                if ($this->uploadFileSize > 0 and $this->uploadFileSize < $fileSize)
                    UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error file is too big.');

                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error writing to storage.');

                } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error unsupported file format.');
            } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error loading to temporary folder.');
        } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error file not name.');
    }
}