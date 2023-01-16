<?php

namespace Extra\Src;

use METHOD;
use Warframe;

abstract class TelegramBot extends Controller
{
    /**
     * 
     * TelegramBot
     * 
     * @version 2.3 betta
     * 
     * 
     * 
     *  @codeExample
     * 
     *  use Extra\Src\TelegramBot;

        class BotController extends TelegramBot
        {
            protected bool $debug = true;

            protected function cluster(array $data): void
            {
                if (mb_stripos($data['message']['text'], 1) !== false) {
                    $this->questStart('handle', $data);
                } else {
                    $this->sendMessage($data['message']['chat']['id'], 'Хай уёбок!');
                }
            }

            public static function handle(array $data)
            {
                $class = BotController::questHandle();
                $class->quest($data, 'name', 'What is your name?', function ($value)
                {
                    return is_string($value);
                });
                $class->quest($data, 'age', 'How old are you?', function ($value)
                {
                    return is_numeric($value);
                });
                $result = $class->getSession($data);
                $class->sendMessage($data['message']['chat']['id'], 'Name:' . $result['name'] . ' Age: ' . $result['age']);
                $class->questStop($data);
            }

        }
     * 
     */

    private static string $token;
    public static string $api = 'https://api.telegram.org/bot';
    public static string $apiSrc = 'https://api.telegram.org/file/bot';
    protected bool $debug = false;
    protected string $serverScheme = SERVER_SCHEME;
    private string $uploadFolder = PATH_MEDIA;
    private string $folderQuestion = 'question';
    private string $folderPhoto = 'photos';
    private string $folderAudio = 'audios';
    private string $folderDocument = 'documents';

    public function receiver(): void
    {
        $this->method(METHOD::POST);
        self::$token = Warframe::$cfg['TELEGRAM']['TOKEN'];
        $data = $this->receiverConstruct();
        if ($this->debug) $this->saveMessageToJson('message', $data);
        if(array_key_exists('message', $data)) $this->receiverDownloads($data['message']);
        $this->questCluster($data);
    }

    public final function connection()
    {
        $this->method(METHOD::GET);
        Route::isAuthAdmin();
        $token = Warframe::$cfg['TELEGRAM']['TOKEN'];
        if (!$token) Route::ApiError(503);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => self::$api . $token . '/setWebhook?url=' . $this->serverScheme . '/bot/receiver',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $this->renderJson(json_decode($response));
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
        $chatId = $this->getChatId($data);
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

    protected function questStart(string $funcName, array $data, ?array $args = []): void
    {
        $uploadFolder = $this->uploadFolder . '/' . $this->folderQuestion;
        if( !is_dir($uploadFolder) ) mkdir($uploadFolder);
        $sessionData = ['session' => $funcName, 'args' => $args];
        $this->saveMessageToJson($this->folderQuestion . '/' . $this->getChatId($data), $sessionData);
        call_user_func(static::class . '::' . $funcName, $data, $args);
    }

    protected function questStop(array $data): void
    {
        $userFile = $this->uploadFolder . '/' . $this->folderQuestion . '/' . $this->getChatId($data) . '.json';
        if(file_exists($userFile)) unlink($userFile);
    }
    
    protected final function sendMessage(int $chatId, string $message, ?array $messageAddition = []): void
    {
        $class = get_class($this);
        $request = curl_init($class::$api . $class::$token . '/sendMessage');  
        curl_setopt($request, CURLOPT_POST, 1);  
        curl_setopt($request, CURLOPT_POSTFIELDS, [
            'chat_id' => $chatId,
            'text' => $message,
            ...$messageAddition
        ]);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($request, CURLOPT_HEADER, false);
        curl_exec($request);
        curl_close($request);
    }

    public final function getChatId(array $data): int
    {
        if(array_key_exists('message', $data)) return $data['message']['chat']['id'];
        elseif (array_key_exists('callback_query', $data)) return $data['callback_query']['message']['chat']['id'];
        else return 0;
    }

    /*
    ---------------------------------------------
        PRIVATE
    ---------------------------------------------
    */

    private function questCluster(array $data): void
    {
        $chatId = $this->getChatId($data);
        $userSession = $this->uploadFolder . '/' . $this->folderQuestion . '/' . $chatId;
        if (file_exists($userSession . '.json')) {
            $session = $this->getMessageToJson($this->folderQuestion . '/' . $chatId);
            if (array_key_exists('args', $session)) {
                call_user_func(static::class . '::' . $session['session'], $data, $session['args']);
            } else call_user_func(static::class . '::' . $session['session'], $data);
        } else $this->cluster($data);
    }
    
    private function receiverConstruct(): array
    {
        $folder = str_replace('Controller', '', get_class($this));
        if( !is_dir(PATH_MEDIA . '/' . $folder) ) mkdir(PATH_MEDIA . '/' . $folder);
        $this->uploadFolder = PATH_MEDIA . '/' . $folder;
        $data = file_get_contents('php://input');
        return json_decode($data, true);
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
        if( !is_dir(PATH_MEDIA . '/' . $folder) ) mkdir(PATH_MEDIA . '/' . $folder);
        $class->uploadFolder = PATH_MEDIA . '/' . $folder;
        return $class;
    }

}