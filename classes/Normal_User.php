<?php

class Normal_User {

    // refer to database

    private $database;

    // Instantiate object with database connection

    public function __construct($db_connection)
    {
        $this->database = $db_connection;
        
    }

    // Register new User
    
    public function register_normal_user( $user_phone_number, $user_email, $user_name, $user_password, $user_location, $user_sec_question )
    {
        try {
                $user_role = 'normal';

                // encrypt password

                $hashed_user_password = password_hash( $user_password, PASSWORD_DEFAULT );
                
                // defining query to insert data into user table

                $sql = "INSERT INTO users ( user_phone, user_name, user_role, user_location, user_security_question, user_password) VALUES( :user_phone_number, :user_name, :user_role,  :user_location, :user_sec_question, :user_password )";

                // prepare the statement

                $query = $this->database->prepare($sql);

                // Bind parameters

                $query->bindParam( ':user_phone_number', $user_phone_number );
                $query->bindParam( ':user_name', $user_name );
                $query->bindParam( ':user_role', $user_role );
                $query->bindParam( ':user_email', $user_email );
                $query->bindParam( ':user_location', $user_location );
                $query->bindParam( ':user_sec_question', $user_sec_question );
                $query->bindParam( ':user_password', $hashed_user_password );

                // Execute the query

                $query->execute();



        } catch( PDOException $e ) {

            array_push($errors, $e->getMessage());
            //exit();

        }

    }  //end of register function


    // Login registered users with their phone number and their password

    public function login_normal_user( $user_phone_number, $user_password )
    {

        try {
            
            // Define query to insert values into the users table

            $sql = " SELECT * FROM users WHERE user_phone=:user_phone_number LIMIT 1 ";

            // prepare the statement

            $query = $this->database->prepare($sql);

            // Bind Parameters

            $query->bindParam( ':user_phone_number', $user_phone_number );

             // Execute the query

             $query->execute();

             // Return a data as an array indexed by phone number

             $returned_data = $query->fetch(PDO::FETCH_ASSOC);

             //checking if data is returned row wisely

             if ($query->rowCount() > 0)
              {

                    // Verify hashed password against entered password
                    if (password_verify($user_password, $returned_data['user_password']))
                    {
                        // Define session on successful login
                        $_SESSION['user_session'] = $returned_data['user_id'];

                        return true;

                    } else {
                        // Define failure
                        return false;
                    }
             }


            
        } catch ( PDOException $e ) {
           
            array_push($errors, $e->getMessage());
        }

    } //end of login function


    // check if a user is already logged in

    public function is_logged_in()
    {
        // check if user session is set

        if ( isset( $_SESSION['user_session'])) {

            return true;
            
        }

    } /// end of is_logged_in function


    // check if user is already exist

    public function is_already_exist( $user_phone_number ) 
    {

        try {

            // defining query to get data from database

            $sql = " SELECT * FROM users WHERE user_phone=:user_phone_number LIMIT 1 ";

            // prepare the statement

            $query = $this->database->prepare($sql);

            // Bind Parameters

            $query->bindParam( ':user_phone_number', $user_phone_number );

             // Execute the query

             $query->execute();

             if ( $query->rowCount() > 0 ) {
                 
                return true;

             } else {

                 return false;
             }
            
        } catch ( PDOException $e ) {
            
            array_push($errors, $e->getMessage());

        }


    }  // end of is_already_exist function



     // Log out user

     public function log_out()
     {
        // Destroy and unset active session
        session_destroy();

        unset($_SESSION['user_session']);

        return true;
     }



      // Redirect user
      
    public function redirect($url) 
    {
        header("Location: $url");
    }


    // Request Forget Password

    public function forget_password_request( $user_phone_number )
    {

        try {

            // defining query to get data from database

            $sql = " SELECT 'id', 'user_phone','user_email' FROM users WHERE user_phone=:user_phone_number LIMIT 1 ";

            // prepare the statement

            $query = $this->database->prepare($sql);

             // Bind Parameters

            $query->bindParam( ':user_phone_number', $user_phone_number );

             // Execute the query

             $query->execute();

            // Return a data as an array indexed by phone number

            $returned_data = $query->fetch(PDO::FETCH_ASSOC);

            if (!empty($returned_data)) {
                
                $user_phone = $returned_data['user_phone'];
                $user_id = $returned_data['id'];
                $email = $returned_data['email'];

                // Create secure token for this forget password request

                $token = openssl_random_pseudo_bytes(16);
                $token = bin2hex($token);

                // Insert the request information for forget password into password_reset table
                
                $insertsql = "INSERT INTO password_reset_request (user_id, date_requested, token) VALUES (:user_id, :date_requested, :token)";

                // prepare the statement

                 $insertquery = $this->database->prepare($insertsql);

                 // bind parameters

                 $insertquery->bindParam(':user_id', $user_id);
                 $insertquery->bindParam(':date_requested', date("Y-m-d H:i:s"));
                 $insertquery->bindParam(':token', $token);

                 // Exwcute Query

                 $insertquery->execute();

                 // Getting the id of the row that has been inserted

                 $password_request_id = $this->database->lastInsertId();

                 //Create a link to the URL that will verify the
                 //forgot password request and allow the user to change their
                 //password.

                 $verifyScript = 'https://your-website.com/forgot-pass.php';

                    //The link that we will send the user via email.

                 $linkToSend = $verifyScript . '?uid=' . $user_id . '&id=' . $password_request_id . '&t=' . $token;

                 // print email

                 $to      = $email;
                 $subject = 'Reset Password';
                 $message = "Dear $email,\r\n";
                 $message .= "Please visit the following link to reset your password:\r\n";
                 $message .= "-----------------------\r\n";
                 $message .= "$linkToSend\r\n";
                 $message .= "-----------------------\r\n";
                 $message .= "Please be sure to copy the entire link into your browser. The link will expire after 3 days for security reasons.\r\n\r\n";
                 $message .= "If you did not request this forgotten password email, no action is needed, your password will not be reset as long as the link above is not visited. However, you may want to log into your account and change your security password and answer, as someone may have guessed it.\r\n\r\n";
                 $message .= "Thanks,\r\n";

                 mail($to,$subject,$message);

                // $this->redirect('success');
                 
            } else {

                $_SESSION['message'] = " Email does not exist.";

            }
  
            
        } catch ( PDOException $e) {
            
            array_push($errors, $e->getMessage());

        }
    }


    











    

}