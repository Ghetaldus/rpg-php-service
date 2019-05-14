<?php

function Login($username, $password)
{
    $output = array('error' => '');
    if (empty($username) || empty($password)) {
        $output['error'] = 'ERROR_EMPTY_USERNAME_OR_PASSWORD';
    } else {
        $playerAuthDb = new PlayerAuth();
        $playerAuth = $playerAuthDb->load(array(
            'username = ? AND password = ? AND type = 1',
            $username,
            $password
        ));
        if (!$playerAuth) {
            $output['error'] = 'ERROR_INVALID_USERNAME_OR_PASSWORD';
        } else {
            $playerDb = new Player();
            $player = $playerDb->load(array(
                'id = ?',
                $playerAuth->playerId
            ));
            if (!$player) {
                $output['error'] = 'ERROR_INVALID_USERNAME_OR_PASSWORD';
            } else {
                $player = UpdatePlayerLoginToken($player);
                UpdateAllPlayerStamina($player->id);
                $output['player'] = CursorToArray($player);
            }
        }
    }
    echo json_encode($output);
}

function GuestLogin($deviceId)
{
    $output = array('error' => '');
    if (empty($deviceId)) {
        $output['error'] = 'ERROR_EMPTY_USERNAME_OR_PASSWORD';
    }  else if (IsPlayerWithUsernameFound(0, $deviceId)) {
        $playerDb = new Player();
        $player = $playerDb->load(array(
            'id = ?',
            $playerAuth->playerId
        ));
        if (!$player) {
            $output['error'] = 'ERROR_INVALID_USERNAME_OR_PASSWORD';
        } else {
            $player = UpdatePlayerLoginToken($player);
            UpdateAllPlayerStamina($player->id);
            $output['player'] = CursorToArray($player);
        }
    } else {
        $player = InsertNewPlayer(0, $deviceId, $deviceId);
        $output['player'] = CursorToArray($player);
    }
    echo json_encode($output);
}

function ValidateLoginToken($refreshToken)
{
    $output = array('error' => '');
    $player = GetPlayer();
    if (!$player) {
        $output['error'] = 'ERROR_INVALID_LOGIN_TOKEN';
    } else {
        if ($refreshToken) {
            $player = UpdatePlayerLoginToken($player);
        }
        UpdateAllPlayerStamina($player->id);
        $output['player'] = CursorToArray($player);
    }
    echo json_encode($output);
}

function SetProfileName($profileName)
{
    $output = array('error' => '');
    $player = GetPlayer();
    $playerDb = new Player();
    $countPlayerWithName = $playerDb->count(array(
        'profileName = ?',
        $profileName
    ));
    if (!$player) {
        $output['error'] = 'ERROR_INVALID_LOGIN_TOKEN';
    } else if (empty($profileName)) {
        $output['error'] = 'ERROR_EMPTY_PROFILE_NAME';
    } else if ($countPlayerWithName > 0) {
        $output['error'] = 'ERROR_EXISTED_PROFILE_NAME';
    } else {
        $player->profileName = $profileName;
        $player->update();
        $output['player'] = CursorToArray($player);
    }
    echo json_encode($output);
}

function Register($username, $password)
{
    $output = array('error' => '');
    if (empty($username) || empty($password)) {
        $output['error'] = 'ERROR_EMPTY_USERNAME_OR_PASSWORD';
    } else if (IsPlayerWithUsernameFound(1, $username)) {
        $output['error'] = 'ERROR_EXISTED_USERNAME';
    } else {
        $player = InsertNewPlayer(1, $username, $password);
        $output['player'] = CursorToArray($player);
    }
    echo json_encode($output);
}
?>