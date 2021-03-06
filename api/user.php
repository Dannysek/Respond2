<?php

/**
 * A protected API call to login a user
 * @uri /user/login
 */
class UserLoginResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function login() {

        // parse request
        parse_str($this->request->data, $request);

        $email = $request['email'];
        $password = $request['password'];

        // get the user from the credentials
        $user = User::GetByEmailPassword($email, $password);

        if($user!=null){
            
            // create a session from the user
            AuthUser::Create($user);

            // return a successful response (200)
            return new Tonic\Response(Tonic\Response::OK);
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}


/**
 * A protected API call to send an email if you forgot your password
 * @uri /user/forgot
 */
class UserForgotResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function forgot() {

        // parse request
        parse_str($this->request->data, $request);

        $email = $request['email'];
        
        $user = User::GetByEmail($email);

        if($user!=null){
            
            $token = urlencode(User::SetToken($user['UserUniqId']));
            
            // send an email to reset the password
        	$to = $email;
    		$subject = 'RespondCMS: Reset your password';
    		$message = '<html>
    			<head>
    			  <title>RespondCMS: Reset your password</title>
    			</head>
    			<body>
    			  <p>
    			  	To reset your password, click on the <br>
    				<a href="'.APP_URL.'/forgot?t='.$token.'">'.APP_URL.'/forgot?t='.$token.'
    				</a>
    			  </p>
    			</body>
    			</html>';
    
    		$headers  = 'MIME-Version: 1.0' . "\r\n";
    		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    		$headers .= 'From: no-reply@respondcms.com' . "\r\n" .
        				'Reply-To: no-reply@respondcms.com' . "\r\n";
    
    		mail($to, $subject, $message, $headers);
            
            // return a successful response (200)
            return new Tonic\Response(Tonic\Response::OK);

        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * A protected API call to login a user
 * @uri /user/reset
 */
class UserResetResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function reset() {

        // parse request
        parse_str($this->request->data, $request);

        $token = $request['token'];
        $password = $request['password'];

        // get the user from the credentials
        $user = User::GetByToken($token);

        if($user!=null){
            
            User::EditPassword($user['UserUniqId'], $password);
            
            // return a successful response (200)
            return new Tonic\Response(Tonic\Response::OK);
        }
        else{
            // return an unauthorized exception (401)
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }
}

/**
 * A protected API call to add a user
 * @uri /user/add
 */
class UserAddResource extends Tonic\Resource {

    /**
     * @method POST
     */
    function add() {

        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserUniqId)){ // check if authorized

            parse_str($this->request->data, $request); // parse request

            $email = $request['email'];
            $password = $request['password'];
            $firstName = $request['firstName'];
            $lastName = $request['lastName'];
            $role = $request['role'];

            $user = User::Add($email, $password, $firstName, $lastName, $role, $authUser->SiteId);

            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'applicaton/json';
            $response->body = json_encode($user);

            return $response;
        
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }

}

/**
 * A protected API call to edit, delete an existing user
 * @uri /user/{userUniqId}
 */
class UserResource extends Tonic\Resource {

    /**
     * @method GET
     */
    function get($userUniqId) {
        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserUniqId)){ // check if authorized

            $user = User::GetByUserUniqId($userUniqId);

            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'applicaton/json';
            $response->body = json_encode($user);

            return $response;
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }

    /**
     * @method POST
     */
    function update($userUniqId) {

        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserUniqId)){ // check if authorized

            parse_str($this->request->data, $request); // parse request

            $email = $request['email'];
            $password = $request['password'];
            $firstName = $request['firstName'];
            $lastName = $request['lastName'];
            $role = $request['role'];

            User::Edit($userUniqId, $email, $password, $firstName, $lastName, $role);
            
            return new Tonic\Response(Tonic\Response::OK);
        
        } else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
        
    }

    /**
     * @method DELETE
     */
    function remove($userUniqId) {
        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserUniqId)){ // check if authorized

            User::Remove($userUniqId);

            return new Tonic\Response(Tonic\Response::OK);
        }
        else{
            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }
    }

}


/**
 * A protected API call that shows all pages
 * @uri /user/list/all
 */
class UserListAll extends Tonic\Resource {

    /**
     * @method GET
     */
    function get() {

        // get an authuser
        $authUser = new AuthUser();

        if(isset($authUser->UserUniqId)){ // check if authorized

            // get pages
            $list = User::GetUsersForSite($authUser->SiteId, true);
      
            // return a json response
            $response = new Tonic\Response(Tonic\Response::OK);
            $response->contentType = 'applicaton/json';
            $response->body = json_encode($list);

            return $response;

        }
        else{ // unauthorized access

            return new Tonic\Response(Tonic\Response::UNAUTHORIZED);
        }

    }

}

?>