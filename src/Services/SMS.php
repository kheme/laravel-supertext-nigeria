<?php

namespace Kheme\SuperTextNg\Services;

class SMS
{
    protected $api_url     = 'https://www.supertextng.com/api.php?';
    protected $balance_url = 'https://www.supertextng.com/getbalance.php?';
    protected $message;
    protected $username;
    protected $password;
    protected $sender;
    protected $ignore_dnd;
    protected $recipients        = [];
    protected $can_send          = false;
    protected $return_balance    = false;
    protected $return_units_used = false;

    public $errors;

    /**
     * Create a new instance
     *
     * @author Okiemute Omuta <iamkheme@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->username   = config('supertextng.access.username');
        $this->password   = config('supertextng.access.password');
        $this->sender     = config('supertextng.settings.sender');
        $this->ignore_dnd = config('supertextng.settings.ignore_dnd');

        $this->errors = [
            '100' => 'One or more required url parameter is missing or misspelt.',
            '101' => 'Username is blank.',
            '102' => 'Password is blank.',
            '103' => 'Destination is blank.',
            '104' => 'Message is blank.',
            '105' => 'Sender is blank.',
            '200' => 'Wrong username or password.',
            '201' => 'Account has not been activated.',
            '202' => 'Inactive account.',
            '300' => 'Insufficient credit.',
            '400' => 'Failed delivery (no credit deducted).',
        ];
    }

    /**
     * Add a number to the array of recipients
     * 
     * @param mixed $recipient Array or single phone number in international format
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return object
     */
    public function to($recipient) : object
    {
        if (is_array($recipient)) {
            $this->recipients = array_merge($this->recipients, $recipient);
        } else {
            $this->recipients[] = (trim($recipient));
        }

        return $this;
    }

    /**
     * Set the sender ID
     * 
     * @param string $sender Sender ID
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return string
     */
    public function from(string $sender) : object
    {
        $this->sender = substr($sender, 0, 11);

        return $this;
    }

    /**
     * Set the message body
     * 
     * @param string $message The body of the SMS message to be sent
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return object
     */
    public function message(string $message) : object
    {
        $this->message  = $message;
        $this->can_send = true;

        return $this;
    }

    /**
     * Set sending to DND numbers
     * 
     * @param bool $ignore Send fo DND numbers (true) or not (false)? Defaults to true
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return object
     */
    public function ignoreDND(bool $ignore = true) : object
    {
        if ($ignore) {
            $this->ignore_dnd = 'yes';
        }

        return $this;
    }

    /**
     * Should we return balance after sending message(s)?
     * 
     * @param bool $return Return balance (true) or not (false)? Defaults to true
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return object
     */
    public function returnBalance(bool $return = true) : object
    {
        $this->return_balance = $return;

        return $this;
    }

    /**
     * Should we return the numnber of units used after sending message(s)?
     * 
     * @param bool $return Return balance (true) or not (false)? Defaults to true
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return object
     */
    public function returnUnitsUsed(bool $return = true) : object
    {
        $this->return_units_used = $return;

        return $this;
    }

    /**
     * Send the SMS message
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * return json
     */
    public function send()
    {
        $this->checkPrerequisites();

        $query = [
            'username' => $this->username,
            'password' => $this->password,
            'sender'   => $this->sender,
            'message'  => $this->message
        ];

        if ($this->ignore_dnd) {
            $query['no_dnd'] = 'yes';
        }

        $this->recipients = array_unique(array_filter($this->recipients));

        $url_list   = [];
        $send_count = 0;
        $units_used = 0.0;

        foreach ($this->recipients as $number) {
            $query['destination'] = $number;

            $url_list[] = $this->api_url . http_build_query($query);                
        }

        try {
            $responses = $this->multiCurl($url_list);

            // if message was sent successfully
            foreach ($responses as $response) {
                $split_response = explode(':', $response);

                if ($split_response[0] == 'SENT') {
                    $send_count += 1;
                    $units_used += $split_response[1] ?? 0;
                }
            }
        } catch (\Exception $exception) {
            // there was an error
        }

        $message = $send_count . ' of ' . count($this->recipients) . ' SMS sent';

        $return = [
            'success' => true,
            'message' => $message,
        ];

        if ($this->return_units_used) {
            $return['data']['units_used'] = $units_used;
        }

        if ($this->return_balance) {
            $return['data']['balance'] = $this->balance(false) ?? 'Cannot get balance!';
        }

        exit($this->jsonResponse($return));
    }

    /**
     * Method to get the user's balance
     * 
     * @param boolean $can_exit (Optional) Should we exit after execution?
     *                          If we call this method from within this class,
     *                          we don't need to exit after execution.
     *                          Otherwise if called from outside, we should exit.
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     * 
     * @return array|int
     */
    public function balance($can_exit = true)
    {
        $url = $this->balance_url . http_build_query(
            [
                'username' => $this->username,
                'password' => $this->password
            ]
        );

        $response = trim(file_get_contents($url));

        $this->handleResponseError($response);

        $return = [
            'success' => true,
            'data' => [ 'balance' => (float) round(explode(':', $response)[1], 1) ],
        ];

        if ($can_exit) {
            exit($this->jsonResponse($return));
        }

        return $return['data']['balance'];
    }

    /**
     * Return json response to the browser
     *
     * @param array $data             Array containing data to reutnr as json
     * @param int   $http_status_code Optional error code to include in header
     *
     * @author Okiemute Omuta <iamkheme@gmail.com>
     *
     * @return json
     */
    protected function jsonResponse($data, $http_status_code = 200)
    {
        header_remove();
        header('Content-Type: application/json;charset=utf-8');
        http_response_code($http_status_code);

        return json_encode($data);
    }

    /**
     * Check that all requirements are set before attempting to send a message
     *
     * @author Okiemute Omuta <iamkheme@gmail.com>
     *
     * @return void
     */
    protected function checkPrerequisites()
    {
        if (! $this->username || ! $this->password) {
            throw new \Exception('Username and password not set: cannot send message!');
        }

        if (! $this->can_send || trim($this->message) == '') {
            throw new \Exception('Message not set: cannot send emtpy message!');
        }

        if (! $this->recipients) {
            throw new \Exception('Recipient(s) not set: cannot send to nobody!');
        }

        if (! $this->sender || trim($this->sender) == '') {
            throw new \Exception('Sender ID not set: cannot send from nobody!');
        }
    }

    /**
     * Execute a multi-curl
     *
     * @param array $urls Array of URLs to call simultensously
     * 
     * @author Okiemute Omuta <iamkheme@gmail.com>
     *
     * @return array
     */
    protected function multiCurl(array $urls) : array
    {
        $results  = [];
        $curl_arr = [];
        $master   = curl_multi_init();

        foreach ($urls as $key => $url) {
            $curl_arr[$key] = curl_init($url);
            curl_setopt($curl_arr[$key], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($master, $curl_arr[$key]);
        }

        do {
            curl_multi_exec($master, $running);
        } while ($running > 0);
        
        foreach ($curl_arr as $key => $curl_resource) {
            $temp = curl_multi_getcontent($curl_resource);
            
            if (is_integer($key)) {
                $results[($key + 1)] = trim($temp);
            } else {
                $results[$key] = trim($temp);
            }
        }
        
        return $results;
    }

    /**
     * Handle errors returned from SuperText NG
     *
     * @param string $response String response from SuperText NG
     *
     * @author Okiemute Omuta <iamkheme@gmail.com>
     *
     * @return json
     */
    protected function handleResponseError($response)
    {
        if (strlen($response) == 3) {
            exit($this->jsonResponse([
                'success' => false,
                'message' => $this->errors[$response]
            ], 400));
        }
    }
}