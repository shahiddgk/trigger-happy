<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

class Firestore {

    private $firestore;

    function __construct() {
        // Initialize FirestoreClient here, but don't use parent::__construct()
        $this->firestore = new Google\Cloud\Firestore\FirestoreClient([
            'keyFilePath' => FCPATH.'uploads/triggerhappy-a7406.json',
            'projectId' => 'triggerhappy-a7406',
        ]);

        $this->collectionRef = $this->firestore->collection('connections');
    }

    function getData() {
        $documents = $this->collectionRef->documents();
        $data = [];

        foreach ($documents as $document) {
            $data[] = $document->data();
        }

        return $data;
    }

    public function addData($user_id, $field) {
        $existingDocument = $this->getDocument($user_id);
        
        if ($existingDocument->exists()) {
            $documentData = $existingDocument->data();
            $exist_field = $documentData[$field] + 1;
            $dataToUpdate = [$field => $exist_field];
            $this->updateDocument($user_id, $dataToUpdate);
        } else {
            $this->createDocument($user_id, ['user_id' => (int) $user_id, 'con_request' => 1, 'shared_response'=> 0]);
        }
    }     

    public function resetCount($user_id , $field) {
        $existingDocument = $this->getDocument($user_id);
    
        if ($existingDocument->exists()) {
            $this->updateDocument($user_id, [$field => 0]);
        } 
        return;
    }

    private function getDocument($user_id) {
        return $this->collectionRef->document((string) $user_id)->snapshot();
    }

    private function createDocument($user_id, $data) {
       return $this->collectionRef->document((string) $user_id)->set($data);
    }

    private function updateDocument($user_id, $data) {
       return $this->collectionRef->document((string) $user_id)->set($data, ['merge' => true]);
    }
}
