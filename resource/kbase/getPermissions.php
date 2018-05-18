<?php
/**
 * Created by PhpStorm.
 * User: mvillalobos
 * Date: 4/16/2018
 * Time: 1:22 PM
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../config/Database.php';
include_once '../../object/Auth.php';
include_once '../../object/User.php';
include_once '../../object/KnowledgeBase.php';
include_once '../../object/Folder.php';
include_once '../../object/Register.php';
include_once '../../service/AuthService.php';
include_once '../../service/UserService.php';
include_once '../../service/KnowledgeBaseService.php';
include_once '../../service/FolderService.php';
include_once '../../service/RegisterService.php';
include_once '../../util/Constants.php';
include_once '../../util/ValidateHelper.php';

$headers = apache_request_headers();

$token = $headers['Authorization'];

$kb = $_REQUEST['id'];

try {
    $db = new Database();
    $conn = $db->getConnection();

    $authService = new AuthService($conn);
    $auth = $authService->getAuth($token);

    $userService = new UserService($conn);
    $user = $userService->getById($auth->getUser());

    $kbService = new KnowledgeBaseService($conn);
    $kb = $kbService->getById($kb);

    if(!$kbService->checkPermission($user->getId(), $kb->getId(), "own")){
        throw new Exception("User has no permission for this operation");
    }

    $perm_arr = array();
    foreach ($kb->getPermissions() as $permission){
        $temp_user = $userService->getById($permission['user']);
        $perm = array(
            "user" => $permission['user'],
            "role" => $permission['role'],
            "username" => $temp_user->getUsername(),
            "email" => $temp_user->getEmail(),
            "first_name" => $temp_user->getFirstName(),
            "last_name" => $temp_user->getLastName(),
            "profile_picture" => $temp_user->getProfilePicture()
        );

        array_push($perm_arr, $perm);
    }

    echo json_encode(array(
        'permissions' => $perm_arr,
        'success' => true
    ));
} catch (Exception $e) {
    echo json_encode(array('title' => 'Error', 'message' => $e->getMessage(), 'success' => false));
}