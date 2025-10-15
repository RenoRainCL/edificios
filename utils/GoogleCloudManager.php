<?php

// 📁 utils/GoogleCloudManager.php
require_once __DIR__.'/../vendor/autoload.php'; // Composer Google Cloud

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Storage\StorageClient;

class GoogleCloudManager
{
    private $storage;
    private $bucket;

    public function __construct()
    {
        $config = include __DIR__.'/../config/.env_proyecto';

        try {
            $this->storage = new StorageClient([
                'keyFilePath' => $config['GOOGLE_CREDENTIALS_PATH'],
            ]);
            $this->bucket = $this->storage->bucket($config['GOOGLE_CLOUD_BUCKET']);
        } catch (GoogleException $e) {
            error_log('Error Google Cloud: '.$e->getMessage());
            throw new Exception('Error inicializando Google Cloud');
        }
    }

    public function uploadFile($fileTmpPath, $fileName, $folder = 'documents')
    {
        try {
            $uniqueName = $this->generateUniqueFileName($fileName);
            $cloudPath = $folder.'/'.$uniqueName;

            $object = $this->bucket->upload(fopen($fileTmpPath, 'r'), [
                'name' => $cloudPath,
                'predefinedAcl' => 'publicRead',
            ]);

            return [
                'success' => true,
                'file_url' => $object->info()['mediaLink'],
                'file_path' => $cloudPath,
                'google_drive_id' => $object->info()['id'],
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function deleteFile($filePath)
    {
        try {
            $object = $this->bucket->object($filePath);
            $object->delete();

            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getSignedUrl($filePath, $expiration = 3600)
    {
        try {
            $object = $this->bucket->object($filePath);
            $signedUrl = $object->signedUrl(new DateTime('+'.$expiration.' seconds'));

            return $signedUrl;
        } catch (Exception $e) {
            return null;
        }
    }

    private function generateUniqueFileName($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $name = pathinfo($originalName, PATHINFO_FILENAME);

        return uniqid().'_'.$this->sanitizeFileName($name).'.'.$extension;
    }

    private function sanitizeFileName($fileName)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);
    }
}
