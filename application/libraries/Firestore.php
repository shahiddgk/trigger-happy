<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';

class Firestore {

    private $firestore;

    function __construct() {
        // Initialize FirestoreClient here, but don't use parent::__construct()
        $this->firestore = new Google\Cloud\Firestore\FirestoreClient([
            'keyFilePath' => FCPATH.'uploads/burgeon-1311c-firebase-adminsdk-bpuzc-e1ce07f533.json',
            'projectId' => 'burgeon-1311c',
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

    public function addData($user_id) {
        $existingDocument = $this->getDocument($user_id);

        if ($existingDocument->exists()) {
            $pending = $existingDocument->data()['pending'] + 1;
            $this->updateDocument($user_id, ['pending' => $pending]);
        } else {
            $this->createDocument($user_id, ['user_id' => (int) $user_id, 'pending' => 1]);
        }
        return true;
    }

    public function resetCount($user_id) {
        $existingDocument = $this->getDocument($user_id);
    
        if ($existingDocument->exists()) {
            $this->updateDocument($user_id, ['pending' => 0]);
        } else {
            // Handle the case where the document doesn't exist as needed.
        }
    }

    private function getDocument($user_id) {
        return $this->collectionRef->document((string) $user_id)->snapshot();
    }

    private function createDocument($user_id, $data) {
        $this->collectionRef->document((string) $user_id)->set($data);
        return true;
    }

    private function updateDocument($user_id, $data) {
        $this->collectionRef->document((string) $user_id)->set($data, ['merge' => true]);
        return true;
    }
}
