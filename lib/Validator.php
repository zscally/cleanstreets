<?php
/**
 * @description: PHP Validator
 * User: zscally
 * Date: 1/19/2016
 * Time: 7:18 AM
 */

namespace lib;

class Validator {
    public $error_messages = array();
    public function validate($data, $constraint){
        $messages = array();
        foreach( $data as $k => $input_data )
        {
            if( ! empty( $constraint[$k] ) )
            {
                if( isset($constraint[$k]) )
                {
                    $rules = $constraint[$k];
                }
                else
                {
                    $messages[] = 'Data Structure not met exiting';
                    exit();
                }

                $error_msgs = array();
                foreach( $rules as $rule => $value )
                {
                    switch(strtolower($rule))
                    {
                        case 'require':
                            if( $value === true && empty( $input_data ) ){
                                $error_msgs[] = str_replace('_', ' ', $k) . ' is a required field';
                            }
                            break;
                        case 'validate':
                            if( $value == 'email' && filter_var($input_data, FILTER_VALIDATE_EMAIL) === false ) {
                                $error_msgs[] = 'Email Address is not valid';
                            }elseif( $value == 'int' && ! empty( $input_data ) && ! is_int( $input_data ) ){
                                $error_msgs[] = $input_data . ' Is not a valid integer';
                            }elseif( $value == 'phone' && ! empty( $input_data ) && ! $this->sanitizePhoneNumber($input_data) ){
                                $error_msgs[] = $input_data . ' Is not a valid phone number';
                            }

                            break;
                    }

                }

                if( count($error_msgs) > 0 ) {
                    $messages[$k] = $error_msgs;
                }
            }
        }

        if( ! empty ( $messages ) )
        {
            $this->error_messages = $messages;
            return false;
        }
        return true;
    }


    public function sanitizePhoneNumber($number){
        return preg_match("/^(\d[\s-]?)?[\(\[\s-]{0,2}?\d{3}[\)\]\s-]{0,2}?\d{3}[\s-]?\d{4}$/i", $number);
    }

}

