<?php

namespace Extra\Src\Trait;

use Extra\Src\Enum\HttpCode;
use Extra\Src\Route;

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
     *
     * @return string the path to the saved file
     */
    final protected function uploadFile(array $file, ?string $prefixFolder = null): string
    {
        if( !is_dir(PATH_MEDIA) ) mkdir(PATH_MEDIA, 0777, true);
        $uploadFolder = PATH_MEDIA;

        if ($prefixFolder) {
            $uploadFolder .= '/' . $prefixFolder;
            if( !is_dir(PATH_MEDIA . '/' . $uploadFolder ) ) mkdir(PATH_MEDIA . '/' . $uploadFolder, 0777, true);
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
                    Route::Throwable(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error file is too big.');

                // File format
                if (empty($this->uploadFileFormat) or ($this->uploadFileFormat > 0 and (in_array($fileExtension, $this->uploadFileFormat) or $this->uploadFileFormat == $fileExtension)) ) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return "$uploadFolder/$newFileName";
                    else Route::Throwable(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error writing to storage.');

                } else Route::Throwable(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error unsupported file format.');
            } else Route::Throwable(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error loading to temporary folder.');
        } else Route::Throwable(HttpCode::INSUFFICIENT_STORAGE, 'UploadFile: Error file not name.');
    }
}