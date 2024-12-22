<?php
// -------------------- Another way of creating notes --------------------

require_once '../rest.api.php'; 
require_once '../../../lib/notes.class.php';
require_once '../../../lib/auth.class.php'; 


class NewNote extends REST {
    public function __construct() {
        parent::__contruct();
        }

    
}





// ${basename(__FILE__, '.php')} = function(){
//     if($this->get_request_method() == "POST" and $this->isAuthenticated() and isset($this->_request['title']) and isset($this->_request['body']) and isset($this->_request['folder'])){
//         $f = new Notes();
//         $id = $f->createNew($this->_request['title'], $this->_request['body'], $this->_request['folder']);
//         $data = [
//             'note_id' => $id
//         ];
//         $data = $this->json($data);
//         $this->response($data, 200);
//     } else {
//         $data = [
//             "error" => "Bad request"
//         ];
//         $data = $this->json($data);
//         $this->response($data, 400);
//     }
// };