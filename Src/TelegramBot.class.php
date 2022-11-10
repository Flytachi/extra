<?php

namespace Extra\Src;

abstract class TelegramBot extends Controller
{
    /**
     * 
     * TelegramBot
     * 
     * @version 1.0
     */

    public static string $token = '';
    public static string $api = 'https://api.telegram.org/bot';
    public static string $apiSrc = 'https://api.telegram.org/file/bot';
    protected bool $debug = false;
    private string $uploadFolder = PATH_MEDIA;
    private string $folderQuestion = 'question';
    private string $folderPhoto = 'photos';
    private string $folderAudio = 'audios';
    private string $folderDocument = 'documents';

    public function receiver(): void
    {
        $data = $this->receiverConstruct();
        if ($this->debug) $this->saveMessageToJson('message', $data);
        $this->receiverDownloads($data['message']);
        $this->questCluster($data);
    }

    /*
    ---------------------------------------------
        PROTECTED
    ---------------------------------------------
    */

    protected function cluster(array $data): void
    {
        $message = '[' . date('Y-m-d H:i:s', $data['message']['date']) . '] ' . $data['message']['text'];
        $this->sendMessage($data['message']['chat']['id'], $message);
    }

    protected function quest(array $data, string $responseName, string $questionContent, callable $funcValidation): void
    {
        $chatId = $data['message']['chat']['id'];
        $dataJson = $this->getMessageToJson($this->folderQuestion . '/' . $chatId);
        if (array_key_exists($responseName , $dataJson)) {
            if (is_null($dataJson[$responseName])) {
                if ($funcValidation($data['message']['text']) == true) {
                    $dataJson[$responseName] = $data['message']['text'];
                    $this->saveMessageToJson($this->folderQuestion . '/' . $chatId, $dataJson);
                } else {
                    $this->sendMessage($chatId, $questionContent);
                    exit;
                }
            }
        } else {
            $this->sendMessage($chatId, $questionContent);
            $dataJson[$responseName] = null;
            $this->saveMessageToJson($this->folderQuestion . '/' . $chatId, $dataJson);
            exit;
        }
    }

    protected function getSession(array $data): array
    {
        return $this->getMessageToJson($this->folderQuestion . '/' . $data['message']['chat']['id']);
    }

    protected function questStart(string $funcName, array $data): void
    {
        $uploadFolder = $this->uploadFolder . '/' . $this->folderQuestion;
        if( !is_dir($uploadFolder) ) mkdir($uploadFolder);
        $this->saveMessageToJson($this->folderQuestion . '/' .  $data['message']['chat']['id'], ['session' => $funcName ]);
        call_user_func(static::class . '::' . $funcName, $data);
    }

    protected function questStop(array $data): void
    {
        $userFile = $this->uploadFolder . '/' . $this->folderQuestion . '/' .  $data['message']['chat']['id'] . '.json';
        if(file_exists($userFile)) unlink($userFile);
    }
    
    protected final function sendMessage(int $chatId, string $message): void
    {
        $class = get_class($this);
        $request = curl_init($class::$api . $class::$token . '/sendMessage');  
        curl_setopt($request, CURLOPT_POST, 1);  
        curl_setopt($request, CURLOPT_POSTFIELDS, [
            'chat_id' => $chatId,
            'text' => $message
        ]);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        curl_exec($request);
        curl_close($request);
    }

    /*
    ---------------------------------------------
        PRIVATE
    ---------------------------------------------
    */

    private function questCluster(array $data): void
    {
        $userSession = $this->uploadFolder . '/' . $this->folderQuestion . '/' .  $data['message']['chat']['id'];
        if (file_exists($userSession . '.json')) {
            $session = $this->getMessageToJson($this->folderQuestion . '/' .  $data['message']['chat']['id']);
            call_user_func(static::class . '::' . $session['session'], $data);
        } else $this->cluster($data);
    }
    
    private function receiverConstruct(): array
    {
        $folder = str_replace('Controller', '', get_class($this));
        if( !is_dir(PATH_MEDIA . $folder) ) mkdir(PATH_MEDIA . $folder);
        $this->uploadFolder = PATH_MEDIA . $folder;
        $data = file_get_contents('php://input');
        return json_decode($data, true);
        // return $this->getMessageToJson('message');
    }

    private function receiverDownloads(array $mediaData): void
    {
        if (array_key_exists('photo', $mediaData)) $this->receiverDownloadPhoto($mediaData['photo']);
        if (array_key_exists('audio', $mediaData)) $this->receiverDownloadAudio($mediaData['audio']);
        if (array_key_exists('document', $mediaData)) $this->receiverDownloadDocument($mediaData['document']);
    }

    private function receiverDownloadPhoto(array $photo): void
    {
        $class = get_class($this); 
        $photo = array_pop($photo);
        $uploadFolder = $this->uploadFolder . '/' . $this->folderPhoto;
        if( !is_dir($uploadFolder) ) mkdir($uploadFolder);
        
        $request = curl_init($class::$api . $class::$token . '/getFile');  
        curl_setopt($request, CURLOPT_POST, 1);  
        curl_setopt($request, CURLOPT_POSTFIELDS, ['file_id' => $photo['file_id']]);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        $result = curl_exec($request);
        curl_close($request);
        
        $result = json_decode($result, true);
        if ($result['ok']) {
            $src  = $class::$apiSrc . $class::$token . '/' . $result['result']['file_path'];
            $dest = $uploadFolder . '/' . time() . '-' . basename($src);
            copy($src, $dest);
        }
    }

    private function receiverDownloadAudio(array $audio): void
    {
        $class = get_class($this);
        $uploadFolder = $this->uploadFolder . '/' . $this->folderAudio;
        if( !is_dir($uploadFolder) ) mkdir($uploadFolder);
        
        $request = curl_init($class::$api . $class::$token . '/getFile');  
        curl_setopt($request, CURLOPT_POST, 1);  
        curl_setopt($request, CURLOPT_POSTFIELDS, ['file_id' => $audio['file_id']]);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        $result = curl_exec($request);
        curl_close($request);
        
        $result = json_decode($result, true);
        if ($result['ok']) {
            $src  = $class::$apiSrc . $class::$token . '/' . $result['result']['file_path'];
            $dest = $uploadFolder . '/' . time() . '-' . basename($src);
            copy($src, $dest);
        }
    }

    private function receiverDownloadDocument(array $document): void
    {
        $class = get_class($this); 
        $uploadFolder = $this->uploadFolder . '/' . $this->folderDocument;
        if( !is_dir($uploadFolder) ) mkdir($uploadFolder);
	
        $request = curl_init($class::$api . $class::$token . '/getFile');  
        curl_setopt($request, CURLOPT_POST, 1);  
        curl_setopt($request, CURLOPT_POSTFIELDS, array('file_id' => $document['file_id']));
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        $result = curl_exec($request);
        curl_close($request);
        
        $result = json_decode($result, true);
        if ($result['ok']) {
            $src  = $class::$apiSrc . $class::$token . '/' . $result['result']['file_path'];
            $dest = $uploadFolder . '/' . time() . '-' . basename($src);
            copy($src, $dest);
        }
    }

    private function saveMessageToJson(string $fileName,  array $data): void
    {
        file_put_contents($this->uploadFolder . "/$fileName.json", json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getMessageToJson(string $fileName): array
    {
        $filePath = $this->uploadFolder . "/$fileName.json";
        return json_decode(file_get_contents($filePath), 1);
    }

    public static function questHandle(): TelegramBot
    {
        $className = static::class;
        $class = new $className;
        $folder = str_replace('Controller', '', $className);
        if( !is_dir(PATH_MEDIA . $folder) ) mkdir(PATH_MEDIA . $folder);
        $class->uploadFolder = PATH_MEDIA . $folder;
        return $class;
    }

}
