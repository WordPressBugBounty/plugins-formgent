<?php

namespace FormGent\App\DTO\Mailchimp;

defined( "ABSPATH" ) || exit;

class QueueDTO extends \FormGent\WpMVC\DTO\DTO {
    private int $id;

    private int $mailchimp_feed_id;

    private int $response_id;

    private string $status;

    private string $message;

    /**
     * Get the value of id
     *
     * @return int
     */
    public function get_id(): int {
        return $this->id;
    }
    
    /**
     * Set the value of id
     *
     * @param int $id
     * @return self
     */
    public function set_id( int $id ): self {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get the value of mailchimp_feed_id
     *
     * @return int
     */
    public function get_mailchimp_feed_id(): int {
        return $this->mailchimp_feed_id;
    }
    
    /**
     * Set the value of mailchimp_feed_id
     *
     * @param int $mailchimp_feed_id
     * @return self
     */
    public function set_mailchimp_feed_id( int $mailchimp_feed_id ): self {
        $this->mailchimp_feed_id = $mailchimp_feed_id;
    
        return $this;
    }

    /**
     * Get the value of response_id
     *
     * @return int
     */
    public function get_response_id(): int {
        return $this->response_id;
    }
    
    /**
     * Set the value of response_id
     *
     * @param int $response_id
     * @return self
     */
    public function set_response_id( int $response_id ): self {
        $this->response_id = $response_id;
    
        return $this;
    }

    /**
     * Get the value of status
     *
     * @return string
     */
    public function get_status(): string {
        return $this->status;
    }
    
    /**
     * Set the value of status
     *
     * @param string $status
     * @return self
     */
    public function set_status( string $status ): self {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get the value of message
     *
     * @return string
     */
    public function get_message(): string {
        return $this->message;
    }
    
    /**
     * Set the value of message
     *
     * @param string $message
     * @return self
     */
    public function set_message( string $message ): self {
        $this->message = $message;
    
        return $this;
    }
}