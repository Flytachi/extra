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
 * - `uploadFile(array $file, ?string $prefixFolder = null): string`: Uploads a file to the media directory. Returns the path to saved file.
 * - `removeFile(string $fileName, ?string $prefixFolder = null): bool`: removes a file from the media directory. Returns the bool.
 *
 * @version 1.0
 * @author Flytachi
 */
trait UploadTrait {
    /** @var array $uploadFileFormat upload file format */
    protected array $uploadFileFormat = [];
    /** @var null|int $uploadFileSize upload file size (byte) */
    protected ?int $uploadFileSize = null;

    /**
     * Uploads a file to the media directory.
     *
     * @param array $fileProperty The file properties including name, error, tmp_name, and size.
     * @param string|null $prefixFolder (optional) The prefix folder to store the uploaded file.
     *
     * @return string The path of the uploaded file relative to the media directory.
     */
    final protected function uploadFile(array $fileProperty, ?string $prefixFolder = null): string
    {
        if( !is_dir(PATH_MEDIA) ) mkdir(PATH_MEDIA, 0777, true);
        $uploadFolder = '';

        if ($prefixFolder) {
            $uploadFolder .= $prefixFolder;
            if( !is_dir(PATH_MEDIA . '/' . $uploadFolder) ) mkdir(PATH_MEDIA . '/' . $uploadFolder, 0777, true);
        }

        if ( $fileProperty['name'] ) {
            // Upload File
            if ($fileProperty['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $fileProperty['tmp_name'];
                $fileSize = $fileProperty['size'];
                $fileNameCms = explode(".", $fileProperty['name']);
                $fileExtension = strtolower(end($fileNameCms));
                $newFileName = sha1(time() . $fileProperty['name']) . '.' . $fileExtension;
                // $fileType = $file['type'];

                // File size
                if ($this->uploadFileSize !== null && $this->uploadFileSize > 0 && $this->uploadFileSize < $fileSize)
                    UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFileError file is too big');

                // File format
                if (empty($this->uploadFileFormat) || ($this->uploadFileFormat > 0 && (in_array($fileExtension, $this->uploadFileFormat) || $this->uploadFileFormat == $fileExtension))) {

                    if(move_uploaded_file($fileTmpPath, PATH_MEDIA . "/$uploadFolder/$newFileName")) return trim("$uploadFolder/$newFileName", '/');
                    else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFileError writing to storage');

                } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFileError unsupported file format');
            } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFileError loading to temporary folder');
        } else UploadError::throw(HttpCode::INSUFFICIENT_STORAGE, 'UploadFileError file not name');
    }

    /**
     * Removes a file from the media directory.
     *
     * @param string $fileName The name of the file to be removed.
     * @param string|null $prefixFolder (optional) The prefix folder where the file resides.
     *
     * @return bool Returns `true` if the file was successfully removed or `false` otherwise.
     */
    final protected function removeFile(string $fileName, ?string $prefixFolder = null): bool
    {
        $filePath = PATH_MEDIA;
        if ($filePath != null) $filePath .= "/{$prefixFolder}";
        $filePath .= "/{$fileName}";
        if (!file_exists($filePath)) UploadError::throw(HttpCode::NOT_FOUND, "RemoveFileError '/{$prefixFolder}/{$fileName}' file not found");
        return unlink($filePath);
    }

}